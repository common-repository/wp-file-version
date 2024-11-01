<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

require_once 'wfv_file.php';
require_once 'wfv-download-file.php';

class WFV {

    private $fileObject;

    /**
     * Constructor
     */
    public function __construct() {
        // Actions        
        add_action('admin_menu', array($this, 'wfvCreateMenu'));
        add_action('admin_init', array($this, 'wfvRegisterSettings'));
        add_action('admin_notices', array($this, 'wfvAdminNotices'));

        //enque front end styles and scripts
        add_action('wp_enqueue_scripts', array($this, 'wfvEnqueueFrontScripts'));
        //enque global backend scripts
        add_action('admin_enqueue_scripts', array($this, 'wfvEnqueueGlobalScripts'));

        //add custom css with head
        add_action('wp_head', array($this, 'wfvEnqueCustomStyle'));

        //file download request redirect
        add_action('template_redirect', array($this, 'wfvHandleDownloadRequest'));

        //ajax request handlers
        add_action('wp_ajax_wfv_add_file', array($this, 'wfvAjaxAddFile'));
        add_action('wp_ajax_wfv_refresh_file_list', array($this, 'wfvAjaxRefreshFileList'));
        add_action('wp_ajax_wfv_delete_file', array($this, 'wfvAjaxDeleteFile'));
        add_action('wp_ajax_wfv_get_edit_form', array($this, 'wfvAjaxEditForm'));
        add_action('wp_ajax_wfv_edit_file', array($this, 'wfvAjaxEditFile'));
        add_action('wp_ajax_wfv_get_icon_form', array($this, 'wfvAjaxGetIconForm'));
        add_action('wp_ajax_wfvshowFile', array($this, 'wfvAjaxShowFile'));

        //short code
        add_shortcode('wfv', array($this, 'wfvShortCode'));

        //add file select meta box to selected post types
        $allowedPostTypes = get_option('wfv_post_types');
        if (!empty($allowedPostTypes)) {
            foreach ($allowedPostTypes as $post_type) {
                add_action('add_meta_boxes_' . $post_type, array($this, 'wfvMetaBoxAdd'));
                add_action('save_post', array($this, 'wfvFilesMetaSave'));
            }
        }

        //initialize file objetc
        $this->fileObject = new wfv_File();
    }

    /*
     * public function wfvCreateMenu to create admin menu
     */

    public function wfvCreateMenu() {

        $menu = array();
        $icon_url = plugins_url() . DIRECTORY_SEPARATOR . 'wp-file-version' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'wfv_icon.png';
        $menu[] = add_menu_page('WP File Version', 'WP File Version', 'manage_options', 'wfv_manage_files', array($this, 'wfvManageFiles'), $icon_url);
        $menu[] = add_submenu_page('wfv_manage_files', 'All Files', 'All Files', 'manage_options', 'wfv_manage_files', array($this, 'wfvManageFiles'));
        $menu[] = add_submenu_page('wfv_manage_files', 'Add File', 'Add File', 'manage_options', 'wfv_manage_files', array($this, 'wfvManageFiles'));
        $menu[] = add_submenu_page(null, 'WP File Manage Versions', 'WP File Manage Versions', 'manage_options', 'wfv_manage_versions', array($this, 'wfvManageVersions'));
        $menu[] = add_submenu_page(null, 'WP Edit File', 'WP Edit File', 'manage_options', 'wfv_edit_file', array($this, 'wfvEditFile'));

        //admin settings sub menu        
        $menu[] = add_submenu_page('wfv_manage_files', 'Icons', 'Icons', 'manage_options', 'wfv_settings_icons', array($this, 'wfvIconSettings'));
        $menu[] = add_submenu_page('wfv_manage_files', 'Template', 'Template', 'manage_options', 'wfv_settings_templates', array($this, 'wfvTemplateSettings'));
        $menu[] = add_submenu_page('wfv_manage_files', 'Settings', 'Settings', 'manage_options', 'wfv_settings', array($this, 'wfvSettings'));

        //enque scripts and styles to these menu pages only
        if (!empty($menu)) {
            foreach ($menu as $item) {
                if ($item === false) {
                    continue;
                }
                add_action('admin_print_styles-' . $item, array($this, 'wfvEnqueueStyles'));
                add_action('admin_print_scripts-' . $item, array($this, 'wfvEnqueueScripts'));
            }
        }
    }

    /*
     * public function wfvregisterSettings to register settings 
     */

