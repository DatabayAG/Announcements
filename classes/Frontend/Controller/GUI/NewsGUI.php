<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Plugin\Announcements\Frontend\Controller\GUI;


use ILIAS\Plugin\Announcements\Entry\Model;

/**
 * Class News
 * @package ILIAS\Plugin\Announcements\Frontend\Controller
 * @author  Ingmar Szmais <iszmais@databay.de>
 */
class NewsGUI extends \ilPropertyFormGUI
{

    protected $user;

    protected $plugin;

    public function __construct(\ilPlugin $plugin)
    {
        global $DIC;
        $this->user = $DIC->user();
        $this->plugin = $plugin;

        parent::__construct();
    }

    /**
     * @param string $action
     * @param Model|null $model
     * @return \ilPropertyFormGUI
     * @throws \ilDateTimeException
     */
    public function initForm(string $action, Model $model = null) : \ilPropertyFormGUI
    {
        $this->setTitle($this->translate('create_news'));
        $this->setFormAction($action);

        if($model){
            $this->setTitle($this->translate('update_news'));

            $input = new \ilHiddenInputGUI('id');
            $input->setValue($model->getId());
            $this->addItem($input);
        }

        $input = new \ilTextInputGUI($this->translate('title'),'title');
        $input->setInfo($this->translate('news_title_info'));
        if($model && $model->getTitle()){
            $input->setValue($model->getTitle());
        }
        $input->setRequired(true);
        $this->addItem($input);

        $input = new \ilTextAreaInputGUI($this->translate('content'),'content');
        $input->setRows(3);
        $input->setCols(40);
        $input->setInfo($this->translate('news_content_info'));
        if($model && $model->getContent()){
            $input->setValue($model->getContent());
        }
        $this->addItem($input);

        $input = new \ilDateTimeInputGUI($this->translate('publish_date'),'publish_date');
        $input->setShowTime(true);
        $input->setInfo($this->translate('news_publish_date_info'));
        if($model && $model->getPublishTs()){
            $input->setDate(new \ilDateTime($model->getPublishTs(),IL_CAL_UNIX, $this->user->getTimeZone()));
        }
        $this->addItem($input);

        $input = new \ilDateTimeInputGUI($this->translate('expiration_date'),'expiration_date');
        $input->setShowTime(true);
        $input->setInfo($this->translate('news_expiration_date_info'));
        if($model && $model->getExpirationTs()){
            $input->setDate(new \ilDateTime($model->getExpirationTs(),IL_CAL_UNIX, $this->user->getTimeZone()));
        }
        $this->addItem($input);

        $input = new \ilCheckboxInputGUI($this->translate('fixed'),'fixed');
        $input->setInfo($this->translate('news_fixed_info'));
        if($model && $model->getFixed()){
            $input->setValue($model->getFixed());
        }
        $this->addItem($input);

        $input = new \ilSelectInputGUI($this->translate('category'),'category');
        $input->setOptions(['0' => '', '1' => $this->translate('room_change')]);
        $input->setInfo($this->translate('news_category_info'));
        if($model && $model->getCategory()){
            $input->setValue($model->getCategory());
        }
        $this->addItem($input);

        $this->addCommandButton("save", $this->translate("save"));
        $this->addCommandButton("cancle", $this->translate("cancel"));

        return $this;
    }
    
    public function translate(string $key, bool $forceGlobal = false){
        global $DIC;
        $translation = $DIC->language()->txt($this->plugin->getPrefix().'_'.$key);
        if($forceGlobal || $translation[0] == "-"){
            $translation = $DIC->language()->txt($key);
        }
        return $translation;
    }

    /**
     * @inheritdoc
     */
    public function checkInput()
    {

        return parent::checkInput();
    }
}