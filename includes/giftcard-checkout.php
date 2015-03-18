<?php
/**
 * Product Functions
 *
 * @package     Woocommerce
 * @subpackage  Giftcards
 * @copyright   Copyright (c) 2014, Ryan Pletcher
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * AJAX apply coupon on checkout page
 * @access public
 * @return void
 */
function woocommerce_ajax_apply_giftcard($giftcard_code) {
	global $wpdb;

	if ( ! empty( $_POST['giftcard_code'] ) ) {
		$giftcard_number = sanitize_text_field( $_POST['giftcard_code'] );

		if ( ! isset( WC()->session->giftcard_post ) ) {
			$giftcard_id = wcgc_get_giftcard_by_code( $giftcard_number );

			if ( $giftcard_id ) {
				$current_date = date("Y-m-d");
				$cardExperation = wcgc_get_giftcard_expiration( $giftcard_id );

				if ( ( strtotime($current_date) <= strtotime($cardExperation) ) || ( strtotime($cardExperation) == '' ) ) {
					if( wcgc_get_giftcard_balance( $giftcard_id ) > 0 ) {
						WC()->session->giftcard_post = $giftcard_id;

						wc_add_notice(  __( 'Gift card applied successfully.', 'wcgiftcards' ), 'success' );

					} else {
						wc_add_notice( __( 'Gift Card does not have a balance!', 'wcgiftcards' ), 'error' );
					}
				} else {
					wc_add_notice( __( 'Gift Card has expired!', 'wcgiftcards' ), 'error' ); // Giftcard Entered has expired
				}
			} else {
				wc_add_notice( __( 'Gift Card does not exist!', 'wcgiftcards' ), 'error' ); // Giftcard Entered does not exist
			}
		} else {		
			wc_add_notice( __( 'Gift Card already in the cart!', 'wcgiftcards' ), 'error' );  //  You already have a gift card in the cart
		}

		wc_print_notices();

		die();
	}
}
add_action( 'wp_ajax_woocommerce_apply_giftcard', 'woocommerce_ajax_apply_giftcard' );
add_action( 'wp_ajax_nopriv_woocommerce_apply_giftcard', 'woocommerce_ajax_apply_giftcard' );


function woocommerce_apply_giftcard($giftcard_code) {
	global $wpdb;

	if ( ! empty( $_POST['giftcard_code'] ) ) {
		$giftcard_number = sanitize_text_field( $_POST['giftcard_code'] );

		if ( ! isset( WC()->session->giftcard_post ) ) {
			$giftcard_id = wcgc_get_giftcard_by_code( $giftcard_number );

			if ( $giftcard_id ) {
				$current_date = date("Y-m-d");
				$cardExperation = wcgc_get_giftcard_expiration( $giftcard_id );

				if ( ( strtotime($current_date) <= strtotime($cardExperation) ) || ( strtotime($cardExperation) == '' ) ) {
					if( wcgc_get_giftcard_balance( $giftcard_id ) > 0 ) {
						WC()->session->giftcard_post = $giftcard_id;

						wc_add_notice(  __( 'Gift card applied successfully.', 'wcgiftcards' ), 'success' );

					} else {
						wc_add_notice( __( 'Gift Card does not have a balance!', 'wcgiftcards' ), 'error' );
					}
				} else {
					wc_add_notice( __( 'Gift Card has expired!', 'wcgiftcards' ), 'error' ); // Giftcard Entered has expired
				}
			} else {
				wc_add_notice( __( 'Gift Card does not exist!', 'wcgiftcards' ), 'error' ); // Giftcard Entered does not exist
			}
		} else {		
			wc_add_notice( __( 'Gift Card already in the cart!', 'wcgiftcards' ), 'error' );  //  You already have a gift card in the cart
		}

		wc_print_notices();

	}
}


/**
 * Function to add the giftcard data to the cart display on both the card page and the checkout page WC()->session->giftcard_balance
 *
 */
