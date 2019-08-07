<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Plugin\Announcements\Frontend;

use ILIAS\DI\Container;
use ILIAS\Plugin\Announcements\Frontend\Controller\Base;

/**
 * Class Dispatcher
 * @package ILIAS\Plugin\Announcements\Frontend
 * @author  Michael Jansen <mjansen@databay.de>
 */
class Dispatcher
{
    /**
     * @var self
     */
    private static $instance = null;

    /** @var \ilAnnouncementsUIHookGUI */
    private $coreController;

    /** @var string */
    private $defaultController = '';

    /** @var Container */
    private $dic;

    /**
     * Dispatcher constructor.
     * @param \ilAnnouncementsUIHookGUI $baseController
     * @param string                  $defaultController
     */
    private function __construct(\ilAnnouncementsUIHookGUI $baseController, string $defaultController = '')
    {
        $this->coreController    = $baseController;
        $this->defaultController = $defaultController;
    }

    /**
     * @param \ilAnnouncementsUIHookGUI $baseController
     * @return self
     */
    public static function getInstance(\ilAnnouncementsUIHookGUI $baseController)
    {
        if (self::$instance === null) {
            self::$instance = new self($baseController);
        }

        return self::$instance;
    }

    /**
     *
     */
    private function __clone()
    {
    }

    /**
     * @param string $controller
     */
    protected function requireController(string $controller)
    {
        require_once $this->getControllerPath() . $controller . '.php';
    }

    /**
     * @return string
     */
    protected function getControllerPath(): string
    {
        $path = $this->getCoreController()->getPluginObject()->getDirectory() .
            DIRECTORY_SEPARATOR .
            'classes' .
            DIRECTORY_SEPARATOR .
            'Frontend' .
            DIRECTORY_SEPARATOR .
            'Controller' .
            DIRECTORY_SEPARATOR;

        return $path;
    }

    /**
     * @param Container $dic
     */
    public function setDic(Container $dic)
    {
        $this->dic = $dic;
    }

    /**
     * @param string $cmd
     * @return string
     */
    public function dispatch(string $cmd): string
    {
        $controller = $this->getController($cmd);
        $command    = $this->getCommand($cmd);
        $controller = $this->instantiateController($controller);

        return $controller->$command();
    }

    /**
     * @param string $cmd
     * @return string
     */
    protected function getController(string $cmd): string
    {
        $parts = explode('.', $cmd);

        if (count($parts) >= 1) {
            return $parts[0];
        }

        return $this->defaultController ? $this->defaultController : 'Error';
    }

    /**
     * @param string $cmd
     * @return string
     */
    protected function getCommand(string $cmd): string
    {
        $parts = explode('.', $cmd);

        if (count($parts) === 2) {
            $cmd = $parts[1];

            return $cmd . 'Cmd';
        }

        return '';
    }

    /**
     * @param string $controller
     * @return Base
     */
    protected function instantiateController(string $controller): Base
    {
        $class = "ILIAS\\Plugin\\Announcements\\Frontend\\Controller\\$controller";

        return new $class($this->getCoreController(), $this->dic);
    }

    /**
     * @return \ilAnnouncementsUIHookGUI
     */
    public function getCoreController(): \ilAnnouncementsUIHookGUI
    {
        return $this->coreController;
    }

    /**
     * @param \ilAnnouncementsUIHookGUI $coreController
     */
    public function setCoreController(\ilAnnouncementsUIHookGUI $coreController)
    {
        $this->coreController = $coreController;
    }
}