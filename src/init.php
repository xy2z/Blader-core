<?php

/**
 * Init Blader
 * This should be included in the top of your public/index.php file.
 */

define('ROOT_DIR', $_SERVER['DOCUMENT_ROOT'] . '/..');

require_once ROOT_DIR . '/vendor/autoload.php';

class_alias('xy2z\LiteConfig\LiteConfig', 'Config');
use xy2z\Blader\Blader;

# Load config
Config::loadDir(ROOT_DIR . '/config', true);

# Prepare blader
$blader = new Blader;
