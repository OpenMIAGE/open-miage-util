<?php

require_once dirname(__DIR__) . '/src/Import.class.php';
Import::php("util.file.OpenM_Dir");
Import::php("util.file.OpenM_Zip");
$temp = "temp/";
$version = file_get_contents("build.version");
$count = intval(file_get_contents("build.count"));
$build_config = explode("=",file_get_contents("build.config"));
$dir = $temp."lib/".$build_config[0]."/$version";
if (is_dir($temp)) {
    OpenM_Dir::rm($temp);
    echo " - $temp <b>correctly removed</b><br>";
}
mkdir($dir, 0777, true);
echo " - $temp <b>correctly created</b><br>";

OpenM_Dir::cp("../src", $dir);
echo " - ../src <b>correctly copied to</b> $dir<br>";
$file_lst = file_get_contents("build.file.lst");
$file_array = explode("\r\n", $file_lst);
$path = "../";
foreach ($file_array as $value) {
    if (is_file($path . $value)) {
        copy($path . $value, $temp . $value);
        echo " - $value <b>correctly copied to</b> $temp$value<br>";
    } else if (is_dir($path . $value)) {
        OpenM_Dir::cp($path . $value, $temp . $value);
        echo " - $value <b>correctly copied to</b> $temp$value<br>";
    }
    else
        die("$path$value is not a file or a directory");
}
$target_file_name = $build_config[1]."_$version" . "_$count.zip";
OpenM_Zip::zip($temp, $target_file_name);
echo " - $temp <b>correctly ziped to</b> $target_file_name<br>";
OpenM_Dir::rm($temp);
echo " - $temp <b>correctly removed</b><br>";
file_put_contents("build.count", $count + 1);
?>