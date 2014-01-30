jQuery( document ).ready(function( $ ) {
	var key = false;
	if( sessionStorage.getItem( 'front_key' ) && sessionStorage.getItem( 'front_key' ) != null ) {
		key = sessionStorage.getItem( 'front_key' );
	} else {
		key = prompt( 'Please enter the encryption key. The key will not pass over the internet.' );
		sessionStorage.setItem( 'front_key', key);
	}

	GibberishAES.size( 256 );

	var content = switchEditors.pre_wpautop( $('#content').val() ) || '';
	try {
		content = GibberishAES.dec( content, key );
	} catch( err ) {
		// If this fires then content probably wasn't encrypted to begin with.
	}
	$( '#content' ).val( content );
	
	$( '#publish' ).click(function() {
		editor = typeof tinymce != 'undefined' && tinymce.get( 'content' );
		if ( editor && ! editor.isHidden() && typeof switchEditors != 'undefined' ) {
			var content = tinymce.get( 'content' ).getContent();
			content = GibberishAES.enc( content, key );
			editor.setContent( content ? switchEditors.wpautop( content ) : '' );
		} else {
			var content = $( '#content' ).val();
			content = GibberishAES.enc( content, key );
			// Make sure the Text editor is selected
			$( '#content-html' ).click();
			$( '#content' ).val( content );
		}
	});
});