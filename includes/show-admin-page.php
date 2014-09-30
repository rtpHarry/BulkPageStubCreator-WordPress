<?php

function bsps_create_result_array_element($error_level, $post_title, $post_name, $post_id) {
	return array(
			'error_level' => $error_level,
			'post_title' => $post_title,
			'post_name' => $post_name,
			'post_id' => $post_id
	);
}

function bsps_process_admin_page() {
	// grab text area and split into array
	$input = $_POST["bpsc_pagestocreate"];
	$output = explode("\r\n", $input);
	$results = array();

	// TODO - update to use array format below		
	// TODO - expand to check if lines % number of input fields and return error if not	
	// TODO - format nicely
	if((count($output) % 2) == 1) {
		return array("ERROR: You have not supplied an even number of inputs");
	}
		
	// bulk create pages
	// TODO - validate urls (no preceeding slash)
	for($i = 0, $size = count($output); $i < $size; $i = $i + 2) {
		$postToAdd = array(
			'post_title' => $output[$i],
			'post_name' => $output[$i + 1],
			'post_status' => 'publish',
			'post_type' => 'page'
		);
		$lastPostID = wp_insert_post($postToAdd);
				
		if($lastPostID == 0) {
			// log error
			array_push($results, bsps_create_result_array_element(
				"wp-insert-post-error", 
				$postToAdd['post_title'], 
				$postToAdd['post_name'], 
				$lastPostID));			
		}
		else {
			// log post details
			$lastPost = get_post($lastPostID);
						
			array_push($results, bsps_create_result_array_element(
				strcmp($postToAdd['post_name'], $lastPost->post_name) == 0 ? "none" : "url-in-use", 
				$lastPost->post_title, 
				$lastPost->post_name, 
				$lastPostID));
		}
		
	}
	
	return $results;
}

function bsps_display_admin_results_page($results) {
	// show output
	ob_start(); ?>
    <div class="wrap">
    	<h2>Bulk Page Stub Creator</h2>
        <h3>Bulk Page Creation Results</h3>
        <p>Page results listed below, click the links to edit the pages</p>
        <p>
		<?php
		foreach($results as $result)
		{
			$cssClass = $result['error_level'];// != "none" ? "errorclass" : "successclass";
			echo '<a class="' . $cssClass . '" href="post.php?action=edit&post=' . $result['post_id'] . '">' . $result['post_title'] . "</a><br>";
		}
		?>
        </p>
        
        <form method="post" action="">
        <p>
        	<input class="button-primary" type="submit" name="save" value='<?php _e("Return to main page"); ?>' id="submitbutton" />
        </p>
        </form>        
    </div>
	<?php
	echo ob_get_clean();   
}

function bsps_display_admin_page() {
	ob_start(); ?>
    <div class="wrap">
    	<h2><div id="icon-edit-pages" class="icon32"></div> Bulk Page Stub Creator</h2>
        <p><?php _e("Enter the pages into the text area below, one line for the page title, one line for the url, then repeat for as many page stubs that you want to create."); ?></p>
        <h4>Example</h4>
        <pre>Some Page
optimised-url-for-some-page
Another Page Title Here
custom-url-for-another-page
Site Map
site-map
Contact Us
contact-this-company</pre>
        <form method="post" action="">
        <h4><?php _e("Bulk Create Pages"); ?></h4>
        <p>
        	<label class="description" for="bpsc_pagestocreate"><?php _e('Enter the site map data for the pages you want to create'); ?>:</label><br>
            <textarea id="bpsc_pagestocreate" name="bpsc_pagestocreate" rows="20" cols="100"></textarea>
        </p>
        <p>
        	<input class="button-primary" type="submit" name="save" value='<?php _e("Create page stubs"); ?>' id="submitbutton" />
        </p>
        </form>
    </div>
	<?php
	echo ob_get_clean();    	
}

function bspc_admin_page() {
	if ($_POST["bpsc_pagestocreate"]) {
		$results = bsps_process_admin_page();
		bsps_display_admin_results_page($results);
	}
	else {
		bsps_display_admin_page();
	}
}

function bpsc_add_admin_page_link() {
	add_management_page("Bulk Page Stub Creator", "Bulk Page Stub Creator", "publish_pages", "bulk-page-stub-creator", "bspc_admin_page");
}
 
add_action('admin_menu', 'bpsc_add_admin_page_link');
?>