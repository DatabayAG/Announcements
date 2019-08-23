<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Plugin\Announcements\Storage\KeyValue;

use ILIAS\Plugin\Announcements\Storage\KeyValue;

/**
 * Class Session
 * @package ILIAS\Plugin\Announcements\Storage\KeyValue
 */
class Session implements KeyValue
{
    const PSR16_RESERVED = '/\{|\}|\(|\)|\/|\\\\|\@|\:/u';

    /** @var string */
    private $sessionKey;
    
    /** @var int */
    private $defaultTtl = 60 * 60 * 24 * 7;

    /**
     * Session constructor.
     * @param string $sessionKey
     */
    public function __construct(string $sessionKey)
    {
        $this->sessionKey = $sessionKey;
    }

    /**
     * @return int
     */
    private function getTime() : int
    {
        return time();
    }

    /**
     * @param string $key
     */
    protected function validateKey(string $key)
    {
        if (preg_match(self::PSR16_RESERVED, $key, $matches) === 1) {
            throw new \InvalidArgumentException("Invalid character in key: {$matches[0]}");
        }
    }


    /**
     * @return array
     */
    private function getSession() : array
    {
        $session = \ilSession::get($this->sessionKey);
        if (!is_array($session)) {
            $session = [];
        }
        
        return $session;
    }
    

    /**
     * @inheritdoc
     */
    public function set(string $key, $value, $ttl = null)
    {
        $this->validateKey($key);

        $session = $this->getSession();

        if (is_int($ttl)) {
            $expiresAt = $this->getTime() + $ttl;
        } elseif ($ttl instanceof \DateInterval) {
            $expiresAt = \DateTime::createFromFormat('U', $this->getTime())->add($ttl)->getTimestamp();
        } elseif ($ttl === null) {
            $expiresAt = $this->getTime() + $this->defaultTtl;
        } else {
            throw new \InvalidArgumentException('Invalid TTL: ' . print_r($ttl, true));
        }

        $session[$key] = [
            'data' => serialize($value),
            'expires_at' => $expiresAt,
        ];

        \ilSession::set($this->sessionKey, $session);
    }

    /**
     * @inheritdoc
     */
    public function get(string $key, $default = null)
    {
        $session = $this->getSession();

        if (!isset($session[$key])) {
            return $default;
        }
        
        if (!isset($session[$key]['expires_at'])) {
            return $default;
        }

        if ($this->getTime() >= $session[$key]['expires_at'] === false) {
            return $default;
        }

        $data = $session[$key]['data'];
        if ($data === 'b:0;') {
            // For being able to determine an error during unserialization below and distinguish between error/false
            return false;
        }
        
        $value = unserialize($data);
        if ($value === false) {
            return $default;
        }

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function has(string $key) : bool
    {
        return $this->get($key, $this) !== $this;
    }
}