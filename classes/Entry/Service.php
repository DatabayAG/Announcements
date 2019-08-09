<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Plugin\Announcements\Entry;

use ILIAS\Plugin\Announcements\AccessControl\AccessHandler;
use ILIAS\Plugin\Announcements\AccessControl\Exception\PermissionDenied;
use ILIAS\Plugin\Announcements\Entry\Exception\NotFound;
use ILIAS\Plugin\Announcements\Entry\Exception\CommandLogic;

/**
 * Class Service
 * @package ILIAS\Plugin\Announcements\Entry
 * @author Michael Jansen <mjansen@databay.de>
 */
class Service
{
    /** @var AccessHandler */
    private $accessHandler;

    /** @var \ilDBInterface */
    private $db;

    /** @var \ilObjUser */
    private $actor;

    /**
     * Service constructor.
     * @param \ilObjUser $actor
     * @param \ilDBInterface $db
     * @param AccessHandler $accessHandler
     */
    public function __construct(
        \ilObjUser $actor,
        \ilDBInterface $db,
        AccessHandler $accessHandler

    ) {
        $this->actor = $actor;
        $this->db = $db;
        $this->accessHandler = $accessHandler;
    }

    /**
     * @param \ilObjUser $actor
     * @return Service
     */
    public function withActor(\ilObjUser $actor) : self
    {
        $clone = clone $this;
        $clone->actor = $actor;
        $clone->accessHandler = $this->accessHandler->withActor($actor);

        return $clone;
    }

    /**
     * @param Model $entry
     * @throws PermissionDenied
     * @throws CommandLogic
     */
    public function createEntry(Model $entry)
    {
        if (!$this->accessHandler->mayCreateEntries()) {
            throw new PermissionDenied('No permission to create entry!');
        }

        if ($entry->getId()) {
            throw new CommandLogic('An entry with id cannot be created!');
        }

        $entry->setCreatedTs(time());
        $entry->setCreatorUsrId($this->actor->getId());

        $entry->store();
    }

    /**
     * @param Model $entry
     * @throws PermissionDenied
     * @throws CommandLogic
     * @throws NotFound
     */
    public function modifyEntry(Model $entry)
    {
        if (!$this->accessHandler->mayEditEntry($entry)) {
            throw new PermissionDenied('No permission to edit entry!');
        }

        if (!$entry->getId()) {
            throw new CommandLogic('An entry without id cannot be modified!');
        }

        try {
            $entry::findOrFail($entry->getId());
        } catch (\arException $e) {
            throw new NotFound($e->getMessage());
        }

        $entry->setLastModifiedTs(time());
        $entry->setLastModifierUsrId($this->actor->getId());
        $entry->store();
    }

    /**
     * @param Model $entry
     * @throws PermissionDenied
     * @throws CommandLogic
     * @throws NotFound
     */
    public function deleteEntry(Model $entry)
    {
        if (!$this->accessHandler->mayDeleteEntry($entry)) {
            throw new PermissionDenied('No permission to delete entry!');
        }

        if (!$entry->getId()) {
            throw new CommandLogic('An entry without id cannot be deleted!');
        }

        try {
            $entry::findOrFail($entry->getId());
        } catch (\arException $e) {
            throw new NotFound($e->getMessage());
        }

        $entry->delete();
    }

    /**
     * @param Model $entry
     * @throws PermissionDenied
     * @throws CommandLogic
     * @throws NotFound
     */
    public function makeEntrySticky(Model $entry)
    {
        if (!$this->accessHandler->mayMakeStickyEntries()) {
            throw new PermissionDenied('No permission to make entry sticky!');
        }

        if (!$entry->getId()) {
            throw new CommandLogic('An entry without id cannot be deleted!');
        }

        try {
            $entry::findOrFail($entry->getId());
        } catch (\arException $e) {
            throw new NotFound($e->getMessage());
        }

        $entry->setIsSticky(true);
        $entry->store();
    }

    /**
     * @param Model $entry
     * @throws PermissionDenied
     * @throws CommandLogic
     * @throws NotFound
     */
    public function makeEntryLoose(Model $entry)
    {
        if (!$this->accessHandler->mayMakeStickyEntries()) {
            throw new PermissionDenied('No permission to make entry loose!');
        }

        if (!$entry->getId()) {
            throw new CommandLogic('An entry without id cannot be deleted!');
        }

        try {
            $entry::findOrFail($entry->getId());
        } catch (\arException $e) {
            throw new NotFound($e->getMessage());
        }

        $entry->setIsSticky(false);
        $entry->store();
    }

    /**
     * @return string
     * @throws PermissionDenied
     */
    public function getAuthenticationToken() : string 
    {
        if (!$this->accessHandler->mayReadEntries()) {
            throw new PermissionDenied('No permission to read entries!');
        }
        
        return $this->actor::_lookupFeedHash($this->actor->getId(), true);
    }

    /**
     * @param bool $onlyRoomChangeRelated
     * @return Model[]
     * @throws PermissionDenied
     * @throws \arException
     */
    public function findAllValid($onlyRoomChangeRelated = false) : array
    {
        if (!$this->accessHandler->mayReadEntries()) {
            throw new PermissionDenied('No permission to read entries!');
        }

        $effectiveCondition = [];
        $runtimeConditions = [];

        if (!$this->accessHandler->mayReadUnpublishedEntries()) {
            $runtimeConditions[] = '(' . implode(' OR ', [
                'publish_ts <= ' . $this->db->quote(time(), 'integer'),
                'creator_usr_id = ' . $this->db->quote($this->actor->getId(), 'integer'),
            ]) . ')';
        }

        if (!$this->accessHandler->mayReadExpiredEntries()) {
            $runtimeConditions[] = '(' . implode(' OR ', [
                'expiration_ts >= ' . $this->db->quote(time(), 'integer'),
                'creator_usr_id = ' . $this->db->quote($this->actor->getId(), 'integer'),
            ]) . ')';
        }

        if ($onlyRoomChangeRelated) {
            $runtimeConditions[] = '(' . implode(' OR ', [
                'category = ' . $this->db->quote(1, 'integer'),
            ]) . ')';
        }

        if (count($runtimeConditions) > 0) {
            $effectiveCondition = implode(' AND ', $runtimeConditions);
        }

        $list = Model::where($effectiveCondition)->orderBy('fixed', 'DESC')->orderBy('publish_ts', 'DESC');

        $list->get();
        return $list->get();
    }

    /**
     * @param int $id
     * @return Model
     */
    public function findById(int $id) : Model
    {

        $list = Model::where('id = '.$this->db->quote($id, 'integer'));

        $x = $list->first();
        return $list->first();
    }

}