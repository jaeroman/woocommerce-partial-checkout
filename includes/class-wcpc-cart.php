<?php

defined('ABSPATH') || exit;

/**
 * WCPC Cart Class
 *
 * Handles selected items management and cart retrieval via AJAX.
 */
class WCPC_Cart
{
    /**
     * Constructor to initialize AJAX hooks.
     */
    public function __construct()
    {
        add_action('wp_ajax_wcpc_save_selected_items', [$this, 'saveSelectedItems']);
        add_action('wp_ajax_nopriv_wcpc_save_selected_items', [$this, 'saveSelectedItems']);
        
        add_action('wp_ajax_wcpc_get_selected_items', [$this, 'getSelectedItems']);
        add_action('wp_ajax_nopriv_wcpc_get_selected_items', [$this, 'getSelectedItems']);

        add_action('wp_ajax_wcpc_get_cart_items', [$this, 'getCartItems']);
        add_action('wp_ajax_nopriv_wcpc_get_cart_items', [$this, 'getCartItems']);
    }

    /**
     * Saves selected cart items to the session.
     *
     * @return void
     */
    public function saveSelectedItems()
    {
        if (!session_id()) {
            session_start();
        }
    
        if (isset($_POST['selected_items']) && is_array($_POST['selected_items'])) {
            // Allow developers to modify selected items
            $_POST['selected_items'] = apply_filters('wcpc_before_save_selected_items', $_POST['selected_items']);
    
            $_SESSION['wcpc_selected_items'] = array_map('intval', $_POST['selected_items']);
    
            // Ensure WooCommerce cart updates
            WC()->cart->calculate_totals();
            WC()->cart->set_session();
            WC()->cart->maybe_set_cart_cookies();
    
            wp_send_json_success(['message' => 'Cart updated successfully']);
        } else {
            wp_send_json_error(['message' => 'No items received']);
        }
    }
    
    /**
     * Retrieves selected cart items from the session.
     *
     * @return void
     */
    public function getSelectedItems()
    {
        if (!session_id()) {
            session_start();
        }

        $selectedItems = isset($_SESSION['wcpc_selected_items']) ? $_SESSION['wcpc_selected_items'] : [];

        wp_send_json_success(['selected_items' => $selectedItems]);
    }

    /**
     * Retrieves cart items and returns them via AJAX.
     *
     * @return void
     */
    public function getCartItems()
    {
        if (!WC()->cart || WC()->cart->is_empty()) {
            wp_send_json_error(['message' => 'Cart is empty']);
        }

        $cartItems = [];

        foreach (WC()->cart->get_cart() as $cartItemKey => $cartItem) {
            $cartItems[] = [
                'product_id' => $cartItem['product_id'],
                'name'       => $cartItem['data']->get_name(),
                'cart_key'   => $cartItemKey,
            ];
        }

        error_log(print_r($cartItems, true));

        if (!empty($cartItems)) {
            wp_send_json_success(['cart_items' => $cartItems]);
        } else {
            wp_send_json_error(['message' => 'No cart items found']);
        }
    }
}

// Initialize the WCPC Cart functionality
new WCPC_Cart();
