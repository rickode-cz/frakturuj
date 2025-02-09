<?php

function rk_get_vite_assets() {
    $dist_path = get_template_directory() . '/app/';
    $dist_uri = get_template_directory_uri() . '/app/';
  
    // Read the dist directory
    $files = scandir($dist_path);
    $assets = [
        'js' => '',
        'css' => ''
    ];
    
    foreach ($files as $file) {
        if (preg_match('/^main-.*\.js$/', $file)) {
            $assets['js'] = $dist_uri . $file;
        }
        if (preg_match('/^main-.*\.css$/', $file)) {
            $assets['css'] = $dist_uri . $file;
        }
    }
    
    return $assets;
}