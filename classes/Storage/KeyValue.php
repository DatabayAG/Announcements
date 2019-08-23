<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Plugin\Announcements\Storage;

/**
 * Interface KeyValue
 * @package ILIAS\Plugin\Announcements\Storage
 */
interface KeyValue
{
    /**
     * @param string $key
     * @param mixed $value
     * @param int|\DateInterval|null $ttl
     * @throws \InvalidArgumentException
     */
    public function set(string $key, $value, $ttl = null);

    /**
     * Returns the item according to the passed key. The consuming code should ask whether or not an item exists.
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * Checks whether or not the storage contains a valid item
     * @param string $key
     * @return bool
     */
    public function has(string $key) : bool;
}