<?php
/**
 * Checkout giftcard form
 */

if (! defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

function wcgc_cart_form()
{
    if (get_option('woocommerce_enable_giftcard_cartpage') == "yes") {
        do_action('wcgc_before_cart_form'); ?>
        <div class="giftcard" style="float: left;">
            <label for="giftcard_code" style="display: none;"><?php _e('Giftcard', 'woocommerce-gift-cards'); ?>:</label>
            <input type="text" name="giftcard_code" class="input-text" id="giftcard_code" value="" placeholder="<?php _e('Gift Card', 'woocommerce-gift-cards'); ?>" />
            <input type="submit" class="button" name="update_cart" value="<?php _e('Apply Gift card', 'woocommerce-gift-cards'); ?>" />
        </div>
<?php
        do_action('wcgc_after_cart_form');
    }
}
add_action('woocommerce_proceed_to_checkout', 'wcgc_cart_form');


function apply_cart_giftcard()
{
    if (isset($_POST['giftcard_code'])) {
        woocommerce_apply_giftcard($_POST['giftcard_code']);
    }

    WC()->cart->calculate_totals();
}
// Don't think this being used
// Its not being used if the form is not on the checkout
//add_action ( 'woocommerce_before_cart', 'apply_cart_giftcard' );
