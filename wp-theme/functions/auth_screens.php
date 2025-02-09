<?php

// Restrict WP admin access
add_action('init', function() {
    if (!current_user_can('manage_options') && is_admin()) {
        wp_redirect(home_url());
        exit;
    }
});

add_filter('logout_redirect', 'get_home_url');

// Customize login logo URL and title
add_filter('login_headerurl', function() {
    return home_url();
});

add_filter('login_headertext', function() {
    return 'Frakturuj';
});

// Add custom login styles
add_action('login_enqueue_scripts', function() {
    wp_enqueue_style('custom-login', get_template_directory_uri() . '/assets/login.css');
    
    // Add Google Fonts
    wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@400;500;600&display=swap', array(), null);
});

add_action('wp_die_handler', function($handler) {
 echo '<style>
        #error-page {
            min-width: 100vw;
            min-height: 100vh;
            font-family: \'Source Sans 3\', sans-serif;
            background: #18171c;
            margin: 0 !important;
            padding: 0;
            border: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .wp-die-message, p {
          color: #fff;
          max-width: 65ch;
          font-size: 24px !important;
          text-align: center;
        }
    </style>';

return $handler;
});