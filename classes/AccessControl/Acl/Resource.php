<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Plugin\Announcements\AccessControl\Acl;

/**
 * Interface Resource
 * @package ILIAS\Plugin\Announcements\AccessControl\Acl
 */
interface Resource
{
    /**
     * @return string
     */
    public function getResourceId() : string;
}