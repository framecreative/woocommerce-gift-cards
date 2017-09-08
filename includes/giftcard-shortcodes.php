<?php
/**
 * Gift Card Short Codes
 */

// Exit if accessed directly
if (! defined('ABSPATH')) {
    exit;
}


function wcgc_check_giftcard($atts)
{
    global $wpdb, $woocommerce;


    if (isset($_POST['giftcard_code'])) {
        $giftCardNumber = sanitize_text_field($_POST['giftcard_code']);
    }

    $return = '';

    $return .= '<form class="check_giftcard_balance" method="post">';

    $return .= '<p class="form-row form-row-first">';
    $return .= '<input type="text" name="giftcard_code" class="input-text" placeholder="' . __('Gift card', 'woocommerce-gift-cards') . '" id="giftcard_code" value="" />';
    $return .= '</p>';

    $return .= '<p class="form-row form-row-last">';
    $return .= '<input type="submit" class="button" name="check_giftcard" value="' . __('Check Balance', 'woocommerce-gift-cards') . '" />';
    $return .= '</p>';

    $return .= '<div class="clear"></div>';
    $return .= '</form>';
    $return .= '<div id="theBalance"></div>';


    if (isset($_POST['giftcard_code'])) {

        // Check for Giftcard
        $giftcard_found = $wpdb->get_var($wpdb->prepare("
            SELECT $wpdb->posts.ID
            FROM $wpdb->posts
            WHERE $wpdb->posts.post_type = '".WCGC()->post_type."'
            AND $wpdb->posts.post_status = 'publish'
            AND $wpdb->posts.post_title = '%s'
        ", $giftCardNumber));

        if ($giftcard_found) {
            $current_date = date("Y-m-d");
            $cardExperation = get_post_meta($giftcard_found, 'wcgc_expiry_date', true);

            // Valid Gift Card Entered
            if ((strtotime($current_date) <= strtotime($cardExperation)) || (strtotime($cardExperation) == '')) {
                $oldBalance = get_post_meta($giftcard_found, 'wcgc_balance', true);
                $GiftcardBalance = (float) $oldBalance;

                $return .= '<h3>' . __('Remaining Balance', 'woocommerce-gift-cards') . ': ' . woocommerce_price($GiftcardBalance) . '</h3>';
            } else {
                $return .= '<h3>' . __('Gift Card Has Expired', 'woocommerce-gift-cards') . '</h3>';
            }
        } else {
            $return .= '<h3>' . __('Gift Card Does Not Exist', 'woocommerce-gift-cards') . '</h3>';
        }
    }

    return apply_filters('wcgc_check_giftcard', $return) ;
}
add_shortcode('giftcardbalance', 'wcgc_check_giftcard');
