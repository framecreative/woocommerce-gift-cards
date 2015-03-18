<?php
/**
 * WooCommerce Gift Card Settings
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WC_Settings_Accounts
 */
class WCGC_Settings extends WC_Settings_Page
{

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->id    = 'giftcard';
		$this->label = __( 'Gift Cards',  'wcgiftcards'  );

		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
		add_action( 'woocommerce_sections_' . $this->id, array( $this, 'output_sections' ) );

		add_action( 'woocommerce_admin_field_addon_settings', array( $this, 'addon_setting' ) );
		add_action( 'woocommerce_admin_field_excludeProduct', array( $this, 'excludeProducts' ) );
	}


	/**
	 * Get sections
	 *
	 * @return array
	 */
	public function get_sections()
	{

		$sections = apply_filters( 'woocommerce_add_section_giftcard', array( '' => __( 'Gift Card Options', 'wcgiftcards' ) ) );

		return apply_filters( 'woocommerce_get_sections_' . $this->id, $sections );
	}

	/**
	 * Output sections
	 */
	public function output_sections() {
		global $current_section;

		$sections = $this->get_sections();

		if ( empty( $sections ) ) {
			return;
		}

		echo '<ul class="subsubsub">';

		$array_keys = array_keys( $sections );

		foreach ( $sections as $id => $label ) {
			echo '<li><a href="' . admin_url( 'admin.php?page=wc-settings&tab=' . $this->id . '&section=' . sanitize_title( $id ) ) . '" class="' . ( $current_section == $id ? 'current' : '' ) . '">' . $label . '</a> ' . ( end( $array_keys ) == $id ? '' : '|' ) . ' </li>';
		}

		echo '</ul><br class="clear" />';
	}

	/**
	 * Output the settings
	 */
	public function output() {
		global $current_section;

		$settings = $this->get_settings( $current_section );

 		WC_Admin_Settings::output_fields( $settings );
	}

	/**
	 * Save settings
	 */
	public function save() {
		global $current_section;

		$settings = $this->get_settings( $current_section );
		WC_Admin_Settings::save_fields( $settings );
	}


	/**
	 * Get settings array
	 *
	 * @return array
	 */
	public function get_settings( $current_section = '' ) {
		$options = '';
		if( $current_section == '' ) {

			$options = apply_filters( 'woocommerce_giftcard_settings', array(

				array( 'title' 		=> __( 'Processing Options',  'wcgiftcards'  ), 'type' => 'title', 'id' => 'giftcard_processing_options_title' ),

				array(
					'title'         => __( 'Display on Cart?',  'wcgiftcards'  ),
					'desc'          => __( 'Display the giftcard form on the cart page.',  'wcgiftcards'  ),
					'id'            => 'woocommerce_enable_giftcard_cartpage',
					'default'       => 'no',
					'type'          => 'checkbox',
					'autoload'      => false
				),

				array(
					'title'         => __( 'Display on Checkout?',  'wcgiftcards'  ),
					'desc'          => __( 'Display the giftcard form on the checkout page.',  'wcgiftcards'  ),
					'id'            => 'woocommerce_enable_giftcard_checkoutpage',
					'default'       => 'yes',
					'type'          => 'checkbox',
					'autoload'      => false
				),


				array(
					'title'         => __( 'Customize Add to Cart?',  'wcgiftcards'  ),
					'desc'          => __( 'Change Add to cart label and disable add to cart from product list.',  'wcgiftcards'  ),
					'id'            => 'woocommerce_enable_addtocart',
					'default'       => 'no',
					'type'          => 'checkbox',
					'autoload'      => false
				),


				array( 'type' => 'sectionend', 'id' => 'account_registration_options'),

				array( 'title' 		=> __( 'Gift Card Uses',  'wcgiftcards'  ), 'type' => 'title', 'id' => 'giftcard_products_title' ),

				array(
					'title'         => __( 'Shipping',  'wcgiftcards'  ),
					'desc'          => __( 'Allow customers to pay for shipping with their gift card.',  'wcgiftcards'  ),
					'id'            => 'woocommerce_enable_giftcard_charge_shipping',
					'default'       => 'no',
					'type'          => 'checkbox',
					'autoload'      => true
				),

				array(
					'title'         => __( 'Tax',  'wcgiftcards'  ),
					'desc'          => __( 'Allow customers to pay for tax with their gift card.',  'wcgiftcards'  ),
					'id'            => 'woocommerce_enable_giftcard_charge_tax',
					'default'       => 'no',
					'type'          => 'checkbox',
					'autoload'      => true
				),

				array(
					'title'         => __( 'Fee',  'wcgiftcards'  ),
					'desc'          => __( 'Allow customers to pay for fees with their gift card.',  'wcgiftcards'  ),
					'id'            => 'woocommerce_enable_giftcard_charge_fee',
					'default'       => 'no',
					'type'          => 'checkbox',
					'autoload'      => true
				),

				array( 'type' => 'sectionend', 'id' => 'product_giftcard_options'),

				array( 'type' => 'sectionend', 'id' => 'account_registration_options'),

			));
		}
		else if( $current_section == 'extensions')
		{

			$options = array( 
				array( 'type' 	=> 'sectionend', 'id' => 'giftcard_extensions' ),

				array( 'type' => 'addon_settings' ),

			); // End pages settings
		}
		return apply_filters ('get_giftcard_settings', $options, $current_section );
	}




	/**
	 * Output the frontend styles settings.
	 */
	public function addon_setting()
	{
		
			$i = 0;
			$addons = array();


			if ( ! class_exists( 'WCGC_Auto_Send' ) )
			{
				$addons[$i]["title"] = __( 'Auto Send Card', 'wcgiftcards' );
				$addons[$i]["image"] = "";
				$addons[$i]["excerpt"] = __( 'Save time creating gift cards by using this plugin.  Enable it and customers will have their gift card sent out directly upon purchase or payment.', 'wcgiftcards' );
				$addons[$i]["link"] = "https://wp-ronin.com/downloads/auto-send-email-woocommerce-gift-cards/";
				$i++;
			}

			foreach ( $addons as $addon )
			{
				echo '<li class="product" style="float:left; margin:0 1em 1em 0 !important; padding:0; vertical-align:top; width:300px;">';
				echo '<a href="' . $addon['link'] . '">';
				if ( ! empty( $addon['image'] ) ) {
					echo '<img src="' . $addon['image'] . '"/>';
				} else {
					echo '<h3>' . $addon['title'] . '</h3>';
				}
				echo '<p>' . $addon['excerpt'] . '</p>';
				echo '</a>';
				echo '</li>';
			}
		?>
		</ul>
		</div>
		<?php
	}


	/**
	 * Not implemented
	 */
	public function excludeProducts()
	{
		?>
			<tr valign="top" class="">
				<th class="titledesc" scope="row">
					<?php _e( 'Exclude products', 'wcgiftcards' ); ?>
					<img class="help_tip" data-tip='<?php _e( 'Products which gift cards can not be used on', 'wcgiftcards' ); ?>' src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
				</th>
					<td class="forminp forminp-checkbox">
					<fieldset>
						<input type="hidden" class="wc-product-search" data-multiple="true" style="width: 50%;" name="exclude_product_ids" data-placeholder="<?php _e( 'Search for a product&hellip;', 'wcgiftcards' ); ?>" data-action="woocommerce_json_search_products_and_variations" data-selected="<?php
							$product_ids = array_filter( array_map( 'absint', explode( ',', get_option( 'exclude_product_ids' ) ) ) );
							$json_ids    = array();

							foreach ( $product_ids as $product_id ) {
								$product = wc_get_product( $product_id );
								$json_ids[ $product_id ] = wp_kses_post( $product->get_formatted_name() );
							}

							echo esc_attr( json_encode( $json_ids ) );
						?>" value="<?php echo implode( ',', array_keys( $json_ids ) ); ?>" />
					</fieldset>
				</td>
			</tr>
		<?php

	}

}




