jQuery( document ).ready(function( $ ) {
    
	if($.cookie('admin_key') && $.cookie('admin_key') !== '' && $.cookie('admin_key') !== "null") {
		var key = $.cookie('admin_key');
	} else {
		var key = prompt('Please enter the encryption key. The key may pass over the internet.');
		if(key) {
			$.cookie('admin_key', key);
		}
	}
});