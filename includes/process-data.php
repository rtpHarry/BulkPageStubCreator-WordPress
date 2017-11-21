<?php
/*
    BulkPageStubCreator-WordPress v1.1
    Copyright (C) 2014 Matthew Harris aka rtpHarry

    Bulk create page stubs by simply providing a plain text list of titles and slugs.

    http://articles.runtings.co.uk/p/bulk-page-stub-creator-wordpress.html

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

function bpsc_check_slug_for_error_level($slugRequested, $slugReturned) {
	$slugSanitized = sanitize_title_with_dashes($slugRequested);
	
	if((strcmp($slugRequested, $slugSanitized) != 0) 
		|| (strcmp($slugRequested, $slugReturned) != 0)) {
			// requested slug was not sanitized or was in use
			return "url-alternative-was-used";
	}
	
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