<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Wp_Amqp_Producer
 * @subpackage Wp_Amqp_Producer/includes
 * @author     outisnemo <hello@outisnemo.com>
 */
class Wp_Amqp_Producer {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Wp_Amqp_Producer_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

	/**
	 * Define the core functionality of the plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'WP_AMQP_PRODUCER_VERSION' ) ) {
			$this->version = WP_AMQP_PRODUCER_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'wp-amqp-producer';

		if(!defined('AMQP_URL')) {
            throw new \Exception('AMQP_URL must be defined in the wp-config.php file.');
        }

		$this->load_dependencies();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-amqp-producer-loader.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wp-amqp-producer-public.php';

		$this->loader = new Wp_Amqp_Producer_Loader();

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		$plugin_public = new Wp_Amqp_Producer_Public(
            $this->get_const('AMQP_URL'),
            $this->get_const('AMQP_EXCHANGE'),
            $this->get_const('AMQP_DELIVERY_MODE'),
            $this->get_const('AMQP_EXTRA_HEADERS'),
            $this->get_const('AMQP_ALLOWED_TYPES'),
            $this->get_const('AMQP_OUTPUT_FIELDS')
        );

		$this->loader->add_action( 'save_post', $plugin_public, 'save_post_callback', 90, 3);
		$this->loader->add_action( 'shutdown', $plugin_public, 'shutdown_callback', 10, 0 );
	}

	private function get_const($name) {
	    return defined($name) ? constant($name) : null;
    }
}
