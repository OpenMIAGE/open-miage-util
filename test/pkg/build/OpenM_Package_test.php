<?php

require_once dirname(dirname(__DIR__)) . '/src.php';
Import::php("util.pkg.OpenM_Package");
Import::php("util.OpenM_Log");
OpenM_Log::init("./", OpenM_Log::DEBUG, "log", 2000);
OpenM_Package::build_full();
?>