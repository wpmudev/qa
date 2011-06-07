jQuery(function($) {

	function populate_checkboxes() {
		$('#ajax-loader').show();
		// clear checked fields
		$('#capabilities input').attr( 'checked', false );
		// set data
		var data = {
			action: 'qa-get-caps',
			role: $('#roles option:selected').val()
		};
		// make the post request and process the response
		$.post(ajaxurl, data, function(response) {
			$('#ajax-loader').hide();
			$.each(response, function(index) {
				if ( index != null ) {
					$('input[name="capabilities[' + index + ']"]').attr( 'checked', true );
				}
			});
		});
	}

	populate_checkboxes();

	$('#roles').change(populate_checkboxes);
	
	$('.qa-general').submit(function() {
		$('#ajax-loader').show();

		var data = $(this).serializeArray();

		$.post(ajaxurl, data, function() {
			$('#ajax-loader').hide();
		});

		return false;
	});
});
