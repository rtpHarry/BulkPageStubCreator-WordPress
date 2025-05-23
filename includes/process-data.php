<?php

namespace RunthingsBulkPageStubCreator;

if (!defined('WPINC')) {
    die;
}

class Processor {
    /**
     * Check if the requested slug was successfully used or modified
     *
     * @param string $slug_requested The slug that was requested
     * @param string $slug_returned The slug that was actually used
     * @return string Error level or 'none' if no error
     */
    public function check_slug_for_error_level($slug_requested, $slug_returned) {
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

    /**
     * Create a standardized result array element
     *
     * @param string $error_level Error level or 'none'
     * @param string $post_title Post title
     * @param string $post_name Post slug
     * @param int $post_id Post ID
     * @return array Result array
     */
    public function create_result_array_element($error_level, $post_title, $post_name, $post_id) {
        return array(
            'error_level' => sanitize_text_field($error_level),
            'post_title' => sanitize_text_field($post_title),
            'post_name' => sanitize_text_field($post_name),
            'post_id' => intval($post_id)
        );
    }

    /**
     * Bulk create pages from extracted info
     *
     * @param array $extracted_info Array of page titles and slugs
     * @return array Results of page creation
     */
    public function bulk_create_pages($extracted_info) {
        // Validate user capabilities before performing operations
        if (!current_user_can('publish_pages')) {
            return array(
                $this->create_result_array_element(
                    'error-insufficient-permissions',
                    __('Permission Denied', 'bulk-page-stub-creator'),
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
                array_push($results, $this->create_result_array_element(
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
                    array_push($results, $this->create_result_array_element(
                        "get-post-error",
                        $post_to_add['post_title'],
                        $post_to_add['post_name'],
                        $last_post_id
                    ));
                    continue;
                }
                            
                array_push($results, $this->create_result_array_element(
                    $this->check_slug_for_error_level($post_to_add['post_name'], $last_post->post_name),
                    $last_post->post_title, 
                    $last_post->post_name, 
                    $last_post_id));
            }        
        }
        
        return $results;
    }
}