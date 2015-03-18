<?php
/**
 * Admin functions for the post type.
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
// Admin Columns
		


function wcgc_add_columns( $columns ) {
	$new_columns = ( is_array( $columns ) ) ? $columns : array();
	unset( $new_columns['title'] );
	unset( $new_columns['date'] );
	unset( $new_columns['comments'] );

	//all of your columns will be added before the actions column on the Giftcard page

	$new_columns["title"]		= __( 'Giftcard Number', 'wcgiftcards' );
	$new_columns["amount"]		= __( 'Giftcard Amount', 'wcgiftcards' );
	$new_columns["balance"]		= __( 'Remaining Balance', 'wcgiftcards' );
	$new_columns["buyer"]		= __( 'Buyer', 'wcgiftcards' );
	$new_columns["recipient"]	= __( 'Recipient', 'wcgiftcards' );
	$new_columns["expiry_date"]	= __( 'Expiry date', 'wcgiftcards' );

	$new_columns['comments']	= $columns['comments'];
	$new_columns['date']		= __( 'Creation Date', 'wcgiftcards' );

	return  apply_filters( 'wcgc_giftcard_columns', $new_columns);
}
add_filter( 'manage_edit-' . WCGC()->post_type . '_columns', 'wcgc_add_columns' );



/**
 * Define our custom columns shown in admin.
 * @param  string $column
 *
 */
function wcgc_custom_columns( $column )
{
	global $post, $woocommerce;

	switch ( $column ) {

		case "buyer" :
			echo '<div><strong>' . esc_html( get_post_meta( $post->ID, 'wcgc_from', true ) ) . '</strong><br />';
			echo '<span style="font-size: 0.9em">' . esc_html( get_post_meta( $post->ID, 'wcgc_email_from', true ) ) . '</div>';
			break;

		case "recipient" :
			echo '<div><strong>' . esc_html( get_post_meta( $post->ID, 'wcgc_to', true ) ) . '</strong><br />';
			echo '<span style="font-size: 0.9em">' . esc_html( get_post_meta( $post->ID, 'wcgc_email_to', true ) ) . '</span></div>';
		break;

		case "amount" :
			$price = get_post_meta( $post->ID, 'wcgc_amount', true );
			echo wc_price( $price );
		break;

		case "balance" :
			$price = get_post_meta( $post->ID, 'wcgc_balance', true );
			echo wc_price( $price );
		break;

		case "expiry_date" :
			$expiry_date = get_post_meta( $post->ID, 'wcgc_expiry_date', true );

			if ( $expiry_date )
				echo esc_html( date_i18n( 'F j, Y', strtotime( $expiry_date ) ) );
			else
				echo '&ndash;';
		break;
	}
}
add_action( 'manage_' . WCGC()->post_type . '_posts_custom_column', 'wcgc_custom_columns', 2 );



