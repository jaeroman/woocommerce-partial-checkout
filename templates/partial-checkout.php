<?php
defined('ABSPATH') || exit;

$cart_items = WC()->cart->get_cart();
?>
<form method="post" action="<?php echo wc_get_checkout_url(); ?>">
    <?php foreach ($cart_items as $cart_item_key => $cart_item) : ?>
        <label>
            <input type="checkbox" name="wcpc_selected_items[]" value="<?php echo esc_attr($cart_item_key); ?>">
            <?php echo esc_html($cart_item['data']->get_name()); ?>
        </label><br>
    <?php endforeach; ?>
    <button type="submit">Proceed to Checkout</button>
</form>
