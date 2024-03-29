<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Plugin\Announcements\Administration\GeneralSettings\UI;

use ILIAS\Plugin\Announcements\UI\Form\Bindable;
use ILIAS\Plugin\Announcements\AccessControl\Acl;

/**
 * Class Form
 * @package ILIAS\Plugin\Announcements\Administration\GeneralSettings\UI
 * @author  Michael Jansen <mjansen@databay.de>
 */
class Form extends \ilPropertyFormGUI
{
    /** @var \ilAnnouncementsPlugin */
    private $plugin;

    /** @var object */
    private $cmdObject;

    /** @var Bindable */
    private $generalSettings;

    /** @var \ilObjectDataCache */
    private $objectCache;

    /** @var \ilRbacReview */
    protected $rbacReview;

    /** @var Acl */
    private $acl;

    /**
     * Form constructor.
     * @param \ilAnnouncementsPlugin $plugin
     * @param object                 $cmdObject
     * @param Bindable               $generalSettings
     * @param \ilObjectDataCache     $objectCache
     * @param \ilRbacReview          $rbacReview
     * @param Acl                    $acl
     */
    public function __construct(
        \ilAnnouncementsPlugin $plugin,
        $cmdObject,
        Bindable $generalSettings,
        \ilObjectDataCache $objectCache,
        \ilRbacReview $rbacReview,
        Acl $acl
    ) {
        $this->plugin = $plugin;
        $this->cmdObject = $cmdObject;
        $this->generalSettings = $generalSettings;
        $this->objectCache = $objectCache;
        $this->rbacReview = $rbacReview;
        $this->acl = $acl;
        parent::__construct();

        $this->initForm();
    }

    /**
     *
     */
    protected function initForm()
    {
        $this->setFormAction($this->ctrl->getFormAction($this->cmdObject, 'saveSettings'));
        $this->setTitle($this->lng->txt('settings'));

        $roles = [];
        foreach ($this->rbacReview->getGlobalRoles() as $roleId) {
            if ($roleId !== ANONYMOUS_ROLE_ID ) {
                $roles[$roleId] = $this->objectCache->lookupTitle($roleId);
            }
        }
        asort($roles);

        foreach ($this->acl->getRoles() as $role) {
            $roleMapping = new \ilMultiSelectInputGUI(
                $this->plugin->txt('acl_role_' . $role->getRoleId()),
                'role_mapping_' . $role->getRoleId()
            );
            $roleMapping->setInfo($this->plugin->txt('acl_role_mapping_please_assign_groles'));
            $roleMapping->setOptions($roles);
            $this->addItem($roleMapping);
        }

        $rssSection = new \ilFormSectionHeaderGUI();
        $rssSection->setTitle($this->plugin->txt('adm_form_head_rss'));
        $this->addItem($rssSection);

        $channelTitle = new \ilTextInputGUI($this->plugin->txt('adm_form_lbl_channel_title'), 'rss_channel_title');
        $channelTitle->setRequired(true);
        $channelTitle->setInfo($this->plugin->txt('adm_form_lbl_channel_title_info'));
        $this->addItem($channelTitle);

        $channelDescription = new \ilTextInputGUI($this->plugin->txt('adm_form_lbl_channel_desc'), 'rss_channel_desc');
        $channelDescription->setRequired(true);
        $channelDescription->setInfo($this->plugin->txt('adm_form_lbl_channel_desc_info'));
        $this->addItem($channelDescription);

        $this->addCommandButton('saveSettings', $this->lng->txt('save'));

        $this->setValuesByArray($this->generalSettings->toArray());
    }

    /**
     * @inheritdoc
     */
    public function checkInput()
    {
        $bool = parent::checkInput();
        if (!$bool) {
            return $bool;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function saveObject()
    {
        if (!$this->fillObject()) {
            $this->setValuesByPost();
            return false;
        }

        try {
            $this->generalSettings->bindForm($this);
            $this->generalSettings->onFormSaved();
            return true;
        } catch (\ilException $e) {
            \ilUtil::sendFailure($this->plugin->txt($e->getMessage()));
            $this->setValuesByPost();
            return false;
        }
    }

    /**
     *
     */
    protected function fillObject()
    {
        if (!$this->checkInput()) {
            return false;
        }

        $success = true;

        try {
            $this->setValuesByArray(
                $this->generalSettings->toArray()
            );
        } catch (\ilException $e) {
            \ilUtil::sendFailure($e->getMessage());
            $success = false;
        }

        return $success;
    }
}