function wcgc_order_giftcard( ) {
	global $woocommerce;

	if ( isset( $_GET['remove_giftcards'] ) ) {
		$type = $_GET['remove_giftcards'];

		if ( 1 == $type ) {
			unset( WC()->session->giftcard_payment, WC()->session->giftcard_post );
			WC()->cart->calculate_totals();
		}
	}

	if ( isset( WC()->session->giftcard_post ) ) {
		if ( WC()->session->giftcard_post ){

			$currency_symbol = get_woocommerce_currency_symbol();

			$price = WC()->session->giftcard_payment;

			$gotoPage = WC()->cart->get_checkout_url();

			if ( is_cart() )
				$gotoPage = WC()->cart->get_cart_url();


			?>

			<tr class="giftcard">
				<th><?php _e( 'Gift Card Payment', 'wcgiftcards' ); ?> </th>

				<td style="font-size:0.85em;"><?php echo woocommerce_price( $price ); ?> <a href="<?php echo add_query_arg( 'remove_giftcards', '1', $gotoPage ) ?>">[<?php _e( 'Remove Gift Card', 'wcgiftcards' ); ?>]</a></td>
			</tr>

			<?php

		}
	}
}
add_action( 'woocommerce_review_order_before_order_total', 'wcgc_order_giftcard' );
add_action( 'woocommerce_cart_totals_before_order_total', 'wcgc_order_giftcard' );

/**
 * Function to decrease the cart amount by the amount in the giftcard
 *
 */
function subtract_giftcard( $wc_cart ) {
	$giftcard_id 	= WC()->session->giftcard_post;
	$cart 			= WC()->session->cart;

	if ( isset( $giftcard_id ) ) {
		$balance = wcgc_get_giftcard_balance( $giftcard_id );

		$charge_shipping 	= get_option('woocommerce_enable_giftcard_charge_shipping');
		$charge_tax 		= get_option('woocommerce_enable_giftcard_charge_tax');
		$charge_fee 		= get_option('woocommerce_enable_giftcard_charge_fee');
		//$charge_giftcard 	= get_option('woocommerce_enable_giftcard_charge_giftcard');
		$exclude_product 	= array( ); //get_option('exclude_product_ids');

		$giftcardPayment = 0;

		foreach( $cart as $key => $product ) {

			if( ! in_array( $product['product_id'], $exclude_product ) ) {

				if( $charge_tax == 'yes' ){
					$giftcardPayment += $product['line_total'];
					$giftcardPayment += $product['line_tax'];
				} else {
					$giftcardPayment += $product['line_total'];
				}
			}
		}
		

		if( $charge_shipping == 'yes' )
			$giftcardPayment += WC()->session->shipping_tax_total;
			

		if( $charge_tax == "yes" )
			$giftcardPayment += WC()->session->shipping_total;


		if( $charge_fee == "yes" )
			$giftcardPayment += WC()->session->fee_total;


		if ( $giftcardPayment <= $balance ) {
			WC()->session->giftcard_payment = $giftcardPayment;
			WC()->session->discount_cart = $giftcardPayment;

		} else {
			WC()->session->giftcard_payment = $balance;
			WC()->session->discount_cart = $balance;
		}
		
	}
}
add_action( 'woocommerce_calculate_totals', 'subtract_giftcard' ); //woocommerce_calculate_totals   //woocommerce_cart_updated

function wcgc_applydiscount( $total ) {
	$giftcard_id 	= WC()->session->giftcard_post;
	
	if ( isset( $giftcard_id ) ) {
		$total -= WC()->session->discount_cart;
	}
	
	return $total;
}
add_filter( 'woocommerce_calculated_total', 'wcgc_applydiscount', 10, 1 );



function wcgc_add_card_data( $cart_item_key, $product_id, $quantity )
{
	global $woocommerce, $post;

	$is_giftcard = get_post_meta( $product_id, '_giftcard', true );

	if ( $is_giftcard == "yes" ) {

		$giftcard_data = array(
			'To'    	=> 'NA',
			'To Email'  => 'NA',
			'Note'   	=> 'NA',
		);

		if ( isset( $_POST['wcgc_to'] ) && ( $_POST['wcgc_to'] <> '' ) )
			$giftcard_data['To'] = woocommerce_clean( $_POST['wcgc_to'] );

		if ( isset( $_POST['wcgc_to_email'] ) && ( $_POST['wcgc_to_email'] <> '' ) )
			$giftcard_data['To Email'] = woocommerce_clean( $_POST['wcgc_to_email'] );

		if ( isset( $_POST['wcgc_note'] ) && ( $_POST['wcgc_note'] <> '' ) )
			$giftcard_data['Note'] = woocommerce_clean( $_POST['wcgc_note'] );

		$giftcard_data = apply_filters( 'wcgc_giftcard_data', $giftcard_data, $_POST );

		WC()->cart->cart_contents[$cart_item_key]["variation"] = $giftcard_data;
		return $woocommerce;
	}
	
}
add_action( 'woocommerce_add_to_cart', 'wcgc_add_card_data', 10, 3 );


