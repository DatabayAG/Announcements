<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Plugin\Announcements\Frontend\ViewModifier;

use ILIAS\DI\Container;
use ILIAS\Plugin\Announcements\AccessControl\AccessHandler;
use ILIAS\Plugin\Announcements\Entry\Service;
use ILIAS\Plugin\Announcements\Frontend\ViewModifier;
use ILIAS\Plugin\Announcements\Storage\KeyValue;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface ViewModifier
 * @package ILIAS\Plugin\Announcements\Frontend\ViewModifier
 * @author  Michael Jansen <mjansen@databay.de>
 */
abstract class Base implements ViewModifier
{
    /** @var ServerRequestInterface */
    protected $request;

    /** @var \ilTemplate */
    protected $tpl;

    /** @var Factory */
    protected $uiFactory;

    /** @var \ilCtrl */
    protected $ctrl;

    /** @var Renderer */
    protected $uiRenderer;

    /** @var Container */
    protected $dic;

    /** @var \ilToolbarGUI */
    protected $toolbar;

    /** @var \ilObjuser */
    protected $user;

    /** @var Service */
    protected $service;

    /** @var AccessHandler */
    protected $accessHandler;

    /** @var \ilAccessHandler */
    protected $coreAccessHandler;

    /** @var \ilErrorHandling */
    protected $errorHandler;

    /** @var \ilLanguage */
    protected $lng;

    /** @var \ilAnnouncementsUIHookGUI */
    public $coreController;

    /** @var \ilTemplate */
    protected $mainTemplate;

    /** @var KeyValue */
    protected $keyValueStore;

    /**
     * Base constructor.
     * @param \ilAnnouncementsUIHookGUI $controller
     * @param Container $dic
     */
    final public function __construct(\ilAnnouncementsUIHookGUI $controller, Container $dic)
    {
        $this->coreController = $controller;
        $this->dic = $dic;

        $this->request = $dic->http()->request();
        $this->mainTemplate = $dic->ui()->mainTemplate();
        $this->ctrl = $dic->ctrl();
        $this->lng = $dic->language();
        $this->tpl = $dic->ui()->mainTemplate();
        $this->user = $dic->user();
        $this->uiRenderer = $dic->ui()->renderer();
        $this->uiFactory = $dic->ui()->factory();
        $this->service = $dic['plugin.announcements.service'];
        $this->accessHandler = $dic['plugin.announcements.accessHandler'];
        $this->keyValueStore = $dic['plugin.announcements.kvstore'];
        $this->coreAccessHandler = $dic->access();
        $this->errorHandler = $dic['ilErr'];
    }

    /**
     * @return \ilAnnouncementsUIHookGUI
     */
    public function getCoreController() : \ilAnnouncementsUIHookGUI
    {
        return $this->coreController;
    }

    /**
     * @return Container
     */
    public function getDic() : Container
    {
        return $this->dic;
    }
}