<?php 

class Cards extends Stripe_API {

	public function __construct() {

		parent::__construct();

		$this->object = 'cards';
		
	}

}