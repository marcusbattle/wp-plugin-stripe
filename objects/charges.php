<?php 

class Charges extends Stripe_API {

	public function __construct() {

		parent::__construct();

		$this->object = 'charges';
		
	}

}