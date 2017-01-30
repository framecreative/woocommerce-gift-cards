<?php
/*
Plugin Name: Frame WooCommerce Gift Cards
Plugin URI: http://framecreative.com.au
Description: Allow your customers to send gift cards.
Version: 2.0.1
Author: Frame Creative
Author URI: http://framecreative.com.au
*/

if ( ! defined( 'ABSPATH' ) ) exit;


class WC_Gift_Cards
{
	public $plugin_version = '1.0.0';
	public $plugin_prefix;
	public $plugin_url;
	public $plugin_path;
	public $plugin_basefile;
	public $plugin_basefile_path;
	public $plugin_template_path;
	public $theme_template_path;
	public $post_type = 'wc_gift_card';


	/**
	 * Instance of WCGC_Auto_Send
	 * @var class
	 */
	public $auto_send;



	/**
	 * Constructor
	 */
	public function __construct()
	{
		// Define the constants
		$this->plugin_prefix = 'wcgc_';
		$this->plugin_basefile_path = __FILE__;
		$this->plugin_basefile = plugin_basename( $this->plugin_basefile_path );
		$this->plugin_url = plugin_dir_url( $this->plugin_basefile );
		$this->plugin_path = trailingslashit( dirname( $this->plugin_basefile_path ) );
		$this->plugin_template_path = $this->plugin_path . 'templates/';
		$this->theme_template_path = 'woocommerce/gift-cards/';

		// Load after WooCommerce
		add_action( 'woocommerce_init', array( $this, 'load' ) );
	}



	/**
	 *
	 */
	public function load()
	{
		if ( $this->is_woocommerce_activated() )
		{
			$this->includes();
			$this->hooks();

			$this->auto_send = new WCGC_Auto_Send();
			new WCGC_Checkout_Hooks();
		}
	}



	/**
	 * Include necessary files
	 */
	public function includes()
	{
		if ( is_admin() )
		{
			// Create all admin functions and pages
			require_once $this->plugin_path . 'admin/columns.php';
			require_once $this->plugin_path . 'admin/metabox.php';
			
		}

		// Required on checkout
		require_once $this->plugin_path . 'admin/functions.php';

		require_once $this->plugin_path . 'includes/class-auto-send.php';
		require_once $this->plugin_path . 'includes/class-gift-card.php';
		require_once $this->plugin_path . 'includes/class-checkout-hooks.php';

		require_once $this->plugin_path . 'includes/giftcard-product.php';
		require_once $this->plugin_path . 'includes/giftcard-forms.php';
		require_once $this->plugin_path . 'includes/giftcard-paypal.php';
		require_once $this->plugin_path . 'includes/giftcard-shortcodes.php';

		require_once $this->plugin_path . 'includes/giftcard-functions.php';
		require_once $this->plugin_path . 'includes/giftcard-meta.php';

	}


