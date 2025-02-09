<?php
function rk_create_customer($request) {
 $params = $request->get_json_params();
 $user_id = get_current_user_id();
 
 // Check required fields
 if (empty($params['title'])) {
     return new WP_Error('missing_title', 'Název je povinný', array('status' => 400));
 }

 // Check for duplicates by name
 $existing_name = get_posts(array(
     'post_type' => 'customer',
     'title' => $params['title'],
     'post_status' => 'publish',
     'numberposts' => 1
 ));

 if (!empty($existing_name)) {
     return new WP_Error('duplicate_name', 'Zákazník s tímto názvem již existuje', array('status' => 400));
 }

 // Check for duplicate ICO if provided
 if (!empty($params['ico'])) {
     $existing_ico = get_posts(array(
         'post_type' => 'customer',
         'meta_key' => 'ico',
         'meta_value' => $params['ico'],
         'post_status' => 'publish',
         'numberposts' => 1
     ));

     if (!empty($existing_ico)) {
         return new WP_Error('duplicate_ico', 'Zákazník s tímto IČO již existuje', array('status' => 400));
     }
 }

 // Create customer post
 $post_data = array(
     'post_title' => sanitize_text_field($params['title']),
     'post_type' => 'customer',
     'post_status' => 'publish',
     'post_author' => $user_id
 );

 $post_id = wp_insert_post($post_data);

 if (is_wp_error($post_id)) {
     return new WP_Error('insert_failed', 'Nepodařilo se vytvořit zákazníka', array('status' => 500));
 }

 // Save ACF fields
 $fields = array('ico', 'dic', 'street', 'city', 'psc', 'country', 'email');
 foreach ($fields as $field) {
     if (isset($params[$field])) {
         update_field($field, sanitize_text_field($params[$field]), $post_id);
     }
 }

 return new WP_REST_Response(array(
     'status' => 'success',
     'customer' => array(
         'id' => $post_id,
         'title' => $params['title'],
         'created_date' => get_the_date('Y-m-d H:i:s', $post_id),
         'acf' => get_fields($post_id)
     )
 ), 200);
}

function rk_update_customer($request) {
 $customer_id = $request['id'];
 $params = $request->get_json_params();
 $user_id = get_current_user_id();
 
 // Check if customer exists and belongs to current user
 $post = get_post($customer_id);
 if (!$post || $post->post_type !== 'customer') {
     return new WP_Error('not_found', 'Zákazník nenalezen', array('status' => 404));
 }
 
 if ($post->post_author != $user_id) {
     return new WP_Error('unauthorized', 'Nemáte oprávnění upravit tohoto zákazníka', array('status' => 403));
 }

 // Check required fields
 if (empty($params['title'])) {
     return new WP_Error('missing_title', 'Název je povinný', array('status' => 400));
 }

 // Check for duplicates by name (excluding current customer)
 $existing_name = get_posts(array(
     'post_type' => 'customer',
     'title' => $params['title'],
     'post__not_in' => array($customer_id),
     'post_status' => 'publish',
     'numberposts' => 1
 ));

 if (!empty($existing_name)) {
     return new WP_Error('duplicate_name', 'Zákazník s tímto názvem již existuje', array('status' => 400));
 }

 // Check for duplicate ICO if provided (excluding current customer)
 if (!empty($params['ico'])) {
     $existing_ico = get_posts(array(
         'post_type' => 'customer',
         'meta_key' => 'ico',
         'meta_value' => $params['ico'],
         'post__not_in' => array($customer_id),
         'post_status' => 'publish',
         'numberposts' => 1
     ));

     if (!empty($existing_ico)) {
         return new WP_Error('duplicate_ico', 'Zákazník s tímto IČO již existuje', array('status' => 400));
     }
 }

 // Update customer post
 $post_data = array(
     'ID' => $customer_id,
     'post_title' => sanitize_text_field($params['title'])
 );

 wp_update_post($post_data);

 // Update ACF fields
 $fields = array('ico', 'dic', 'street', 'city', 'psc', 'country', 'email');
 foreach ($fields as $field) {
     if (isset($params[$field])) {
         update_field($field, sanitize_text_field($params[$field]), $customer_id);
     }
 }

 // Return formatted customer data using the same format as in rk_api_data
 $customer = array(
     'id' => $customer_id,
     'title' => get_the_title($customer_id),
     'created_date' => get_the_date('Y-m-d H:i:s', $customer_id),
     'acf' => get_fields($customer_id)
 );

 return new WP_REST_Response(array(
     'status' => 'success',
     'customer' => $customer
 ), 200);
}

function rk_delete_customer($request) {
 $customer_id = $request['id'];
 $user_id = get_current_user_id();
 
 // Check if customer exists and belongs to current user
 $post = get_post($customer_id);
 if (!$post || $post->post_type !== 'customer') {
     return new WP_Error('not_found', 'Zákazník nenalezen', array('status' => 404));
 }

 if ($post->post_author != $user_id) {
     return new WP_Error('unauthorized', 'Nemáte oprávnění smazat tohoto zákazníka', array('status' => 403));
 }

 // Find all invoices linked to this customer
 $invoices = get_posts(array(
     'post_type' => 'invoice',
     'posts_per_page' => -1,
     'meta_key' => 'customer',
     'meta_value' => $customer_id
 ));

 // Delete all linked invoices
 foreach ($invoices as $invoice) {
     wp_delete_post($invoice->ID, true);
 }

 // Delete the customer
 wp_delete_post($customer_id, true);

 return new WP_REST_Response(array(
     'status' => 'success',
     'message' => 'Zákazník a jeho faktury byly smazány',
     'deleted_invoices' => count($invoices)
 ), 200);
}