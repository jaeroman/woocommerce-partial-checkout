<?php
/**
 * Plugin Name: WooCommerce Partial Checkout
 * Description: Allows customers to select specific items for checkout in WooCommerce.
 * Version:     1.0.0
 * Author:      Jaerome Roman
 * License:     GPL-2.0+
 * Text Domain: wcpc
 */

defined('ABSPATH') || exit;

// Define Plugin Constants
define('WCPC_VERSION', '1.0.0');
define('WCPC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WCPC_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include the main loader file.
require_once WCPC_PLUGIN_DIR . 'includes/class-wcpc-loader.php';

/**
 * Adds the Partial Checkout settings page under WooCommerce settings.
 *
 * @param array $settings_tabs Existing WooCommerce settings tabs.
 * @return array Modified settings tabs.
 */
function wcpc_add_settings_page($settings_tabs)
{
    $settings_tabs['wcpc_partial_checkout'] = __('Checkout', 'wcpc');
    return $settings_tabs;
}
add_filter('woocommerce_settings_tabs_array', 'wcpc_add_settings_page', 50);

/**
 * Displays the Partial Checkout settings page.
 *
 * @return void
 */
function wcpc_settings_page()
{
    woocommerce_admin_fields(get_wcpc_settings());
}
add_action('woocommerce_settings_tabs_wcpc_partial_checkout', 'wcpc_settings_page');

/**
 * Saves the Partial Checkout settings.
 *
 * @return void
 */
function wcpc_update_settings()
{
    woocommerce_update_options(get_wcpc_settings());
}
add_action('woocommerce_update_options_wcpc_partial_checkout', 'wcpc_update_settings');

/**
 * Retrieves the settings fields for Partial Checkout.
 *
 * @return array Settings fields array.
 */
function get_wcpc_settings()
{
    return [
        'section_title' => [
            'name'     => __('Partial Checkout Settings', 'wcpc'),
            'type'     => 'title',
            'desc'     => 'Enable or disable the Partial Checkout plugin and set a webhook URL.',
            'id'       => 'wcpc_settings_section_title'
        ],
        'enable_partial_checkout' => [
            'name'     => __('Enable Partial Checkout', 'wcpc'),
            'type'     => 'checkbox',
            'desc'     => __('Enable partial checkout functionality.', 'wcpc'),
            'id'       => 'wcpc_enable_partial_checkout'
        ],
        'webhook_url' => [
            'name'     => __('Webhook URL', 'wcpc'),
            'type'     => 'text',
            'desc'     => __('Enter the webhook URL to receive checkout data.', 'wcpc'),
            'id'       => 'wcpc_webhook_url',
            'css'      => 'min-width:300px;',
            'placeholder' => 'https://your-webhook-url.com'
        ],
        'save_settings' => [
            'type'     => 'sectionend',
            'id'       => 'wcpc_settings_section_end'
        ]
    ];
}



/**
 * Initializes the plugin by calling the loader class.
 *
 * @return void
 */
function wcpc_initialize_plugin()
{
    WCPC_Loader::init();
}
add_action('plugins_loaded', 'wcpc_initialize_plugin');
