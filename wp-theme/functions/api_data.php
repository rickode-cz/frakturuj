<?php

function rk_api_data() {   
 $user_id = get_current_user_id();

 // Get invoices ordered by date desc
 $invoices = get_posts(array(
     'post_type' => 'invoice',
     'posts_per_page' => -1,
     'post_status' => 'publish',
     'author' => $user_id,
     'orderby' => array(
         'date' => 'DESC',
         'ID' => 'DESC'  // Secondary sort by ID for same dates
     )
 ));

 // Get customers ordered by title
 $customers = get_posts(array(
     'post_type' => 'customer',
     'posts_per_page' => -1,
     'post_status' => 'publish',
     'author' => $user_id,
     'orderby' => 'title',
     'order' => 'ASC'
 ));

 $formatted_invoices = array_map(function($post) {
     $acf_fields = get_fields($post->ID);
     return array(
         'id' => $post->ID,
         'title' => $post->post_title,
         'created_date' => $post->post_date,
         'acf' => $acf_fields
     );
 }, $invoices);

 $formatted_customers = array_map(function($post) {
     $acf_fields = get_fields($post->ID);
     return array(
         'id' => $post->ID,
         'title' => $post->post_title,
         'created_date' => $post->post_date,
         'acf' => $acf_fields
     );
 }, $customers);

 $response = array(
     'status' => 'success',
     'invoices' => $formatted_invoices,
     'customers' => $formatted_customers,
     'profile' => rk_get_profile(),
     'wp_logout_url' => wp_logout_url('/')
 );

 return new WP_REST_Response($response, 200);
}

function rk_save_profile($request) {    
 $params = $request->get_json_params();
 $user_id = get_current_user_id();

 if (!$user_id) {
     return new WP_Error('not_found', 'User not found', array('status' => 404));
 }

 $fields = array(
     'title', 'ico', 'dic', 'street', 'city', 'psc',
     'country', 'email', 'phone', 'bank_account', 'bank_code',
     'invoice_text'
 );

 foreach ($fields as $field) {
     if (isset($params[$field])) {
         update_option("rk_profile_{$user_id}_{$field}", sanitize_text_field($params[$field]));
     }
 }

 return new WP_REST_Response(array(
     'status' => 'success',
     'profile' => rk_get_profile()
 ), 200);
}

function rk_get_profile() {
 $user_id = get_current_user_id();
 
 if (!$user_id) {
     return array_fill_keys([
         'title', 'ico', 'dic', 'street', 'city', 'psc',
         'country', 'email', 'phone', 'bank_account', 'bank_code',
         'invoice_text', 'user_id'
     ], '');
 }

 return array(
     'title' => get_option("rk_profile_{$user_id}_title", ''),
     'ico' => get_option("rk_profile_{$user_id}_ico", ''),
     'dic' => get_option("rk_profile_{$user_id}_dic", ''),
     'street' => get_option("rk_profile_{$user_id}_street", ''),
     'city' => get_option("rk_profile_{$user_id}_city", ''),
     'psc' => get_option("rk_profile_{$user_id}_psc", ''),
     'country' => get_option("rk_profile_{$user_id}_country", 'Česká republika'),
     'email' => get_option("rk_profile_{$user_id}_email", ''),
     'phone' => get_option("rk_profile_{$user_id}_phone", ''),
     'bank_account' => get_option("rk_profile_{$user_id}_bank_account", ''),
     'bank_code' => get_option("rk_profile_{$user_id}_bank_code", ''),
     'invoice_text' => get_option("rk_profile_{$user_id}_invoice_text", 'Fyzická osoba zapsaná v živnostenském rejstříku.')
 );
}
