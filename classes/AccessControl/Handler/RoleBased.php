<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Plugin\Announcements\AccessControl\Handler;

use ILIAS\Plugin\Announcements\AccessControl\AccessHandler;
use ILIAS\Plugin\Announcements\AccessControl\Acl;
use ILIAS\Plugin\Announcements\Administration\GeneralSettings\Settings;
use ILIAS\Plugin\Announcements\Entry\Model;

/**
 * Class RoleBased
 * @package ILIAS\Plugin\Announcements\AccessControl\Handler
 * @author Michael Jansen <mjansen@databay.de>
 */
class RoleBased implements AccessHandler
{
    /** @var \ilObjUser */
    private $actor;

    /** @var \ilRbacReview */
    private $rbacReview;

    /** @var Acl */
    private $acl;

    /** @var Settings */
    private $settings;

    /** @var int[] */
    protected $assignedGlobalRoles = [];

    /**
     * RoleBasedAccessHandler constructor.
     * @param \ilObjUser    $actor
     * @param Settings      $settings
     * @param \ilRbacReview $rbacReview
     * @param Acl           $acl
     */
    public function __construct(
        \ilObjUser $actor,
        Settings $settings,
        \ilRbacReview $rbacReview,
        Acl $acl
    ) {
        $this->actor = $actor;
        $this->settings = $settings;
        $this->rbacReview = $rbacReview;
        $this->acl = $acl;

        $this->assignedGlobalRoles = $this->rbacReview->assignedGlobalRoles($this->actor->getId());
    }

    /**
     * @param \ilObjUser $actor
     * @return self
     */
    public function withActor(\ilObjUser $actor) : AccessHandler
    {
        $clone = clone $this;
        $clone->actor = $actor;
        $clone->assignedGlobalRoles = $this->rbacReview->assignedGlobalRoles($actor->getId());

        return $clone;
    }

    /**
     * @return bool
     */
    private function isActorAnonymous() : bool
    {
        return $this->actor->isAnonymous() || (int) $this->actor->getId() === 0;
    }

    /**
     * @inheritDoc
     */
    public function mayReadEntries() : bool
    {
        return !$this->isActorAnonymous() && $this->hasAccess('list', 'read');
    }

    /**
     * @inheritDoc
     */
    public function mayCreateEntries() : bool
    {
        return !$this->isActorAnonymous() && $this->hasAccess('entry', 'create');
    }

    /**
     * @inheritDoc
     */
    public function mayEditEntry(Model $entry) : bool
    {
        return (
            !$this->isActorAnonymous() &&
            (
                (int) $this->actor->getId() === (int) $entry->getCreatorUsrId() ||
                $this->hasAccess('entry', 'modify')
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function mayDeleteEntry(Model $entry) : bool
    {
        return (
            !$this->isActorAnonymous() &&
            (
                (int) $this->actor->getId() === (int) $entry->getCreatorUsrId() ||
                $this->hasAccess('entry', 'delete')
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function mayMakeStickyEntries() : bool
    {
        return !$this->isActorAnonymous() && $this->hasAccess('entry', 'makeSticky');
    }

    /**
     * @inheritDoc
     */
    public function mayReadExpiredEntries() : bool
    {
        return !$this->isActorAnonymous() && $this->hasAccess('list', 'readExpired');
    }

    /**
     * @inheritDoc
     */
    public function mayReadUnpublishedEntries() : bool
    {
        return !$this->isActorAnonymous() && $this->hasAccess('list', 'readUnpublished');
    }

    /**
     * @inheritDoc
     */
    public function mayMakeTemporaryUnlimitedEntries() : bool
    {
        return count(
            array_intersect(
                $this->settings->getAclRoleToGlobalRoleMappings()['manager'],
                $this->assignedGlobalRoles
            )
        ) > 0;
    }

    /**
     * @param string $resource
     * @param string $privilege
     * @return bool
     */
    private function hasAccess(string $resource, string $privilege) : bool
    {
        $hasAccess = false;

        foreach ($this->settings->getAclRoleToGlobalRoleMappings() as $aclRole => $globalRoleIds) {
            $roles = array_intersect($globalRoleIds, $this->assignedGlobalRoles);
            if (count($roles) > 0) {
                $hasAccess = $this->acl->isAllowed($aclRole, $resource, $privilege);
                if ($hasAccess) {
                    return $hasAccess;
                }
            }
        }

        return $hasAccess;
    }
}