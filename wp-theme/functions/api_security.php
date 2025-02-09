<?php
function rk_check_api_permission() {
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        return true;
    }

    $nonce = $_SERVER['HTTP_X_WP_NONCE'] ?? '';
    if (!wp_verify_nonce($nonce, 'wp_rest')) {
        return new WP_Error('invalid_nonce', 'Invalid or expired security token', array('status' => 401));
    }

    if (!rk_check_auth()) {
        return new WP_Error('rest_forbidden', 'Not authenticated', array('status' => 401));
    }

    return true;
}

function rk_check_auth() {
 $user_id = get_current_user_id();
 return $user_id && user_can($user_id, 'edit_posts');
}

function rk_refresh_nonce() {
    return array(
        'nonce' => wp_create_nonce('wp_rest')
    );
}

function rk_check_nonce_only() {
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        return true;
    }

    $nonce = $_SERVER['HTTP_X_WP_NONCE'] ?? '';
    return wp_verify_nonce($nonce, 'wp_rest');
}