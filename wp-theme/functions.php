<?php
// Load all function files
$function_files = glob(__DIR__ . '/functions/*.php');
if ($function_files) {
    foreach ($function_files as $filename) {
        require_once $filename;
    }
}
