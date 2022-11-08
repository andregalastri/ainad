<?php

/******
 * This file is the bootstrap for any AINAD utility. It instantiates a class
 * controller and calls a method that will run an action.
******/

/**
 * Remove the first argument because it is just the PHP script itself.
 */
array_shift($argv);

/**
 * Checks if the arguments were informed.
 */
if (!isset($argv[0])) {
    echo "First argument (controller) needs to be a informed.\n";
    exit(1);
}

if (!isset($argv[1])) {
    echo "Second argument (method) needs to be informed.\n";
    exit(2);
}

/**
 * Defining constats
 */

/**
 * Current PHP directory
 */
define('BASE_DIR', dirname(__FILE__));

/**
 * AINAD directory, from environment variable.
 */
define('AINAD_BASE_DIR', getenv('ainadBaseDir'));

/**
 * Requiring the autoload from Composer.
 */
require BASE_DIR.'/vendor/autoload.php';

/**
 * Defines the class namespace of the controllers.
 * 
 * @var string
 */
$controller = '\\Core\\Controllers\\'.array_shift($argv);

/**
 * Defines the name of the method to be called.
 * 
 * @var string
 */
$method = array_shift($argv);

/**
 *  Checks if the controller exists. If so, instantiates it and checks if the
 *  method to be called exists. If so, runs it. If the class or method don't
 *  exist, then throw an error message and exits.
 */
if (class_exists($controller)) {
    $run = new $controller();

    if (method_exists($run, $method)) {
        echo $run->$method($argv);
        exit(0);
    } else {
        echo 'Method "'.$method .'" not found.';
        exit(2);
    }
} else {
    echo 'controller "'.$controller .'" not found.';
    exit(1);
}