function wcgc_validate_form_complete( $passed, $product_id, $quantity, $variation_id = '', $variations = '' )
{
	$passed = true;

	$is_giftcard = get_post_meta( $product_id, '_giftcard', true );

	if ( ( $is_giftcard == "yes" ) ) {

		if ( $_POST["wcgc_to"] == '' ) { $passed = false; }
		if ( $_POST["wcgc_to_email"] == '' ) { $passed = false; }

		if ( $passed == false ) {
	        wc_add_notice( __( 'Please complete form.', 'wcgiftcards' ), 'error' );
		}
	}
	
    return $passed;

}
add_filter( 'woocommerce_add_to_cart_validation', 'wcgc_validate_form_complete', 10, 5 );


/**
 * Displays the giftcard data on the order thank you page
 *
 */
function wcgc_display_giftcard( $order )
{

	$theIDNum =  get_post_meta( $order->id, 'wcgc_id', true );
	$theBalance = get_post_meta( $order->id, 'wcgc_balance', true );

	if( isset( $theIDNum ) ) {
		if ( $theIDNum <> '' ) {
		?>
			<h4><?php _e( 'Gift Card Balance After Order:', 'wcgiftcards' ); ?><?php echo ' ' . woocommerce_price( $theBalance ); ?> <?php do_action('wcgc_after_remaining_balance', $theIDNum, $theBalance ); ?></h4>

			<?php
			
		}
	}

	$theGiftCardData = get_post_meta( $order->id, 'wcgc_data', true );
	if( isset( $theGiftCardData ) ) {
		if ( $theGiftCardData <> '' ) {
	?>
			<h4><?php _e( 'Gift Card Information:', 'wcgiftcards' ); ?></h4>
			<?php
			$i = 1;

			foreach ( $theGiftCardData as $giftcard ) {

				if ( $i % 2 ) echo '<div style="margin-bottom: 10px;">';
				echo '<div style="float: left; width: 45%; margin-right: 2%;>';
				echo '<h6><strong> ' . __('Giftcard',  'wcgiftcards' ) . ' ' . $i . '</strong></h6>';
				echo '<ul style="font-size: 0.85em; list-style: none outside none;">';
				if ( $giftcard[wcgc_product_num] ) 	echo '<li>' . __('Card', 'wcgiftcards') . ': ' . get_the_title( $giftcard[wcgc_product_num] ) . '</li>';
				if ( $giftcard[wcgc_to] ) 			echo '<li>' . __('To',  'wcgiftcards' ) . ': ' . $giftcard[wcgc_to] . '</li>';
				if ( $giftcard[wcgc_to_email] ) 	echo '<li>' . __('Send To',  'wcgiftcards' ) . ': ' . $giftcard[wcgc_to_email] . '</li>';
				if ( $giftcard[wcgc_balance] ) 		echo '<li>' . __('Balance',  'wcgiftcards' ) . ': ' . woocommerce_price( $giftcard[wcgc_balance] ) . '</li>';
				if ( $giftcard[wcgc_note] ) 		echo '<li>' . __('Note',  'wcgiftcards' ) . ': ' . $giftcard[wcgc_note] . '</li>';
				if ( $giftcard[wcgc_quantity] ) 	echo '<li>' . __('Quantity',  'wcgiftcards' ) . ': ' . $giftcard[wcgc_quantity] . '</li>';
				echo '</ul>';
				echo '</div>';
				if ( !( $i % 2 ) ) echo '</div>';
				$i++;
			}
			echo '<div class="clear"></div>';
		}
	}
}
add_action( 'woocommerce_order_details_after_order_table', 'wcgc_display_giftcard' );
add_action( 'woocommerce_email_after_order_table', 'wcgc_display_giftcard' );


