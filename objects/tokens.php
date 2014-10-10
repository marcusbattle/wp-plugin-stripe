<?php 

class Tokens extends Stripe_API {

	public function __construct() {

		parent::__construct();

		$this->object = 'tokens';
		
	}

}