

	<div class="message">

		<?php _e( 'Dear', 'wcgiftcards' ); ?> <?php echo $gift_card->get_to_name(); ?>,<br /><br />

		<?php echo $gift_card->get_from_name(); ?> <?php _e('has selected a', 'wcgiftcards' ); ?> <strong><a href="<?php bloginfo( 'url' ); ?>"><?php bloginfo( 'name' ); ?></a></strong> <?php _e( 'Gift Card for you! This card can be used for online purchases at', 'wcgiftcards' ); ?> <?php bloginfo( 'name' ); ?>. <br />

		<h4><?php _e( 'Gift Card Amount', 'wcgiftcards' ); ?>: <?php echo wc_price( $gift_card->get_balance() ); ?></h4>
		<h4><?php _e( 'Gift Card Number', 'wcgiftcards' ); ?>: <?php echo $gift_card->get_number(); ?></h4>

		<?php
		if ( $gift_card->get_expiry_date() )
		{
			echo __( 'Expiration Date', 'wcgiftcards' ) . ': ' . date_i18n( get_option( 'date_format' ), strtotime( $gift_card->get_expiry_date() ) );
		}
		?>
	</div>

	<div style="padding-top: 10px; padding-bottom: 10px; border-top: 1px solid #ccc;">
		<?php echo $gift_card->get_note(); ?>
	</div>

	<div style="padding-top: 10px; border-top: 1px solid #ccc;">
		<?php _e( 'Using your Gift Card is easy', 'wcgiftcards' ); ?>:

		<ol>
			<li><?php _e( 'Shop at', 'wcgiftcards' ); ?> <?php bloginfo( 'name' ); ?></li>
			<li><?php _e( 'Select "Pay with a Gift Card" during checkout.', 'wcgiftcards' ); ?></li>
			<li><?php _e( 'Enter your card number.', 'wcgiftcards' ); ?></li>
		</ol>
	</div>