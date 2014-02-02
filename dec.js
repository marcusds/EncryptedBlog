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
			content = formatCode.wpautop( content );
			$( this ).replaceWith( content ).show();
		} catch(err) {
			$( this ).show();
			$( '.encblo-error', this ).show();
		}
	});	
	
	// Permantly decrypt old posts with AJX
	$( '.decrypt-old-post' ).click( function( event ) {
	      event.preventDefault();
	      var post_id = $( this ).data('post_id' );
	      var nonce = $( this ).data( 'nonce' );
	      var oldKey = prompt( 'Please enter post encryption key.' );
	      
	      $.ajax({
	    	  type : 'get',
	    	  dataType : 'json',
	    	  url : EB_ajax.ajaxurl,
	    	  data : { action: 'eb_decrypt', post_id : post_id, nonce: nonce, oldKey: oldKey },
	    	  success: function( response ) {
	    		  if( response.type == 'success' ) {
    				  alert( 'Done!' );
    			  } else {
    				  alert( 'Unsuccessful.' )
    			  }
    		  }
    	  });
      });
});

/* Borrowed form wp-admin/js/editor.js - allows us to still format the code properly despite PHP not being able to since it comes encrypted. */

window.formatCode = {
		
	_wp_Autop: function(pee) {
		var preserve_linebreaks = false,
			preserve_br = false,
			blocklist = 'table|thead|tfoot|caption|col|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|select' +
				'|option|form|map|area|blockquote|address|math|style|p|h[1-6]|hr|fieldset|noscript|legend|section' +
				'|article|aside|hgroup|header|footer|nav|figure|figcaption|details|menu|summary';

		if ( pee.indexOf( '<object' ) !== -1 ) {
			pee = pee.replace( /<object[\s\S]+?<\/object>/g, function( a ) {
				return a.replace( /[\r\n]+/g, '' );
			});
		}

		pee = pee.replace( /<[^<>]+>/g, function( a ){
			return a.replace( /[\r\n]+/g, ' ' );
		});

		// Protect pre|script tags
		if ( pee.indexOf( '<pre' ) !== -1 || pee.indexOf( '<script' ) !== -1 ) {
			preserve_linebreaks = true;
			pee = pee.replace( /<(pre|script)[^>]*>[\s\S]+?<\/\1>/g, function( a ) {
				return a.replace( /(\r\n|\n)/g, '<wp-temp-lb>' );
			});
		}

		// keep <br> tags inside captions and convert line breaks
		if ( pee.indexOf( '[caption' ) !== -1 ) {
			preserve_br = true;
			pee = pee.replace( /\[caption[\s\S]+?\[\/caption\]/g, function( a ) {
				// keep existing <br>
				a = a.replace( /<br([^>]*)>/g, '<wp-temp-br$1>' );
				// no line breaks inside HTML tags
				a = a.replace( /<[a-zA-Z0-9]+( [^<>]+)?>/g, function( b ) {
					return b.replace( /[\r\n\t]+/, ' ' );
				});
				// convert remaining line breaks to <br>
				return a.replace( /\s*\n\s*/g, '<wp-temp-br />' );
			});
		}

		pee = pee + '\n\n';
		pee = pee.replace( /<br \/>\s*<br \/>/gi, '\n\n' );
		pee = pee.replace( new RegExp( '(<(?:' + blocklist + ')(?: [^>]*)?>)', 'gi' ), '\n$1' );
		pee = pee.replace( new RegExp( '(</(?:' + blocklist + ')>)', 'gi' ), '$1\n\n' );
		pee = pee.replace( /<hr( [^>]*)?>/gi, '<hr$1>\n\n' ); // hr is self closing block element
		pee = pee.replace( /\r\n|\r/g, '\n' );
		pee = pee.replace( /\n\s*\n+/g, '\n\n' );
		pee = pee.replace( /([\s\S]+?)\n\n/g, '<p>$1</p>\n' );
		pee = pee.replace( /<p>\s*?<\/p>/gi, '');
		pee = pee.replace( new RegExp( '<p>\\s*(</?(?:' + blocklist + ')(?: [^>]*)?>)\\s*</p>', 'gi' ), '$1' );
		pee = pee.replace( /<p>(<li.+?)<\/p>/gi, '$1');
		pee = pee.replace( /<p>\s*<blockquote([^>]*)>/gi, '<blockquote$1><p>');
		pee = pee.replace( /<\/blockquote>\s*<\/p>/gi, '</p></blockquote>');
		pee = pee.replace( new RegExp( '<p>\\s*(</?(?:' + blocklist + ')(?: [^>]*)?>)', 'gi' ), '$1' );
		pee = pee.replace( new RegExp( '(</?(?:' + blocklist + ')(?: [^>]*)?>)\\s*</p>', 'gi' ), '$1' );
		pee = pee.replace( /\s*\n/gi, '<br />\n');
		pee = pee.replace( new RegExp( '(</?(?:' + blocklist + ')[^>]*>)\\s*<br />', 'gi' ), '$1' );
		pee = pee.replace( /<br \/>(\s*<\/?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol)>)/gi, '$1' );
		pee = pee.replace( /(?:<p>|<br ?\/?>)*\s*\[caption([^\[]+)\[\/caption\]\s*(?:<\/p>|<br ?\/?>)*/gi, '[caption$1[/caption]' );

		pee = pee.replace( /(<(?:div|th|td|form|fieldset|dd)[^>]*>)(.*?)<\/p>/g, function( a, b, c ) {
			if ( c.match( /<p( [^>]*)?>/ ) ) {
				return a;
			}

			return b + '<p>' + c + '</p>';
		});

		// put back the line breaks in pre|script
		if ( preserve_linebreaks ) {
			pee = pee.replace( /<wp-temp-lb>/g, '\n' );
		}

		if ( preserve_br ) {
			pee = pee.replace( /<wp-temp-br([^>]*)>/g, '<br$1>' );
		}

		return pee;
	},

	wpautop: function( pee ) {
		var t = this, o = { o: t, data: pee, unfiltered: pee },
			q = typeof( jQuery ) !== 'undefined';

		if ( q ) {
			jQuery( 'body' ).trigger('beforeWpautop', [ o ] );
		}

		o.data = t._wp_Autop( o.data );

		if ( q ) {
			jQuery( 'body' ).trigger('afterWpautop', [ o ] );
		}

		return o.data;
	}
};