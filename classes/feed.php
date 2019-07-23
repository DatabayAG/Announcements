<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Michael Jansen <mjansen@databay.de>
 */

chdir(dirname(__FILE__));
while (!file_exists('ilias.ini.php')) {
	chdir('../');
}

require_once 'Services/Context/classes/class.ilContext.php';
ilContext::init(ilContext::CONTEXT_RSS);

require_once 'Services/Init/classes/class.ilInitialisation.php';
ilInitialisation::initILIAS();

// TODO: Process request from $DIC->http()->request(), validate the 'hash' parameter, determine the user, check permissions, create RSS response