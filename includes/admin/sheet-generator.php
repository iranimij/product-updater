<?php

namespace Product_Updater\Admin;

use Shuchkin\SimpleXLSXGen;

defined( 'ABSPATH' ) || die();

class Sheet_Generator {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'product_updater_generate_orders_sheet', [ $this, 'generate_orders_sheet' ] );
	}

	public function generate_orders_sheet() {
		if ( ! file_exists( product_updater()->plugin_dir() . 'files' ) ) {
			mkdir( product_updater()->plugin_dir() . 'files', 0777, true );
		}

		$args = [
			'post_type' => 'product',
			'posts_per_page' => -1,
			'meta_key' => 'total_sales',
			'orderby' => 'meta_value_num',
			'order' => 'DESC',
			'meta_query' => [
				'relation' => 'and',
				[
					'key'       => 'total_sales',
					'value'     => 0,
					'compare' => '>',
					'type' => 'NUMERIC',
				]
				,[
					'key'     => '_for_export',
					'value'   => 'yes',
					'compare' => '=',
				]
			]
		];

		$query = new \WP_Query( $args );

		$data = [];

		foreach ( $query->posts as $post ) {
			$product = wc_get_product( $post->ID );
			$data[] = [
				'Product SKU' => $product->get_sku(),
				'Product Title' => $product->get_title(),
				'Product Price' => $product->get_regular_price(),
				'Sold Item quantity' => get_post_meta( $post->ID, 'total_sales', true ),
			];
		}

		if ( file_exists( product_updater()->plugin_dir() . 'files/sales.xlsx' ) ) {
			unlink( product_updater()->plugin_dir() . 'files/sales.xlsx' );
		}

		SimpleXLSXGen::fromArray( $data )->saveAs( product_updater()->plugin_dir() . 'files/sales.xlsx' );
	}
}

new Sheet_Generator();