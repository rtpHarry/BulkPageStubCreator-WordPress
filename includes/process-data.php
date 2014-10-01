<?php

function bpsc_check_slug_for_error_level($slugRequested, $slugReturned) {
	$slugSanitized = sanitize_title_with_dashes($slugRequested);
	
	if((strcmp($slugRequested, $slugSanitized) != 0) 
		|| (strcmp($slugRequested, $slugReturned) != 0)) {
			// requested slug was not sanitized or was in use
			return "url-alternative-was-used";
	}
	
/*	// TODO: DELETE THIS SECTION IF NOT USED BEFORE v1.0 LAUNCH
	if((strcmp($slugRequested, $slugSanitized) != 0) 
		&& (strcmp($slugRequested, $slugReturned) != 0)) {
			// requested slug was invalid and sanitized
			return "url-was-not-sanitized-and-sanitized-was-in-use";
	}
	
	if(strcmp($slugRequested, $slugSanitized) != 0){
		// requested slug was not valid
		return "url-was-not-sanitized";
	}
	
	if(strcmp($slugRequested, $slugReturned) != 0) {
		// sanitized slug was already in use
		return "url-in-use";
	}*/
	
	// request slug was used for post
	return "none";
}

function bpsc_create_result_array_element($error_level, $post_title, $post_name, $post_id) {
	return array(
			'error_level' => $error_level,
			'post_title' => $post_title,
			'post_name' => $post_name,
			'post_id' => $post_id
	);
}

function bpsc_bulk_create_pages($extractedInfo) {
	$results = array();

	for($i = 0, $size = count($extractedInfo); $i < $size; $i = $i + 2) {
		$postToAdd = array(
			'post_title' => $extractedInfo[$i],
			'post_name' => $extractedInfo[$i + 1],
			'post_status' => 'publish',
			'post_type' => 'page'
		);
		$lastPostID = wp_insert_post($postToAdd);
				
		if($lastPostID == 0) {
			// log error
			array_push($results, bpsc_create_result_array_element(
				"wp-insert-post-error", 
				$postToAdd['post_title'], 
				$postToAdd['post_name'], 
				$lastPostID));			
		}
		else {
			// log post details
			$lastPost = get_post($lastPostID);
						
			array_push($results, bpsc_create_result_array_element(
				bpsc_check_slug_for_error_level($postToAdd['post_name'], $lastPost->post_name),
				$lastPost->post_title, 
				$lastPost->post_name, 
				$lastPostID));
		}		
	}
	
	return $results;
}

?>