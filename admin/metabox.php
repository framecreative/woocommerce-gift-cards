<?php


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**	
 * Sets up the new meta box for the creation of a gift card.
 * Removes the other three Meta Boxes that are not needed.
 *
 */
function wcgc_meta_boxes() {
	global $post;

	add_meta_box(
		'wcgc-woocommerce-data',
		__( 'Gift Card Data', 'woocommerce-gift-cards' ),
		'wcgc_meta_box',
		WCGC()->post_type,
		'normal',
		'high'
	);

	$data = get_post_meta( $post->ID );

	if ( isset( $data['wcgc_id'] ) )
		if ( $data['wcgc_id'][0] <> '' )
			add_meta_box(
				'wcgc-order-data',
				__( 'Gift Card Informaiton', 'woocommerce-gift-cards' ),
				'wcgc_info_meta_box',
				'shop_order',
				'side',
				'default'
			);

	if ( ! isset( $_GET['action'] ) ) 
		remove_post_type_support( WCGC()->post_type, 'title' );
	
	if ( isset ( $_GET['action'] ) )
		add_meta_box(
			'wcgc-more-options',
			__( 'Additional Card Options', 'woocommerce-gift-cards' ),
			'wcgc_options_meta_box',
			WCGC()->post_type,
			'side',
			'low'
		);		

	remove_meta_box( 'woothemes-settings', WCGC()->post_type , 'normal' );
	remove_meta_box( 'commentstatusdiv', WCGC()->post_type , 'normal' );
	remove_meta_box( 'commentsdiv', WCGC()->post_type , 'normal' );
	remove_meta_box( 'slugdiv', WCGC()->post_type , 'normal' );
}
add_action( 'add_meta_boxes', 'wcgc_meta_boxes' );


/**
 * Creates the Giftcard Meta Box in the admin control panel when in the Giftcard Post Type.  Allows you to create a giftcard manually.
 * @param  [type] $post
 * @return [type]
 */
