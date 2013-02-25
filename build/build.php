<?php

require_once dirname(__DIR__) . '/src/Import.class.php';
Import::php("util.file.OpenM_Dir");
Import::php("util.file.OpenM_Zip");
$temp = "temp";
if (!is_dir($temp))
    mkdir($temp);
$version = file_get_contents("build.version");
$count = intval(file_get_contents("build.count"));
$dir = "$temp/OpenM/util/$version" . "_$count";
if (is_dir($temp))
    OpenM_Dir::rm($temp);
mkdir($dir, 0777, true);
OpenM_Dir::cp("../src", $dir);
$target_file_name = "OpenM.util_$version" . "_$count" . "_src.zip";
OpenM_Zip::zip($temp, $target_file_name);
OpenM_Dir::rm($temp);
file_put_contents("build.count", $count + 1);
?>