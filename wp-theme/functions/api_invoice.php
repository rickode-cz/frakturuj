<?php

function rk_toggle_invoice_paid($request) {
 $invoice_id = $request['id'];
 $user_id = get_current_user_id();
 $post = get_post($invoice_id);
 
 // Check if invoice exists and belongs to current user
 $post = get_post($invoice_id);

 if (!$post || $post->post_type !== 'invoice') {
     return new WP_Error('not_found', 'Faktura nenalezena', array('status' => 404));
 }

 if ($post->post_author != $user_id) {
     return new WP_Error('unauthorized', 'Nemáte oprávnění upravit tuto fakturu', array('status' => 403));
 }

 // Get current paid status and toggle it
 $current_status = get_field('paid', $invoice_id);
 $new_status = !$current_status;
 
 // Update the paid status
 update_field('paid', $new_status, $invoice_id);

 return new WP_REST_Response(array(
     'status' => 'success',
     'invoice' => array(
         'id' => $invoice_id,
         'title' => get_the_title($invoice_id),
         'created_date' => get_the_date('Y-m-d H:i:s', $invoice_id),
         'acf' => get_fields($invoice_id)
     )
 ), 200);
}

function rk_get_next_invoice_number() {
 $year = date('Y');
 
 // Get all invoices from current year
 $invoices = get_posts(array(
     'post_type' => 'invoice',
     'posts_per_page' => -1,
     'post_status' => 'publish',
     'date_query' => array(
         array(
             'year' => $year,
         ),
     ),
 ));

 return sprintf('%d-%02d', $year, count($invoices) + 1);
}

function rk_create_invoice($request) {
 $params = $request->get_json_params();
 $user_id = get_current_user_id();
 
 if (empty($params['customer'])) {
     return new WP_Error('missing_fields', 'Chybí povinné údaje', array('status' => 400));
 }

 // Auto-generate invoice number if not provided
 if (empty($params['title'])) {
     $params['title'] = rk_get_next_invoice_number();
 }

 $post_data = array(
     'post_title' => sanitize_text_field($params['title']),
     'post_type' => 'invoice',
     'post_status' => 'publish',
     'post_author' => $user_id
 );

 // Add custom post date if provided
 if (!empty($params['created_date'])) {
     $post_data['post_date'] = $params['created_date'];
     $post_data['post_date_gmt'] = get_gmt_from_date($params['created_date']);
 }

 $post_id = wp_insert_post($post_data);

 if (is_wp_error($post_id)) {
     return new WP_Error('insert_failed', 'Nepodařilo se vytvořit fakturu', array('status' => 500));
 }

 // Save ACF fields
 $fields = array('customer', 'items', 'maturity');
 foreach ($fields as $field) {
     if (isset($params[$field])) {
         update_field($field, $params[$field], $post_id);
     }
 }

 // Initialize as unpaid
 update_field('paid', false, $post_id);

 return new WP_REST_Response(array(
     'status' => 'success',
     'invoice' => array(
         'id' => $post_id,
         'title' => $params['title'],
         'created_date' => get_the_date('Y-m-d H:i:s', $post_id),
         'acf' => get_fields($post_id)
     )
 ), 200);
}

function rk_update_invoice($request) {
 $invoice_id = $request['id'];
 $params = $request->get_json_params();
 $user_id = get_current_user_id();
 
 // Check if invoice exists and belongs to current user
 $post = get_post($invoice_id);
 if (!$post || $post->post_type !== 'invoice') {
     return new WP_Error('not_found', 'Faktura nenalezena', array('status' => 404));
 }

 if ($post->post_author != $user_id) {
     return new WP_Error('unauthorized', 'Nemáte oprávnění upravit tuto fakturu', array('status' => 403));
 }

 if (empty($params['title']) || empty($params['customer'])) {
     return new WP_Error('missing_fields', 'Chybí povinné údaje', array('status' => 400));
 }

 $post_data = array(
     'ID' => $invoice_id,
     'post_title' => sanitize_text_field($params['title'])
 );

 // Add custom post date if provided
 if (!empty($params['created_date'])) {
     $post_data['post_date'] = $params['created_date'];
     $post_data['post_date_gmt'] = get_gmt_from_date($params['created_date']);
 }

 wp_update_post($post_data);

 $fields = array('customer', 'items', 'maturity');
 foreach ($fields as $field) {
     if (isset($params[$field])) {
         update_field($field, $params[$field], $invoice_id);
     }
 }

 return new WP_REST_Response(array(
     'status' => 'success',
     'invoice' => array(
         'id' => $invoice_id,
         'title' => get_the_title($invoice_id),
         'created_date' => get_the_date('Y-m-d H:i:s', $invoice_id),
         'acf' => get_fields($invoice_id)
     )
 ), 200);
}

function rk_delete_invoice($request) {
 $invoice_id = $request['id'];
 $user_id = get_current_user_id();
 
 // Check if invoice exists and belongs to current user
 $post = get_post($invoice_id);
 if (!$post || $post->post_type !== 'invoice') {
     return new WP_Error('not_found', 'Faktura nenalezena', array('status' => 404));
 }

 if ($post->post_author != $user_id) {
     return new WP_Error('unauthorized', 'Nemáte oprávnění smazat tuto fakturu', array('status' => 403));
 }

 if (!wp_delete_post($invoice_id, true)) {
     return new WP_Error('delete_failed', 'Nepodařilo se smazat fakturu', array('status' => 500));
 }

 return new WP_REST_Response(array(
     'status' => 'success',
     'message' => 'Faktura byla smazána'
 ), 200);
}