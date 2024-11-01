<?php
/*
Plugin Name: WP File Version
Plugin URI: infobeans.com
Description: This plugin is for file management system with files/document version control ability. It keeps the files in its separate directory. It allows all file types like image, doc, word, excel, pdf, audio, video, archive etc. but admin can manage allowed file types. File download access can be restricted/controlled by user roles. Unlimited file versions can be added. Custom File Icons can be added from the Icons setting page.
Author: Shivraj Singh Rawat [shivraj.singh@infobeans.com]
Version: 1.0
Author URI: http://infobeans.com
Compatibility: WordPress 4.2.2
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
define('WFV_PLUGIN_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);
define('WFV_PLUGIN_CLASS_PATH', WFV_PLUGIN_PATH . 'classes' . DIRECTORY_SEPARATOR . 'wfv_class.php');
define('WFV_UPLOAD_PATH', WP_CONTENT_DIR . DIRECTORY_SEPARATOR .'wfv_files'. DIRECTORY_SEPARATOR );
define('WFV_UPLOAD_DIR_NAME','wfv_files');
define('WFV_DB_VERSION',1.0);
include(WFV_PLUGIN_CLASS_PATH);
include(WFV_PLUGIN_PATH . 'classes' . DIRECTORY_SEPARATOR . 'wfv_activation.php');

$WFV = new WFV();

register_activation_hook( __FILE__, array( 'WFV_Activation', 'install'));