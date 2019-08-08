<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Plugin\Announcements\Frontend\ViewModifier;

use ILIAS\Plugin\Announcements\Entry\Model;
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
     * @throws \ilDateTimeException
     */
    public function modifyHtml(string $component, string $part, array $parameters) : array
    {
        $this->mainTemlate->addCss($this->getCoreController()->getPluginObject()->getDirectory() . '/css/announcements.css');

        $listTemplate = $this->getCoreController()->getPluginObject()->getTemplate('tpl.landing_page_list.html', true, true);

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
        $listTemplate->setVariable(
            'CREATE_NEWS',
            $this->uiRenderer->render(
                $this->uiFactory->link()->standard(
                    '', 
                    $this->ctrl->getLinkTargetByClass(
                        [\ilUIPluginRouterGUI::class, get_class($this->getCoreController())],
                        'News.create'
                    )
                )
            )
        );

        $repository = new \ActiveRecordList(new Model());
        foreach ($repository->get() as $object) {
            $acc = new \ilAccordionGUI();
            $author = new \ilObjUser($object->getCreatorUsrId());
            $published = new \ilDateTime($object->getPublishTs(), IL_CAL_UNIX, $this->user->getTimeZone());

            $edit = $this->uiRenderer->render(
                $this->uiFactory->link()->standard(
                    '',
                    $this->ctrl->getLinkTargetByClass(
                        [\ilUIPluginRouterGUI::class, get_class($this->getCoreController())],
                        'News.update'
                    ) . '&id=' . $object->getId()
                )
            );
            $header_action =
                $edit .
                '<span class="announcements_meta pull-right">' .
                $author->getPublicName() . ' | ' . $published .
                '</span>';

            $acc->addItem($object->getTitle() . $header_action, $object->getContent());
            $listTemplate->setVariable('NEWS_ENTRY', $acc->getHTML());
            $listTemplate->parseCurrentBlock();
        }

        $content = [];
        if (isset($this->request->getQueryParams()['saved'])) {
            $content[] = $this->uiFactory->messageBox()->success($this->lng->txt('saved_successfully'));
        }
        $content[] = $this->uiFactory->legacy($listTemplate->get());

        return ['mode' => \ilUIHookPluginGUI::PREPEND, 'html' => $this->uiRenderer->render($content)];
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
            $triggerButton
        ];

        return $components;
    }
}