    public function wfvRegisterSettings() {
        //register general settings
        register_setting('wfv-general-settings', 'wfv_setting_allowed_ftypes');
        register_setting('wfv-general-settings', 'wfv_setting_date_format');
        register_setting('wfv-general-settings', 'wfv_setting_allow_direct_access', array($this, 'wfvmanageHtaccess'));
        register_setting('wfv-general-settings', 'wfv_post_types');

        //register icon settings
        register_setting('wfv-icon-settings', 'wfvIcon');

        //register template settings
        register_setting('wfv-tpl-settings', 'wfv_setting_file_tpl');
        register_setting('wfv-tpl-settings', 'wfv_setting_file_css');
    }

    /*
     * publis function to handle file download request
     */

    public function wfvHandleDownloadRequest() {
        $downloadRequest = new WFV_download_file($this->fileObject);
        $downloadRequest->processDownloadRequest();
        return;
    }

    /*
     * Public function to manage general admin settings 
     */

    public function wfvSettings() {
        include(WFV_PLUGIN_PATH . 'views' . DIRECTORY_SEPARATOR . 'option-page' . DIRECTORY_SEPARATOR . 'general.php');
    }

    /*
     * public function to manage file Icons settings
     */

    public function wfvIconSettings() {
        include(WFV_PLUGIN_PATH . 'views' . DIRECTORY_SEPARATOR . 'option-page' . DIRECTORY_SEPARATOR . 'icons.php');
    }

    /*
     * public function to manage file template settings
     */

    public function wfvTemplateSettings() {
        include(WFV_PLUGIN_PATH . 'views' . DIRECTORY_SEPARATOR . 'option-page' . DIRECTORY_SEPARATOR . 'templates.php');
    }

    /*
     * public function wfvOptions to create admin option page     
     */

    public function wfvManageFiles() {
        include(WFV_PLUGIN_PATH . 'views' . DIRECTORY_SEPARATOR . 'wfv-files.php');
    }

    /*
     * public function wfv file edit page    
     */

    public function wfvManageVersions() {
        include(WFV_PLUGIN_PATH . 'views' . DIRECTORY_SEPARATOR . 'templates/manageVersion.php');
    }

    /*
     * public function to edit file
     */

    public function wfvEditFile() {
        include(WFV_PLUGIN_PATH . 'views' . DIRECTORY_SEPARATOR . 'templates/editForm.php');
    }

    /*
     * Enqueues Scripts and CSS
     */

    public function wfvEnqueueScripts() {
        wp_enqueue_script(
                'wfv-bootstrap-script', plugins_url('wp-file-version' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'bootstrap.min.js', WFV_PLUGIN_PATH), array('wfv-data-table-script', 'jquery')
        );
        wp_enqueue_script(
                'wfv-script', plugins_url('wp-file-version' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'script.js', WFV_PLUGIN_PATH), array('wfv-bootstrap-script', 'jquery')
        );
        wp_enqueue_script(
                'wfv-validate-script', plugins_url('wp-file-version' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'jquery.validate.min.js', WFV_PLUGIN_PATH), array('jquery')
        );
        wp_enqueue_script(
                'wfv-data-table-script', plugins_url('wp-file-version' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'jquery.dataTables.min.js', WFV_PLUGIN_PATH), array('jquery')
        );
        wp_enqueue_script(
                'wfv-data-table-responsive-script', plugins_url('wp-file-version' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'data-table-responsive.min.js', WFV_PLUGIN_PATH), array('wfv-data-table-script')
        );
        wp_enqueue_media();
    }

    /*
     * Enque styles
     */

    public function wfvEnqueueStyles() {
        wp_enqueue_style(
                'wfv-bootstrap', plugins_url('wp-file-version' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'bootstrap.min.css', WFV_PLUGIN_PATH)
        );
        wp_enqueue_style(
                'wfv-style', plugins_url('wp-file-version' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'style.css', WFV_PLUGIN_PATH)
        );
        wp_enqueue_style(
                'wfv-data-table-style', plugins_url('wp-file-version' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'jquery.dataTables.min.css', WFV_PLUGIN_PATH)
        );
        wp_enqueue_style(
                'wfv-data-table-responsive-style', plugins_url('wp-file-version' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'data-table-responsive.min.css', WFV_PLUGIN_PATH)
        );
    }

    /*
     * enque stype and scripts on front end
     */

    public function wfvEnqueueFrontScripts() {
        wp_enqueue_style(
                'wfv-style', plugins_url('wp-file-version' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'front-style.css', WFV_PLUGIN_PATH)
        );
    }

    /*
     * public function wfvEnqueueGlobalScripts to enque global scripts on admin section
     */

    public function wfvEnqueueGlobalScripts() {
        wp_enqueue_style(
                'select2', plugins_url('wp-file-version' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'select2.css', WFV_PLUGIN_PATH)
        );
        wp_enqueue_script(
                'select2', plugins_url('wp-file-version' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'select2.min.js', WFV_PLUGIN_PATH), array('jquery')
        );
    }

    /*
     * public function add custom css with header(front end)
     */

