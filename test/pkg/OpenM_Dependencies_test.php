<?php

require_once dirname(__DIR__) . '/src.php';
Import::php("util.pkg.OpenM_Dependencies");
Import::php("util.OpenM_Log");
OpenM_Log::init("./", OpenM_Log::DEBUG, "log", 2000);

$dependencies = new OpenM_Dependencies("lib");
$dependencies->addInClassPath();
?>