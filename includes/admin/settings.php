<?php

namespace Product_Updater\Admin;

defined( 'ABSPATH' ) || die();

class Settings {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Adding a checkbox to single product page in admin.
		add_filter( 'product_type_options', [ $this, 'add_product_type_options' ], 1 );

		// Adding a new tab for woocmmerce.
		add_action( 'woocommerce_settings_tabs', [ $this, 'add_ftp_connector_tab' ] );

		// The setting tab content
		add_action( 'woocommerce_settings_ftp_settings', [ $this, 'display_ftp_settings_tab_content' ] );

		// saving data in single product page.
		add_action( 'save_post', [ $this, 'save_meta_data' ], 20, 1 );
	}

	public function display_ftp_settings_tab_content() {
		$fields = [
			[
				'title' => __( 'FTP details', 'woocommerce' ),
				'type'  => 'title',
				'desc'  => '',
				'id'    => 'ftp_options',
			],
			[
				'title'       => __( 'Server Address', 'product_updater' ),
				'id'          => 'product_update_server_address',
				'type'        => 'text',
				'default'     => '',
				'class'       => '',
				'css'         => '',
			],
			[
				'title'       => __( 'Server Port', 'product_updater' ),
				'id'          => 'product_update_server_port',
				'type'        => 'number',
				'default'     => '',
				'class'       => '',
				'css'         => '',
			],
			[
				'title'       => __( 'Files directory', 'product_updater' ),
				'id'          => 'product_update_files-directory',
				'type'        => 'text',
				'default'     => '',
				'class'       => '',
				'css'         => '',
			],
			[
				'title'       => __( 'username', 'product_updater' ),
				'id'          => 'product_update_ftp_username',
				'type'        => 'text',
				'default'     => '',
				'class'       => '',
				'css'         => '',
			],
			[
				'title'       => __( 'Password', 'product_updater' ),
				'id'          => 'product_update_ftp_password',
				'type'        => 'text',
				'default'     => '',
				'class'       => '',
				'css'         => '',
			],
			[
				'type' => 'sectionend',
				'id'   => 'ftp_options',
			],
		];
		\WC_Admin_Settings::save_fields( $fields );
		\WC_Admin_Settings::output_fields( $fields );
	}

	public function add_ftp_connector_tab() {
		//link to custom tab
		$current_tab = ( isset($_GET['tab']) && $_GET['tab'] === 'ftp_settings' ) ? 'nav-tab-active' : '';
		echo '<a href="admin.php?page=wc-settings&tab=ftp_settings" class="nav-tab '.$current_tab.'">'.__( "FTP", "product-updater" ).'</a>';
	}

	public function save_meta_data( $post_id ) {
		$is_for_export = ! empty( $_POST['_for_export'] ) ? 'yes' : 'no';

		update_post_meta( $post_id, '_for_export', $is_for_export );
	}

	public function add_product_type_options( $options ) {
		$new_options = [
			'for_export' => [
				'id'            => '_for_export',
				'wrapper_class' => 'show_if_simple show_if_variable',
				'label'         => __( 'For Export', 'product_updater' ),
				'description'   => __( 'Is it for export or not, it\'s coming from product updater plugin.', 'product_updater' ),
				'default'       => 'no',
			],
		];

		return array_merge( $new_options, $options );
	}
}

new Settings();