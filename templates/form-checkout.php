<?php
/**
 * Checkout gift card form form
 */

if ( ! defined( 'ABSPATH' ) ) exit;


$info_message = apply_filters(
	WCGC()->plugin_prefix . 'gift_card_message',
	__( 'Have a giftcard?', 'woocommerce-gift-cards' ) . ' <a href="#" class="showgiftcard">' . __( 'Click here to enter your giftcard number', 'woocommerce-gift-cards' ) . '</a>'
);

wc_print_notice( $info_message, 'notice' );

?>


	<form class="checkout_giftcard" method="post" style="display:none">

		<p class="form-row form-row-first">
			<input type="text" name="giftcard_code" class="input-text" placeholder="<?php _e( 'Gift card number', 'woocommerce-gift-cards' ); ?>" id="giftcard_code" value="" >
		</p>

		<p class="form-row form-row-last">
			<input type="submit" class="button" name="apply_giftcard" value="<?php _e( 'Apply Gift card', 'woocommerce-gift-cards' ); ?>" >
		</p>

		<div class="clear"></div>
	</form>

	<?php do_action( WCGC()->plugin_prefix . 'after_checkout_form' ); ?>
