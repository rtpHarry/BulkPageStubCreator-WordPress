<?php
/*
    BulkPageStubCreator-WordPress v1.0
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

$BPSC_DEBUG = false; // if set to true then the <textarea> is prepopulated with some sample data

function bpsc_extract_info() {
	$input = $_POST["bpsc_pagestocreate"]; // grab textarea contents 
	$input = trim($input); // trim start and end
	$extractedInfo = explode("\r\n", $input); // split into array
	$extractedInfo = array_filter($extractedInfo); // trim empty lines out of array
	return $extractedInfo;
}

function bpsc_process_admin_page($extractedInfo) {		
	$results = bpsc_bulk_create_pages($extractedInfo);
	
	return $results;
}

function bpsc_display_admin_results_page($results) {
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
			$cssClass = $result['error_level'];
			echo '<a target="_blank" class="' . $cssClass . '" href="post.php?action=edit&post=' . $result['post_id'] . '">' . $result['post_title'] . "</a>";
			
			if(strcmp($cssClass, "none") != 0) {
				echo " (<strong style='color: #ff0000;'>ERROR:</strong> requested slug invalid or in use, page slug is: /" . $result['post_name'] . ")";
			}
			
			echo "<br>";
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

function bpsc_display_admin_page($isUnevenInputsError = NULL, $input = NULL) {
	global $BPSC_DEBUG;
	
	ob_start(); ?>
    <div class="wrap">
    	<h2><div id="icon-edit-pages" class="icon32"></div> Bulk Page Stub Creator</h2>
        <?php if($BPSC_DEBUG) { ?>
	        <div style="background: #E7373A; color: #fff; padding: 10px; font-weight: bold;">WARNING - $BPSC_DEBUG = true; - disable before deploying</div>
        <?php } ?>        
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
            <?php if($isUnevenInputsError) { ?>
            <strong style='color: #ff0000;'>ERROR:</strong> You have not supplied an even number of inputs.</p>
            <?php } ?>
            <textarea id="bpsc_pagestocreate" name="bpsc_pagestocreate" rows="20" class="large-text code"><?php if($isUnevenInputsError == true) { echo $input; } elseif ($BPSC_DEBUG == true) { ?>Some Page
optimised-url-for-some-page
Another Page Title Here
custom-url-for-another-page
Site Map
site-map
Contact Us
contact-this-company<?php } ?></textarea>
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
		$extractedInfo = bpsc_extract_info();
		
		// check even number of inputs
		if((count($extractedInfo) % 2) == 1) {
			bpsc_display_admin_page(true, $_POST["bpsc_pagestocreate"]);
		} else {		
			$results = bpsc_process_admin_page($extractedInfo);
			bpsc_display_admin_results_page($results);
		}
	}
	else {
		bpsc_display_admin_page();
	}
}

function bpsc_add_admin_page_link() {
	add_management_page("Bulk Page Stub Creator", "Bulk Page Stub Creator", "publish_pages", "bulk-page-stub-creator", "bspc_admin_page");
}
 
add_action('admin_menu', 'bpsc_add_admin_page_link');
?>