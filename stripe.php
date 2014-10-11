<?php
	
/**
 * Plugin Name: Stripe By Marcus
 * Plugin URI: http://marcusbattle.com
 * Description: Plugin that adds Stripe integration
 * Version: 0.1.0
 * Author: Marcus Battle
 * Author URI: http://marcusbattle.com
 * License: GPL2
*/

class Stripe {

	public $customers, $charges, $cards, $tokens;

	public function __construct() {

		include plugin_dir_path( __FILE__ ) . 'api.php';
		include plugin_dir_path( __FILE__ ) . 'objects/customers.php';
		include plugin_dir_path( __FILE__ ) . 'objects/cards.php';
		include plugin_dir_path( __FILE__ ) . 'objects/charges.php';
		include plugin_dir_path( __FILE__ ) . 'objects/tokens.php';

		$this->customers = new Customers();
		$this->cards = new Cards();
		$this->charges = new Charges();
		$this->tokens = new Tokens();
		
		
		// Attempt to connect to the account
		
		// If we can't connect then set admin notice
  		
		add_shortcode( 'checkout', array( $this, 'shortcode_checkout' ) );
		add_action( 'init', array( $this, 'process_checkout' ) );
		
		add_action( 'admin_menu', array( $this, 'build_pages' ) );
		add_action( 'init', array( $this, 'save_pages' ) );

		$account = $this->tokens->api('account');

		if ( isset( $account->error ) ) {
		
			wp_redirect( $redirect_to . '?error=' . urlencode( $account->error->message ) );
			exit;

		}

	}

	public function build_pages() {
		
		add_menu_page( 'Stripe', 'Stripe', 'manage_options', 'stripe', 'page_stripe' , '', 100 );

		function page_stripe() { 

			ob_start();

			include plugin_dir_path( __FILE__ ) . 'pages/stripe.php';
			
			$page = ob_get_contents();
			ob_end_clean();

			echo $page;

		}

	}

	public function save_pages() {

		if ( isset( $_POST ) && ! empty( $_POST ) ) {
			
			if ( isset( $_POST['stripe_test_secret_key'] ) && isset( $_POST['stripe_test_public_key'] ) && isset( $_POST['stripe_live_secret_key'] ) && isset( $_POST['stripe_live_public_key'] ) ) {

				if ( isset( $_POST['stripe_mode'] ) && $_POST['stripe_mode'] == 'on' ) {
					update_option( 'stripe_mode', 1 );
				} else {
					update_option( 'stripe_mode', 0 );
				}

				update_option( 'stripe_test_secret_key', $_POST['stripe_test_secret_key'] );
				update_option( 'stripe_test_public_key', $_POST['stripe_test_public_key'] );
				update_option( 'stripe_live_secret_key', $_POST['stripe_live_secret_key'] );
				update_option( 'stripe_live_public_key', $_POST['stripe_live_public_key'] );
				
				wp_redirect( $_SERVER['HTTP_REFERER'] . '&updated' );
				exit;

			}

		}

	}

	/**
	 * Creates a Stripe Checkout button
	 */
	public function shortcode_checkout( $atts ) {

		ob_start();

		include plugin_dir_path( __FILE__ ) . 'checkout.php';
		
		$form = ob_get_contents();
		ob_end_clean();

		return $form;

	}

	/**
	 * 
	 */
	public function process_checkout() { 

		// Check to see if amount, description and token are preent
		if ( isset( $_POST ) && ! empty( $_POST ) ) {

			$redirect_to = home_url( $_SERVER['REQUEST_URI']);

			$stripe_token = isset( $_POST['stripeToken'] ) ? $_POST['stripeToken'] : '';
			$stripe_token_type = isset( $_POST['stripeType'] ) ? $_POST['stripeType'] : '';

			$first_name = isset( $_POST['first_name'] ) ? $_POST['first_name'] : '';
			$last_name = isset( $_POST['last_name'] ) ? $_POST['last_name'] : '';
			$purchase_description = isset( $_POST['description'] ) ? $_POST['description'] : '';
			
			$quantity = isset( $_POST['quantity'] ) ? $_POST['quantity'] : '';
			$amount = isset( $_POST['amount'] ) ? $_POST['amount'] : '';

			$email = isset( $_POST['stripeEmail'] ) ? $_POST['stripeEmail'] : '';

			// Validate fields before processing transaction
			if ( empty( $stripe_token ) || empty( $stripe_token_type ) || empty( $amount ) || empty( $email ) )
				return false;

		} else {

			// Cancel transaction
			return false;

		}

		// Check to see if we can connect to Stripe
		$account = $this->tokens->api('account');

		if ( isset( $account->error ) ) {
		
			wp_redirect( $redirect_to . '?error=' . urlencode( $account->error->message ) );
			exit;

		}

		// Check to see if user exists in WordPress
		$wp_user = get_user_by( 'email', $email );

		// If user exists, get their Stripe Customer ID
		if ( $wp_user )
			$stripe_user_id = get_post_meta( $wp_user->ID, 'stripe_user_id', true );


		// If no stripe user present, check to see if one exists in Stripe
		if ( ! $stripe_user_id ) {
			
			$stripe_user = $this->customers->get_by_email( $email );

			if ( ! $stripe_user ) {

				$customer_args = array(
					'description' => $first_name . ' ' . $last_name,
					'email' => $email,
					$stripe_token_type => $stripe_token
				);

				$stripe_user = $this->customers->create( $customer_args );
				$stripe_user_id = $stripe_user->id;

			} else {

				$stripe_user_id = $stripe_user->id;

			}

		}	

		// Check to see if there was an error in finding/creating a user
		if ( ! $stripe_user_id )
			return false;


		// Add card to customer
		$card_args = array(
			'id' => $stripe_user_id,
			'card' => $stripe_token
		);

		$card = $this->cards->create( $card_args );

		if ( isset( $card->error ) ) {
			// Do something
		}

		// Prepare & Process charge
		$charge_args = array(
			'amount' => $amount,
			'description' => $purchase_description,
			'customer' => $stripe_user_id,
			'card' => $card->id,
			'currency' => isset( $_POST['currency'] ) ? $_POST['currency'] : 'USD'
		);

		$charge = $this->charges->create( $charge_args );

		// Create user in WordPress
		if( ! $wp_user ) {
			
			$random_password = wp_generate_password( $length = 12, $include_standard_special_chars = false );
			$wp_user_id = wp_create_user( $email, $random_password, $email );

		} else {

			$wp_user_id = $wp_user->ID;

		}	

		// Update user meta to save Stripe User ID
		update_post_meta( $wp_user_id, 'stripe_user_id', $stripe_user_id );

		if ( $charge ) {
			wp_redirect( $redirect_to . '?payment_id=' . $charge->id );
			exit;
		} else {
			wp_redirect( $redirect_to . '?error=There was a problem processing your card' );
			exit;
		}


	}

}

$stripe = new Stripe(); 