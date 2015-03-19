

<tr class="giftcard">
	<th><?php _e( 'Gift Card', 'woocommerce-gift-cards' ); ?> </th>

	<td>-<?php echo wc_price( $discount ); ?>
		<a href="<?php echo add_query_arg( 'remove_giftcards', '1', WC()->cart->get_checkout_url() ) ?>">
			[<?php _e( 'Remove', 'woocommerce-gift-cards' ); ?>]
		</a>
	</td>
</tr>