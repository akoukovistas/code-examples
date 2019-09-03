<?php
defined( 'ABSPATH' ) or die();

/**
 * @package Script_Injector
 * @version 4.24.1

Plugin Name: Script Injector
Plugin URI: https://hallaminternet.com
Description: SCRIPT INJECTOR TM - As seen on TV
Author: Alexander at Hallam
Version: 4.24
Author URI: https://hallaminternet.com
Text Domain: script-injector
*/

// Define constants :) .
define( 'SIN_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'SIN_PLUGIN_NAME', trim( dirname( SIN_PLUGIN_BASENAME ), '/' ) );
define( 'SIN_PLUGIN_DIR', untrailingslashit( dirname( __FILE__ ) ) );
define( 'SIN_PLUGIN_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) );

// Include script paths.
include SIN_PLUGIN_DIR . '/inc/functions.php';
include SIN_PLUGIN_DIR . '/inc/hooks.php';

// Register the admin stuff.
if ( is_admin() ) {
	include SIN_PLUGIN_DIR . '/admin/inc/hooks.php';
	include SIN_PLUGIN_DIR . '/admin/inc/functions.php';
}
