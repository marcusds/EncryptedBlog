<!DOCTYPE html>
	<!--[if IE 8]>
		<html xmlns="http://www.w3.org/1999/xhtml" class="ie8" lang="en-US">
	<![endif]-->
	<!--[if !(IE 8) ]><!-->
		<html xmlns="http://www.w3.org/1999/xhtml" lang="en-US">
	<!--<![endif]-->
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Enter Encryption Key</title>
	<link rel='stylesheet' id='buttons-css'  href='<?php echo site_url(); ?>/wp-includes/css/buttons.min.css?ver=3.9.1' type='text/css' media='all' />
<link rel='stylesheet' id='open-sans-css'  href='//fonts.googleapis.com/css?family=Open+Sans%3A300italic%2C400italic%2C600italic%2C300%2C400%2C600&#038;subset=latin%2Clatin-ext&#038;ver=3.9.1' type='text/css' media='all' />
<link rel='stylesheet' id='dashicons-css'  href='<?php echo site_url(); ?>/wp-includes/css/dashicons.min.css?ver=3.9.1' type='text/css' media='all' />
<link rel='stylesheet' id='login-css'  href='<?php echo site_url(); ?>/wp-admin/css/login.min.css?ver=3.9.1' type='text/css' media='all' />
<meta name='robots' content='noindex,follow' />
	</head>
	<body class="login login-action-login wp-core-ui  locale-en-us">
	<div id="login">
		<h1><a href="http://wordpress.org/" title="Powered by WordPress"><?php echo bloginfo( 'name' ); ?></a></h1>
<form name="loginform" id="loginform" action="<?php
if( isset( $_GET['redirect_to'] ) && !empty( $_GET['redirect_to'] ) )
{
	echo esc_url($_GET['redirect_to']);
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
	<input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="Continue" />
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
	
	</div>

	
		<div class="clear"></div>
	</body>
	</html>