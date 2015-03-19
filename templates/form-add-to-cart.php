<?php


?>

	<div class="wcgc-add-to-cart">

		<div class="wcgc-product-message"><?php _e('All fields below are required', 'woocommerce-gift-cards' ); ?></div>

		<?php  do_action( WCGC()->plugin_prefix . 'before_product_fields' ); ?>

		<input name="wcgc_to" id="wcgc_to" class="input-text" placeholder="Recipient name">

		<input type="email" name="wcgc_to_email" id="wcgc_to_email" class="input-text" placeholder="Recipient email" >

		<textarea class="input-text" id="wcgc_note" name="wcgc_note" rows="2" placeholder="Enter a personal message"></textarea>

		<?php do_action( WCGC()->plugin_prefix . 'after_product_fields' ); ?>
	</div>


	<script>
		jQuery(document).ready( function( $ ){ $( ".quantity" ).hide(); });
	</script>