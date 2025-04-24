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

function bpsc_check_slug_for_error_level($slug_requested, $slug_returned) {
	$slug_requested = sanitize_text_field($slug_requested);
	$slug_returned = sanitize_text_field($slug_returned);
	
	$slug_sanitized = sanitize_title_with_dashes($slug_requested);
	
	if((strcmp($slug_requested, $slug_sanitized) != 0) 
		|| (strcmp($slug_requested, $slug_returned) != 0)) {
			// requested slug was not sanitized or was in use
			return "url-alternative-was-used";
	}
	
	// request slug was used for post
	return "none";
}

function bpsc_create_result_array_element($error_level, $post_title, $post_name, $post_id) {
	return array(
		'error_level' => sanitize_text_field($error_level),
		'post_title' => sanitize_text_field($post_title),
		'post_name' => sanitize_text_field($post_name),
		'post_id' => intval($post_id)
	);
}

function bpsc_bulk_create_pages($extracted_info) {
    // Validate user capabilities before performing operations
    if (!current_user_can('publish_pages')) {
        return array(
            bpsc_create_result_array_element(
                'error-insufficient-permissions',
                esc_html__('Permission Denied'),
                '',
                0
            )
        );
    }

    $results = array();

    if (!is_array($extracted_info) || empty($extracted_info)) {
        return $results;
    }

	for($i = 0, $size = count($extracted_info); $i < $size; $i = $i + 2) {
		// Ensure array indexes exist
		if (!isset($extracted_info[$i]) || !isset($extracted_info[$i + 1])) {
			continue;
		}
		
		$post_title = sanitize_text_field($extracted_info[$i]);
		$post_name = sanitize_title($extracted_info[$i + 1]);
		
		$post_to_add = array(
			'post_title' => $post_title,
			'post_name' => $post_name,
			'post_status' => 'publish',
			'post_type' => 'page'
		);
		
		$last_post_id = wp_insert_post($post_to_add);
				
		if($last_post_id == 0) {
			// log error
			array_push($results, bpsc_create_result_array_element(
				"wp-insert-post-error", 
				$post_to_add['post_title'], 
				$post_to_add['post_name'], 
				$last_post_id));			
		}
		else {
			// log post details
			$last_post = get_post($last_post_id);
			
			if (!$last_post) {
				// Handle case where get_post fails
				array_push($results, bpsc_create_result_array_element(
					"get-post-error",
					esc_html($post_to_add['post_title']),
					esc_html($post_to_add['post_name']),
					$last_post_id
				));
				continue;
			}
						
			array_push($results, bpsc_create_result_array_element(
				bpsc_check_slug_for_error_level($post_to_add['post_name'], $last_post->post_name),
				$last_post->post_title, 
				$last_post->post_name, 
				$last_post_id));
		}		
	}
	
	return $results;
}