function wcgc_pro_settings( $sections )
{

	$pro = array( 'pro' => __( 'In Store Credit', 'wpr-pro' ) );

	return array_merge( $sections, $pro );

}
//add_filter ('woocommerce_add_section_giftcard', 'wcgc_pro_settings' );


function wcgc_pro_add_section ( $options, $current_section )
{

	if ( $current_section == 'pro' )
	{

		$options = array(

			array( 'type' => 'sectionend', 'id' => 'wcgc_cn_settings'),

			array( 'title' 		=> __( 'In Store Credit',  'wpr-pro'  ), 'type' => 'title', 'id' => 'giftcard_processing_options_title' ),

			array(
				'name'		=> __( 'Remaining Gift Card Funds', 'wpr-pro' ),
				'desc'		=> __( 'How do you want to handle remaining funds on the gift card.', 'wpr-pro' ),
				'id'		=> 'wcgc_handle_isc',
				'std'		=> '', // WooCommerce < 2.0
				'default'	=> '', // WooCommerce >= 2.0
				'type'		=> 'select',
				'class'		=> 'chosen_select',
				'options'	=> array(
					'never'		=> __( 'Never convert to ISC', 'wpr-pro' ),
					'always'	=> __( 'Always convert to ISC', 'wpr-pro' ),
					'ask'		=> __( 'Ask customer', 'wpr-pro' )

				),
				'desc_tip' =>  true,
			),

			array( 'type' => 'sectionend', 'id' => 'wcgc_isc_settings'),
		);
	}

	return $options;

}
//add_filter ('get_giftcard_settings', 'wcgc_pro_add_section', 10, 2);



return new WCGC_Settings();
