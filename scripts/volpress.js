jQuery(document).ready(function($) {
	//alert('Handler for .ready() started.');
	volpress_sort_rows(); //in case there is an error in the retreived data this will reset to what the user sees

	//add datepicker
	jQuery( "#volpress_date" ).datepicker({ dateFormat: php_data.date_format });

	//make sortable
	$( "#volpress_task_table #volpress_task_table_body" ).sortable({
		stop: volpress_sort_rows,
	});
	function volpress_sort_rows() {
		var inputs = $('input.volpress_sort');
		$('input.volpress_sort').each(function(idx) {
			$(this).val((idx+1));
		});
	}

	//make timepickers
	//basic - chain example at http://fgelinas.com/code/timepicker/ but need to edit to handle multiple chains on a page for each task
	volpress_timepicker();
	function volpress_timepicker() {
		$('.volpress_time').timepicker({
			showPeriod: true,
			showLeadingZero: true,
			minutes: { interval: 15 },
			minuteText: 'Min'
		});
	} //else try http://stackoverflow.com/questions/10433154/putting-datepicker-on-dynamically-created-elements-jquery-jqueryui
	//end basic timepicker

	//add row
	row_key = 0;
	$(".volpress_add_task").click(function() {
		row_key++;
		$('#volpress_task_table tr:last').after('<tr id="volpress_task_'+row_key+'">'+
				'<td><input type="text" name="volpress_task_new['+row_key+'][title]" value="" class="volpress_title" /></td>' + 
				'<td><input type="text" name="volpress_task_new['+row_key+'][qty_min]" value="" class="volpress_qty" /></td>' + 
				'<td><input type="text" name="volpress_task_new['+row_key+'][qty_max]" value="" class="volpress_qty" /></td>' + 
				'<td><input type="text" name="volpress_task_new['+row_key+'][time_start]" value="" class="volpress_time" /></td>' + 
				'<td><input type="text" name="volpress_task_new['+row_key+'][time_end]" value="" class="volpress_time" /></td>' + 
				'<td class="volpress_sort_cell"><img src="'+php_data.volpress_plugin_url+'/images/icon-drag-y.png" /><input type="hidden" name="volpress_task_new['+row_key+'][sort]" class="volpress_sort" value="" /></td>' + 
			'</tr>'); //after
		volpress_sort_rows(); //get it the right sort number
		volpress_timepicker();
		return false;
	});



	//for (var key in php_data) alert( key + " = " + php_data[key] ); //debug what you have
	//var inc_file = php_data.volpress_plugin_url + 'admin/tasks.php?id=' + php_data.post_id;
	//alert(inc_file);
	//$('#vp_admin_tasks').load(inc_file);
	/*
	jQuery.post(
		// see tip #1 for how we declare global javascript variables
		php_data.ajaxurl,
		{
			// here we declare the parameters to send along with the request
			// this means the following action hooks will be fired:
			// wp_ajax_nopriv_myajax-submit and wp_ajax_myajax-submit
			action : 'myajax-submit',
	 
			// other parameters can be added along with "action"
			postID : php_data.postID
		},
		function( response ) {
			alert( 'in admin.js' );
			alert( response );
			for (var key in response) alert( key + " = " + response[key] ); //debug what you have
		}
	);
	*/
	//alert('Handler for .ready() finished.');
});