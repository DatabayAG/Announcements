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
     * @throws \ilDateTimeException
     */
    public function modifyHtml(string $component, string $part, array $parameters) : array
    {
        try{
            $announcements = $this->service->findAllValid();
        }catch(PermissionDenied $e){
            return [];
        }

        $this->mainTemlate->addCss($this->getCoreController()->getPluginObject()->getDirectory() . '/css/announcements.css');
        $this->mainTemlate->addJavaScript($this->getCoreController()->getPluginObject()->getDirectory() . '/js/announcements.js');

        $listTemplate = $this->getCoreController()->getPluginObject()->getTemplate('tpl.landing_page_list.html', true, true);

        $listTemplate->setVariable('TITLE', 'Dummy News');
        $listTemplate->setVariable(
            'RSS_COMPONENT',
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
        if($this->accessHandler->mayCreateEntries()){
            $listTemplate->setVariable(
                'CREATE_NEWS',
                $this->uiRenderer->render(
                    $this->getNewsCommandLink('', 'create')
                )
            );
        }

        $acc = new \ilAccordionGUI();
        $usrIds = array_map(function($announcement){return $announcement->getCreatorUsrId();},$announcements);
        $names = \ilUserUtil::getNamePresentation($usrIds);
        foreach ($announcements as $object) {
            $published =  \ilDatePresentation::formatDate(
                new \ilDateTime($object->getPublishTs(), IL_CAL_UNIX, $this->user->getTimeZone())
            );
            if($this->accessHandler->mayEditEntry($object)) {
                $edit = $this->uiRenderer->render(
                    $this->getNewsCommandLink('', 'update', $object->getId())
                );
            }
            if($this->accessHandler->mayDeleteEntry($object)) {
                $deleteModal = $this->uiFactory
                    ->modal()
                    ->interruptive(
                        $this->getCoreController()->getPluginObject()->txt('news_delete'),
                        $this->getCoreController()->getPluginObject()->txt('news_delete_q'),
                        $this->ctrl->getLinkTargetByClass(
                            [\ilUIPluginRouterGUI::class, get_class($this->getCoreController())],
                            'News.delete'
                        ). '&id=' . $object->getId()
                    );
                $deleteBtn =
                $deleteBtn = $this->uiFactory
                    ->button()
                    ->shy('', '#')
                    ->withOnClick($deleteModal->getShowSignal());
                $delete =  $this->uiRenderer->render([$deleteModal,$deleteBtn]);
            }
            $header_action =
                $object->getTitle() .
                '<span class="pull-right announcements_meta">' .
                preg_replace('/^\[([^\s]*)\]$/', '$1', $names[$object->getCreatorUsrId()]) .
                ' | ' . $published . $delete . $edit .
                '</span>';

            $acc->addItem($header_action, $object->getContent());
        }
        $listTemplate->setVariable('NEWS_ENTRY', $acc->getHTML());
        $listTemplate->parseCurrentBlock();

        if (isset($this->request->getQueryParams()['saved'])) {
            $content[] = $this->uiFactory->messageBox()->success($this->lng->txt('saved_successfully'));
        }
        if (isset($this->request->getQueryParams()['failed'])) {
            $content[] = $this->uiFactory->messageBox()->failure($this->lng->txt('insufficent_permission'));
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
            'News.'.$command
        );
        if($objectId > 0){
            $link .= '&id=' . $objectId;
        }
        return $this->uiFactory->link()->standard($label, $link);
    }
}
