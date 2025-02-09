<?php

function rk_register_required_plugins()
{
	$plugins = array(
		array(
			'name'      => 'Advanced Custom Fields',
			'slug'      => 'advanced-custom-fields',
			'required'           => true,
			'force_activation'   => true
		) 
	);

	$config = array(
		'id'           => 'frakturuj',
		'default_path' => '',
		'menu'         => 'tgmpa-install-plugins',
		'parent_slug'  => 'themes.php',
		'capability'   => 'edit_theme_options',
		'has_notices'  => true,
		'dismissable'  => false,
		'dismiss_msg'  => '',
		'is_automatic' => true,
		'message'      => '',
	);

	tgmpa($plugins, $config);
}
add_action('tgmpa_register', 'rk_register_required_plugins');