function wcgc_meta_box( $post ) {
	global $woocommerce;

	wp_nonce_field( 'woocommerce_save_data', 'woocommerce_meta_nonce' );
	?>
	<style type="text/css">
		#edit-slug-box, #minor-publishing-actions { display:none }

		.form-field input, .form-field textarea { width:100%;}

		input[type="checkbox"], input[type="radio"] { float: left; width:16px;}
	</style>

	<div id="giftcard_options" class="panel woocommerce_options_panel">
	<?php
	
	do_action( 'wcgc_woocommerce_options_before_sender' );

	// Description
	woocommerce_wp_textarea_input(
		array(
			'id' 			=> 'wcgc_description',
			'label'			=> __( 'Gift Card description', 'woocommerce-gift-cards' ),
			'placeholder' 	=> '',
			'description' 	=> __( 'Optionally enter a description for this gift card for your reference.', 'woocommerce-gift-cards' ),
		)
	);
	
	do_action( 'wcgc_woocommerce_options_after_description' );

	echo '<h2>' . __('Who are you sending this to?',  'woocommerce-gift-cards' ) . '</h2>';
	// To
	woocommerce_wp_text_input(
		array(
			'id' 			=> 'wcgc_to',
			'label' 		=> __( 'To', 'woocommerce-gift-cards' ),
			'placeholder' 	=> '',
			'description' 	=> __( 'Who is getting this gift card.', 'woocommerce-gift-cards' ),
		)
	);
	// To Email
	woocommerce_wp_text_input(
		array(
			'id' 			=> 'wcgc_email_to',
			'type' 			=> 'email',
			'label' 		=> __( 'Email To', 'woocommerce-gift-cards' ),
			'placeholder' 	=> '',
			'description' 	=> __( 'What email should we send this gift card to.', 'woocommerce-gift-cards' ),
		)
	);

	// From
	woocommerce_wp_text_input(
		array(
			'id' 			=> 'wcgc_from',
			'label' 		=> __( 'From', 'woocommerce-gift-cards' ),
			'placeholder' 	=> '',
			'description' 	=> __( 'Who is sending this gift card.', 'woocommerce-gift-cards' ),
		)
	);
	// From Email
	woocommerce_wp_text_input(
		array(
			'id' 			=> 'wcgc_email_from',
			'type'	 		=> 'email',
			'label' 		=> __( 'Email From', 'woocommerce-gift-cards' ),
			'placeholder' 	=> '',
			'description' 	=> __( 'What email account is sending this gift card.', 'woocommerce-gift-cards' ),
		)
	);
	
	do_action( 'wcgc_woocommerce_options_after_sender' );

	echo '</div><div class="panel woocommerce_options_panel">';

	echo '<h2>' . __('Personalize it',  'woocommerce-gift-cards' ) . '</h2>';
	
	do_action( 'wcgc_woocommerce_options_before_personalize' );
	
	// Amount
	woocommerce_wp_text_input(
		array(
			'id'     					=> 'wcgc_amount',
			'label'   					=> __( 'Gift Card Amount', 'woocommerce-gift-cards' ),
			'placeholder'  				=> '0.00',
			'description'  				=> __( 'Value of the Gift Card.', 'woocommerce-gift-cards' ),
			'type'    					=> 'number',
			'custom_attributes' 		=> array( 'step' => 'any', 'min' => '0' )
		)
	);
	if ( isset( $_GET['action']  ) ) {
		if ( $_GET['action'] == 'edit' ) {
			// Remaining Balance
			woocommerce_wp_text_input(
				array(
					'id'    			=> 'wcgc_balance',
					'label'    			=> __( 'Gift Card Balance', 'woocommerce-gift-cards' ),
					'placeholder'  		=> '0.00',
					'description'  		=> __( 'Remaining Balance of the Gift Card.', 'woocommerce-gift-cards' ),
					'type'    			=> 'number',
					'custom_attributes' => array( 'step' => 'any', 'min' => '0' )
				)
			);
		}
	}
	// Notes
	woocommerce_wp_textarea_input(
		array(
			'id' 						=> 'wcgc_note',
			'label' 					=> __( 'Gift Card Note', 'woocommerce-gift-cards' ),
			'description' 				=> __( 'Enter a message to your customer.', 'woocommerce-gift-cards' ),
			'class' 					=> 'short'
			
		)
	);

	// Expiry date
	woocommerce_wp_text_input(
		array(
			'id' 						=> 'wcgc_expiry_date',
			'label' 					=> __( 'Expiry date', 'woocommerce-gift-cards' ),
			'placeholder' 				=> _x( 'Never expire', 'placeholder', 'woocommerce-gift-cards' ),
			'description' 				=> __( 'The date this Gift Card will expire, <code>YYYY-MM-DD</code>.', 'woocommerce-gift-cards' ),
			'class' 					=> 'date-picker, short',
			'custom_attributes' 		=> array( 'pattern' => "[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" )
		)
	);

	do_action( 'wcgc_woocommerce_options' );
	do_action( 'wcgc_woocommerce_options_after_personalize' );


	echo '</div>';
}



/**
 * Creates the Giftcard Regenerate Meta Box in the admin control panel when in the Giftcard Post Type.  Allows you to click a button regenerate the number.
 * @param  [type] $post
 * @return [type]
 */
function wcgc_options_meta_box( $post ) {
	global $woocommerce;

	wp_nonce_field( 'woocommerce_save_data', 'woocommerce_meta_nonce' );	
	
	echo '<div id="giftcard_regenerate" class="panel woocommerce_options_panel">';
	echo '    <div class="options_group">';

	if( $post->post_status <> 'zerobalance' ) {
		// Regenerate the Card Number
		woocommerce_wp_checkbox( array( 'id' => 'wcgc_resend_email', 'label' => __( 'Send Gift Card Email', 'woocommerce-gift-cards' ) ) );

		// Regenerate the Card Number
		woocommerce_wp_checkbox( array( 'id' => 'wcgc_regen_number', 'label' => __( 'Regenerate Card Number', 'woocommerce-gift-cards' ) ) );

		do_action( 'wcgc_add_more_options' );

	} else {
		_e( 'No additional options available. Zero balance', 'woocommerce-gift-cards' );

		
	}

	echo '    </div>';
	echo '</div>';

}



