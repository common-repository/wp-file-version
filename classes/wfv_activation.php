<?php
/**
 * Description of wfv_activation
 * This class will create required database tables and directory on plugin activation hook
 * @author shivraj
 */
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly 

class WFV_Activation {

    static function install() {
        $htFile = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . WFV_UPLOAD_DIR_NAME . DIRECTORY_SEPARATOR . '.htaccess';
        $wfvUploadDir = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . WFV_UPLOAD_DIR_NAME;

        //create dircetory wfv_files and htaccess file
        if (!file_exists($wfvUploadDir) && wp_mkdir_p($wfvUploadDir)) {
            if (get_option('wfv_setting_allow_direct_access') != 1) {
                file_put_contents($htFile, 'Order Deny,Allow Deny from all');
            }
        }

        $installed_ver = get_option("wfv_db_version");

        if ($installed_ver != WFV_DB_VERSION) {
            //create required DB tables
            global $wpdb;
            $file_table_name = $wpdb->prefix . 'wfv_files';
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $file_table_name (
            `id` int(255) NOT NULL AUTO_INCREMENT,
            `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `version` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `description` longtext COLLATE utf8mb4_unicode_ci,
            `author` int(255) NOT NULL,
            `permission` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `parent` int(255) DEFAULT NULL,
            `status` int(1) DEFAULT NULL,
            `file_details` longtext COLLATE utf8mb4_unicode_ci,
            `publish_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
            `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) $charset_collate";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta($sql);
            update_option("wfv_db_version", WFV_DB_VERSION);
        }

        //save default options
        //initilaze default values for the options
        $default_template_html = '<div class="wfv_file_details">
                                        <div class="wfv_left">
                                            <div class="wfv_file_icon">{file_icon}</div>
                                        </div>
                                        <div class="wfv_right">
                                            <div class="wfv_file_title"><b>Title:</b> {title}</div>
                                            <div class="wfv_file_name"><b>File Name:</b> {file_name}</div>
                                            <div class="wfv_file_version"><b>File Version:</b> {version}</div>
                                            <div class="wfv_file_size"><b>File Size:</b> {file_size}</div>
                                            <div class="wfv_file_desc"><b>File Description:</b> {description}</div>
                                            <div class="wfv_file_download"><b>Download:</b> <a href="{file_download_link}">Download <i class="fa fa-download"></i></a></div>
                                        </div>
                                        <div class="clear"></div>
                                    </div>';
        $default_template_css = '.wfv_file_details {
                                    display: block;
                                    margin: 0 auto;
                                    background: whitesmoke;
                                    padding: 19px;
                                    margin-bottom: 8px;
                                }

                                .wfv_right {
                                    float: right;    
                                    width: 87%;
                                }

                                .wfv_left {
                                    float: left;   
                                    width: 10%; 
                                }';
        //check if is not already set
        if (get_option('wfv_setting_file_tpl') === false) {
            update_option('wfv_setting_file_tpl', $default_template_html);
        }
        if (get_option('wfv_setting_file_css') === false) {
            update_option('wfv_setting_file_css', $default_template_css);
        }
    }

}
