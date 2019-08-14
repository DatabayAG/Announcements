<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once __DIR__ . '/vendor/autoload.php';

/**
 * @author Michael Jansen <mjansen@databay.de>
 */
spl_autoload_register(function ($class) {
    $path = str_replace("\\", '/', str_replace("ILIAS\\Plugin\\Announcements\\", '', $class)) . '.php';

    $pathToFile = ilAnnouncementsPlugin::getInstance()->getDirectory() . '/classes/' . $path;
    if (file_exists($pathToFile)) {
        ilAnnouncementsPlugin::getInstance()->includeClass($path);
    }
});