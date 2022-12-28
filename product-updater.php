<?php
/**
 *
 * @link              http://www.iranimij.com
 * @since             1.0.0
 * @package           Product-updater
 *
 * @wordpress-plugin
 * Plugin Name:       Product Updater
 * Plugin URI:        http://www.iranimij.com
 * Description:       Updating product prices by a FTP.
 * Version:           1.0.0
 * Author:            Iman Heydari
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       product-updater
 * Domain Path:       /languages
 */

defined( 'ABSPATH' ) || die();

require_once 'vendor/autoload.php';

/**
 * Check If product-updater Class exists.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
if ( ! class_exists( 'Product_Updater' ) ) {

	/**
	 * product_updater class.
	 *
	 * @since 1.0.0
	 */
	class Product_Updater {

		/**
		 * Class instance.
		 *
		 * @since 1.0.0
		 * @var Product_Updater
		 */
		private static $instance = null;

		/**
		 * The plugin version number.
		 *
		 * @since 1.0.0
		 *
		 * @access private
		 * @var string
		 */
		private static $version;

		/**
		 * The plugin basename.
		 *
		 * @since 1.0.0
		 *
		 * @access private
		 * @var string
		 */
		private static $plugin_basename;

		/**
		 * The plugin name.
		 *
		 * @since 1.0.0
		 *
		 * @access private
		 * @var string
		 */
		private static $plugin_name;

		/**
		 * The plugin directory.
		 *
		 * @since 1.0.0
		 *
		 * @access private
		 * @var string
		 */
		public static $plugin_dir;

		/**
		 * The plugin URL.
		 *
		 * @since 1.0.0
		 *
		 * @access private
		 * @var string
		 */
		private static $plugin_url;

		/**
		 * The plugin assets URL.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @var string
		 */
		public static $plugin_assets_url;

		/**
		 * Get a class instance.
		 *
		 * @since 1.0.0
		 *
		 * @return product_updater Class
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Class constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			$this->define_constants();

			add_action( 'init', [ $this, 'init' ] );
			add_action( 'admin_init', [ $this, 'admin_init' ] );
			add_action( 'admin_menu', [ $this, 'register_admin_menu' ] );

			// Register activation and deactivation hook.
			register_activation_hook( __FILE__, array( $this, 'activation' ) );
			register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );

			if ( ! $this->is_product_updater_screen() ) {
				return;
			}

			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ] );
		}

		/**
		 * Defines constants used by the plugin.
		 *
		 * @since 1.0.0
		 */
		protected function define_constants() {
			$plugin_data = get_file_data( __FILE__, array( 'Plugin Name', 'Version' ), 'product-updater' );

			self::$plugin_basename   = plugin_basename( __FILE__ );
			self::$plugin_name       = array_shift( $plugin_data );
			self::$version           = array_shift( $plugin_data );
			self::$plugin_dir        = trailingslashit( plugin_dir_path( __FILE__ ) );
			self::$plugin_url        = trailingslashit( plugin_dir_url( __FILE__ ) );
			self::$plugin_assets_url = trailingslashit( self::$plugin_url . 'assets' );
		}

		/**
		 * Do some stuff on plugin activation.
		 *
		 * @since  NEXT
		 * @return void
		 */
		public function activation() {
			if ( ! wp_next_scheduled ( 'product_updater_calculate_new_prices' ) ) {
				wp_schedule_event( time(), 'hourly', 'product_updater_calculate_new_prices' );
			}

			if ( ! wp_next_scheduled ( 'product_updater_generate_orders_sheet' ) ) {
				wp_schedule_event( time(), 'daily', 'product_updater_generate_orders_sheet' );
			}
		}

		/**
		 * Do some stuff on plugin activation
		 *
		 * @since  NEXT
		 * @return void
		 */
		public function deactivation() {
			wp_clear_scheduled_hook( 'product_updater_generate_orders_sheet' );
			wp_clear_scheduled_hook( 'product_updater_calculate_new_prices' );
		}

		/**
		 * Initialize admin.
		 *
		 * @since 1.0.0
		 */
		public function admin_init() {
			$this->load_files(
                [
					'admin/settings',
                ]
            );
		}

		/**
		 * Initialize.
		 *
		 * @since 1.0.0
		 */
		public function init() {
			$this->load_files(
				[
					'admin/price-calculator',
					'admin/sheet-generator',
				]
			);

			load_plugin_textdomain( 'product-updater', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}

		/**
		 * Enqueue admin scripts.
		 *
		 * @since 1.0.0
		 */
		public function enqueue_admin_scripts() {
			wp_enqueue_editor();

			wp_enqueue_script(
				'product-updater',
				product_updater()->plugin_url() . 'assets/dist/admin/admin.js',
				[ 'lodash', 'wp-element', 'wp-i18n', 'wp-util' ],
				product_updater()->version(),
				true
			);

			wp_localize_script( 'product-updater', 'productUpdater', [
				'nonce' => wp_create_nonce( 'product-updater' ),
				'productUpdaterOptions' => wp_options_manager()->get(),
			] );

			wp_enqueue_style( 'product-updater', product_updater()->plugin_url() . 'assets/dist/admin/admin.css', [], self::version() );

			wp_set_script_translations( 'product-updater', 'product-updater', product_updater()->plugin_dir() . 'languages' );
		}

		/**
		 * Register admin menu.
		 *
		 * @since 1.0.0
		 * @SuppressWarnings(PHPMD.NPathComplexity)
		 */
		public function register_admin_menu() {
			add_menu_page(
				__( 'Product Updater', 'product-updater' ),
				__( 'Product Updater', 'product-updater' ),
				'manage_options',
				'product-updater/product-updater.php',
				[ $this, 'product_updater_menu' ],
				'dashicons-admin-settings',
				1
			);
		}

		public function product_updater_menu() {
			if ( ! empty( $_POST['update_prices'] ) ) {
				wp_schedule_single_event( time(), 'product_updater_calculate_new_prices' );
			}

			if ( ! empty( $_POST['generate_sheet'] ) ) {
				wp_schedule_single_event( time(), 'product_updater_generate_orders_sheet' );
			}

			require_once product_updater()->plugin_dir() . 'includes/admin/templates/dashboard.php';
		}

		/**
		 * Check if in product updater pages.
		 *
		 * @since 1.0.0
		 *
		 * @return boolean product-updater screen.
		 */
		private function is_product_updater_screen() {
			global $pagenow;

            if ( ! is_admin() ) {
                return false;
            }

			$page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

            if ( isset( $page ) && str_contains( $page, 'product-updater' ) ) {
                return true;
            }

			return false;
		}

		/**
		 * Loads specified PHP files from the plugin includes directory.
		 *
		 * @since 1.0.0
		 *
		 * @param array $file_names The names of the files to be loaded in the includes directory.
		 */
		public function load_files( $file_names = array() ) {
			foreach ( $file_names as $file_name ) {
				$path = self::plugin_dir() . 'includes/' . $file_name . '.php';

				if ( file_exists( $path ) ) {
					require_once realpath( $path );
				}
			}
		}

		/**
		 * Returns the version number of the plugin.
		 *
		 * @since 1.0.0
		 *
		 * @return string
		 */
		public function version() {
			return self::$version;
		}

		/**
		 * Returns the plugin basename.
		 *
		 * @since 1.0.0
		 *
		 * @return string
		 */
		public function plugin_basename() {
			return self::$plugin_basename;
		}

		/**
		 * Returns the plugin name.
		 *
		 * @since 1.0.0
		 *
		 * @return string
		 */
		public function plugin_name() {
			return self::$plugin_name;
		}

		/**
		 * Returns the plugin directory.
		 *
		 * @since 1.0.0
		 *
		 * @return string
		 */
		public function plugin_dir() {
			return self::$plugin_dir;
		}

		/**
		 * Returns the plugin URL.
		 *
		 * @since 1.0.0
		 *
		 * @return string
		 */
		public function plugin_url() {
			return self::$plugin_url;
		}

		/**
		 * Returns the plugin assets URL.
		 *
		 * @since 1.0.0
		 *
		 * @return string
		 */
		public function plugin_assets_url() {
			return self::$plugin_assets_url;
		}
	}
}

if ( ! function_exists( 'product_updater' ) ) {
	/**
	 * Initialize the aiu.
	 *
	 * @since 1.0.0
	 */
	function product_updater() {
		return Product_Updater::get_instance();
	}
}

/**
 * Initialize the aiu application.
 *
 * @since 1.0.0
 */
product_updater();
