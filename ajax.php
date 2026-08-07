<?php

/**
 * Single entry point for all ajax calls.
 *
 * ?a=list        -> /ajax/list.php
 * ?a=type&type=Activity&fn=set_done -> /types/Activity/ajax/set_done.php
 */

require __DIR__ . '/vendor/autoload.php';

use Sys\Ajax;
use Sys\App;

$app = App::boot(__DIR__);

(new Ajax($app))->run();
