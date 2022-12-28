<?php

namespace Product_Updater\Admin;

defined( 'ABSPATH' ) || die();

class Price_Calculator {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
//		update_post_meta( 42, 'the_price_is_updated', false );
//		var_dump(get_post_meta(34,'the_price_is_updated',true));die();
		add_action( 'product_updater_calculate_new_prices', [ $this, 'calculate_new_prices' ] );
	}

	public function calculate_new_prices() {
		$ftp_server    = \WC_Admin_Settings::get_option( 'product_update_server_address', 'iranimij.com');
		$port          = \WC_Admin_Settings::get_option( 'product_update_server_port' , 21 );
		$conn_id       = ftp_connect( $ftp_server, $port );
		$ftp_user_name = \WC_Admin_Settings::get_option( 'product_update_ftp_username' );
		$ftp_user_pass = \WC_Admin_Settings::get_option( 'product_update_ftp_password' );
		$directory     = \WC_Admin_Settings::get_option( 'product_update_files-directory' );
		$login_result  = ftp_login( $conn_id, $ftp_user_name, $ftp_user_pass );

		if ( empty( $login_result ) ) {
			return new \WP_Error( 'something_went_wrong_in_updating', __( 'Something went wrong', 'product-updater' ) );
		}

		$contents      = ftp_nlist( $conn_id, $directory );

		foreach ( $contents as $content ) {
			if ( $directory . '/.' !== $content && $directory . '/..' !== $content ) {
				$content = file_get_contents( 'ftp://' . $ftp_user_name . ':' . $ftp_user_pass . '@'. $ftp_server . $content );

				if ( empty( $content ) ) {
					continue;
				}

				$this->update_product_price( $content );
			}
		}

		ftp_close( $conn_id );
	}

	public function update_product_price( $file_content ) {
		$file_data = explode( ';', $file_content );
		$product   = wc_get_product_id_by_sku( $file_data[0] );
		$product   = wc_get_product( $product );

		if ( is_bool( $product ) ) {
			return false;
		}

		if ( ! empty( $product->get_meta( 'the_price_is_updated' ) ) ) {
			return false;
		}

		if ( 'variable' === $product->get_type() ) {
			foreach ( $product->get_children() as $child ) {
				$variation = wc_get_product( $child );

				if ( ! empty( $variation->get_meta( 'the_price_is_updated' ) ) ) {
					continue;
				}

				$variation->set_regular_price( $this->calculate_regular_price( $file_data[1], $file_data[2] ) );
				$variation->update_meta_data( 'the_price_is_updated', true );
				$variation->save();
			}
		}

		if ( 'simple' === $product->get_type() ) {
			$product->set_regular_price( $this->calculate_regular_price( $file_data[1], $file_data[2] ) );
			$product->update_meta_data( 'the_price_is_updated', true );
			$product->save();
		}
	}

	/**
	 * Calculates product price.
	 *
	 * @return integer
	 */
	public function calculate_regular_price( $price, $benefit_margin ) {
		return $price + ( $price * ( $benefit_margin / 100 ) );
	}

}

new Price_Calculator();