<?php

require_once dirname(__DIR__) . '/src.php';
Import::php("util.pkg.OpenM_Dependencies");
$dependencies = new OpenM_Dependencies("lib");
$list = $dependencies->explore();
$e = $list->keys();
echo "run:<br>";
while ($e->hasNext()) {
    $key = $e->next();
    $value = $list->get($key);
    echo "$key=$value<br>";
}
echo "test:<br>";
$list = $dependencies->explore(true);
$e = $list->keys();
while ($e->hasNext()) {
    $key = $e->next();
    $value = $list->get($key);
    echo "$key=$value<br>";
}

$dependencies->install("./temp", true);
?>