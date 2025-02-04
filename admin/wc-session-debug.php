<?php
/**
 * WooCommerce Session Debug Page
 */

defined('ABSPATH') || exit;

if (!current_user_can('manage_options')) {
    wp_die(__('You do not have permission to access this page.', 'wcpc'));
}

// Ensure WooCommerce is fully loaded
if (!class_exists('WooCommerce') || !WC()->session) {
    echo '<div class="notice notice-error"><p><strong>WooCommerce session is not available.</strong> Please visit the frontend (cart page) to initialize the session.</p></div>';
    return;
}

// Get WooCommerce session data
$wc_session = WC()->session->get_session_data();

?>

<div class="wrap">
    <h1>WooCommerce Session Debug</h1>
    <p><strong>Note:</strong> This page shows all active WooCommerce session data.</p>

    <h2>Session Data</h2>
    <pre><?php print_r($wc_session); ?></pre>

    <h2>Specific Session Keys</h2>
    <ul>
        <li><strong>Selected Items:</strong> <?php echo esc_html(json_encode(WC()->session->get('wcpc_selected_items', []))); ?></li>
        <li><strong>Unselected Items:</strong> <?php echo esc_html(json_encode(WC()->session->get('wcpc_unselected_items', []))); ?></li>
    </ul>

    <h2>Actions</h2>
    <form method="post">
        <input type="hidden" name="clear_wc_session" value="1">
        <?php submit_button('Clear WooCommerce Session'); ?>
    </form>

    <?php
    // Handle session clearing
    if (isset($_POST['clear_wc_session'])) {
        WC()->session->destroy_session();
        echo '<p><strong>WooCommerce session has been cleared.</strong> Refresh this page.</p>';
    }
    ?>
</div>
