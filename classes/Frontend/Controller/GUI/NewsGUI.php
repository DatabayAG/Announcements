<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */
namespace ILIAS\Plugin\Announcements\Frontend\Controller\GUI;

use ILIAS\Plugin\Announcements\Entry\Model;
use ILIAS\Plugin\Announcements\Entry\Purifier;

/**
 * Class News
 * @package ILIAS\Plugin\Announcements\Frontend\Controller
 * @author  Ingmar Szmais <iszmais@databay.de>
 */
class NewsGUI extends \ilPropertyFormGUI
{
    /**
     * @var \ilObjUser
     */
    protected $user;

    /**
     * @var \ilPlugin
     */
    protected $plugin;

    /**
     * NewsGUI constructor.
     * @param \ilPlugin $plugin
     */
    public function __construct(\ilPlugin $plugin)
    {
        global $DIC;
        $this->user = $DIC->user();
        $this->plugin = $plugin;

        parent::__construct();
    }

    /**
     * @param bool $unlimitedDateRange
     * @param string $action
     * @param Model|null $model
     * @return \ilPropertyFormGUI
     * @throws \ilDateTimeException
     */
    public function initForm(bool $unlimitedDateRange, string $action, Model $model = null) : \ilPropertyFormGUI
    {
        $this->setTitle($this->translate('create_news'));
        $this->setFormAction($action);

        if ($model) {
            $this->setTitle($this->translate('update_news'));

            $input = new \ilHiddenInputGUI('id');
            $input->setValue($model->getId());
            $this->addItem($input);
        }

        $input = new \ilTextInputGUI($this->translate('title'), 'title');
        $input->setInfo($this->translate('news_title_info'));
        if ($model && $model->getTitle()) {
            $input->setValue($model->getTitle());
        }
        $input->setRequired(true);
        $this->addItem($input);

        $input = new \ilTextAreaInputGUI($this->translate('content'), 'content');
        $input->setRows(3);
        $input->setUseRte(true);
        $input->setRteTagSet('standard');
        if (\ilObjAdvancedEditing::_getRichTextEditor()) {
            $input->usePurifier(true);
            $input->setPurifier(new Purifier());
        }
        $input->setInfo($this->translate('news_content_info'));
        if ($model && $model->getContent()) {
            $input->setValue($model->getContent());
        }
        $input->setRequired(true);
        $this->addItem($input);

        $input = new \ilDateTimeInputGUI($this->translate('publish_date'), 'publish_date');
        $input->setShowTime(true);
        $input->setInfo($this->translate('news_publish_date_info'));
        if ($model && $model->getPublishTs()) {
            $input->setDate(new \ilDateTime($model->getPublishTs(), IL_CAL_UNIX, $this->user->getTimeZone()));
        } elseif (!$model) {
            $input->setDate(new \ilDateTime(time(), IL_CAL_UNIX));
        }
        if (!$unlimitedDateRange) {
            $input->setRequired(true);
        }
        $this->addItem($input);

        $input = new \ilDateTimeInputGUI($this->translate('expiration_date'), 'expiration_date');
        $input->setShowTime(true);
        $input->setInfo($this->translate('news_expiration_date_info'));
        if ($model && $model->getExpirationTs()) {
            $input->setDate(new \ilDateTime($model->getExpirationTs(), IL_CAL_UNIX, $this->user->getTimeZone()));
        }
        if (!$unlimitedDateRange) {
            $input->setRequired(true);
        }
        $this->addItem($input);

        if ($unlimitedDateRange) {
            $input = new \ilCheckboxInputGUI($this->translate('fixed'), 'fixed');
            $input->setInfo($this->translate('news_fixed_info'));
            if ($model && $model->getFixed()) {
                $input->setChecked(true);
            }
            $this->addItem($input);
        }

        $input = new \ilSelectInputGUI($this->translate('category'), 'category');
        $input->setOptions(['0' => $this->translate('default'), '1' => $this->translate('room_change')]);
        $input->setInfo($this->translate('news_category_info'));
        if ($model && $model->getCategory()) {
            $input->setValue($model->getCategory());
        }
        $this->addItem($input);

        $this->addCommandButton("submit", $this->translate("save"));
        $this->addCommandButton("cancel", $this->translate("cancel"));

        return $this;
    }
    
    public function translate(string $key, bool $forceGlobal = false){
        global $DIC;
        $translation = $this->plugin->txt($key);
        if ($forceGlobal || $translation[0] == "-") {
            $translation = $DIC->language()->txt($key);
        }
        return $translation;
    }
}