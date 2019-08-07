<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Plugin\Announcements\Frontend\Controller\GUI;


/**
 * Class News
 * @package ILIAS\Plugin\Announcements\Frontend\Controller
 * @author  Ingmar Szmais <iszmais@databay.de>
 */
class NewsGUI
{
    
    protected $plugin;
    
    public function __construct(\ilPlugin $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @return string
     */
    public function renderCreateNews(string $action) : string
    {
        $formGUI = new \ilPropertyFormGUI();

        $formGUI->setTitle($this->translate('create_news'));
        $formGUI->setFormAction($action);
        
        $input = new \ilTextInputGUI($this->translate('title'),'title');
        $input->setInfo($this->translate('news_title_info'));
        $formGUI->addItem($input);

        $input = new \ilTextAreaInputGUI($this->translate('details'),'details');
        $input->setRows(3);
        $input->setCols(40);
        $input->setInfo($this->translate('news_details_info'));
        $formGUI->addItem($input);

        $input = new \ilDateTimeInputGUI($this->translate('publish_date'),'publish_date');
        $input->setInfo($this->translate('news_publish_date_info'));
        $formGUI->addItem($input);

        $input = new \ilDateTimeInputGUI($this->translate('expiration_date'),'expiration_date');
        $input->setInfo($this->translate('news_expiration_date_info'));
        $formGUI->addItem($input);
        
        $input = new \ilCheckboxInputGUI($this->translate('fixed'),'fixed');
        $input->setInfo($this->translate('news_fixed_info'));
        $formGUI->addItem($input);

        $input = new \ilSelectInputGUI($this->translate('category'),'category');
        $input->setOptions(['0' => '', '1' => $this->translate('room_change')]);
        $input->setInfo($this->translate('news_category_info'));
        $formGUI->addItem($input);

        $formGUI->addCommandButton("save", $this->translate("save"));
        $formGUI->addCommandButton("cancle", $this->translate("cancel"));

        return $formGUI->getHTML();
    }
    
    public function translate(string $key, bool $forceGlobal = false){
        global $DIC;
        $translation = $DIC->language()->txt($this->plugin->getPrefix().'_'.$key);
        if($forceGlobal || $translation[0] == "-"){
            $translation = $DIC->language()->txt($key);
        }
        return $translation;
    }
}