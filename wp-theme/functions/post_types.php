<?php

function rk_post_types()
{
  $base_args = [
    'public' => false,
    'publicly_queryable' => false,
    'has_archive' => false,
    'exclude_from_search' => true,
    'show_in_rest' => true,
    'show_ui' => false,
    'show_in_menu' => false,
    'supports' => ['title'],
  ];

  register_post_type('invoice', array_merge($base_args, [
    'labels' => [
      'name' => 'Faktury',
      'singular_name' => 'Faktura',
    ],
    'menu_icon' => 'dashicons-clipboard',
  ]));

  register_post_type('customer', array_merge($base_args, [
    'labels' => [
      'name' => 'Zákazníci',
      'singular_name' => 'Zákazník',
    ],
    'menu_icon' => 'dashicons-buddicons-buddypress-logo',
  ]));
}
add_action('init', 'rk_post_types');