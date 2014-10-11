<?php 
	
class Stripe_API {

	private $test_secret_key, $live_secret_key;
	public $test_public_key, $live_public_key, $is_live;

	public $object;

	public function __construct() {

		$this->test_secret_key = get_option('stripe_test_secret_key');
		$this->test_public_key = get_option('stripe_test_public_key');
		$this->live_secret_key = get_option('stripe_live_secret_key');
		$this->live_public_key = get_option('stripe_live_public_key');

		$this->is_live = get_option( 'stripe_mode' );

	}

	public function create( $post_args = array() ) { 

		$id = isset( $post_args['id'] ) ? $post_args['id'] : '';

		if ( $id ) {
			$endpoint = 'customers' . '/' . $id . '/' . $this->object;
			unset( $post_args['id'] );
		} else {
			$endpoint = $this->object;
		}

		$request_args = array(
			'body' => $post_args
		);

		return self::api( $endpoint, $request_args, 'POST' );

	}

	/**
	 * Retrieve a single record for the object
	 */
	public function get( $id ) { 

		if ( ! $id )
			return false;

		$endpoint = $this->object . '/' . $id;
		
		return self::api( $endpoint );

	}

	/**
	 * Update a single record
	 */
	public function update( $post_args = array() ) { 

		$id = isset( $post_args['id'] ) ? $post_args['id'] : '';

		if ( ! $id )
			return false;

		unset( $post_args['id'] );

		$request_args = array(
			'body' => $post_args
		);

		$endpoint = $this->object . '/' . $id;

		return self::api( $endpoint, $request_args, 'POST' );

	}

	public function delete() { }

	/**
	 * Lists all of the objects
	 */
	public function all( $post_args = array() ) { 

		$request_args = array(
			'body' => array(
				'limit' => 20,
				'include[]' => 'total_count'
			)
		);

		$list = self::api( $this->object, $request_args );

		return $list;

	}

	public function api( $endpoint, $args = array(), $method = 'GET' ) { 

		$url = 'https://api.stripe.com/v1/' . $endpoint;

		if ( $this->is_live )
			$key = $this->live_secret_key;
		else
			$key = $this->test_secret_key;

		$request_args = array(
			'method' => $method,
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( $key . ':' . '' )
			)
		);

		$request_args = array_merge( $request_args, $args );

		$response = wp_remote_request( $url, $request_args );

		if ( is_wp_error( $response ) ) {
		
			return false;

		}

		if ( $response['response']['code'] == 200 ) {
			return json_decode( $response['body'] );
		}

		// If function gets this far, there's an error
		$error = json_decode( $response['body'] );

		return $error;

	}

}