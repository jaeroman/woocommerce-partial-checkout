<?php

defined('ABSPATH') || exit;

/**
 * WCPC Loader Class
 *
 * Initializes the plugin by including necessary files and setting up hooks.
 */
class WCPC_Loader
{
    /**
     * Initializes the plugin by calling include and hook functions.
     *
     * @return void
     */
    public static function init()
    {
        self::includes();
        self::hooks();
    }

    /**
     * Includes required class files.
     *
     * @return void
     */
    private static function includes()
    {
        require_once WCPC_PLUGIN_DIR . 'includes/class-wcpc-cart.php';
        require_once WCPC_PLUGIN_DIR . 'includes/class-wcpc-checkout.php';
    }

    /**
     * Registers necessary hooks.
     *
     * @return void
     */
    private static function hooks()
    {
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueueScripts']);
    }
    
    /**
     * Enqueues frontend scripts and styles, and localizes script settings.
     *
     * @return void
     */
    public static function enqueueScripts()
    {
        wp_enqueue_script(
            'wcpc-frontend',
            WCPC_PLUGIN_URL . 'assets/js/wcpc-frontend.js',
            ['jquery'],
            WCPC_VERSION,
            true
        );

        wp_enqueue_style(
            'wcpc-style',
            WCPC_PLUGIN_URL . 'assets/css/wcpc-style.css',
            [],
            WCPC_VERSION
        );

        wp_localize_script('wcpc-frontend', 'wcpc_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
        ]);
    
        $wcpc_settings = [
            'enable_partial_checkout' => get_option('wcpc_enable_partial_checkout', 'no') === 'yes' ? 'yes' : 'no',
            'webhook_url' => get_option('wcpc_webhook_url', ''),
        ];
    
        // Pass settings to JavaScript
        wp_localize_script('wcpc-frontend', 'wcpc_settings', $wcpc_settings);
    }
}
