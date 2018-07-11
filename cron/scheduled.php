<?php

require_once (realpath('../../../src/boot.php'));
// as the entry call is not index.php we need to insure that the bootstrap is called

/**
 * Cron dispatcher
 *
 */
class cronTask
{
    function execute ()
    {
         // count of args
        global $argc;

        // array of args passed in 1st is script name
        global $argv;


    }
}

$task = new cronTask();
$task->execute();

