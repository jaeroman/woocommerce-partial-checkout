<?php

defined('ABSPATH') || exit;

/**
 * WCPC Checkout Class
 * 
 * Handles the partial checkout functionality for WooCommerce.
 */
class WCPC_Checkout
{
    /**
     * Constructor to initialize hooks.
     */
    public function __construct()
    {
        add_action('woocommerce_checkout_update_order_review', [$this, 'filterCartBeforeCheckout'], 10);
        add_action('woocommerce_thankyou', [$this, 'restoreUnselectedItems']);
        add_action('wp_ajax_wcpc_restore_cart', [$this, 'restoreFullCartOnBack']);
        add_action('wp_ajax_nopriv_wcpc_restore_cart', [$this, 'restoreFullCartOnBack']);
        add_action('woocommerce_thankyou', [$this, 'sendWebhookData']);
    }

    /**
     * Filters the cart before checkout to remove unselected items.
     *
     * @param string $postedData The checkout form data.
     * @return void
     */
    public function filterCartBeforeCheckout($postedData)
    {
        if (!session_id()) {
            session_start();
        }

        error_log("🔍 WCPC Checkout Filter Triggered");

        if (!isset($_SESSION['wcpc_selected_items']) || empty($_SESSION['wcpc_selected_items'])) {
            error_log("No selected items in session. Skipping cart filtering.");
            return;
        }

        $selectedItems = array_map('intval', $_SESSION['wcpc_selected_items']);
        error_log("Selected Items from Session: " . implode(', ', $selectedItems));

        $cartItems = WC()->cart->get_cart();
        error_log("Current Cart Items Before Filtering:");

        $_SESSION['wcpc_full_cart_backup'] = [];
        $_SESSION['wcpc_unselected_items'] = [];

        foreach ($cartItems as $cartItemKey => $cartItem) {
            $productId = intval($cartItem['product_id']);
            $quantity = $cartItem['quantity'];

            $_SESSION['wcpc_full_cart_backup'][] = [
                'product_id' => $productId,
                'quantity'   => $quantity
            ];

            if (!in_array($productId, $selectedItems)) {
                $_SESSION['wcpc_unselected_items'][] = [
                    'product_id' => $productId,
                    'quantity'   => $quantity
                ];
                WC()->cart->remove_cart_item($cartItemKey);
            }
        }

        WC()->cart->calculate_totals();
        WC()->cart->set_session();
        WC()->cart->maybe_set_cart_cookies();
    }

    /**
     * Restores unselected items back to the cart after checkout.
     *
     * @param int $orderId The order ID.
     * @return void
     */
    public function restoreUnselectedItems($orderId)
    {
        if (!session_id()) {
            session_start();
        }

        if (!isset($_SESSION['wcpc_unselected_items']) || empty($_SESSION['wcpc_unselected_items'])) {
            return;
        }

        foreach ($_SESSION['wcpc_unselected_items'] as $item) {
            WC()->cart->add_to_cart($item['product_id'], $item['quantity']);
        }

        unset($_SESSION['wcpc_unselected_items']);
        $_SESSION['wcpc_order_completed'] = true;
    }

    /**
     * Restores the full cart when the user returns to the cart page.
     *
     * @return void
     */
    public function restoreFullCartOnBack()
    {
        if (!session_id()) {
            session_start();
        }

        if (isset($_SESSION['wcpc_order_completed']) && $_SESSION['wcpc_order_completed'] === true) {
            unset($_SESSION['wcpc_full_cart_backup']);
            unset($_SESSION['wcpc_order_completed']);
            wp_send_json_error(["message" => "Order completed, skipping cart restoration"]);
            return;
        }

        if (!isset($_SESSION['wcpc_full_cart_backup']) || empty($_SESSION['wcpc_full_cart_backup'])) {
            wp_send_json_error(["message" => "No cart backup found"]);
            return;
        }

        WC()->cart->empty_cart();

        foreach ($_SESSION['wcpc_full_cart_backup'] as $item) {
            WC()->cart->add_to_cart($item['product_id'], $item['quantity']);
        }

        unset($_SESSION['wcpc_full_cart_backup']);
        wp_send_json_success(["message" => "Cart restored successfully"]);
    }

    /**
     * Sends order data to a webhook URL after order completion.
     *
     * @param int $orderId The order ID.
     * @return void
     */
    public function sendWebhookData($orderId)
    {
        if (!$orderId) {
            return;
        }
    
        $webhookUrl = get_option('wcpc_webhook_url', '');
        if (empty($webhookUrl)) {
            return;
        }
    
        $order = wc_get_order($orderId);
        $orderData = [
            'order_id'      => $order->get_id(),
            'total'         => $order->get_total(),
            'customer_name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
            'customer_email'=> $order->get_billing_email(),
            'payment_status'=> $order->get_status(),
            'items'         => []
        ];
    
        foreach ($order->get_items() as $item) {
            $orderData['items'][] = [
                'product_id'   => $item->get_product_id(),
                'product_name' => $item->get_name(),
                'quantity'     => $item->get_quantity(),
                'price'        => $item->get_total()
            ];
        }
    
        // Allow developers to modify webhook data before sending
        $orderData = apply_filters('wcpc_webhook_order_data', $orderData, $order);
    
        wp_remote_post($webhookUrl, [
            'body'    => json_encode($orderData),
            'headers' => ['Content-Type' => 'application/json'],
            'method'  => 'POST',
            'timeout' => 45
        ]);
    }
}

// Initialize the WCPC Checkout functionality
new WCPC_Checkout();
