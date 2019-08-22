<?php

/**
 * @wordpress-plugin
 * Plugin Name:       WP AMQP Producer
 * Plugin URI:        https://github.com/OutisNemo/wp-amqp-producer
 * Description:       A WordPress plugin for emitting AMQP messages for post create, update, and delete events.
 * Version:           1.0.0
 * Author:            outisnemo
 * Author URI:        https://github.com/OutisNemo
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
define( 'WP_AMQP_PRODUCER_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wp-amqp-producer-activator.php
 */
function activate_wp_amqp_producer() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-amqp-producer-activator.php';
	Wp_Amqp_Producer_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wp-amqp-producer-deactivator.php
 */
function deactivate_wp_amqp_producer() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-amqp-producer-deactivator.php';
	Wp_Amqp_Producer_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wp_amqp_producer' );
register_deactivation_hook( __FILE__, 'deactivate_wp_amqp_producer' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-wp-amqp-producer.php';

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function run_wp_amqp_producer() {

	$plugin = new Wp_Amqp_Producer();
	$plugin->run();

}
run_wp_amqp_producer();
