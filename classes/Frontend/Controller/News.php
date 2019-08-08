<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Plugin\Announcements\Frontend\Controller;

use ILIAS\Plugin\Announcements\Entry\Model;
use ILIAS\Plugin\Announcements\Exception;
use ILIAS\Plugin\Announcements\Frontend\Controller\GUI\NewsGUI;

/**
 * Class News
 * @package ILIAS\Plugin\Announcements\Frontend\Controller
 * @author  Ingmar Szmais <iszmais@databay.de>
 */
class News extends Base
{

    /**
     * @var NewsGUI
     */
    protected $gui;

    /**
     * @inheritDoc
     */
    protected function init()
    {
        $this->gui = new NewsGUI($this->coreController->getPluginObject());
        $this->tpl->setTitle($this->lng->txt('news'));
    }

    /**
     * @inheritdoc
     */
    public function getDefaultCommand() : string
    {
        return 'createCmd';
    }


    /**
     * @return string
     */
    public function createCmd() : string
    {
        $action = $this->ctrl->getLinkTargetByClass(
            [\ilUIPluginRouterGUI::class, get_class($this->getCoreController())],
            'News.submit'
        );

        return $this->gui->initForm($action)->getHTML();
    }

    /**
     * @return string
     */
    public function updateCmd() : string
    {
        $action = $this->ctrl->getLinkTargetByClass(
            [\ilUIPluginRouterGUI::class, get_class($this->getCoreController())],
            'News.submit'
        );

        $id = (int) ($this->request->getQueryParams()['id'] ?? 0);
        $model = $this->service->findById($id);

        return $this->gui->initForm($action, $model)->getHTML();
    }

    /**
     * @return string
     * @throws \ilDateTimeException
     */
    public function submitCmd() : string
    {
        $action = $this->ctrl->getLinkTargetByClass(
            [\ilUIPluginRouterGUI::class, get_class($this->getCoreController())],
            'News.submit'
        );

        $form = $this->gui->initForm($action);
        if ($form->checkInput()) {
            $content = [];
            try {
                if ($form->getInput('id')) {
                    $model = new Model($form->getInput('id'));
                } else {
                    $model = new Model();
                }

                $model->setTitle($form->getInput('title'));
                $model->setContent($form->getInput('content'));
                if($form->getInput('publish_date')){
                    $date = new \DateTime($form->getInput('publish_date'));
                    $model->setPublishTs($date->getTimestamp());
                }
                $model->setPublishTimezone($this->user->getTimeZone());
                if($form->getInput('expiration_date')) {
                    $date = new \DateTime($form->getInput('expiration_date'));
                    $model->setExpirationhTs($date->getTimestamp());
                }
                $model->setExpirationTimezone($this->user->getTimeZone());
                $model->setFixed($form->getInput('fixed'));
                $model->setCategory($form->getInput('category'));

                if($model->getId()){
                    $this->service->modifyEntry($model);
                }else{
                    $this->service->createEntry($model);
                }

                $this->ctrl->setParameter($this, 'saved', 1);
                $this->ctrl->redirectToURL('ilias.php?baseClass=ilPersonalDesktopGUI&saved=1');
            } catch (Exception $e) {
                $content[] = $this->uiRenderer->render(
                    $this->uiFactory->messageBox()->failure($this->lng->txt('form_input_not_valid'))
                );
            }
        }

        $form->setValuesByPost();
        $content[] = $this->uiFactory->legacy($form->getHtml());

        return $this->uiRenderer->render($content);
    }}