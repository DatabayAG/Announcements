<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Plugin\Announcements\Frontend\Controller;

use ILIAS\DI\Container;
use ILIAS\Plugin\Announcements\AccessControl\AccessHandler;
use ILIAS\Plugin\Announcements\Entry\Service;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class Base
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
        $this->ctrl = $dic->ctrl();
        $this->lng = $dic->language();
        $this->tpl = $dic->ui()->mainTemplate();
        $this->user = $dic->user();
        $this->uiRenderer = $dic->ui()->renderer();
        $this->uiFactory = $dic->ui()->factory();
        $this->toolbar = $dic->toolbar();
        $this->service = $dic['plugin.announcements.service'];
        $this->accessHandler = $dic['plugin.announcements.accessHandler'];
        $this->coreAccessHandler = $dic->access();
        $this->errorHandler = $dic['ilErr'];

        $this->init();
    }

    /**
     *
     */
    protected function init()
    {
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    final public function __call(string $name, array $arguments)
    {
        return call_user_func_array([$this, $this->getDefaultCommand()], []);
    }

    /**
     * @return string
     */
    abstract public function getDefaultCommand() : string;

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

    /**
     * @return string
     * @throws \ReflectionException
     */
    final public function getControllerName() : string
    {
        return (new \ReflectionClass($this))->getShortName();
    }
}