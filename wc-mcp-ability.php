<?php
/**
 * Plugin Name: WooCommerce MCP Ability Demo
 * Plugin URI: https://github.com/woocommerce/wc-mcp-ability
 * Description: Demonstrates how to expose a WooCommerce store ability through the WordPress MCP Adapter.
 * Version: 1.1.0
 * Requires at least: 7.0
 * Requires PHP: 8.0
 * Author: WooCommerce
 * Author URI: https://woocommerce.com/
 * Text Domain: wc-mcp-ability
 * Domain Path: /languages
 * Requires Plugins: woocommerce
 *
 * @package WCAbilitiesDemo
 */

// phpcs:ignoreFile WordPress.Files.FileName.InvalidClassFileName -- This single-file demo keeps its class in the plugin entry file.

declare( strict_types=1 );

namespace WCAbilitiesDemo;

defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce Abilities Demo Plugin.
 *
 * This plugin demonstrates how third-party developers can integrate with the
 * WordPress MCP Adapter by registering an ability with the WordPress Abilities API.
 */
class WCAbilitiesDemo {

	/**
	 * Plugin version.
	 */
	public const VERSION = '1.1.0';

	/**
	 * Initialize the plugin.
	 */
	public static function init(): void {
		add_action( 'wp_abilities_api_categories_init', array( __CLASS__, 'register_ability_category' ) );
		add_action( 'wp_abilities_api_init', array( __CLASS__, 'register_store_info_ability' ) );
	}

	/**
	 * Register the category before abilities are registered.
	 */
	public static function register_ability_category(): void {
		wp_register_ability_category(
			'woocommerce-demo',
			array(
				'label'       => __( 'WooCommerce Demo', 'wc-mcp-ability' ),
				'description' => __( 'Example abilities for WooCommerce stores.', 'wc-mcp-ability' ),
			)
		);
	}

	/**
	 * Register the store info ability with the WordPress Abilities API.
	 *
	 * This demonstrates how third-party plugins can register abilities that
	 * can be discovered through the WordPress MCP Adapter's default server.
	 */
	public static function register_store_info_ability(): void {
		wp_register_ability(
			'woocommerce-demo/store-info',
			array(
				'label'               => __( 'Get Store Information (Demo)', 'wc-mcp-ability' ),
				'description'         => __( 'Retrieves store information and optional statistics from WooCommerce.', 'wc-mcp-ability' ),
				'category'            => 'woocommerce-demo',
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(
						'include_stats' => array(
							'type'        => 'boolean',
							'description' => 'Whether to include basic store statistics (product count, order count, etc.)',
							'default'     => false,
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'store_name'          => array( 'type' => 'string' ),
						'store_url'           => array( 'type' => 'string' ),
						'admin_email'         => array( 'type' => 'string' ),
						'woocommerce_version' => array( 'type' => 'string' ),
						'wordpress_version'   => array( 'type' => 'string' ),
						'currency'            => array( 'type' => 'string' ),
						'country'             => array( 'type' => 'string' ),
						'plugin_source'       => array( 'type' => 'string' ),
						'stats'               => array(
							'type'       => 'object',
							'properties' => array(
								'product_count'   => array( 'type' => 'integer' ),
								'order_count'     => array( 'type' => 'integer' ),
								'order_breakdown' => array(
									'type'       => 'object',
									'properties' => array(
										'completed'  => array( 'type' => 'integer' ),
										'processing' => array( 'type' => 'integer' ),
										'pending'    => array( 'type' => 'integer' ),
										'on-hold'    => array( 'type' => 'integer' ),
										'cancelled'  => array( 'type' => 'integer' ),
										'refunded'   => array( 'type' => 'integer' ),
										'failed'     => array( 'type' => 'integer' ),
									),
								),
								'customer_count'  => array( 'type' => 'integer' ),
							),
						),
					),
					'required'   => array( 'store_name', 'store_url', 'woocommerce_version', 'plugin_source' ),
				),
				'execute_callback'    => array( __CLASS__, 'execute_store_info_ability' ),
				'permission_callback' => array( __CLASS__, 'check_store_info_permission' ),
				'meta'                => array(
					'mcp' => array(
						'public' => true,
						'type'   => 'tool',
					),
				),
			)
		);
	}

	/**
	 * Execute the store info ability.
	 *
	 * @param array $input Input parameters.
	 * @return array Store information.
	 */
	public static function execute_store_info_ability( array $input ): array {
		// Build the basic store information.
		$result = array(
			'store_name'          => get_bloginfo( 'name' ),
			'store_url'           => get_site_url(),
			'admin_email'         => get_bloginfo( 'admin_email' ),
			'woocommerce_version' => WC()->version,
			'wordpress_version'   => get_bloginfo( 'version' ),
			'currency'            => get_woocommerce_currency(),
			'country'             => WC()->countries->get_base_country(),
			'plugin_source'       => 'WooCommerce Abilities Demo Plugin v' . self::VERSION,
		);

		// Include statistics if requested.
		if ( ! empty( $input['include_stats'] ) ) {
			$result['stats'] = self::get_store_statistics();
		}

		return $result;
	}

	/**
	 * Get store statistics.
	 *
	 * @return array Store statistics.
	 */
	private static function get_store_statistics(): array {
		// Count published products.
		$product_count = (int) wp_count_posts( 'product' )->publish;

		// Count orders in the seven standard statuses.
		$completed_count  = wc_orders_count( 'completed' );
		$processing_count = wc_orders_count( 'processing' );
		$pending_count    = wc_orders_count( 'pending' );
		$on_hold_count    = wc_orders_count( 'on-hold' );
		$cancelled_count  = wc_orders_count( 'cancelled' );
		$refunded_count   = wc_orders_count( 'refunded' );
		$failed_count     = wc_orders_count( 'failed' );

		$order_breakdown = array(
			'completed'  => $completed_count,
			'processing' => $processing_count,
			'pending'    => $pending_count,
			'on-hold'    => $on_hold_count,
			'cancelled'  => $cancelled_count,
			'refunded'   => $refunded_count,
			'failed'     => $failed_count,
		);

		$order_count = array_sum( $order_breakdown );

		// Count users with the customer role.
		$users_counts   = count_users();
		$customer_count = isset( $users_counts['avail_roles']['customer'] ) ? (int) $users_counts['avail_roles']['customer'] : 0;

		return array(
			'product_count'   => $product_count,
			'order_count'     => $order_count,
			'order_breakdown' => $order_breakdown,
			'customer_count'  => $customer_count,
		);
	}

	/**
	 * Check permission for the store info ability.
	 *
	 * @return bool Whether user has permission.
	 */
	public static function check_store_info_permission(): bool {
		// Match the WooCommerce system status endpoint's capability check.
		// phpcs:ignore WordPress.WP.Capabilities.Unknown -- WooCommerce registers the manage_woocommerce capability.
		return current_user_can( 'manage_woocommerce' );
	}
}

// Initialize the plugin.
WCAbilitiesDemo::init();
