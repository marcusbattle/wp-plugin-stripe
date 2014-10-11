<?php
	
	$test_secret_key = get_option('stripe_test_secret_key');
	$test_public_key = get_option('stripe_test_public_key');
	$live_secret_key = get_option('stripe_live_secret_key');
	$live_public_key = get_option('stripe_live_public_key');

	$stripe_mode = get_option('stripe_mode');
	
?>

<div class="wrap">
	<form method="POST">
		<p>
			<label>Live Charges</label>
			<input type="checkbox" name="stripe_mode" <?php echo ($stripe_mode) ? "checked" : '' ?> />
		</p>
		<p>
			<label>Test Secret Key</label>
			<input type="text" name="stripe_test_secret_key" value="<?php echo $test_secret_key ?>" />
		</p>
		<p>
			<label>Test Public Key</label>
			<input type="text" name="stripe_test_public_key" value="<?php echo $test_public_key ?>" />
		</p>
		<p>
			<label>Live Secret Key</label>
			<input type="text" name="stripe_live_secret_key" value="<?php echo $live_secret_key ?>" />
		</p>
		<p>
			<label>Live Public Key</label>
			<input type="text" name="stripe_live_public_key" value="<?php echo $live_public_key ?>" />
		</p>
		<button type="submit">Update Keys</button>
	</form>
</div>