<?php

// Makes life easier
chdir(__DIR__);

// Require autoload
require_once '..'
    . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
    . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use DownloadCore\Pattern\ListNextPage as BehavioralPattern;

$BehavioralPattern = new BehavioralPattern(__DIR__);

if (isset($_REQUEST['url'])) {
    $BehavioralPattern->downloadOne($_REQUEST['url']);
} else {
    $BehavioralPattern->downloadAll();
}
