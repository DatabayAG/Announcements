<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Plugin\Announcements\AccessControl\Handler;

use ILIAS\Plugin\Announcements\AccessControl;
use ILIAS\Plugin\Announcements\Entry\Model;

/**
 * Class Cached
 * @package ILIAS\Plugin\Announcements\AccessControl\Handler
 * @author  Michael Jansen <mjansen@databay.de>
 */
class Cached implements AccessControl\AccessHandler
{
    /** @var AccessControl\AccessHandler */
    private $origin;

    /** @var array */
    private $cache = [];

    /**
     * Cached constructor.
     * @param AccessControl\AccessHandler $origin
     */
    public function __construct(
        AccessControl\AccessHandler $origin
    ) {
        $this->origin = $origin;
    }

    /**
     * @inheritDoc
     */
    public function withActor(\ilObjUser $actor) : AccessControl\AccessHandler
    {
        $clone = clone $this;
        $clone->origin = $clone->origin->withActor($actor);
        $clone->cache = [];

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function mayReadEntries() : bool
    {
        if (isset($this->cache[__METHOD__])) {
            return $this->cache[__METHOD__];
        }

        return ($this->cache[__METHOD__] = $this->origin->mayReadEntries());
    }

    /**
     * @inheritDoc
     */
    public function mayCreateEntries() : bool
    {
        if (isset($this->cache[__METHOD__])) {
            return $this->cache[__METHOD__];
        }

        return ($this->cache[__METHOD__] = $this->origin->mayCreateEntries());
    }

    /**
     * @inheritDoc
     */
    public function mayEditEntry(Model $entry) : bool
    {
        if (isset($this->cache[__METHOD__])) {
            return $this->cache[__METHOD__];
        }

        return ($this->cache[__METHOD__] = $this->origin->mayEditEntry($entry));
    }

    /**
     * @inheritDoc
     */
    public function mayDeleteEntry(Model $entry) : bool
    {
        if (isset($this->cache[__METHOD__])) {
            return $this->cache[__METHOD__];
        }

        return ($this->cache[__METHOD__] = $this->origin->mayDeleteEntry($entry));
    }

    /**
     * @inheritDoc
     */
    public function mayMakeStickyEntries() : bool
    {
        if (isset($this->cache[__METHOD__])) {
            return $this->cache[__METHOD__];
        }

        return ($this->cache[__METHOD__] = $this->origin->mayMakeStickyEntries());
    }

    /**
     * @inheritDoc
     */
    public function mayReadExpiredEntries() : bool
    {
        if (isset($this->cache[__METHOD__])) {
            return $this->cache[__METHOD__];
        }

        return ($this->cache[__METHOD__] = $this->origin->mayReadExpiredEntries());
    }

    /**
     * @inheritDoc
     */
    public function mayReadUnpublishedEntries() : bool
    {
        if (isset($this->cache[__METHOD__])) {
            return $this->cache[__METHOD__];
        }

        return ($this->cache[__METHOD__] = $this->origin->mayReadUnpublishedEntries());
    }

    /**
     * @inheritDoc
     */
    public function isManager() : bool
    {
        if (isset($this->cache[__METHOD__])) {
            return $this->cache[__METHOD__];
        }

        return ($this->cache[__METHOD__] = $this->origin->isManager());
    }
}