<!DOCTYPE html>
	<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Enter Encryption Key</title>
	<link rel='stylesheet' id='wp-admin-css'  href='<?php echo site_url(); ?>/wp-admin/css/wp-admin.css?ver=3.4.1' type='text/css' media='all' />
	<link rel='stylesheet' id='colors-fresh-css'  href='<?php echo site_url(); ?>/wp-admin/css/colors-fresh.css?ver=3.4.1' type='text/css' media='all' />
	<meta name='robots' content='noindex,nofollow' />
	</head>
	<body class="login">
	<div id="login">
		<h1><a href="http://wordpress.org/" title="Powered by WordPress"><?php echo bloginfo( 'name' ); ?></a></h1>
	
<form name="loginform" id="loginform" action="<?php
	if( isset( $_GET['redirect_to'] ) && !empty( $_GET['redirect_to'] ) )
	{
		echo $_GET['redirect_to'];
		if( strpos( $_GET['redirect_to'], '?' ) === false && substr( $_GET['page'], -1 ) !== '/') {
			echo '/';
		}
	}
	else
	{
		echo './';
	}
	?>" method="post">

	<p>
		<label for="encryption_key">Encryption key<br />
		<input type="text" name="encryption_key" id="encryption_key" class="input" value="" size="20" tabindex="10" /></label>
	</p>
	<p class="submit">
		<input type="submit" name="wp-submit" id="wp-submit" class="button-primary" value="Continue" tabindex="100" />
	</p>
	<p>
		Key will always be at least 8 characters.
	</p>
</form>

<p id="nav">
<?php
	if( isset( $_GET['redirect_to'] ) && !empty( $_GET['redirect_to'] ) ) 
	{
?>
	<a href="<?php echo wp_logout_url( $_GET['redirect_to'] ); ?>">Log Out</a>
<?php
	}
	else
	{
?>
	<a href="<?php echo wp_logout_url(); ?>">Log Out</a>
<?php
	}
?>
</p>

<script type="text/javascript">
function wp_attempt_focus(){
setTimeout( function(){ try{
d = document.getElementById('encryption_key');
d.focus();
d.select();
} catch(e){}
}, 200);
}

wp_attempt_focus();
if(typeof wpOnload=='function')wpOnload();
</script>

	<p id="backtoblog"><a href="http://jrnl.noveis.net/dev/" title="Are you lost?">&larr; Back to Dev</a></p>
	
	</div>

	
		<div class="clear"></div>
	</body>
	</html>