function wcgc_add_order_giftcard( $total_rows,$order )
{
	global $woocommerce;

	$return = array();

	$order_id = $order->id;

	$giftCardPayment = get_post_meta( $order_id, 'wcgc_payment', true);

	if ( $giftCardPayment <> 0 ) {
		$newRow['wcgc_data'] = array(
			'label' => __( 'Gift Card Payment:', 'wcgiftcards' ),
			'value'	=> woocommerce_price( -1 * $giftCardPayment )
		);

		if( get_option( 'woocommerce_enable_giftcard_process' ) == 'no' ){
			array_splice($total_rows, 1, 0, $newRow);	
		} else {
			array_splice($total_rows, 2, 0, $newRow);
		}
	}

	return $total_rows;
}
add_filter( 'woocommerce_get_order_item_totals', 'wcgc_add_order_giftcard', 10, 2);





/**
 * Updates the Gift Card and the order information when the order is processed
 *
 */
function wcgc_update_card( $order_id ) {
	global $woocommerce;

	$giftCard_id = WC()->session->giftcard_post;
	if ( $giftCard_id <> '' ) {
		$newBalance = wcgc_get_giftcard_balance( $giftCard_id ) - WC()->session->giftcard_payment;

		// Check if the gift card ballance is 0 and if it is change the post status to zerobalance
		if( wcgc_get_giftcard_balance( $giftCard_id ) == 0 )
			wcgc_update_giftcard_status( $giftCard_id, 'zerobalance' );


		$giftCard_IDs = get_post_meta ( $giftCard_id, 'wcgc_existingOrders_id', true );
		$giftCard_IDs[] = $order_id;


		update_post_meta( $giftCard_id, 'wcgc_balance', $newBalance ); // Update balance of Giftcard
		update_post_meta( $giftCard_id, 'wcgc_existingOrders_id', $giftCard_IDs ); // Saves order id to gifctard post
		update_post_meta( $order_id, 'wcgc_id', $giftCard_id );
		update_post_meta( $order_id, 'wcgc_payment', WC()->session->giftcard_payment );
		update_post_meta( $order_id, 'wcgc_balance', $newBalance );

		WC()->session->idForEmail = $order_id;
		unset( WC()->session->giftcard_payment, WC()->session->giftcard_post );
	}

	if ( isset ( WC()->session->giftcard_data ) ) {
		update_post_meta( $order_id, 'wcgc_data', WC()->session->giftcard_data );

		unset( WC()->session->giftcard_data );
	}

}
add_action( 'woocommerce_checkout_order_processed', 'wcgc_update_card' );
//add_action( 'woocommerce_thankyou_paypal', 'wcgc_update_card' );





function wcgc_display_giftcard_in_cart() {
	$cart = WC()->session->cart;
	$gift = 0;
	$card = array();

	foreach( $cart as $key => $product ) {

		if( wcgc_is_giftcard($product['product_id'] ) )
				$card[] = $product;

	}

	if( ! empty( $card ) ) {
		echo '<h6>Gift Cards In Cart</h6>';
		echo '<table width="100%" class="shop_table cart">';
		echo '<thead>';
		echo '<tr><td>' . __( 'Gift Card' ) . '</td><td>' . __( 'Name', 'wcgiftcards' ) . '</td><td>' . __( 'Email', 'wcgiftcards' ) . '</td><td>' . __( 'Price', 'wcgiftcards' ) . '</td><td>' . __( 'Note', 'wcgiftcards' ) . '</td></tr>';
		echo '</thead>';
		foreach( $card as $key => $information ) {
			
			if( wcgc_is_giftcard($information['product_id'] ) ){
				$gift += 1;

				echo '<tr style="font-size: 0.8em">';
					echo '<td>Gift Card ' . $gift . '</td>';
					echo '<td>' . $information["variation"]["To"] . '</td>';
					echo '<td>' . $information["variation"]["To Email"] . '</td>';
					echo '<td>' . woocommerce_price( $information["line_total"] ) . '</td>';
					echo '<td>' . $information["variation"]["Note"] . '</td>';
				echo '</tr>';
			}
		}
		echo '</table>';
	}
}
add_action( 'woocommerce_after_cart_table', 'wcgc_display_giftcard_in_cart' );


