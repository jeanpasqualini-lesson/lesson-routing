<?php

require __DIR__."/../vendor/autoload.php";

$tests = array(
    new \Test\RoutingPhp(),
    new \Test\RoutingYml(),
    new \Test\RoutingXml()
);

foreach ($tests as $test) {
    echo "===".get_class($test)."===".PHP_EOL;

    $test->runTest();
}
