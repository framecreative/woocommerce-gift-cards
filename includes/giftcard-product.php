<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

	

function wcgc_extra_check( $product_type_options ) {

	$giftcard = array(
		'giftcard' => array(
			'id' => '_giftcard',
			'wrapper_class' => 'show_if_simple show_if_variable',
			'label' => __( 'Gift Card', 'woocommerce-gift-cards' ),
			'description' => __( 'Make product a gift card.', 'woocommerce-gift-cards' )
		),
	);

	// combine the two arrays
	$product_type_options = array_merge( $giftcard, $product_type_options );

	return apply_filters( 'wcgc_extra_check', $product_type_options );
}
add_filter( 'product_type_options', 'wcgc_extra_check' );


function wcgc_process_meta( $post_id, $post )
{
	global $wpdb, $woocommerce, $woocommerce_errors;

	$is_giftcard  = isset( $_POST['_giftcard'] ) ? 'yes' : 'no';
	
	if( $is_giftcard == 'yes' )
	{
		update_post_meta( $post_id, '_giftcard', $is_giftcard );
		update_post_meta( $post_id, '_sold_individually', $is_giftcard );
		update_post_meta( $post_id, '_virtual', $is_giftcard );
	}
}
add_action( 'save_post', 'wcgc_process_meta', 10, 2 );


//  Sets a unique ID for gift cards so that multiple giftcards can be purchased (Might move to the main gift card Plugin)
function wcgc_uniqueID($cart_item_data, $product_id)
{
	$is_giftcard = get_post_meta( $product_id, '_giftcard', true );

	if ( $is_giftcard == "yes" ) {

		$unique_cart_item_key = md5("gc" . microtime().rand());
		$cart_item_data['unique_key'] = $unique_cart_item_key;

	}
	
	return apply_filters( 'wcgc_uniqueID', $cart_item_data, $product_id );
}
add_filter('woocommerce_add_cart_item_data','wcgc_uniqueID',10,2);



///EDIT THIS TO INCLUDE IF CUSTOM PRICE IS ENABLED TO MAKE IT A CUSTOMIZE
///MAKE a function that will do the checks with a filter at the end to allow for custom price enabled
function wcgc_change_add_to_cart_button ( $link )
{
	global $post;


	if ( preventAddToCart( $post->ID ) ) {
		$giftCard_button = get_option( "woocommerce_giftcard_button" );

		if( $giftCard_button <> '' ){
			$giftCardText = get_option( "woocommerce_giftcard_button" );
		} else {
			$giftCardText = 'Customize';
		}

		$link = '<a href="' . esc_url( get_permalink( $post->ID ) ) . '" rel="nofollow" data-product_id="' . esc_attr( $post->ID ) . '" data-product_sku="' . esc_attr( $post->ID ) . '" class="button product_type_' . esc_attr( $post->product_type ) . '">' . $giftCardText . '</a>';
	}

	return  apply_filters( 'wcgc_change_add_to_cart_button', $link, $post);
}
add_filter( 'woocommerce_loop_add_to_cart_link', 'wcgc_change_add_to_cart_button' );


function preventAddToCart( $id ){
	$return = false;
	$is_giftcard = get_post_meta( $id, '_giftcard', true );

	if ( $is_giftcard == "yes" && get_option( 'woocommerce_enable_addtocart' ) == "yes" )
		$return = true;

	return apply_filters( 'wcgc_preventAddToCart', $return, $id );
}

