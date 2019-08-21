<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Plugin\Announcements\Frontend\ViewModifier;

use ILIAS\Plugin\Announcements\AccessControl\Exception\PermissionDenied;
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
     * @throws \arException
     * @throws \ilDateTimeException
     */
    public function modifyHtml(string $component, string $part, array $parameters) : array
    {
        try {
            $announcements = $this->service->findAllValid();
        } catch (PermissionDenied $e) {
            return [];
        }

        $plugin = $this->getCoreController()->getPluginObject();
        $this->mainTemlate->addCss($plugin->getDirectory() . '/css/announcements.css');
        $this->mainTemlate->addJavaScript($plugin->getDirectory() . '/js/announcements.js');

        $listTemplate = $plugin->getTemplate('tpl.landing_page_list.html', true, true);

        $listTemplate->setVariable('TITLE', $plugin->txt('news'));
        $listTemplate->setVariable('RSS_COMPONENT',
            $this->uiRenderer->render(
                $this->getRssSubscriptionModalTriggerComponents('', 'getRssModalContent')
            )
        );
        $listTemplate->setVariable(
            'RSS_ROOM_CHANGE_COMPONENT',
            $this->uiRenderer->render(
                $this->getRssSubscriptionModalTriggerComponents('', 'getRssRoomChangeModalContent')
            )
        );
        if ($this->accessHandler->mayCreateEntries()) {
            $listTemplate->setVariable('CREATE_NEWS',
                $this->uiRenderer->render(
                    $this->getNewsCommandLink('', 'create')
                )
            );
        }

        $acc = new \ilAccordionGUI();
        if (empty($announcements)) {
            $listTemplate->setVariable(
                'NEWS_EMPTY',
                $this->uiRenderer->render($this->uiFactory->messageBox()->info($plugin->txt('news_empty')))
            );
        } else {
            $usrIds = array_map(
                function ($announcement) {
                    return $announcement->getCreatorUsrId();
                },
                $announcements
            );
            $names = \ilUserUtil::getNamePresentation($usrIds);
            foreach ($announcements as $object) {
                $published = \ilDatePresentation::formatDate(
                    new \ilDateTime($object->getPublishTs(), IL_CAL_UNIX, $this->user->getTimeZone())
                );
                $edit = '';
                if ($this->accessHandler->mayEditEntry($object)) {
                    $edit = $this->uiRenderer->render(
                        $this->getNewsCommandLink('', 'update', $object->getId())
                    );
                }
                $delete = '';
                if ($this->accessHandler->mayDeleteEntry($object)) {
                    $deleteModal = $this->uiFactory
                        ->modal()
                        ->interruptive(
                            $plugin->txt('news_delete'),
                            $plugin->txt('news_delete_q'),
                            $this->ctrl->getLinkTargetByClass(
                                [\ilUIPluginRouterGUI::class, get_class($this->getCoreController())],
                                'News.delete'
                            ) . '&id=' . $object->getId()
                        );
                    $deleteBtn =
                    $deleteBtn = $this->uiFactory->button()->shy('', '#')->withOnClick($deleteModal->getShowSignal());
                    $delete = $this->uiRenderer->render([$deleteModal, $deleteBtn]);
                }

                $header = $plugin->getTemplate('tpl.announcement_header.html', true, true);
                $header->setVariable('TITLE', $object->getTitle());
                $header->setVariable('ACTIONS', $delete . $edit);
                $header->setVariable(
                    'META_INFOS',
                    preg_replace('/^\[([^\s]*)\]$/', '$1', $names[$object->getCreatorUsrId()]) . ' | ' . $published
                );

                $acc->addItem($header->get(), \ilUtil::makeClickable(nl2br($object->getContent())));
            }
            $listTemplate->setVariable('NEWS_ENTRY', $acc->getHTML());

        }
        $listTemplate->parseCurrentBlock();
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

    /**
     * @param string $label
     * @param string $command
     * @return Component
     */
    private function getNewsCommandLink(string $label, string $command, int $objectId = 0) : Component
    {
        $link = $this->ctrl->getLinkTargetByClass(
            [\ilUIPluginRouterGUI::class, get_class($this->getCoreController())],
            'News.' . $command
        );
        if ($objectId > 0) {
            $link .= '&id=' . $objectId;
        }
        return $this->uiFactory->link()->standard($label, $link);
    }
}
