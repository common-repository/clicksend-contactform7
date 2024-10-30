(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
	$( document ).on('click','#delete_message',function() {
		if (confirm("Are you sure want to delete?")) {
        	var id = $(this).data('id');
			$.ajax({
				// url:zohoajax.ajaxurl,
				url:ajaxurl,
				data:{'action':'delete_message','id':id},
				method:'POST',
				success:function(result){
					 // location.reload();
					var r = JSON.parse(result);
					if( r["code"] == 200 ){
						location.reload();
					}
					else{
						alert("Error on deletion");
					}
				}
			});
    	}
    	else{
    		return false;
    	}
		
	});
	
})( jQuery );