    public function wfvEnqueCustomStyle() {
        echo sprintf('<style>%s</style>', get_option('wfv_setting_file_css'));
    }

    /*
     * public function to show error notices on admin screen
     */

    public function wfvAdminNotices() {
        $htFile = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . WFV_UPLOAD_DIR_NAME . DIRECTORY_SEPARATOR . '.htaccess';
        $wfvUploadDir = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . WFV_UPLOAD_DIR_NAME;
        //check if upload directory created during installation
        if (!wp_mkdir_p($wfvUploadDir)) {
            $class = 'notice notice-error';
            $message = __('WP File Version upload directory ' . $wfvUploadDir .
                    ' not created with plugin activation. please create it manually', '');
            printf('<div class="%1$s"><p>%2$s</p></div>', $class, $message);
        }
        //check if htaccess file created during installation
        if (!file_exists($htFile)) {
            //check admin settings if direct url access is allowed or not
            if (get_option('wfv_setting_allow_direct_access') != 1) {
                //try to create htaccess file                
                if (false === file_put_contents($htFile, 'Order Deny,Allow Deny from all')) {
                    $class = 'notice notice-error';
                    $message = __('WP File Version .htaccess file not created with plugin activation in diectory' . $wfvUploadDir .
                            ' Please create this file manually and add below two lines to this .htaccess <br>
                        <b>Order Deny,<br>Allow Deny from all</b>', '');
                    printf('<div class="%1$s"><p>%2$s</p></div>', $class, $message);
                }
            }
        }
    }

    /*
     * public ajax function to add new file
     */

    public function wfvAjaxAddFile() {
        check_ajax_referer('wfv_add_file');
        $response = array();
        $response = $this->fileObject->addNewFile();
        wp_send_json($response);
    }

    /*
     * public ajax function to refresh file list
     */

    public function wfvAjaxRefreshFileList() {
        if (isset($_POST['action']) && $_POST['action'] == 'wfv_refresh_file_list') {
            if (isset($_POST['sub_action']) && $_POST['sub_action'] == 'wfv_add_file_version' && isset($_POST['id'])) {
                include(WFV_PLUGIN_PATH . 'views' . DIRECTORY_SEPARATOR . 'templates/versionList.php');
            } else {
                include(WFV_PLUGIN_PATH . 'views' . DIRECTORY_SEPARATOR . 'templates/fileList.php');
            }
        }
        die();
    }

    /*
     * public ajax function to delete file
     */

    public function wfvAjaxDeleteFile() {
        //verify nonce first
        if (!isset($_POST['id']) || !isset($_POST['wfv-delete-nonce']) || !wp_verify_nonce($_POST['wfv-delete-nonce'], 'wfv-delete-nonce_' . $_POST['id'])) {
            $response['errors'][] = 'Unable to verify security check !';
            wp_send_json($response);
        }

        if (isset($_POST['action']) && $_POST['action'] == 'wfv_delete_file') {
            $response = $this->fileObject->deleteFile($_POST['id']);
            wp_send_json($response);
        }
        die();
    }

    /*
     * public ajax function to return edit form model
     */

    public function wfvAjaxEditForm() {
        if (isset($_POST['action']) && $_POST['action'] == 'wfv_get_edit_form' && isset($_POST['id'])) {
            include(WFV_PLUGIN_PATH . 'views' . DIRECTORY_SEPARATOR . 'templates/editForm.php');
        }
        die();
    }

    /*
     * public function to update file via ajax
     */

    public function wfvAjaxEditFile() {
        if (isset($_POST['action']) && isset($_POST['wfv_id']) && $_POST['action'] == 'wfv_edit_file') {
            check_ajax_referer('wfv-edit-nonce_' . $_POST['wfv_id']);
            $response = $this->fileObject->updateFile();
            wp_send_json($response);
        }
        die();
    }

    /*
     * public function to add short code wfvShortCode
     */

    public function wfvShortCode() {
        $retunHtml = '';
        //get all the files for current post
        global $post;
        $AttachedFileIds = get_post_meta($post->ID, 'wfv_files', true);
        if (empty($AttachedFileIds)) {
            return $retunHtml;
        }
        foreach ($AttachedFileIds as $fileId) {
            //get the active version of this file id
            $activeFileId = $this->fileObject->getActiveFile($fileId);
            if (false !== ($fileTpl = $this->fileObject->getFileTemplate($activeFileId))) {
                $retunHtml = $retunHtml . $fileTpl;
            }
        }
        return $retunHtml;
    }

    /*
     * ajax function to provide add new icon form via ajax
     */

    public function wfvAjaxGetIconForm() {
        if (isset($_POST['action']) && $_POST['action'] == 'wfv_get_icon_form') {
            include(WFV_PLUGIN_PATH . 'views' . DIRECTORY_SEPARATOR . 'option-page' . DIRECTORY_SEPARATOR . 'icon-detail.php');
        }
        die();
    }

    /*
     * public function to manage .htaccess file in upload directory to manage file restrictions
     */

    public function wfvmanageHtaccess($data) {
        $file = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . WFV_UPLOAD_DIR_NAME . DIRECTORY_SEPARATOR . '.htaccess';
        if ($data == 1) {
            if (false === file_put_contents($file, '')) {
                add_settings_error(
                        'Unable to update .htaccess', 'Unable to update .htaccess', 'Unable to update .htaccess in wp-content/wfv-files directory due to write permisson update file manually and remove all content in this file, in order to allow direct url access for the files', 'error'
                );
                $data = NULL;
            }
        } else {
            if (false === file_put_contents($file, 'Order Deny,Allow Deny from all')) {
                add_settings_error(
                        'Unable to update .htaccess', 'Unable to update .htaccess', 'Unable to update .htaccess in wp-content/wfv-files directory due to write permisson update file manually and remove all content in this file, in order to allow direct url access for the files', 'error'
                );
                $data = NULL;
            }
        }
        return sanitize_text_field($data);
    }

    /*
     * public function to handle excerpt text
     */

    public static function wfvExcerpt($text, $numb) {
        if (strlen($text) > $numb) {
            $text = substr($text, 0, $numb);
            $text = substr($text, 0, strrpos($text, " "));
            $etc = " ...";
            $text = $text . $etc;
        }
        return $text;
    }

    /*
     * AJAX function to file details when user click on read more button
     */

    public function wfvAjaxShowFile() {

        if (isset($_POST['action']) && $_POST['action'] == 'wfvshowFile' && isset($_POST['file_id']) && isset($_POST['wfv_rm_nonce'])) {

            if (!wp_verify_nonce($_POST['wfv_rm_nonce'], 'wfv-read-more-nonce_' . $_POST['file_id'])) {
                wp_die('Security Check Failed !');
            }
            include(WFV_PLUGIN_PATH . 'views' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'fileDetail.php');
        }
        die();
    }

    /*
     * show ajax loader
     */

    public function wfvShowLoader() {
        $loader = plugins_url() . DIRECTORY_SEPARATOR . 'wp-file-version' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'wfv_loader.gif';
        echo '<div class="wvf_loading wfv-hide"><img src="' . $loader . '"></div>';
    }

    /*
     * Public function to add meta box with selected post types
     */

    public function wfvMetaBoxAdd() {
        add_meta_box('wfv-files-select', 'Select Version Controllable Files/Documents', array($this, 'wfvMetaBox'), '', 'normal', 'high');
    }

    /* public function to add meta box with post edit screen */

    public function wfvMetaBox() {
        global $post;
        $values = get_post_custom($post->ID);
        $selectedFiles = '';
        if (isset($values['wfv_files'][0])) {
            $selectedFiles = maybe_unserialize($values['wfv_files'][0]);
            if (!empty($selectedFiles)) {
                $selectedFiles = implode(',', $selectedFiles);
            }
        }
        ?><label for="wfv_files">Select Files</label>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                $("#wfv_files").select2().select2('val', [<?php echo $selectedFiles; ?>]);
            });
        </script>
        <select multiple id="wfv_files" name="wfv_files[]" style="width:90%">
            <?php
            $wfvFile = new WFV_File();
            //get parent files
            $wfvFiles = $wfvFile->getFiles('where `status` = 1');
            if (!empty($wfvFiles)) {
                foreach ($wfvFiles as $file) {
                    $selectedOption = (is_array($selectedFiles) && in_array($file['id'], $selectedFiles)) ? 'selected="selected"' : '';
                    echo sprintf('<option value="%s">%s</option>', $file['id'], $file['title']);
                }
            }
            ?>
        </select>
        <p>Start typing the file name and select from populated result</p>
        <input type="hidden" name="wfv_meta_box_nonce" value="<?php echo wp_create_nonce('wfvFilesMetaSave'); ?>">
        <?php
    }

    function wfvFilesMetaSave($post_id) {
        // Bail if we're doing an auto save
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;

        // if our nonce isn't there, or we can't verify it, bail
        if (!isset($_POST['wfv_meta_box_nonce']) || !wp_verify_nonce($_POST['wfv_meta_box_nonce'], 'wfvFilesMetaSave'))
            return;

        // if our current user can't edit this post, bail
        if (!current_user_can('edit_post'))
            return;

        // Make sure your data is set before trying to save it
        if (isset($_POST['wfv_files'])) {
            update_post_meta($post_id, 'wfv_files', esc_sql($_POST['wfv_files']));
        } else {
            update_post_meta($post_id, 'wfv_files', '');
        }
    }

}
