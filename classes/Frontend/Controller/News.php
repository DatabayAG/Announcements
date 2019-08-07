<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Plugin\Announcements\Frontend\Controller;

use ILIAS\Plugin\Announcements\AccessControl\Exception\PermissionDenied;
use ILIAS\Plugin\Announcements\Frontend\Controller\GUI\NewsGUI;

/**
 * Class News
 * @package ILIAS\Plugin\Announcements\Frontend\Controller
 * @author  Ingmar Szmais <iszmais@databay.de>
 */
class News extends Base
{
    /**
     * @inheritDoc
     */
    protected function init()
    {
    }

    /**
     * @inheritdoc
     */
    public function getDefaultCommand() : string
    {
        return 'createCmd';
    }
    
    
    public function createCmd() : string
    {
        $gui = new NewsGUI($this->coreController->getPluginObject());
        $this->tpl->setTitle($this->lng->txt('news'));

        $action = $this->ctrl->getLinkTargetByClass(
            [\ilUIPluginRouterGUI::class, get_class($this->getCoreController())],
            'News.submit'
        );
        return $gui->renderCreateNews($action);
    }

    public function submitCmd() : string
    {
        $x = 0;
        return "AHAAA";
    }
}