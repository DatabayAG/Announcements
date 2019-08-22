<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Plugin\Announcements\Frontend\Controller;

use ILIAS\Plugin\Announcements\AccessControl\Exception\PermissionDenied;
use ILIAS\Plugin\Announcements\AccessControl\Exception\PermissionRestricted;
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
    /** @var NewsGUI */
    protected $gui;

    /** @var string */
    protected $action = '';

    /**
     * @inheritDoc
     */
    protected function init()
    {
        if (!$this->accessHandler->mayReadEntries()) {
            \ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
            $this->ctrl->redirectToURL('ilias.php?baseClass=ilPersonalDesktopGUI');
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
        if (!$this->accessHandler->mayCreateEntries()) {
            \ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
            $this->ctrl->redirectToURL('ilias.php?baseClass=ilPersonalDesktopGUI');
        }
        
        return $this->gui->initForm(
            $this->accessHandler->mayMakeTemporaryUnlimitedEntries(),
            $this->action
        )->getHTML();
    }

    /**
     * @return string
     * @throws \ilDateTimeException
     */
    public function updateCmd() : string
    {
        $id = (int) ($this->request->getQueryParams()['id'] ?? 0);
        $model = $this->service->findById($id);

        if (!$this->accessHandler->mayEditEntry($model)) {
            \ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
            $this->ctrl->redirectToURL('ilias.php?baseClass=ilPersonalDesktopGUI');
        }

        return $this->gui->initForm(
            $this->accessHandler->mayMakeTemporaryUnlimitedEntries(),
            $this->action, $model
        )->getHTML();
    }

    /**
     * @return string
     * @throws PermissionDenied
     */
    public function deleteCmd() : string
    {
        $id = (int) ($this->request->getQueryParams()['id'] ?? 0);
        $model = $this->service->findById($id);

        if (!$this->accessHandler->mayDeleteEntry($model)) {
            \ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
            $this->ctrl->redirectToURL('ilias.php?baseClass=ilPersonalDesktopGUI');
        }

        $this->service->deleteEntry($model);

        \ilUtil::sendSuccess($this->coreController->getPluginObject()->txt('deleted_successfully'), true);
        $this->ctrl->redirectToURL('ilias.php?baseClass=ilPersonalDesktopGUI&deleted=1');
    }

    /**
     * @return string
     * @throws \ilDateTimeException
     */
    public function submitCmd() : string
    {
        if (isset($this->request->getParsedBody()['cmd']['cancel'])) {
            $this->ctrl->redirectToURL('ilias.php?baseClass=ilPersonalDesktopGUI');
        }

        if ($this->request->getParsedBody()['id']) {
            $model = $this->service->findById((int) $this->request->getParsedBody()['id']);

            if (!$this->accessHandler->mayEditEntry($model)) {
                \ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
                $this->ctrl->redirectToURL('ilias.php?baseClass=ilPersonalDesktopGUI');
            }

            $form = $this->gui->initForm(
                $this->accessHandler->mayMakeTemporaryUnlimitedEntries(),
                $this->action, $model
            );
        } else {
            if (!$this->accessHandler->mayCreateEntries()) {
                \ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
                $this->ctrl->redirectToURL('ilias.php?baseClass=ilPersonalDesktopGUI');
            }

            $model = new Model();
            $form = $this->gui->initForm(
                $this->accessHandler->mayMakeTemporaryUnlimitedEntries(),
                $this->action
            );
        }

        if ($form->checkInput()) {
            try {
                $model->setTitle($form->getInput('title'));
                $model->setContent($form->getInput('content'));
                if ($form->getInput('publish_date')) {
                    $date = new \DateTime($form->getInput('publish_date'));
                    $model->setPublishTs($date->getTimestamp());
                }
                $model->setPublishTimezone($this->user->getTimeZone());
                if ($form->getInput('expiration_date')) {
                    $date = new \DateTime($form->getInput('expiration_date'));
                    $model->setExpirationTs($date->getTimestamp());
                }
                $model->setExpirationTimezone($this->user->getTimeZone());
                $model->setFixed($form->getInput('fixed'));
                $model->setCategory($form->getInput('category'));

                try {
                    if ($model->getId()) {
                        $this->service->modifyEntry($model);
                    } else {
                        $this->service->createEntry($model);
                    }

                    \ilUtil::sendSuccess($this->coreController->getPluginObject()->txt('saved_successfully'), true);
                    $this->ctrl->redirectToURL('ilias.php?baseClass=ilPersonalDesktopGUI');
                } catch (PermissionRestricted $e) {
                    $item = $form->getItemByPostVar('expiration_date');
                    $item->setAlert($this->coreController->getPluginObject()->txt('form_msg_invalid_date_range'));
                }
            } catch (Exception $e) {
                $content[] = $this->uiFactory->messageBox()->failure($this->lng->txt('form_input_not_valid'));
            }
        }

        $form->setValuesByPost();

        $content[] = $this->uiFactory->legacy($form->getHtml());

        return $this->uiRenderer->render($content);
    }
}