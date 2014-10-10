<form action="" method="POST">
	<input name="amount" type="hidden" value="700" /> 
	<input name="description" type="hidden" value="" /> 
	<input name="currency" type="hidden" value="USD" /> 
  	<input name="submitted_from" type="hidden" value="stripe_checkout" />
  	<script
    src="https://checkout.stripe.com/checkout.js" class="stripe-button"
    data-key="pk_0GPSN2GT3hbJijZPFJS2XF8ZiEmRg"
    data-image="/square-image.png"
    data-name="TammyBattle.com"
    data-description="Broken Live Concert 1 Ticket ($7.00)"
    data-amount="700"
    data-label="Get Ticket">
  </script>
</form>

<style type="text/css">
	.stripe-button {
		background-color: red;
	}
</style>