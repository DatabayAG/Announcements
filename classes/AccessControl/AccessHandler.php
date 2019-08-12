<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Plugin\Announcements\AccessControl;

use ILIAS\Plugin\Announcements\Entry\Model;

/**
 * Interface AccessHandler
 * @package ILIAS\Plugin\Announcements\AccessControl
 * @author  Michael Jansen <mjansen@databay.de>
 */
interface AccessHandler
{
	/**
	 * @param \ilObjUser $actor
	 * @return self
	 */
	public function withActor(\ilObjUser $actor) : self;

	/** @return bool */
	public function mayReadEntries() : bool;

	/** @return bool */
	public function mayCreateEntries() : bool;

	/**
	 * @param Model $entry
	 * @return bool
	 */
	public function mayEditEntry(Model $entry) : bool;

	/**
	 * @param Model $entry
	 * @return bool
	 */
	public function mayDeleteEntry(Model $entry) : bool;

	/** @return bool */
	public function mayMakeStickyEntries() : bool;

	/** @return bool */
	public function mayReadExpiredEntries() : bool;

	/** @return bool */
	public function mayReadUnpublishedEntries() : bool;

    /** @return bool */
    public function isManager(): bool;
}