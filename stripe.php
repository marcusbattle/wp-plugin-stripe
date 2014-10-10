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

	public $customers, $charges;

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

		$stripe_token = isset( $_POST['stripeToken'] ) ? $_POST['stripeToken'] : null;
		$stripe_email = isset( $_POST['stripeEmail'] ) ? $_POST['stripeEmail'] : '';

		if ( empty( $_POST ) && ! isset( $stripe_token ) )
			return false;
		

		// Check to see if user exists in WordPress
		$wp_user = get_user_by( 'email', $stripe_email );

		// If user exists, get their Stripe Customer ID
		$stripe_user_id = get_post_meta( $wp_user_id, 'stripe_user_id', $stripe_user_id );

		// $token = $this->tokens->get( $stripe_token );

		echo "<pre>";
		print_r( $_POST );
		print_r( $wp_user );
		print_r( $stripe_user_id );
		echo "</pre>";
		exit;

		// Check to see if the customer exists in Stripe
		$stripe_user = $this->customers->get_by_email( $stripe_email );

		if ( ! $stripe_user ) {
			
			// Check to see if it exists in WP
			$wp_user = get_user_by( 'email', $stripe_email );

			// Create stripe user if not in Stripe OR WP
			if ( ! $wp_user ) {

				$customer_args = array(
					'email' => $stripe_email,
					'card' => $stripe_token
				);

				$stripe_user = $this->customers->create( $customer_args );

				$stripe_user_id = $stripe_user->id;

			} else {

				$stripe_user_id = get_user_meta( $wp_user->ID, 'stripe_user_id', true );

			}

		} else {

			$stripe_user_id = $stripe_user->id;

		}

		// Add card to customer
		if ( empty( $stripe_user->default_card ) ) {

		}

		// Prepare & Process charge
		$charge_args = array(
			'amount' => isset( $_POST['amount'] ) ? $_POST['amount'] : '',
			'description' => isset( $_POST['description'] ) ? $_POST['description'] : '',
			'customer' => $stripe_user_id,
			'currency' => isset( $_POST['currency'] ) ? $_POST['currency'] : 'USD'
		);

		// $charge = $this->charges->create( $charge_args );

		// Create user in WordPress
		if( ! $wp_user ) {
			
			$random_password = wp_generate_password( $length = 12, $include_standard_special_chars = false );
			$wp_user_id = wp_create_user( $stripe_email, $random_password, $stripe_email );

		} else {

			$wp_user_id = $wp_user->ID;

		}	

		// Update user meta to save Stripe User ID
		update_post_meta( $wp_user_id, 'stripe_user_id', $stripe_user_id );

		echo "<pre>";
		print_r( $stripe_user_id );
		echo "</pre>";

		exit;

	}

}

$stripe = new Stripe(); 