function wcgc_info_meta_box( $post ) {
	global $wpdb;
	
	$data = get_post_meta( $post->ID );

	$orderCardNumber 	= wcgc_get_order_card_number( $post->ID );
	$orderCardBalance 	= wcgc_get_order_card_balance( $post->ID );
	$orderCardPayment 	= wcgc_get_order_card_payment( $post->ID );
	$isAlreadyRefunded	= wcgc_get_order_refund_status( $post->ID );
	
	echo '<div id="giftcard_regenerate" class="panel woocommerce_options_panel">';
	echo '    <div class="options_group">';
		echo '<ul>';
			if ( isset( $orderCardNumber ) )
				echo '<li>' . __( 'Gift Card #:', 'woocommerce-gift-cards' ) . ' ' . esc_attr( $orderCardNumber ) . '</li>';

			if ( isset( $orderCardPayment ) )
				echo '<li>' . __( 'Payment:', 'woocommerce-gift-cards' ) . ' ' . wc_price( $orderCardPayment ) . '</li>';

			if ( isset( $orderCardBalance ) )
				echo '<li>' . __( 'Balance remaining:', 'woocommerce-gift-cards' ) . ' ' . wc_price( $orderCardBalance ) . '</li>';

		echo '</ul>';

		$giftcard_found = wcgc_get_giftcard_by_code( $orderCardNumber );

		if ( $giftcard_found ) {
			echo '<div>';
				$link = 'post.php?post=' . $giftcard_found . '&action=edit';
				echo '<a href="' . admin_url( $link ) . '">' . __('Access Gift Card', 'woocommerce-gift-cards') . '</a>';
				if( isset( $isAlreadyRefunded ) )
					echo  '<br /><span style="color: #dd0000;">' . __( 'Gift card refunded ', 'woocommerce-gift-cards' ) . ' ' . wc_price( $orderCardPayment ) . '</span>';
			echo '</div>';
		
		}

	echo '    </div>';
	echo '</div>';
}

function wcgc_giftcard_usage_data( $post ) {

	$giftcardIDs = get_post_meta( $post->ID, 'wcgc_existingOrders_id', true );

	if( ! empty($giftcardIDs) ) {
	?>

		<div id="giftcard_sidebar" style="position: absolute; top:50px; right:76px; padding:15px; background: #fff;">
		
									
			<h2 style="margin: 0;">
				<span><?php _e( 'Card Usage Details', 'woocommerce-gift-cards' ); ?></span>
			</h2>


			<?php 
			foreach ($giftcardIDs as $giftID ) { 

				$giftcardPayment = wcgc_get_order_card_payment( $post->ID );
				$giftcarBalance = wcgc_get_order_card_balance( $post->ID );
				$giftcarBalance -= $giftcardPayment;
				$orederLink = admin_url( 'edit.php?post_type=download&page=edd-payment-history&view=view-order-details&id=' . $giftID );

			?>

				<div class="edd-admin-box-inside">
					<p>
						<strong><?php _e( 'Order Number:', 'woocommerce-gift-cards' ); ?></strong>&nbsp;
						<span><a href="<?php echo $orederLink; ?>"><?php echo esc_attr( $giftID ); ?></a></span>
						<br />
						<strong><?php _e( 'Amount Used:', 'woocommerce-gift-cards' ); ?></strong>&nbsp;
						<span><?php echo edd_format_giftcard_rate( 0, $giftcardPayment ); ?></span>
						<br />
						<strong><?php _e( 'Card Balance After Order:', 'woocommerce-gift-cards' ); ?></strong>&nbsp;
						<span><?php echo edd_format_giftcard_rate( 0, $giftcarBalance ); ?></span>
					</p>
				</div>

			<?php } ?>

		</div>
		<?php
	}
}

//add_action ('edd_giftcard_sidebar', 'edd_giftcard_usage_data', 10, 1 );



