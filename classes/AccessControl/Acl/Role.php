<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Plugin\Announcements\AccessControl\Acl;

/**
 * Interface Role
 * @package ILIAS\Plugin\Announcements\AccessControl\Acl
 */
interface Role
{
	/**
	 * @return string
	 */
	public function getRoleId() : string;
}