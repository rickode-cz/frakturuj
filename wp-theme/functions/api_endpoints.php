<?php

add_action('rest_api_init', function () {
    register_rest_route('rk', '/data', array(
        'methods' => 'GET',
        'callback' => 'rk_api_data',
        'permission_callback' => 'rk_check_api_permission'
    ));

    register_rest_route('rk', '/profile', array(
        'methods' => 'POST',
        'callback' => 'rk_save_profile',
        'permission_callback' => 'rk_check_api_permission'
    ));

    register_rest_route('rk', '/customer', array(
        'methods' => 'POST',
        'callback' => 'rk_create_customer',
        'permission_callback' => 'rk_check_api_permission'
    ));

    register_rest_route('rk', '/customer/(?P<id>\d+)', array(
        array(
            'methods' => 'PUT',
            'callback' => 'rk_update_customer',
            'permission_callback' => 'rk_check_api_permission'
        ),
        array(
            'methods' => 'DELETE',
            'callback' => 'rk_delete_customer',
            'permission_callback' => 'rk_check_api_permission'
        )
    ));

    register_rest_route('rk', '/invoice/(?P<id>\d+)/toggle-paid', array(
        'methods' => 'POST',
        'callback' => 'rk_toggle_invoice_paid',
        'permission_callback' => 'rk_check_api_permission'
    ));

    register_rest_route('rk', '/invoice', array(
        'methods' => 'POST',
        'callback' => 'rk_create_invoice',
        'permission_callback' => 'rk_check_api_permission'
    ));

    register_rest_route('rk', '/invoice/(?P<id>\d+)', array(
        array(
            'methods' => 'PUT',
            'callback' => 'rk_update_invoice',
            'permission_callback' => 'rk_check_api_permission'
        ),
        array(
            'methods' => 'DELETE',
            'callback' => 'rk_delete_invoice',
            'permission_callback' => 'rk_check_api_permission'
        )
    ));

    register_rest_route('rk', '/invoice/(?P<id>\d+)/pdf', array(
        'methods' => 'GET',
        'callback' => 'rk_generate_invoice_pdf',
        'permission_callback' => 'rk_check_api_permission'
    ));

    register_rest_route('rk', '/refresh-nonce', array(
     'methods' => 'GET',
     'callback' => 'rk_refresh_nonce',
     'permission_callback' => 'rk_check_nonce_only'
   ));
});
