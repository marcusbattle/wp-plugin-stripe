<?php 

class Customers extends Stripe_API {

	public function __construct() {

		parent::__construct();

		$this->object = 'customers';

	}

	/**
	 * Uses the Search API to lookup a user by email
	 */
	public function get_by_email( $email = '' ) { 
	
		if ( ! $email )
			return false;

		$search_args = array(
			'body' => array(
				'query' => $email
			)
		);

		$result = $this->api( 'search', $search_args );
		
		if ( $result ) {

			foreach ( $result->data as $data ) {

				if ( $data->object == 'customer' && isset( $data->email ) && $data->email == $email ) {

					return $data;

				}

			}
			
			return false;
			
		}

	}

}