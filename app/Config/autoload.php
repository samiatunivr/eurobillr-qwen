<?php
/**
 * Autoloader - PSR-4 Compliant
 * Eurobillr Platform
 */

spl_autoload_register(function ($class) {
    // Base directory for the namespace prefix
    $baseDir = dirname(__DIR__);
    
    // Define namespace to directory mappings
    $namespaces = [
        'App\\' => $baseDir . '/app/',
        'Core\\' => $baseDir . '/core/',
        'Services\\' => $baseDir . '/services/',
        'Lib\\' => $baseDir . '/lib/',
        'Database\\' => $baseDir . '/database/',
    ];
    
    foreach ($namespaces as $prefix => $dir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }
        
        // Get the relative class name
        $relativeClass = substr($class, $len);
        
        // Replace namespace separators with directory separators
        $file = $dir . str_replace('\\', '/', $relativeClass) . '.php';
        
        // If file exists, require it
        if (file_exists($file)) {
            require $file;
            return true;
        }
    }
    
    return false;
});

// Load helper functions
$helpers = glob($baseDir . '/app/Helpers/*.php');
foreach ($helpers as $helper) {
    require_once $helper;
}
