<?php

require_once dirname(__DIR__) . '/src.php';
Import::php("util.pkg.OpenM_Dependencies");
Import::php("util.OpenM_Log");
OpenM_Log::init("./", OpenM_Log::DEBUG, "log", 2000);

$dependencies = new OpenM_Dependencies("lib");
//$list = $dependencies->explore(OpenM_Dependencies::RUN);
//$e = $list->keys();
//echo "run:<br>";
//while ($e->hasNext()) {
//    $key = $e->next();
//    $value = $list->get($key);
//    echo "$key=$value<br>";
//}
//echo "test:<br>";
//$list = $dependencies->explore(OpenM_Dependencies::TEST);
//$e = $list->keys();
//while ($e->hasNext()) {
//    $key = $e->next();
//    $value = $list->get($key);
//    echo "$key=$value<br>";
//}
//echo "display:<br>";
//$list = $dependencies->explore(OpenM_Dependencies::DISPLAY);
//$e = $list->keys();
//while ($e->hasNext()) {
//    $key = $e->next();
//    $value = $list->get($key);
//    echo "$key=$value<br>";
//}

//$dependencies->install("./temp", OpenM_Dependencies::ALL(), TRUE);
//OpenM_Dir::rm("temp");
$dependencies->addInClassPath();
?>