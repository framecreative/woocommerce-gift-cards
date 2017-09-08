<?php


/**
 */
function wcgc_process_giftcard_meta($post_id, $post)
{
    global $wpdb, $woocommerce_errors;

    $description     = '';
    $to              = '';
    $toEmail         = '';
    $from            = '';
    $fromEmail       = '';
    $sendto_from     = '';
    $sendautomaticly = '';
    $amount          = '';
    $balance         = '';
    $note            = '';
    $expiry_date     = '';
    $sendTheEmail    = 0;


    // Ensure gift card code is correctly formatted
    $wpdb->update($wpdb->posts, [ 'post_title' => $post->post_title ], [ 'ID' => $post_id ]);

    if (isset($_POST['wcgc_description'])) {
        $description    = wc_clean($_POST['wcgc_description']);
        update_post_meta($post_id, 'wcgc_description', $description);
    }
    if (isset($_POST['wcgc_to'])) {
        $to                = wc_clean($_POST['wcgc_to']);
        update_post_meta($post_id, 'wcgc_to', $to);
    }
    if (isset($_POST['wcgc_email_to'])) {
        $toEmail        = wc_clean($_POST['wcgc_email_to']);
        update_post_meta($post_id, 'wcgc_email_to', $toEmail);
    }
    if (isset($_POST['wcgc_from'])) {
        $from            = wc_clean($_POST['wcgc_from']);
        update_post_meta($post_id, 'wcgc_from', $from);
    }
    if (isset($_POST['wcgc_email_from'])) {
        $fromEmail        = wc_clean($_POST['wcgc_email_from']);
        update_post_meta($post_id, 'wcgc_email_from', $fromEmail);
    }
    if (isset($_POST['wcgc_amount'])) {
        $amount        = wc_clean($_POST['wcgc_amount']);
        update_post_meta($post_id, 'wcgc_amount', $amount);

        if (! isset($_POST['wcgc_balance'])) {
            $balance    = wc_clean($_POST['wcgc_amount']);
            update_post_meta($post_id, 'wcgc_balance', $balance);
            $sendTheEmail = 1;
        }
    }
    if (isset($_POST['wcgc_balance'])) {
        $balance   = wc_clean($_POST['wcgc_balance']);
        update_post_meta($post_id, 'wcgc_balance', $balance);
    }
    if (isset($_POST['wcgc_note'])) {
        $note   = wc_clean($_POST['wcgc_note']);
        update_post_meta($post_id, 'wcgc_note', $note);
    }
    if (isset($_POST['wcgc_expiry_date'])) {
        $expiry_date = wc_clean($_POST['wcgc_expiry_date']);
        update_post_meta($post_id, 'wcgc_expiry_date', $expiry_date);
    } else {
        $expiry_date = '';
    }

    if (isset($_POST['wcgc_regen_number'])) {
        $newNumber = apply_filters('wcgc_regen_number', wpgc_generate_number());

        $wpdb->update($wpdb->posts, [ 'post_title' => $newNumber ], [ 'ID' => $post_id ]);
        $wpdb->update($wpdb->posts, [ 'post_name' => $newNumber ], [ 'ID' => $post_id ]);
    }

    if ((($sendTheEmail == 1) && ($balance <> 0)) || isset($_POST['wcgc_resend_email'])) {
        $gift_card = new WC_Gift_Card();
        $gift_card->get_by_post_id($post_id);
        WCGC()->send_gift_card_email($gift_card);
    }

    /* Deprecated - same hook name as in the meta */
    do_action('woocommerce_wcgc_options');
    do_action('woocommerce_wcgc_options_save');
}
add_action('save_post', 'wcgc_process_giftcard_meta', 20, 2);




/**
 * Creates a random 15 digit giftcard number
 *
 */
function wcgc_create_number($data, $postarr)
{
    if (isset($_POST['original_publish'])) {
        if (($data['post_type'] == WCGC()->post_type) && ($_POST['original_publish'] == "Publish")) {
            $myNumber = wpgc_generate_number();

            $data['post_title'] = $myNumber;
            $data['post_name'] = $myNumber;
        }
    }

    return apply_filters('wcgc_create_number', $data);
}
add_filter('wp_insert_post_data', 'wcgc_create_number', 10, 2);


function wpgc_generate_number()
{
    $randomNumber = substr(number_format(time() * rand(), 0, '', ''), 0, 15);

    return apply_filters('wcgc_generate_number', $randomNumber);
}

/**
 * Function to refund the amount paid by Giftcard back to the Card when the entire order is refunded
 *
 */
function wcgc_refund_order($order_id)
{
    $giftCard_id = get_post_meta($order_id, 'wcgc_id', true);
    $giftCard_refunded = get_post_meta($order_id, 'wcgc_refunded', true);

    if ($giftCard_id  && ! ($giftCard_refunded == 'yes')) {
        $oldBalance = wcgc_get_giftcard_balance($giftCard_id);
        $refundAmount = get_post_meta($order_id, 'wcgc_payment', true);

        $giftcard_balance = (float) $oldBalance + (float) $refundAmount;

        update_post_meta($giftCard_id, 'wcgc_balance', $giftcard_balance); // Update balance of Giftcard
        update_post_meta($order_id, 'wcgc_refunded', 'yes'); // prevents multiple refunds of Giftcard
    }
}
add_action('woocommerce_order_status_refunded', 'wcgc_refund_order');
add_action('woocommerce_order_status_pending_to_cancelled', 'wcgc_refund_order');
add_action('woocommerce_order_status_on-hold_to_cancelled', 'wcgc_refund_order');


function wcgc_display_giftcard_on_order($order_id)
{
    $giftPayment = wcgc_get_order_card_payment($order_id);

    if ($giftPayment > 0) {
        ?>
        <tr>
            <td class="label"><?php _e('Gift Card Payment', 'woocommerce'); ?>:</td>
            <td class="giftcardTotal">
                <div class="view"><?php echo wc_price($giftPayment); ?></div>
            </td>
        </tr>
        <?php
    }
}
add_action('woocommerce_admin_order_totals_after_discount', 'wcgc_display_giftcard_on_order');
