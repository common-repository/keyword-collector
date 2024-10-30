<?php
function settingsUpdate($args = false, $do_auto_insert = false) {
	if ($args) {
		if (is_array($args)) {
			$result = array();
		  	foreach ($args as $option_name => $option_value) {
		  		if(!get_option($option_name) && !is_string(get_option($option_name))){
		  			$result[$option_name] = "Option does not exist";
		  		} else if(get_option($option_name) == $option_value){
		  			$result[$option_name] = "Option has the same value";
		  		} else {
		  			if (update_option($option_name, $option_value, false)) {
		  				$result[$option_name] = "Option value changed";	
		  			} else {
		  				$result[$option_name] = "Some error!";
		  			}	
		  		}
		  	}
		  	var_dump($result);
		} else {
			var_dump("Argument must be array");
		}
	} else {
		var_dump("Missing argument in settingsUpdate() or argument is empty");
	}
}