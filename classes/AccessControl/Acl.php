<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Plugin\Announcements\AccessControl;

use ILIAS\Plugin\Announcements\AccessControl\Acl\Role;

/**
 * Class Acl
 * @package ILIAS\Plugin\Announcements\AccessControl
 * @author  Michael Jansen <mjansen@databay.de>
 */
interface Acl
{
	/**
	 * @param string $role
	 * @param string $resource
	 * @param string $privilege
	 * @return bool
	 */
	public function isAllowed(string $role, string $resource, string $privilege) : bool;

	/**
	 * @return Role[]
	 */
	public function getRoles() : array;
}