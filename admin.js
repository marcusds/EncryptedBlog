jQuery( document ).ready(function( $ ) {
	var key = false;
	if( sessionStorage.getItem( 'front_key' ) && sessionStorage.getItem( 'front_key' ) != null ) {
		key = sessionStorage.getItem( 'front_key' );
	} else {
		key = prompt( 'Please enter the encryption key. The key will not pass over the internet.' );
		sessionStorage.setItem( 'front_key', key);
	}

	GibberishAES.size( 256 ); // 256 bit security, might be smart to lower it, 128 bit would have smaller storage requirements and be faster.

	if ( typeof switchEditors !== 'undefined' ) {
		var content = switchEditors.pre_wpautop( $('#content').val() ) || '';
		try {
			content = GibberishAES.dec( content, key );
		} catch( err ) {
			// If this fires then content probably wasn't encrypted to begin with so let's not even return an error.
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
	}
	
	$( '.clickDecrypt' ).click( function( event ) {
	      event.preventDefault();
	      var post_id = $( this ).data('post_id' );
	      var nonce = $( this ).data( 'nonce' );
	      	      
	      $.get( EB_ajax.ajaxurl, { action: 'eb_decrypt', post_id: post_id, nonce: nonce, task: 'get' }, 'json' ).done(function( data ) {
	    	  var obj = jQuery.parseJSON(data);
	    	  content = obj.content;

	    	  if( ! obj.is_encrypted) {
	    		  alert('Content already decrypted.');
	    	  } else {
		    	  content = GibberishAES.dec( content, key );
		    	  
		    	  $.ajax({
			    	  type : 'post',
			    	  dataType : 'json',
			    	  url : EB_ajax.ajaxurl,
			    	  data : { action: 'eb_decrypt', post_id : post_id, nonce: nonce, task: 'dec', content: content },
			    	  success: function( response ) {
			    		  alert('Post decrypted');
		    		  }
		    	  });
	    	  }
	      });
	});
	
	$( '.clickEncrypt' ).click( function( event ) {
	      event.preventDefault();
	      var post_id = $( this ).data('post_id' );
	      var nonce = $( this ).data( 'nonce' );
	      	      
	      $.get( EB_ajax.ajaxurl, { action: 'eb_decrypt', post_id: post_id, nonce: nonce, task: 'get' }, 'json' ).done(function( data ) {
	    	  	    	  
	    	  var obj = jQuery.parseJSON(data);
	    	  content = obj.content;
	    	  	    	  
	    	  if( obj.is_encrypted ) {
	    		  alert('Content already encrypted.');
	    	  } else {	    	  
		    	  content = GibberishAES.enc( content, key );
	    		  
		    	  $.ajax({
			    	  type : 'post',
			    	  dataType : 'json',
			    	  url : EB_ajax.ajaxurl,
			    	  data : { action: 'eb_decrypt', post_id : post_id, nonce: nonce, task: 'enc', content: content },
			    	  success: function( response ) {
			    		  alert('Post encrypted');
		    		  }
		    	  });
	    	  }
	      });
	});
});