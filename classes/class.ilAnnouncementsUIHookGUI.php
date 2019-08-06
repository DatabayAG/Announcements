<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\DI\Container;

/**
 * @author            Michael Jansen <mjansen@databay.de>
 * @ilCtrl_isCalledBy ilAnnouncementsUIHookGUI: ilUIPluginRouterGUI
 */
class ilAnnouncementsUIHookGUI extends ilUIHookPluginGUI
{
	/** @var Container */
	protected $dic;

	/** @var ilCtrl */
	protected $ctrl;

	/** @var ilObjUser */
	protected $actor;

	/** @var \ILIAS\DI\HTTPServices */
	protected $http;

	/**
	 * ilAnnouncementsUIHookGUI constructor.
	 */
	public function __construct()
	{
		global $DIC;

		$this->dic = $DIC;

		$this->http = $DIC->http();
		$this->ctrl = $DIC->ctrl();
		$this->actor = $DIC->user();
	}

	/**
	 * 
	 */
	public function executeCommand()
	{
		if ($this->actor->isAnonymous() || 0 === (int) $this->actor->getId()) {
			$target = '';
			$entryId = (int) ($this->http->request()->getQueryParams()['entry_id'] ?? 0);
			if ($entryId > 0) {
				$target = '&target=announcements_' . $entryId;
			}
			$this->ctrl->redirectToURL('login.php?cmd=force_login&client_id=' . CLIENT_ID . $target);
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getHTML($a_comp, $a_part, $a_par = [])
	{
		$unmodified = ['mode' => ilUIHookPluginGUI::KEEP, 'html' => ''];

		if ('Services/Utilities' === $a_comp && 'redirect' === $a_part) {
			$url = (string) ($a_par['html'] ?? '');
			$pluginDirectory = basename(dirname(__DIR__));
			if ('error.php' === basename($url) && strpos($url, $pluginDirectory) !== false) {
				$correctUrl = preg_replace(
					'/(.*?)\/(Customizing\/(.*?))(\/error.php)$/',
					'$1$4',
					$url
				);
				return ['mode' => ilUIHookPluginGUI::REPLACE, 'html' => $correctUrl];
			}
		}
        else if ('Services/PersonalDesktop' === $a_comp && 'center_column' === $a_part) {
          
            global $tpl;
            
            $tpl->addCss('Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/Announcements/templates/announcements.css');
            $main_tpl = $this->plugin_object->getTemplate('tpl.main_template.html', true, true);
            $main_tpl = $this->addNewsView($main_tpl);
            return ['mode' => ilUIHookPluginGUI::PREPEND, 'html' => $main_tpl->get()];
        }

		return $unmodified;
	}

    /**
     * @param ilTemplate $main_tpl
     * @return ilTemplate
     */
	protected function addNewsView($main_tpl){
        $settings = $GLOBALS['DIC']['plugin.announcements.settings'];
        $news_entries = [
            ['title' => 'Hochschulsport 2019', 'author' => 6 , 'published_date' => '22.07.2019' , 'content' => 'Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Donec velit neque, auctor sit amet aliquam vel, ullamcorper sit amet ligula. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur non nulla sit amet nisl tempus convallis quis ac lectus. Vestibulum ac diam sit amet quam vehicula elementum sed sit amet dui. Sed porttitor lectus nibh.'],
            ['title' => 'Ringvorlesung 2019', 'author' => 453 , 'published_date' => '20.07.2019' , 'content' => 'Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Donec velit neque, auctor sit amet aliquam vel, ullamcorper sit amet ligula. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur non nulla sit amet nisl tempus convallis quis ac lectus. Vestibulum ac diam sit amet quam vehicula elementum sed sit amet dui. Sed porttitor lectus nibh.'],
            ['title' => 'Termine Prüfungsauschschuss', 'author' => 6 , 'published_date' => '22.06.2019' ,  'content' => 'Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Donec velit neque, auctor sit amet aliquam vel, ullamcorper sit amet ligula. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur non nulla sit amet nisl tempus convallis quis ac lectus. Vestibulum ac diam sit amet quam vehicula elementum sed sit amet dui. Sed porttitor lectus nibh.'],
        ];


        $main_tpl->setVariable('TITLE',  $settings->getRssChannelTitle());

        foreach($news_entries as $entry){
            $acc = new ilAccordionGUI();
            $acc->setBehaviour(ilAccordionGUI::ALL_CLOSED);
            $author = new ilObjUser($entry['author']);

            $header_action = '<span class="announcements_meta pull-right">' . $author->getPublicName() . ' | 22.06.2018</span>';
            $acc->addItem($entry['title'] . $header_action, $entry['content']);
            $main_tpl->setVariable('NEWS_ENTRY',  $acc->getHTML());
            $main_tpl->parseCurrentBlock();
        }
        return $main_tpl;
    }

	/**
	 * @inheritDoc
	 */
	public function checkGotoHook($a_target)
	{
		global $DIC;

		$parts = explode('_', $a_target);
		if ('announcements' === $parts[0]) {
			$_GET['baseClass'] = ilUIPluginRouterGUI::class;
			$DIC->ctrl()->setTargetScript('ilias.php');
			// TODO: Permanent links should be created with ilLink::_getLink($entry->getId(), 'announcements')
			if (isset($parts[1]) && is_numeric($parts[1])) {
				$DIC->ctrl()->setParameterByClass(self::class, 'entry_id', $parts[1]);
				$DIC->ctrl()->redirectByClass([
					ilUIPluginRouterGUI::class, self::class],
					'###A command for the detail view###' //TODO Use a command link for a detail entry presentation
				);
			}
		}

		return parent::checkGotoHook($a_target);
	}
}