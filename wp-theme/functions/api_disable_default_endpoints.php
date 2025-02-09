<?php
// Disable REST API for non-authenticated users
add_filter('rest_authentication_errors', function ($result) {
  if (!is_user_logged_in()) {
    return new WP_Error('rest_disabled', __('The REST API on this site is disabled.'), array('status' => 401));
  }

  $user_id = get_current_user_id();
  
  if (!user_can($user_id, 'manage_options')) {
   $allowed_endpoints = array(
     '/wp-json/rk/data',
     '/wp-json/rk/profile',
     '/wp-json/rk/customer',
     '/wp-json/rk/customer/(?P<id>\d+)',
     '/wp-json/rk/invoice/(?P<id>\d+)/toggle-paid',
     '/wp-json/rk/invoice',
     '/wp-json/rk/invoice/(?P<id>\d+)',
     '/wp-json/rk/invoice/(?P<id>\d+)/pdf'
   );

   $current_route = $_SERVER['REQUEST_URI'];

   foreach ($allowed_endpoints as $endpoint) {
     if (strpos($current_route, $endpoint) !== false) {
       return $result;
     }
   }

   return new WP_Error('rest_disabled', __('The REST API on this site is disabled.'), array('status' => 401));
 }

  return $result;
});
