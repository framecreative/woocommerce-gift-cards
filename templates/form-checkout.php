<?php
/**
 * Checkout gift card form form
 */

if ( ! defined( 'ABSPATH' ) ) exit;


$info_message = apply_filters(
	WCGC()->plugin_prefix . 'gift_card_message',
	__( 'Have a giftcard?', 'wcgiftcards' ) . ' <a href="#" class="showgiftcard">' . __( 'Click here to enter your giftcard number', 'wcgiftcards' ) . '</a>'
);

wc_print_notice( $info_message, 'notice' );

?>


	<form class="checkout_giftcard" method="post" style="display:none">

		<p class="form-row form-row-first">
			<input type="text" name="giftcard_code" class="input-text" placeholder="<?php _e( 'Gift card number', 'wcgiftcards' ); ?>" id="giftcard_code" value="" >
		</p>

		<p class="form-row form-row-last">
			<input type="submit" class="button" name="apply_giftcard" value="<?php _e( 'Apply Gift card', 'wcgiftcards' ); ?>" >
		</p>

		<div class="clear"></div>
	</form>

	<?php do_action( WCGC()->plugin_prefix . 'after_checkout_form' ); ?>


	<script>
		jQuery(document).ready(function($) {
			$('a.showgiftcard').click(function(){
				$('.checkout_giftcard').slideToggle();
				$('#giftcard_code').focus();
				return false;
			});

			/* AJAX Coupon Form Submission */
			$('form.checkout_giftcard').submit( function() {
				var $form = $(this);

				if ( $form.is('.processing') ) return false;

				$form.addClass('processing').block({message: null, overlayCSS: {background: '#fff url(' + woocommerce_params.ajax_loader_url + ') no-repeat center', backgroundSize: '16px 16px', opacity: 0.6}});

				var data = {
					action: 			'woocommerce_apply_giftcard',
					security: 			'apply-giftcard',
					giftcard_code:		$form.find('input[name=giftcard_code]').val()
				};

				$.ajax({
					type: 		'POST',
					url: 		woocommerce_params.ajax_url,
					data:		data,
					success: 	function( code ) {
						$('.woocommerce-error, .woocommerce-message').remove();
						$form.removeClass('processing').unblock();

						if ( code ) {
							$form.before( code );
							$form.slideUp();

							$('body').trigger('update_checkout');
						}
					},
					dataType: 	"html"
				});
				return false;
			});

		});

	</script>