	/**
	 * Run action and filter hooks
	 */
	public function hooks()
	{
		global $wcgc_woo_giftcard_settings;
		$wcgc_woo_giftcard_settings = get_option( 'wcgc_wg_options' );

		add_action( 'init', array( $this, 'create_post_type' ) );
		add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_settings_page' ), 10, 1 );
		add_action( 'wp_enqueue_scripts', array( $this, 'load_frontend_scripts' ) );

		if ( is_admin() )
		{
			add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_scripts' ), 99 );
		}

		// Front end hooks
		add_action( 'woocommerce_before_checkout_form', array( $this, 'render_checkout_form' ), 10 );
		add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'render_add_to_cart_form' ) );
		add_filter( 'woocommerce_get_item_data', array( $this, 'render_cart_item_fields' ), 10, 2 );

	}


	/**
	 * Queue up the JavaScript file for the admin page, only on our admin page
	 *
	 * @param  string $hook The current page in the admin
	 *
	 * @return void
	 * @access public
	 */
	public function load_admin_scripts( $hook )
	{
		global $wp_scripts;

		if ( $this->post_type != $hook && 'post-new.php' != $hook && 'post.php' != $hook )
		{
			return;
		}

		wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css' );

		$jquery_version = isset( $wp_scripts->registered['jquery-ui-core']->ver ) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.9.2';

		wp_enqueue_script( 'woocommerce_writepanel' );

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-datepicker' );

	}


	/**
	 *
	 */
	public function load_frontend_scripts()
	{
		wp_enqueue_script(
			'woocommerce-gift-cards',
			$this->plugin_url . 'assets/js/woocommerce-gift-cards.js',
			array('jquery-blockui'),
			$this->plugin_version,
			true
		);
	}


	/**
	 * Adds WC settings page
	 *
	 * @param $settings
	 * @return mixed|void
	 */
	public function add_settings_page( $settings )
	{
		$settings[] = include( $this->plugin_path . 'admin/settings-page.php' );

		return apply_filters( $this->plugin_prefix . 'setting_classes', $settings );
	}



	/**
	 * Register custom post types
	 */
	public function create_post_type()
	{
		$show_in_menu = current_user_can( 'manage_woocommerce' ) ? 'woocommerce' : true;

		register_post_type( $this->post_type,
			array(
				'labels'              => array(
					'name'               => __( 'Gift Cards', 'woocommerce-gift-cards' ),
					'singular_name'      => __( 'Gift Card', 'woocommerce-gift-cards' ),
					'menu_name'          => _x( 'Gift Cards', 'Admin menu name', 'woocommerce-gift-cards' ),
					'add_new'            => __( 'Add Gift Card', 'woocommerce-gift-cards' ),
					'add_new_item'       => __( 'Add New Gift Card', 'woocommerce-gift-cards' ),
					'edit'               => __( 'Edit', 'woocommerce-gift-cards' ),
					'edit_item'          => __( 'Edit Gift Card', 'woocommerce-gift-cards' ),
					'new_item'           => __( 'New Gift Card', 'woocommerce-gift-cards' ),
					'view'               => __( 'View Gift Cards', 'woocommerce-gift-cards' ),
					'view_item'          => __( 'View Gift Card', 'woocommerce-gift-cards' ),
					'search_items'       => __( 'Search Gift Cards', 'woocommerce-gift-cards' ),
					'not_found'          => __( 'No Gift Cards found', 'woocommerce-gift-cards' ),
					'not_found_in_trash' => __( 'No Gift Cards found in trash', 'woocommerce-gift-cards' ),
					'parent'             => __( 'Parent Gift Card', 'woocommerce-gift-cards' )
				),
				'public'              => true,
				'has_archive'         => true,
				'publicly_queryable'  => false,
				'exclude_from_search' => false,
				'show_in_menu'        => $show_in_menu,
				'hierarchical'        => false,
				'supports'            => array( 'title', 'comments' )
			)
		);

		register_post_status( 'zerobalance', array(
			'label'                     => __( 'Zero Balance', 'woocommerce-gift-cards' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Zero Balance <span class="count">(%s)</span>', 'Zero Balance <span class="count">(%s)</span>' )
		) );

	}


	/**
	 * Render the Gift card form for the checkout
	 *
	 * @return void
	 */
	function render_checkout_form()
	{
		if ( get_option( 'woocommerce_enable_giftcard_checkoutpage' ) == 'yes' )
		{
			wc_get_template('form-checkout.php', array(), $this->theme_template_path, $this->plugin_template_path );
		}
	}


	/**
	 * @param $gift_card WC_Gift_Card object
	 */
	function send_gift_card_email( $gift_card )
	{
		$send_email = get_bloginfo( 'admin_email' );

		$subject = apply_filters(
			$this->plugin_prefix . 'gift_card_email_subject',
			__( 'You have been sent a gift card', 'woocommerce-gift-cards' )
		);

		$email_html = $this->generate_email_html( $subject, $gift_card );

		$headers = "From: " . $send_email . "\r\n" . " Reply-To: " . $send_email . "\r\n" . " Content-Type: text/html\r\n";

		// Send the mail
		add_filter( 'wp_mail_from', array( $this, 'from_email' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'from_name' ) );

		wp_mail( $gift_card->get_to_email(), $subject, $email_html, $headers );

		remove_filter( 'wp_mail_from', array( $this, 'from_email' ) );
		remove_filter( 'wp_mail_from_name', array( $this, 'from_name' ) );

		$gift_card->set_email_sent();
	}


	/**
	 * @param $subject
	 * @param $gift_card WC_Gift_Card object
	 *
	 * @return string
	 */
	function generate_email_html( $subject, $gift_card )
	{
		$mailer = WC()->mailer();

		// Get email body
		ob_start();
		wc_get_template('email-gift-recipient.php', array( 'gift_card' => $gift_card ), $this->theme_template_path, $this->plugin_template_path );
		$email_body = ob_get_clean();
		$email_body = apply_filters( $this->plugin_prefix . 'gift_card_email_body', $email_body, $gift_card );

		$email_html = $mailer->wrap_message( $subject, $email_body );

		return $email_html;
	}



	/**
	 * @return mixed|void
	 */
	function from_email()
	{
		return apply_filters( $this->plugin_prefix . 'from_email', WC()->mailer()->get_from_address() );
	}



	/**
	 * @return mixed|void
	 */
	function from_name()
	{
		return apply_filters( $this->plugin_prefix . 'from_name', WC()->mailer()->get_from_name() );
	}



	/**
	 *
	 */
	function render_add_to_cart_form()
	{
		global $post;

		$is_gift_card = get_post_meta( $post->ID, '_giftcard', true );

		if ( $is_gift_card == 'yes' )
		{
			do_action( WCGC()->plugin_prefix . 'before_all_giftcard_fields', $post );

			wc_get_template('form-add-to-cart.php', array(), WCGC()->theme_template_path, WCGC()->plugin_template_path );
		}
	}


	/**
	 * TODO make field names customisable and translatable
	 * @param $cart_item
	 */
	function render_cart_item_fields( $fields, $cart_item )
	{
		if ( $this->is_gift_card( $cart_item['product_id'] ) )
		{
			$fields[] = array(
				'name' => 'Recipient name',
				'value' => isset( $cart_item['variation']['To'] ) ? $cart_item['variation']['To'] : ''
			);

			$fields[] = array(
				'name' => 'Recipient email',
				'value' => isset( $cart_item['variation']['To Email'] ) ? $cart_item['variation']['To Email'] : ''
			);

			$fields[] = array(
				'name' => 'Personal message',
				'value' => isset( $cart_item['variation']['Note'] ) ? $cart_item['variation']['Note'] : ''
			);
		}
		return $fields;
	}



	/**
	 * @param $product_id
	 *
	 * @return bool
	 */
	function is_gift_card( $product_id )
	{
		$gift_card = get_post_meta( $product_id, '_giftcard', true );

		if ( $gift_card != 'yes' )
			return false;

		return true;
	}


	/**
	 * @param $gift_card instance of WC_Gift_Card
	 *
	 * @return bool|WP_Error
	 */
	function apply_gift_card_to_cart( $gift_card )
	{
		// Validate gift card already added to cart
		if ( isset( WC()->session->giftcard_post ) )
		{
			return new WP_Error( 'wc-gift-cards', __( 'A Gift Card is already in the cart!', 'woocommerce-gift-cards' ) );
		}

		if ( ! $gift_card->exists() )
		{
			return new WP_Error( 'wc-gift-cards', __( 'Gift Card does not exist.', 'woocommerce-gift-cards' ) );
		}

		if ( $gift_card->is_expired() )
		{
			return new WP_Error( 'wc-gift-cards', __( 'Gift Card has expired.', 'woocommerce-gift-cards' ) );
		}

		if ( $gift_card->get_balance() <= 0 )
		{
			return new WP_Error( 'wc-gift-cards', __( 'Gift Card does not have a balance left.', 'woocommerce-gift-cards' ) );
		}

		return true;
	}




	/**
	 * Check if woocommerce is activated
	 */
	public function is_woocommerce_activated()
	{
		$blog_plugins = get_option( 'active_plugins', array() );
		$site_plugins = get_site_option( 'active_sitewide_plugins', array() );
		$woocommerce_basename = plugin_basename( WC_PLUGIN_FILE );

		if( ( in_array( $woocommerce_basename, $blog_plugins ) || isset( $site_plugins[$woocommerce_basename] ) ) && version_compare( WC_VERSION, '2.1', '>=' )) {
			return true;
		} else {
			return false;
		}
	}




	protected static $_instance = null;
	public static function instance()
	{
		if ( is_null( self::$_instance ) )
			self::$_instance = new self();
		return self::$_instance;
	}
}


function WCGC()
{
	return WC_Gift_Cards::instance();
}

WCGC();
