<?php
/*
 * This file is part of the Engine framework.
 * (c) Mathias Methner <mathiasmethner@gmail.com>
 * Please view the LICENSE file
 */
error_log('mmtest');
spl_autoload_register(function ($class) {
    // project-specific namespace prefix
    $prefix = 'Engine\\Test\\';

    // base directory for the namespace prefix
    $base_dir = __DIR__ . '/';

    error_log($prefix);
    // does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }

    // get the relative class name
    $relative_class = substr($class, $len);

    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        /** @noinspection PhpIncludeInspection */
        require($file);
    }
});