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

namespace RunthingsBulkPageStubCreator;

if (!defined('WPINC')) {
    die;
}

if (!defined('BPSC_DEBUG')) {
    define('BPSC_DEBUG', false); // if set to true then the <textarea> is prepopulated with some sample data
}

function bpsc_extract_info() {
    // Check user capabilities before processing any data
    if (!current_user_can('publish_pages')) {
        return array();
    }
    
    // Sanitize and validate the textarea content
    $input = '';
    if (isset($_POST["bpsc_pagestocreate"])) {
        // Use sanitize_textarea_field to properly handle the multiline input
        $input = sanitize_textarea_field($_POST["bpsc_pagestocreate"]);
    }
    
    // Just handle different line endings properly
    $input = str_replace(array("\r\n", "\r"), "\n", $input);
    $extracted_info = explode("\n", $input); // split into array
    
    return $extracted_info;
}

function bpsc_process_admin_page($extracted_info) {
    // Validate user capabilities
    if (!current_user_can('publish_pages')) {
        return array();
    }
    
    // Add basic validation before processing
    if (empty($extracted_info)) {
        return array();
    }
    
    $results = bpsc_bulk_create_pages($extracted_info);
    return $results;
}

function bpsc_display_admin_results_page($results) {
    // Verify permissions before displaying admin content
    if (!current_user_can('publish_pages')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    
    // show output
    ob_start(); ?>
    <div class="wrap">
        <h2><?php esc_html_e('Bulk Page Stub Creator'); ?></h2>
        <h3><?php esc_html_e('Bulk Page Creation Results'); ?></h3>
        <p><?php esc_html_e('Page results listed below, click the links to edit the pages'); ?></p>
        <p>
        <?php
        if (empty($results)) {
            esc_html_e('No pages were created. Please check your input and try again.');
        } else {
            foreach($results as $result) {
                $css_class = isset($result['error_level']) ? esc_attr($result['error_level']) : 'none';
                $post_id = isset($result['post_id']) ? intval($result['post_id']) : 0;
                $post_title = isset($result['post_title']) ? esc_html($result['post_title']) : '';
                
                if ($post_id > 0) {
                    echo '<a target="_blank" class="' . $css_class . '" href="' . 
                         esc_url(admin_url('post.php?action=edit&post=' . $post_id)) . 
                         '">' . $post_title . '</a>';
                    
                    if (strcmp($css_class, "none") != 0 && isset($result['post_name'])) {
                        printf(
                            ' (<strong style="color: #ff0000;">%s</strong> %s /%s)',
                            esc_html__('ERROR:'),
                            esc_html__('requested slug invalid or in use, page slug is:'),
                            esc_html($result['post_name'])
                        );
                    }
                    
                    echo "<br>";
                }
            }
        }
        ?>
        </p>
        
        <form method="post" action="">
        <?php wp_nonce_field('bpsc_return_action', 'bpsc_return_nonce'); ?>
        <p>
            <input class="button-primary" type="submit" name="save" value="<?php esc_attr_e('Return to main page'); ?>" id="submitbutton" />
        </p>
        </form>        
    </div>
    <?php
    echo ob_get_clean();   
}

function bpsc_display_admin_page($is_uneven_inputs_error = NULL, $input = NULL) {
    // Verify permissions before displaying admin content
    if (!current_user_can('publish_pages')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    
    ob_start(); ?>
    <div class="wrap">
        <h2><div id="icon-edit-pages" class="icon32"></div> <?php esc_html_e('Bulk Page Stub Creator'); ?></h2>
        <?php if(BPSC_DEBUG) { ?>
            <div style="background: #E7373A; color: #fff; padding: 10px; font-weight: bold;"><?php esc_html_e('WARNING - BPSC_DEBUG = true; - disable before deploying'); ?></div>
        <?php } ?>
        
        <p><?php esc_html_e("Enter the pages into the text area below, one line for the page title, one line for the url, then repeat for as many page stubs that you want to create."); ?></p>
        <h4><?php esc_html_e('Example'); ?></h4>
        <pre><?php echo esc_html("Some Page
optimised-url-for-some-page
Another Page Title Here
custom-url-for-another-page
Site Map
site-map
Contact Us
contact-this-company"); ?></pre>
        <form method="post" action="">
        <?php wp_nonce_field('bpsc_create_pages_action', 'bpsc_create_pages_nonce'); ?>
        <h4><?php esc_html_e("Bulk Create Pages"); ?></h4>
        <p>
            <label class="description" for="bpsc_pagestocreate"><?php esc_html_e('Enter the site map data for the pages you want to create'); ?>:</label><br>
            <?php if($is_uneven_inputs_error) { ?>
            <strong style='color: #ff0000;'><?php esc_html_e('ERROR:'); ?></strong> <?php esc_html_e('You have not supplied an even number of inputs.'); ?></p>
            <?php } ?>
            <textarea id="bpsc_pagestocreate" name="bpsc_pagestocreate" rows="20" class="large-text code"><?php 
                if($is_uneven_inputs_error == true) { 
                    echo esc_textarea($input); 
                } elseif (BPSC_DEBUG == true) { 
                    echo esc_textarea("Some Page
optimised-url-for-some-page
Another Page Title Here
custom-url-for-another-page
Site Map
site-map
Contact Us
contact-this-company"); 
                } 
            ?></textarea>
        </p>
        <p>
            <input class="button-primary" type="submit" name="save" value='<?php esc_attr_e("Create page stubs"); ?>' id="submitbutton" />
        </p>
        </form>
    </div>
    <?php
    echo ob_get_clean();    	
}

function bspc_admin_page() {
    // Main capability check - verify user can access this admin page
    if (!current_user_can('publish_pages')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    
    // Verify nonce when processing form submissions
    if (isset($_POST["bpsc_pagestocreate"])) {
        // Verify the nonce for page creation
        if (!isset($_POST['bpsc_create_pages_nonce']) || 
            !wp_verify_nonce($_POST['bpsc_create_pages_nonce'], 'bpsc_create_pages_action')) {
            wp_die(__('Security check failed. Please try again.'));
        }
        
        $extracted_info = bpsc_extract_info();
        
        // check even number of inputs
        if((count($extracted_info) % 2) == 1) {
            bpsc_display_admin_page(true, $_POST["bpsc_pagestocreate"]);
        } else {        
            $results = bpsc_process_admin_page($extracted_info);
            bpsc_display_admin_results_page($results);
        }
    }
    else {
        bpsc_display_admin_page();
    }
}

function bpsc_add_admin_page_link() {
    // The capability check is already handled by the 3rd parameter in add_management_page()
    add_management_page("Bulk Page Stub Creator", "Bulk Page Stub Creator", "publish_pages", "bulk-page-stub-creator", "bspc_admin_page");
}
 
add_action('admin_menu', 'bpsc_add_admin_page_link');