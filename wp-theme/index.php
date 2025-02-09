<?php

if (!function_exists('is_single') || !function_exists('wp_redirect')) {
  require_once(dirname(__FILE__) . '/wp-load.php');
}

if ($_SERVER['HTTP_HOST'] === 'demo.frakturuj.cz') {
    if (!is_user_logged_in()) {
        wp_set_current_user(2);
        wp_set_auth_cookie(2);
        wp_redirect($_SERVER['REQUEST_URI']);
        exit;
    }
} else {
    if (!is_user_logged_in()) {
        wp_redirect(wp_login_url(home_url('/')));
        exit;
    }
}

$vite_assets = rk_get_vite_assets();
?>

<!DOCTYPE html>
<html lang="cs">
<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>Frakturuj</title>

 <link rel="icon" href="<?php echo get_template_directory_uri(); ?>/favicon.ico" type="image/x-icon">
 <link rel="shortcut icon" href="<?php echo get_template_directory_uri(); ?>/favicon.ico" type="image/x-icon">

 <link rel="preconnect" href="https://fonts.googleapis.com">
 <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
 <link href="https://fonts.googleapis.com/css2?family=Chango&family=Source+Sans+3:ital,wght@0,200..900;1,200..900&display=swap" rel="stylesheet">

 <?php if ($vite_assets['css']): ?>
    <link rel="stylesheet" href="<?php echo $vite_assets['css']; ?>">
 <?php endif; ?>
</head>
<body>
 <div id="app"></div>
 <script>
     window.wpSettings = {
         nonce: '<?php echo wp_create_nonce('wp_rest'); ?>',
         ajaxUrl: '<?php echo admin_url('admin-ajax.php'); ?>'
     };
 </script>
 <?php if ($vite_assets['js']): ?>
    <script type="module" src="<?php echo $vite_assets['js']; ?>"></script>
 <?php endif; ?>
</body>
</html>