jQuery( document ).ready(function( $ ) {
    	
	if(sessionStorage.getItem( 'front_key' ) && sessionStorage.getItem( 'front_key' ) != null) {
		var key = sessionStorage.getItem( 'front_key' );
	} else {
		var key = false;
	}
	
	// Decrypt posts.
	$('.encDataStore').each(function( index ) {
		if( ! key ) {
			key = prompt( 'Please enter the encryption key. The key will not pass over the internet.' );
			sessionStorage.setItem( 'front_key', key );
		}
		
		var content = $( this ).data( 'enc' );
		
		GibberishAES.size( 256 );
		try {
			content = GibberishAES.dec( content, key );
			$( this ).replaceWith( content );
		} catch(err) {
			$( '.encblo-error', this ).show();
		}
	});	
	
	// Permantly decrypt old posts with AJX
	$( '.decrypt-old-post' ).click( function( event ) {
	      event.preventDefault();
	      var post_id = $( this ).data('post_id' );
	      var nonce = $( this ).data( 'nonce' );
	      var oldKey = prompt( 'Please enter post encryption key.' );
	      
	      jQuery.ajax({
	    	  type : 'get',
	    	  dataType : 'json',
	    	  url : EB_ajax.ajaxurl,
	    	  data : { action: 'eb_decrypt', post_id : post_id, nonce: nonce, key: oldKey },
	    	  success: function( response ) {
	    		  if( response.type == 'success' ) {
    				  alert( 'Done!' );
    			  } else {
    				  alert( 'Unsuccessful.' )
    			  }
    		  }
    	  })
      });
});