<?php

require_once dirname(dirname(__DIR__)) . '/src.php';
Import::php("util.pkg.OpenM_Package");
Import::php("util.OpenM_Log");
OpenM_Log::init("./", OpenM_Log::DEBUG, "log", 2000);
//
//$file = "../lib/openm.util.dependencies";
//if (!is_file($file))
//    throw new OpenM_PackageException("$file not found");
//$util_version = explode("=", file_get_contents($file));
//$file = "../../lib/" . $util_version[0] . "/Import.class.php";
//if (!is_file($file))
//    throw new OpenM_PackageException("$file not found");
//require_once realpath($file);
OpenM_Package::build_full();
?>