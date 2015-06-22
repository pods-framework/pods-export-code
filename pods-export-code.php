<?php
/**
 * Plugin Name:       Pods Export to Code
 * Plugin URI:        http://pods.io/
 * Description:       Pods Export to Code
 * Version:           0.9
 * Author:            Pods Framework Team
 * Author URI:        http://pods.io/about/
 *
 * Copyright 2013-2014  Pods Foundation, Inc  (email : contact@podsfoundation.org)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'PODS_EXPORT_TO_CODE_DIR', plugin_dir_path( __FILE__ ) );

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/
require_once PODS_EXPORT_TO_CODE_DIR . 'public/class-pods-export-code.php';

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 */
register_activation_hook( __FILE__, array( 'Pods_Export_Code', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Pods_Export_Code', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'Pods_Export_Code', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

if ( is_admin() ) {
	require_once PODS_EXPORT_TO_CODE_DIR . 'admin/classes/pods-export-code-object.php';
	require_once PODS_EXPORT_TO_CODE_DIR . 'admin/classes/pods-export-post-object.php';
	require_once PODS_EXPORT_TO_CODE_DIR . 'admin/classes/pods-export-pods.php';
	require_once PODS_EXPORT_TO_CODE_DIR . 'admin/classes/pods-export-code-api.php';
	require_once PODS_EXPORT_TO_CODE_DIR . 'admin/class-pods-export-code.php';

	add_action( 'plugins_loaded', array( 'Pods_Export_Code_Admin', 'get_instance' ) );
}