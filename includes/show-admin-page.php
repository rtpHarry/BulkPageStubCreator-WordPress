<?php

namespace RunthingsBulkPageStubCreator;

if (!defined('WPINC')) {
    die;
}

// Define debug mode constant if not already defined
if (!defined('BPSC_DEBUG')) {
    // In production environments, always set to false
    // In development, allow enabling via wp-config.php
    if (defined('WP_DEBUG') && WP_DEBUG === true) {
        define('BPSC_DEBUG', false); // Still false by default, but can be overridden
    } else {
        define('BPSC_DEBUG', false); // Always false in production
    }
}

class Admin {
    /**
     * @var Processor Data processing instance
     */
    private $processor;
    
    /**
     * Demo content for the textarea example
     */
    const DEMO_CONTENT = "Some Page
optimised-url-for-some-page
Another Page Title Here
custom-url-for-another-page
Site Map
site-map
Contact Us
contact-this-company";
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->processor = new Processor();
        add_action('admin_menu', array($this, 'add_admin_page_link'));
    }
    
    /**
     * Extract info from the textarea input
     * 
     * @return array Extracted info
     */
    private function extract_info() {
        // Check user capabilities before processing any data
        if (!current_user_can('publish_pages')) {
            return array();
        }
        
        // Since this method is only called after nonce verification in admin_page(),
        // we don't need to verify the nonce again, but we'll add a check to be extra safe
        if (!isset($_POST['bpsc_create_pages_nonce']) || 
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['bpsc_create_pages_nonce'])), 'bpsc_create_pages_action')) {
            return array();
        }
        
        // Sanitize and validate the textarea content
        $input = '';
        if (isset($_POST["bpsc_pagestocreate"])) {
            // Use sanitize_textarea_field to properly handle the multiline input
            $input = sanitize_textarea_field(wp_unslash($_POST["bpsc_pagestocreate"]));
        }
        
        // Just handle different line endings properly
        $input = str_replace(array("\r\n", "\r"), "\n", $input);
        $extracted_info = explode("\n", $input); // split into array
        
        return $extracted_info;
    }

    /**
     * Process extracted info and create pages
     *
     * @param array $extracted_info Info extracted from textarea
     * @return array Results of page creation
     */
    private function process_admin_page($extracted_info) {
        // Validate user capabilities
        if (!current_user_can('publish_pages')) {
            return array();
        }
        
        // Add basic validation before processing
        if (empty($extracted_info)) {
            return array();
        }
        
        $results = $this->processor->bulk_create_pages($extracted_info);
        return $results;
    }

    /**
     * Display the results page after page creation
     *
     * @param array $results Results of page creation
     */
    private function display_admin_results_page($results) {
        // Verify permissions before displaying admin content
        if (!current_user_can('publish_pages')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'bulk-page-stub-creator'));
        }
        
        ?>
        <div class="wrap">
            <h2><?php esc_html_e('Bulk Page Stub Creator', 'bulk-page-stub-creator'); ?></h2>
            <h3><?php esc_html_e('Bulk Page Creation Results', 'bulk-page-stub-creator'); ?></h3>
            <p><?php esc_html_e('Page results listed below, click the links to edit the pages', 'bulk-page-stub-creator'); ?></p>
            <p>
            <?php
            if (empty($results)) {
                esc_html_e('No pages were created. Please check your input and try again.', 'bulk-page-stub-creator');
            } else {
                foreach($results as $result) {
                    $css_class = isset($result['error_level']) ? esc_attr($result['error_level']) : 'none';
                    $post_id = isset($result['post_id']) ? intval($result['post_id']) : 0;
                    $post_title = isset($result['post_title']) ? esc_html($result['post_title']) : '';
                    
                    if ($post_id > 0) {
                        echo '<a target="_blank" class="' . esc_attr($css_class) . '" href="' . 
                             esc_url(admin_url('post.php?action=edit&post=' . $post_id)) . 
                             '">' . esc_html($post_title) . '</a>';
                        
                        if (strcmp($css_class, "none") != 0 && isset($result['post_name'])) {
                            printf(
                                ' (<strong style="color: #ff0000;">%s</strong> %s /%s)',
                                esc_html__('ERROR:', 'bulk-page-stub-creator'),
                                esc_html__('requested slug invalid or in use, page slug is:', 'bulk-page-stub-creator'),
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
                <input class="button-primary" type="submit" name="save" value="<?php esc_attr_e('Return to main page', 'bulk-page-stub-creator'); ?>" id="submitbutton" />
            </p>
            </form>        
        </div>
        <?php
    }

    /**
     * Display the main admin page
     *
     * @param bool $is_uneven_inputs_error Whether an error occurred due to uneven inputs
     * @param string $input The original input to redisplay
     */
    private function display_admin_page($is_uneven_inputs_error = NULL, $input = NULL) {
        // Verify permissions before displaying admin content
        if (!current_user_can('publish_pages')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'bulk-page-stub-creator'));
        }
        
        ?>
        <div class="wrap">
            <h2><div id="icon-edit-pages" class="icon32"></div> <?php esc_html_e('Bulk Page Stub Creator', 'bulk-page-stub-creator'); ?></h2>
            <?php if(BPSC_DEBUG) { ?>
                <div class="notice notice-error">
                    <p><strong><?php esc_html_e('WARNING - Debug mode is enabled. Disable before deploying to production.', 'bulk-page-stub-creator'); ?></strong></p>
                </div>
            <?php } ?>
            
            <p><?php esc_html_e("Enter the pages into the text area below, one line for the page title, one line for the url, then repeat for as many page stubs that you want to create.", 'bulk-page-stub-creator'); ?></p>
            <h4><?php esc_html_e('Example', 'bulk-page-stub-creator'); ?></h4>
            <pre><?php echo esc_html(self::DEMO_CONTENT); ?></pre>
            <form method="post" action="">
            <?php wp_nonce_field('bpsc_create_pages_action', 'bpsc_create_pages_nonce'); ?>
            <h4><?php esc_html_e("Bulk Create Pages", 'bulk-page-stub-creator'); ?></h4>
            
            <label class="description" for="bpsc_pagestocreate"><?php esc_html_e('Enter the site map data for the pages you want to create', 'bulk-page-stub-creator'); ?>:</label><br>
            
            <?php if($is_uneven_inputs_error) { ?>
            <p>
                <strong style='color: #ff0000;'><?php esc_html_e('ERROR:', 'bulk-page-stub-creator'); ?></strong> 
                <?php esc_html_e('You have not supplied an even number of inputs.', 'bulk-page-stub-creator'); ?>
            </p>
            <?php } ?>
            
            <div>
                <textarea id="bpsc_pagestocreate" name="bpsc_pagestocreate" rows="20" class="large-text code"><?php 
                    if($is_uneven_inputs_error == true) { 
                        echo esc_textarea($input); 
                    } elseif (BPSC_DEBUG) { 
                        echo esc_textarea(self::DEMO_CONTENT); 
                    } 
                ?></textarea>
            </div>
            <div>
                <input class="button-primary" type="submit" name="save" value="<?php esc_attr_e('Create page stubs', 'bulk-page-stub-creator'); ?>" id="submitbutton" />
            </div>
            </form>
        </div>
        <?php 	
    }

    /**
     * Main admin page handler
     */
    public function admin_page() {
        // Main capability check - verify user can access this admin page
        if (!current_user_can('publish_pages')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'bulk-page-stub-creator'));
        }
        
        // Verify nonce when processing form submissions
        if (isset($_POST["bpsc_pagestocreate"])) {
            // Verify the nonce for page creation
            if (!isset($_POST['bpsc_create_pages_nonce']) || 
                !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['bpsc_create_pages_nonce'])), 'bpsc_create_pages_action')) {
                wp_die(esc_html__('Security check failed. Please try again.', 'bulk-page-stub-creator'));
            }
            
            $extracted_info = $this->extract_info();
            
            // check even number of inputs
            if((count($extracted_info) % 2) == 1) {
                $this->display_admin_page(true, sanitize_textarea_field(wp_unslash($_POST["bpsc_pagestocreate"])));
            } else {        
                $results = $this->process_admin_page($extracted_info);
                $this->display_admin_results_page($results);
            }
        }
        else {
            $this->display_admin_page();
        }
    }

    /**
     * Add the admin page link to the WordPress menu
     */
    public function add_admin_page_link() {
        // The capability check is already handled by the 3rd parameter in add_management_page()
        add_management_page(
            __("Bulk Page Stub Creator", 'bulk-page-stub-creator'), 
            __("Bulk Page Stub Creator", 'bulk-page-stub-creator'), 
            "publish_pages", 
            "bulk-page-stub-creator", 
            array($this, 'admin_page')
        );
    }
}

// Initialize the Admin class
new Admin();