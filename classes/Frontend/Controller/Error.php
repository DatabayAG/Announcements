<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Plugin\Announcements\Frontend\Controller;

/**
 * Class Error
 * @package ILIAS\Plugin\Announcements\Frontend\Controller
 * @author  Michael Jansen <mjansen@databay.de>
 */
class Error extends Base
{
    /**
     * @inheritdoc
     */
    public function getDefaultCommand(): string
    {
        return 'showCmd';
    }

    /**
     * @return string
     */
    public function showCmd(): string
    {
        \ilUtil::sendFailure($this->getCoreController()->getPluginObject()->txt('controller_not_found'));

        return '';
    }
}