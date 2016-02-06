<?php
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $file = __DIR__ . $_SERVER['REQUEST_URI'];
    if (is_file($file)) {
        return false;
    }
}

session_start();
date_default_timezone_set("Asia/kolkata");

//echo dirname(dirname(__FILE__));
require dirname(dirname(__FILE__)) . '/vendor/autoload.php';

// Instantiate the app
require dirname(dirname(__FILE__)) . '/src/config/settings.php';
$settings = require dirname(dirname(__FILE__)) . '/src/settings.php';
$app = new \Slim\App($settings);

// Set up dependencies
require dirname(dirname(__FILE__)) . '/src/dependencies.php';

// Register middleware
require dirname(dirname(__FILE__)) . '/src/middleware.php';

// Require Helper Classes
require dirname(dirname(__FILE__)) . '/src/helpers/MailChimpSubs.php';

// Register routes
require dirname(dirname(__FILE__)) . '/src/routes.php';

// Run app
$app->run();
