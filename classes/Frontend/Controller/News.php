<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Plugin\Announcements\Frontend\Controller;

use ILIAS\Plugin\Announcements\AccessControl\Exception\PermissionDenied;
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
     * @var string
     */
    protected $action;

    /**
     * @inheritDoc
     */
    protected function init()
    {
        if (!$this->accessHandler->mayReadEntries()) {
            throw new PermissionDenied('No permission to read entries!');
        }

        $this->gui = new NewsGUI($this->coreController->getPluginObject());
        $this->tpl->setTitle($this->lng->txt('news'));
        $this->action = $this->ctrl->getLinkTargetByClass(
            [\ilUIPluginRouterGUI::class, get_class($this->getCoreController())],
            'News.submit'
        );
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
     * @throws \ilDateTimeException
     */
    public function createCmd() : string
    {
        return $this->gui->initForm($this->accessHandler->isManager(), $this->action)->getHTML();
    }

    /**
     * @return string
     * @throws \ilDateTimeException
     */
    public function updateCmd() : string
    {
        $id = (int) ($this->request->getQueryParams()['id'] ?? 0);
        $model = $this->service->findById($id);

        return $this->gui->initForm($this->accessHandler->isManager(), $this->action, $model)->getHTML();
    }

    /**
     * @return string
     * @throws PermissionDenied
     */
    public function deleteCmd() : string
    {
        $id = (int) ($this->request->getQueryParams()['id'] ?? 0);
        $model = $this->service->findById($id);

        try{
            $this->service->deleteEntry($model);
        }catch (PermissionDenied $e){
            $this->ctrl->redirectToURL('ilias.php?baseClass=ilPersonalDesktopGUI&failed=1');
        }

        $this->ctrl->redirectToURL('ilias.php?baseClass=ilPersonalDesktopGUI&deleted=1');
    }

    /**
     * @return string
     * @throws \ilDateTimeException
     */
    public function submitCmd() : string
    {
        if(isset($this->request->getParsedBody()['cmd']['cancel'])){
            $this->ctrl->redirectToURL('ilias.php?baseClass=ilPersonalDesktopGUI');
        }

        $form = $this->gui->initForm($this->accessHandler->isManager(), $this->action);
        if ($form->checkInput()) {

            if ($form->getInput('id')) {
                $model = $this->service->findById((int) $form->getInput('id'));
            } else {
                $model = new Model();
            }
            try {
                $model->setTitle($form->getInput('title'));
                $model->setContent($form->getInput('content'));
                if($form->getInput('publish_date')){
                    $date = new \DateTime($form->getInput('publish_date'));
                    $model->setPublishTs($date->getTimestamp());
                }
                $model->setPublishTimezone($this->user->getTimeZone());
                if($form->getInput('expiration_date')) {
                    $date = new \DateTime($form->getInput('expiration_date'));
                    $model->setExpirationTs($date->getTimestamp());
                }
                $model->setExpirationTimezone($this->user->getTimeZone());
                $model->setFixed($form->getInput('fixed'));
                $model->setCategory($form->getInput('category'));

                if(!$this->checkDateLimitation($model)){
                    $item = $form->getItemByPostVar('expiration_date');
                    $item->setAlert($this->coreController->getPluginObject()->txt('form_msg_invalid_date_range'));
                    throw new PermissionDenied('Invalid date Range');
                }
                try{
                    if($model->getId()){
                        $this->service->modifyEntry($model);
                    }else{
                        $this->service->createEntry($model);
                    }
                }catch (PermissionDenied $e){
                    $this->ctrl->redirectToURL('ilias.php?baseClass=ilPersonalDesktopGUI&failed=1');
                }

                $this->ctrl->redirectToURL('ilias.php?baseClass=ilPersonalDesktopGUI&saved=1');
            } catch (Exception $e) {
                $content[] = $this->uiFactory->messageBox()->failure($this->lng->txt('form_input_not_valid'));
            }
        }

        $form->setValuesByPost();
        $content[] = $this->uiFactory->legacy($form->getHtml());

        return $this->uiRenderer->render($content);
    }

    private function checkDateLimitation(Model $model) : bool
    {
        if(!$this->accessHandler->isManager()){
            return $model->getPublishTs() <= $model->getExpirationTs() && $model->getPublishTs() + (60*60*24*21) >= $model->getExpirationTs();
        }
        return true;
    }
}