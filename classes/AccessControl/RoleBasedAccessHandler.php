<?php
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

	/**
	 * RoleBasedAccessHandler constructor.
	 * @param \ilObjUser    $actor
	 * @param \ilRbacReview $rbacReview
	 */
	public function __construct(
		\ilObjUser $actor,
		\ilRbacReview $rbacReview
	) {
		$this->actor = $actor;
		$this->rbacReview = $rbacReview;
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
		// TODO: Implement mayReadEntry() method.
		return !$this->isActorAnonymous();
	}

	/**
	 * @inheritDoc
	 */
	public function mayCreateEntries() : bool
	{
		// TODO: Implement mayCreateEntries() method.
		return !$this->isActorAnonymous();
	}

	/**
	 * @inheritDoc
	 */
	public function mayEditEntry(Model $entry) : bool
	{
		return (
			!$this->isActorAnonymous() &&
			(
				(int) $this->actor->getId() === (int) $entry->getCreatorUsrId()
				// TODO: Order Rolle darf
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
				(int) $this->actor->getId() === (int) $entry->getCreatorUsrId()
				// TODO: Order Rolle darf
			)
		);
	}

	/**
	 * @inheritDoc
	 */
	public function mayMakeStickyEntries() : bool
	{
		// TODO: Implement mayMakeStickyEntries() method.
		return !$this->isActorAnonymous();
	}

	/**
	 * @inheritDoc
	 */
	public function mayReadExpiredEntries() : bool
	{
		return !$this->isActorAnonymous();
	}

	/**
	 * @inheritDoc
	 */
	public function mayReadUnpublishedEntries() : bool
	{
		return !$this->isActorAnonymous();
	}
}