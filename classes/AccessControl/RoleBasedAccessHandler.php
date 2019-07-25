<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Plugin\Announcements\AccessControl;

use ILIAS\Plugin\Announcements\Entry\Model;

/**
 * Class RoleBasedAccessHandler
 * @package ILIAS\Plugin\Announcements\AccessControl
 * @author Michael Jansen <mjansen@databay.de>
 */
class RoleBasedAccessHandler implements AccessHandler
{
	/** @var \ilObjUser */
	private $actor;

	/** @var \ilRbacReview */
	private $rbacReview;

	/** @var Acl */
	private $acl;
	
	/** @var string */
	private $aclRole = '';

	/**
	 * RoleBasedAccessHandler constructor.
	 * @param \ilObjUser    $actor
	 * @param \ilRbacReview $rbacReview
	 * @param Acl           $acl
	 */
	public function __construct(
		\ilObjUser $actor,
		\ilRbacReview $rbacReview,
		Acl $acl
	) {
		$this->actor = $actor;
		$this->rbacReview = $rbacReview;
		$this->acl = $acl;

		// TODO: Determine reader, creator, or manager
		$this->aclRole = 'reader';
	}

	/**
	 * @param \ilObjUser $actor
	 * @return self
	 */
	public function withActor(\ilObjUser $actor) : AccessHandler
	{
		$clone = clone $this;
		$clone->actor = $actor;

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
		return !$this->isActorAnonymous() && $this->acl->isAllowed($this->aclRole, 'list', 'read');
	}

	/**
	 * @inheritDoc
	 */
	public function mayCreateEntries() : bool
	{
		return !$this->isActorAnonymous() && $this->acl->isAllowed($this->aclRole, 'entry', 'create');
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
				$this->acl->isAllowed($this->aclRole, 'entry', 'modify')
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
				$this->acl->isAllowed($this->aclRole, 'entry', 'delete')
			)
		);
	}

	/**
	 * @inheritDoc
	 */
	public function mayMakeStickyEntries() : bool
	{
		return !$this->isActorAnonymous() && $this->acl->isAllowed($this->aclRole, 'entry', 'makeSticky');
	}

	/**
	 * @inheritDoc
	 */
	public function mayReadExpiredEntries() : bool
	{
		return !$this->isActorAnonymous() && $this->acl->isAllowed($this->aclRole, 'list', 'readExpired');
	}

	/**
	 * @inheritDoc
	 */
	public function mayReadUnpublishedEntries() : bool
	{
		return !$this->isActorAnonymous() && $this->acl->isAllowed($this->aclRole, 'list', 'readUnpublished');
	}
}