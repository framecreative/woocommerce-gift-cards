<?php

if (! defined('ABSPATH')) {
    exit;
} // Exit if accessed directly



class WCGC_Auto_Send
{
    /**
     * Run action and filter hooks
     */
    public function __construct()
    {
        if (is_admin()) {
            add_action('get_giftcard_settings', [ $this, 'autosend_page' ], 10, 2);
            add_filter('woocommerce_add_section_giftcard', [ $this, 'autosend_settings' ]);
            add_filter('get_giftcard_settings', [ $this, 'auto_send_settings' ], 10, 2);
            add_filter('woocommerce_giftcard_settings', [ $this, 'auto_send_settings' ]);
        }

        add_action('woocommerce_checkout_order_processed', [ $this, 'create_automatically' ], 10, 1);
        add_action('woocommerce_order_status_processing', [ $this, 'send_automatically' ], 10, 1);
        add_action('woocommerce_order_status_completed', [ $this, 'send_automatically' ], 10, 1);
    }

    public function auto_send_settings($options, $current_section = false)
    {
        if ($current_section == 'auto') {
            $options = [
                [
                    'title' => __('Auto Send Settings', 'wpr-pro'),
                    'type'  => 'title',
                    'id'    => 'giftcard_processing_options_title'
                ],
                [
                    'name'     => __('Time to Expire', 'wpr-pro'),
                    'desc'     => __('Select the number of days you would like cards to be valid for.', 'wpr-pro'),
                    'id'       => 'wcgc_auto_defaultExpiry',
                    'std'      => '0', // WooCommerce < 2.0
                    'default'  => '0', // WooCommerce >= 2.0
                    'type'     => 'text',
                    'desc_tip' => true,
                ],
                [
                    'name'     => __('Send', 'wpr-pro'),
                    'desc'     => __('Select when you would like the card sent.', 'wpr-pro'),
                    'id'       => 'wcgc_auto_when',
                    'std'      => 'payment', // WooCommerce < 2.0
                    'default'  => 'payment', // WooCommerce >= 2.0
                    'type'     => 'select',
                    'class'    => 'chosen_select',
                    'options'  => [
                        'payment' => __('On Payment', 'wpr-pro'),
                        'order'   => __('On Purchase', 'wpr-pro')
                    ],
                    'desc_tip' => true,
                ],
                [ 'type' => 'sectionend', 'id' => 'wcgc_auto_settings' ],
            ]; // End pages settings
        }

        return $options;
    }

    public function autosend_page($options, $current_section)
    {
        if ($current_section == 'auto') {
            $options = [

                [
                    'title' => __('Gift Cards Auto Send', 'wpr-pro'),
                    'type'  => 'title',
                    'id'    => 'giftcard_autosend_options_title'
                ],
                //array( 'type' => 'import_settings' ),
                [ 'type' => 'sectionend', 'id' => 'giftcard_import' ],
            ];
        }

        return $options;
    }

    public function autosend_settings($sections)
    {
        $auto = [ 'auto' => __('Gift Cards Auto Send', 'wpr-pro') ];

        return array_merge($sections, $auto);
    }

