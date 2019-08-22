<?php
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Wp_Amqp_Producer
 * @subpackage Wp_Amqp_Producer/includes
 * @author     OutisNemo <hello@outisnemo.com>
 */
class Wp_Amqp_Producer_Activator {
	public static function activate() {
        if (!defined('AMQP_URL')) {
            throw new \Exception('AMQP_URL must be defined in wp-config.php file');
        }

        if (!defined('AMQP_EXCHANGE')) {
            throw new \Exception('AMQP_EXCHANGE must be defined in wp-config.php file');
        }
	}
}
