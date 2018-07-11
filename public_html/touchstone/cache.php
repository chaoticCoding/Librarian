<?php
/**
 * Created by PhpStorm.
 * User: shawn
 * Date: 8/6/2015
 * Time: 4:38 PM
 */
include_once ("core/includes/cache/cache.stub");

$cache = new core\cacheStub([
        'name' => 'core',
        'path' => 'core/temp',
        'cache_life' => '86400' //caching time, in seconds
]);

//$cache->Set('config', "path");
//$cache->Set('moreconfig', "path");

$cache->Set('configAry', [
    'test1' => 'hello world!4',
    'test2' => 'hello world5',
    'test3' => 'hello world6',
]);


print_r( $cache->Get('configAry'));

$cache->writeCache();