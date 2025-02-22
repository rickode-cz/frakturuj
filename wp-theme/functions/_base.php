<?php
/* Head meta */
function rk_cleanup()
{
  remove_action('wp_head', 'rsd_link');
  remove_action('wp_head', 'wlwmanifest_link');
  remove_action('wp_head', 'wp_generator');
}
add_action('init', 'rk_cleanup');

/* Disable comments */
function rk_remove_comment_support()
{
  remove_post_type_support('post', 'comments');
  remove_post_type_support('page', 'comments');
}
add_action('admin_menu', 'rk_remove_comment_support');

function rk_remove_admin_menus()
{
  remove_menu_page('edit-comments.php');
}
add_action('admin_init', 'rk_remove_admin_menus');

/* Disable XML-RPC */
add_filter('xmlrpc_enabled', '__return_false');

/* Control Interval Heartbeat API */
function rk_control_heartbeat($settings)
{
  $settings['interval'] = 60;
  return $settings;
}
add_filter('heartbeat_settings', 'rk_control_heartbeat');

/* Hide admin bar by default */
function rk_hide_bar()
{
  add_filter('show_admin_bar', '__return_false');
}
add_action('init', 'rk_hide_bar');

/* Text domain */
function rk_text_domain()
{
  load_theme_textdomain('rk', get_template_directory() . '/languages');
}
add_action('after_setup_theme', 'rk_text_domain');

/* Disable Guttenberg */
add_filter('use_block_editor_for_post', '__return_false', 10);

/* Hide preview button */
function rk_disable_preview_button()
{
  global $post;
  if ($post && $post->post_status == 'publish') {
    echo '<style type="text/css">#post-preview, #view-post-btn {display: none;}</style>';
  }
}
add_action('admin_head', 'rk_disable_preview_button');

/* Hide ACF */
add_filter('acf/settings/show_admin', '__return_false');
