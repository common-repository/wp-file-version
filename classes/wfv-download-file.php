<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WFV_download_file {
    
    public $file_class_object;


    public function __construct($obj) {        
        $this->file_class_object = $obj;
    }
    
    public function processDownloadRequest(){
        $nonce = isset($_REQUEST['_wfv_dwn_key']) ? $_REQUEST['_wfv_dwn_key']:'';
        $file_id = isset($_REQUEST['_file_id']) ? $_REQUEST['_file_id']:'';

        //check request type
        if (!empty($file_id) && !empty($nonce)) {
            if (!wp_verify_nonce($nonce, '_wfv_dwn_key')) {
                wp_die('Access Denied');
            } else {
                $this->serverRequestedFile($file_id);
            }
        }
        return;
    }
    
    private function serverRequestedFile($file_id){
        $fileData = $this->file_class_object->getFile($file_id);
        if (empty($fileData)) {
            wp_die('Access Denied');
        }
        $file = $fileData['file_path'];
        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($file) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            exit;
        } else {
            wp_die('File not found.');
        }
    }

}