    public function create_automatically($order_id)
    {
        global $wpdb, $current_user;
        $current_user = wp_get_current_user();

        $order     = new WC_Order($order_id);
        $theItems  = $order->get_items();
        $firstName = $order->billing_first_name;
        $lastName  = $order->billing_last_name;

        $numberofGiftCards = 0;

        foreach ($theItems as $item) {
            $qty = (int) $item["item_meta"]["_qty"][0];

            $theItem = (int) $item["item_meta"]["_product_id"][0];


            if (WCGC()->is_gift_card($theItem)) {
                for ($i = 0; $i < $qty; $i ++) {
                    $product = new WC_Product($theItem);

                    $gift_card_value = (float) $product->get_regular_price();


                    if (($item["item_meta"]["To Email"][0] <> "NA") || ($item["item_meta"]["To Email"][0] <> "")) {
                        $giftCardInfo[ $numberofGiftCards ]["To Email"] = $item["item_meta"]["To Email"][0];
                    } else {
                        $giftCardInfo[ $numberofGiftCards ]["To Email"] = $current_user->user_email;
                    }

                    if (($item["item_meta"]["To"][0] <> "NA") || ($item["item_meta"]["To"][0] <> "")) {
                        $giftCardInfo[ $numberofGiftCards ]["To"] = $item["item_meta"]["To"][0];
                    } else {
                        $giftCardInfo[ $numberofGiftCards ]["To"] = '' . $firstName . ' ' . $lastName . '';
                    }

                    if ($item["item_meta"]["Note"][0] <> "NA") {
                        $giftCardInfo[ $numberofGiftCards ]["Note"] = $item["item_meta"]["Note"][0];
                    } else {
                        $giftCardInfo[ $numberofGiftCards ]["Note"] = "";
                    }


                    $giftCardInfo[ $numberofGiftCards ]["Balance"]     = $gift_card_value / $qty;
                    $giftCardInfo[ $numberofGiftCards ]["Amount"]      = $gift_card_value / $qty;
                    $giftCardInfo[ $numberofGiftCards ]["Description"] = 'Generated from Website';
                    $giftCardInfo[ $numberofGiftCards ]["From"]        = '' . $firstName . ' ' . $lastName . '';
                    $giftCardInfo[ $numberofGiftCards ]["From Email"]  = $order->billing_email;
                    $giftCardInfo[ $numberofGiftCards ]["Expiry Date"] = '';

                    $deafultExpiry = get_option('wcgc_auto_defaultExpiry');

                    if (isset($deafultExpiry)) {
                        $timeToExpire = (int) get_option('wcgc_auto_defaultExpiry');

                        if ($timeToExpire > 0) {
                            $newdate                                           = strtotime('+' . $timeToExpire . ' day', strtotime(today));
                            $giftCardInfo[ $numberofGiftCards ]["Expiry Date"] = date('Y-m-j', $newdate);
                        }
                    }

                    $numberofGiftCards ++;
                }
            }
        }

        $giftNumbers = [];

        for ($i = 0; $i < $numberofGiftCards; $i ++) {

            // Create gift card object
            $my_giftCard = [
                'post_title'  => wpgc_generate_number(),
                'post_status' => 'publish',
                'post_type'   => WCGC()->post_type,
                'post_author' => 1,
            ];

            // Insert the gift card into the database
            $post_id = wp_insert_post($my_giftCard);

            $giftCardInfo[ $i ]["ID"]     = $post_id;
            $giftCardInfo[ $i ]["Number"] = $my_giftCard["post_title"];

            $giftNumbers[] = $giftCardInfo[ $i ]["ID"];

            if (isset($giftCardInfo[ $i ]["Description"])) {
                update_post_meta($post_id, 'wcgc_description', wc_clean($giftCardInfo[ $i ]["Description"]));
            }

            if (isset($giftCardInfo[ $i ]["To"])) {
                $toEmail = wc_clean($giftCardInfo[ $i ]["To"]);
            }
            update_post_meta($post_id, 'wcgc_to', $toEmail);

            if (isset($giftCardInfo[ $i ]["To Email"])) {
                update_post_meta($post_id, 'wcgc_email_to', wc_clean($giftCardInfo[ $i ]["To Email"]));
            }

            if (isset($giftCardInfo[ $i ]["From"])) {
                update_post_meta($post_id, 'wcgc_from', wc_clean($giftCardInfo[ $i ]["From"]));
            }

            if (isset($giftCardInfo[ $i ]["From Email"])) {
                update_post_meta($post_id, 'wcgc_email_from', wc_clean($giftCardInfo[ $i ]["From Email"]));
            }

            if (isset($giftCardInfo[ $i ]["Amount"])) {
                update_post_meta($post_id, 'wcgc_amount', wc_clean($giftCardInfo[ $i ]["Amount"]));
            }

            if (isset($giftCardInfo[ $i ]["Balance"])) {
                update_post_meta($post_id, 'wcgc_balance', wc_clean($giftCardInfo[ $i ]["Balance"]));
            }

            if (isset($giftCardInfo[ $i ]["Note"])) {
                update_post_meta($post_id, 'wcgc_note', wc_clean($giftCardInfo[ $i ]["Note"]));
            }

            if (isset($giftCardInfo[ $i ]["Expiry Date"])) {
                update_post_meta($post_id, 'wcgc_expiry_date', wc_clean($giftCardInfo[ $i ]["Expiry Date"]));
            }

            update_post_meta($post_id, 'wcgc_email_sent', false);
        }

        update_post_meta($order_id, 'wcgc_numbers', $giftNumbers);


        // Maybe send on order placement
        if (get_option('wcgc_auto_when') == "order") {
            $gift_cards = get_post_meta($order_id, 'wcgc_numbers', true);

            if (is_array($gift_cards)) {
                foreach ($gift_cards as $gift_card_id) {
                    $gift_card = new WC_Gift_Card();
                    $gift_card->get_by_post_id($gift_card_id);
                    WCGC()->send_gift_card_email($gift_card);
                }
            }
        }
    }

    /**
     * @param $order_id
     */
    public function send_automatically($order_id)
    {
        $gift_cards = get_post_meta($order_id, 'wcgc_numbers', true);

        if (is_array($gift_cards)) {
            foreach ($gift_cards as $gift_card_id) {
                $gift_card = new WC_Gift_Card();
                $gift_card->get_by_post_id($gift_card_id);

                // Maybe send emails if they're not sent
                if (! $gift_card->get_email_sent()) {
                    WCGC()->send_gift_card_email($gift_card);
                }
            }
        }
    }
}
