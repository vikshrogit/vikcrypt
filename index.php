<?php

ini_set('memory_limit','128M');
date_default_timezone_set("Asia/Calcutta");
require __DIR__ . '/vendor/autoload.php';

use VIKSHRO\VIKCRYPT\VIKCRYPT;
$vc = new VIKCRYPT("Testing VICRYPT");
print_r($vc->getLicenseData());

print_r(\VIKSHRO\VIKCRYPT\Component\App::url());
