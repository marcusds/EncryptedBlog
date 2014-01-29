<script type="text/javascript" src="gibberish-aes-1.0.0.min.js"></script>

<?php

include 'GibberishAES.php';


$key = 'my secret key (таен ключ)';
$secret_string = 'my secret message (тайно съобщение)';
$old_key_size = GibberishAES::size();
GibberishAES::size(256);    // Also 192, 128
$encrypted_secret_string = GibberishAES::enc($secret_string, $key);
$decrypted_secret_string = GibberishAES::dec($encrypted_secret_string, $key);


?>

<script>

var key = 'Amy secret key (таен ключ)';
var secret_string = 'my secret message (тайно съобщение)';
GibberishAES.size(256);    // Also 192, 128
var decrypted_secret_string = GibberishAES.dec("<?php echo $encrypted_secret_string; ?>", key);

if(!decrypted_secret_string)
{
	console.log('A');
}
else
{
	console.log('B');
}

console.log(decrypted_secret_string);

</script>