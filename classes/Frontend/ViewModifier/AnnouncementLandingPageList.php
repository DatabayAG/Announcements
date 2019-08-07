<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Plugin\Announcements\Frontend\ViewModifier;

use ILIAS\Plugin\Announcements\Frontend\ViewModifier;
use ILIAS\UI\Component\Component;

/**
 * Class AnnouncementLandingPageList
 * @package ILIAS\Plugin\Announcements\Frontend\ViewModifier
 * @author  Michael Jansen <mjansen@databay.de>
 * @author  Guido Vollbach <gvollbach@databay.de>
 */
class AnnouncementLandingPageList extends Base implements ViewModifier
{
    /**
     * @inheritDoc
     */
    public function shouldModifyHtml(string $component, string $part, array $parameters) : bool
    {
        return (
            'Services/PersonalDesktop' === $component && 'center_column' === $part
        );
    }

    /**
     * @inheritDoc
     */
    public function modifyHtml(string $component, string $part, array $parameters) : array
    {
        $this->mainTemlate->addCss($this->getCoreController()->getPluginObject()->getDirectory() . '/css/announcements.css');

        $listTemplate = $this->getCoreController()->getPluginObject()->getTemplate('tpl.landing_page_list.html', true, true);

        $news_entries = [
            [
                'title' => 'Hochschulsport 2019',
                'author' => 6,
                'published_date' => '22.07.2019',
                'content' => 'Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Donec velit neque, auctor sit amet aliquam vel, ullamcorper sit amet ligula. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur non nulla sit amet nisl tempus convallis quis ac lectus. Vestibulum ac diam sit amet quam vehicula elementum sed sit amet dui. Sed porttitor lectus nibh.'
            ],
            [
                'title' => 'Ringvorlesung 2019',
                'author' => 6,
                'published_date' => '20.07.2019',
                'content' => 'Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Donec velit neque, auctor sit amet aliquam vel, ullamcorper sit amet ligula. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur non nulla sit amet nisl tempus convallis quis ac lectus. Vestibulum ac diam sit amet quam vehicula elementum sed sit amet dui. Sed porttitor lectus nibh.'
            ],
            [
                'title' => 'Termine Prüfungsauschschuss',
                'author' => 6,
                'published_date' => '25.06.2019',
                'content' => 'Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Donec velit neque, auctor sit amet aliquam vel, ullamcorper sit amet ligula. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur non nulla sit amet nisl tempus convallis quis ac lectus. Vestibulum ac diam sit amet quam vehicula elementum sed sit amet dui. Sed porttitor lectus nibh.'
            ],
        ];

        $listTemplate->setVariable('TITLE', 'Dummy News');

        $listTemplate->setVariable(
            'RSS_COMPONENT',
            $this->uiRenderer->render(
                $this->getRssSubscriptionModalTriggerComponents(
                    $this->getCoreController()->getPluginObject()->txt('rss_subscription_btn_label'),
                    'getRssModalContent'
                )
            )
        );
        $listTemplate->setVariable(
            'RSS_ROOM_CHANGE_COMPONENT',
            $this->uiRenderer->render(
                $this->getRssSubscriptionModalTriggerComponents(
                    $this->getCoreController()->getPluginObject()->txt('rss_subscription_room_change_btn_label'),
                    'getRssRoomChangeModalContent'
                )
            )
        );

        foreach ($news_entries as $entry) {
            $acc = new \ilAccordionGUI();
            $acc->setBehaviour(\ilAccordionGUI::ALL_CLOSED);

            $author = new \ilObjUser($entry['author']);
            $published_on = $entry['published_date'];
            $header_action = '<span class="announcements_meta pull-right">' . $author->getPublicName() . ' | ' . $published_on . '</span>';
            $acc->addItem($entry['title'] . $header_action, $entry['content']);
            $listTemplate->setVariable('NEWS_ENTRY', $acc->getHTML());
            $listTemplate->parseCurrentBlock();
        }

        return ['mode' => \ilUIHookPluginGUI::PREPEND, 'html' => $listTemplate->get()];
    }

    /**
     * @param string $label
     * @param string $command
     * @return Component[]
     */
    private function getRssSubscriptionModalTriggerComponents(string $label, string $command) : array
    {
        global $DIC;
        $f = $DIC->ui()->factory();

        $rss_1 = 'Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/Announcements/templates/images/rss_1.svg';
        $rss_2 = 'Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/Announcements/templates/images/rss_2.svg';

        $ico = $f->icon()->custom($rss_1, $label);
        if($command === "getRssRoomChangeModalContent"){
            $ico = $f->icon()->custom($rss_2, $label);
        }
        
        $modal = $this->uiFactory
            ->modal()
            ->roundtrip('', [])
            ->withAsyncRenderUrl($this->ctrl->getLinkTargetByClass(
                [\ilUIPluginRouterGUI::class, get_class($this->getCoreController())],
                'Subscription.' . $command,
                '', true, false
            ));

        $triggerButton = $this->uiFactory
            ->button()
            ->shy($label, '')
            ->withOnClick(
                $modal->getShowSignal()
            );

        $components = [
            $modal,
            $triggerButton, $ico
        ];

        return $components;
    }
}
