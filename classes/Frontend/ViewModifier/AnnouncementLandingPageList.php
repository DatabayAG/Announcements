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
     * @return int
     */
    private function getPageIndex() : int
    {
        $pageIndex = 0;
        if (isset($this->request->getQueryParams()['apage'])) {
            $pageIndex = max((int) $this->request->getQueryParams()['apage'], $pageIndex);
            $this->keyValueStore->set('announcement_page', $pageIndex);
        }

        return $this->keyValueStore->get('announcement_page', 0);
    }

    /**
     * @inheritDoc
     * @throws \arException
     * @throws \ilDateTimeException
     */
    public function modifyHtml(string $component, string $part, array $parameters) : array
    {
        $pageSize = max(1, (int) $this->user->getPref('hits_per_page', 10));
        $pageIndex = $this->getPageIndex();

        try {
            $announcementList = $this->service->findAllValid(false);
        } catch (PermissionDenied $e) {
            return [];
        }

        $plugin = $this->getCoreController()->getPluginObject();

        $this->mainTemplate->addCss($plugin->getDirectory() . '/css/announcements.css');
        $this->mainTemplate->addJavaScript($plugin->getDirectory() . '/js/announcements.js');

        $listTemplate = $plugin->getTemplate('tpl.landing_page_list.html', true, true);

        $listTemplate->setVariable('TITLE', $plugin->txt('news'));
        $listTemplate->setVariable('RSS_COMPONENT',
            $this->uiRenderer->render(
                $this->getRssSubscriptionModalTriggerComponents(
                    $this->uiRenderer->render(
                        $this->uiFactory->image()->standard(
                            "./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/Announcements/templates/images/rss_1.svg",
                            ''
                        )
                    )
                    , 'getRssModalContent')
            )
        );
        $listTemplate->setVariable(
            'RSS_ROOM_CHANGE_COMPONENT',
            $this->uiRenderer->render(
                $this->getRssSubscriptionModalTriggerComponents(
                    $this->uiRenderer->render(
                        $this->uiFactory->image()->standard(
                            "./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/Announcements/templates/images/rss_2.svg",
                            ''
                        )
                    )
                    , 'getRssRoomChangeModalContent')
            )
        );
        if ($this->accessHandler->mayCreateEntries()) {
            $listTemplate->setVariable('CREATE_NEWS',
                $this->uiRenderer->render(
                    $this->getNewsCommandLink(
                        $this->uiRenderer->render(
                            $this->uiFactory->image()->standard(
                                "./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/Announcements/templates/images/plus.svg",
                                ''
                            )
                        )
                        , 'create')
                )
            );
        }

        $numberOfAnnouncements = $announcementList->count();
        if ($numberOfAnnouncements > $pageSize) {
            $listTemplate->setVariable('PAGINATION', $this->uiRenderer->render(
                $this->uiFactory
                    ->viewControl()
                    ->pagination()
                    ->withTargetURL('ilias.php?baseClass=ilPersonalDesktopGUI', 'apage')
                    ->withTotalEntries($numberOfAnnouncements)
                    ->withPageSize($pageSize)
                    ->withMaxPaginationButtons(10)
                    ->withCurrentPage($pageIndex)
            ));
        }
        
        $announcementList->limit((int) $pageIndex * (int) $pageSize, (int) $pageSize);

        $announcements = $announcementList->get();

        $componentsForOuterRendering = [];

        if (empty($announcements)) {
            if ((int) $pageIndex > 0) {
                $this->ctrl->redirectToURL('ilias.php?baseClass=ilPersonalDesktopGUI&apage=0');
            }

            $listTemplate->setVariable(
                'NEWS_EMPTY',
                $this->uiRenderer->render($this->uiFactory->messageBox()->info($plugin->txt('news_empty')))
            );
        } else {
            $acc = new \ilAccordionGUI();

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
                        $this->getNewsCommandLink(
                            $this->uiRenderer->render(
                                $this->uiFactory->image()->standard(
                                    "./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/Announcements/templates/images/pen.svg",
                                    ''
                                )
                            )
                        , 'update', $object->getId())
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
                    $deleteBtn = $this->uiFactory->button()->shy(
                        $this->uiRenderer->render(
                            $this->uiFactory->image()->standard(
                                "./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/Announcements/templates/images/trash.svg",
                                ''
                            )
                        )
                        , '#')->withOnClick($deleteModal->getShowSignal());

                    $delete =  $this->uiRenderer->render([$deleteBtn]);
                    $componentsForOuterRendering[] = $deleteModal;
                }

                $header = $plugin->getTemplate('tpl.announcement_header.html', true, true);
                $header->setVariable('TITLE', $object->getTitle());
                $header->setVariable('ACTIONS', $edit . $delete);
                $header->setVariable(
                    'META_INFOS',
                    preg_replace('/^\[([^\s]*)\]$/', '$1', $names[$object->getCreatorUsrId()]) . ' | ' . $published
                );

                $acc->addItem($header->get(), \ilUtil::makeClickable(nl2br($object->getContent())));
            }

            $listTemplate->setVariable('NEWS_ENTRY', $acc->getHTML());
        }
        
        foreach ($componentsForOuterRendering as $component) {
            $listTemplate->setCurrentBlock('ui_components');
            $listTemplate->setVariable('COMPONENT', $this->uiRenderer->render([$component]));
            $listTemplate->parseCurrentBlock();
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
