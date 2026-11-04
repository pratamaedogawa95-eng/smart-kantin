<?php

use CodeIgniter\Boot;
use Config\Paths;

/*
 *---------------------------------------------------------------
 * CHECK PHP VERSION
 *---------------------------------------------------------------
 */

$minPhpVersion = '8.2';
if (version_compare(PHP_VERSION, $minPhpVersion, '<')) {
    $message = sprintf(
        'Your PHP version must be %s or higher to run CodeIgniter. Current version: %s',
        $minPhpVersion,
        PHP_VERSION,
    );

    header('HTTP/1.1 503 Service Unavailable.', true, 503);
    echo $message;

    exit(1);
}

/*
 *---------------------------------------------------------------
 * SET THE CURRENT DIRECTORY
 *---------------------------------------------------------------
 */

// Path ke folder public (FCPATH)
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

// Tentukan root directory aplikasi (satu level di atas public)
define('ROOTPATH', realpath(FCPATH . '../') . DIRECTORY_SEPARATOR);

// Ensure the current directory is pointing to the front controller's directory
if (getcwd() . DIRECTORY_SEPARATOR !== FCPATH) {
    chdir(FCPATH);
}

/*
 *---------------------------------------------------------------
 * BOOTSTRAP THE APPLICATION
 *---------------------------------------------------------------
 */

// LOAD OUR PATHS CONFIG FILE
require ROOTPATH . 'app/Config/Paths.php';
$paths = new Config\Paths();

// LOAD THE FRAMEWORK BOOTSTRAP FILE menggunakan systemDirectory dari Paths.php
require rtrim($paths->systemDirectory, '/\\') . '/Boot.php';

exit(Boot::bootWeb($paths));