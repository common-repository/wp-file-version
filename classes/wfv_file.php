<?php

/*
 * WFV_File class will perform all the manupulations for files
 */

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

class WFV_File {

    private $fileTable;
    private $dbobj;
    public $response = Array();

    public function __construct() {
        global $wpdb;
        $this->dbobj = $wpdb;
        $this->fileTable = $wpdb->prefix . 'wfv_files';
    }

    /*
     * function to add new file in database
     * @return array
     */

    public function addNewFile() {
        //check form validation
        if (false !== $this->validateForm() &&
                false !== $this->validateFile() &&
                false !== ($fileDetails = $this->uploadFile())) {

            extract($_POST);

            if (!empty($wfv_permission)) {
                $wfv_permission = implode("|", $wfv_permission);
            }

            $result = $this->dbobj->insert(
                    $this->fileTable, array(
                'title' => $wfv_title,
                'version' => $wfv_version,
                'description' => $wfv_description,
                'author' => get_current_user_id(),
                'permission' => $wfv_permission,
                'parent' => $wfv_parent,
                'status' => 1,
                'file_details' => maybe_serialize($fileDetails),
                'publish_date' => date('Y-m-d H:i:s')
                    ), array(
                '%s',
                '%s',
                '%s',
                '%d',
                '%s',
                '%d',
                '%s',
                '%s'
                    )
            );
            if ($result === false) {
                unlink($fileDetails['path']);
                $this->response['errors'][] = 'Error in database insert operation';
            } else {
                $file_id = $this->dbobj->insert_id;
                //if make this version as active
                if ($wfv_status == 1 && $wfv_parent != 0) {
                    //make this file active
                    $updateActiveQuery = 'UPDATE ' . $this->fileTable . ' SET `status` = 0 WHERE ( `parent` = %d OR `id` = %d ) and `id` != %d';
                    $setActive = $this->dbobj->query($this->dbobj->prepare($updateActiveQuery, $wfv_parent, $wfv_parent, $file_id));
                    if (false === $setActive) {
                        $this->response['errors'][] = 'Unable to set current file as active';
                    }
                }
                $this->response['success'][] = 'File added successfully.';
                $this->response['file_id'][] = $file_id;
            }
        }
        return $this->response;
    }

    /*
     * public function to update file
     * @return array
     */

    public function updateFile() {
        if (false !== $this->validateUpdateForm()) {
            extract($_POST);

            if (!empty($wfv_permission)) {
                $wfv_permission = implode("|", $wfv_permission);
            }

            $deleteOlderFile = false;
            $activeFileId = $this->getActiveFile($wfv_id);
            $activeFileId = ($wfv_status == 1 && $activeFileId != $wfv_id) ? $activeFileId : 0;

            //check if new file uploaded
            $fieldArray = array(
                'title' => $wfv_title,
                'version' => $wfv_version,
                'description' => $wfv_description,
                'author' => get_current_user_id(),
                'permission' => $wfv_permission,
                'parent' => $wfv_parent,
                'status' => $wfv_status
            );

            $formatArray = array(
                '%s',
                '%s',
                '%s',
                '%d',
                '%s',
                '%d',
                '%d'
            );

            if (isset($_FILES['wfv_file']) && $_FILES['wfv_file']['size'] > 0) {
                if (false !== $this->validateFile() && false !== ($fileDetails = $this->uploadFile())) {
                    $deleteOlderFile = true;
                    $olderFilePath = $this->getFileData($wfv_id, 'path');
                    $fieldArray['file_details'] = maybe_serialize($fileDetails);
                    $formatArray[] = '%s';
                } else {
                    return $this->response;
                }
            }

            $result = $this->dbobj->update($this->fileTable, $fieldArray, array(id => $wfv_id), $formatArray, array('%d'));

            if ($result === false) {
                $this->response['errors'][] = 'Error in database update operation';
                unlink($fileDetails['path']);
            } else {
                //if make this version as active
                if ($wfv_status == 1) {
                    //if current file is parent in itself
                    if ($wfv_parent == 0) {
                        $activateQuery = 'UPDATE ' . $this->fileTable . ' SET `status` = 0 WHERE `parent` = %d';
                        $setActive = $this->dbobj->query($this->dbobj->prepare($activateQuery, $wfv_id));
                    } else {
                        $activateQuery = 'UPDATE ' . $this->fileTable . ' SET `status` = 0 WHERE (`parent` = %d OR `id` = %d ) AND `id` != %d';
                        $setActive = $this->dbobj->query($this->dbobj->prepare($activateQuery, $wfv_parent, $wfv_parent, $wfv_id));
                    }
                    if (false === $setActive) {
                        $this->response['errors'][] = 'Unable to set current file as active';
                    } else {
                        //if this file is not active file update all posts with this file id                       
                        if ($activeFileId != 0 && false === $this->updatePostAttachedFiles($wfv_id, $activeFileId)) {
                            $this->response['errors'][] = 'Error in updating status as active';
                            //again make older file active
                            $this->setFileData($wfv_id, 'status', 0);
                            $this->setFileData($activeFileId, 'status', 1);
                        } else {
                            $this->response['success'][] = 'File updated successfully.';
                        }
                        if ($deleteOlderFile) {
                            unlink($olderFilePath);
                        }
                    }
                } else {
                    $this->response['success'][] = 'File updated successfully.';
                }
            }
        }
        return $this->response;
    }

    /*
     * function to check if the version exists
     * @param int $file_id, int $parent_id, string $version
     * @return bool
     */

    public function versionExists($file_id = '', $parent_id, $version) {
        $parent_id = ($file_id != '' && $parent_id == 0) ? $file_id : $parent_id;

        $parent_id = esc_sql($parent_id);
        $version = esc_sql($version);
        $query = 'select COUNT(*) from '
                . $this->fileTable
                . ' where (`parent` = '
                . $parent_id
                . ' or '
                . '`id`'
                . ' = '
                . $parent_id
                . ') and `version` = "'
                . $version . '"';

        $count = $this->dbobj->get_var($query);

        if ($count === false) {
            $this->response['errors'][] = 'Error in version check';
            return true;
        } elseif ($count > 0) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * get vesrions
     * @param int $file_id
     * @return string
     */

    public function getActiveVersions($file_id) {

        $versionList = '';

        if (empty($file_id)) {
            return 'No active version';
        }
        $file_id = esc_sql($file_id);

        $query = "select `version` from " . $this->fileTable . ' where (`parent` = ' . $file_id . ' or `id` = ' . $file_id . ') and `status` = 1';

        $versions = $this->dbobj->get_results($query, ARRAY_A);

        if (empty($versions)) {
            return 'No active version';
        } else {
            foreach ($versions as $version) {
                $versionList = $versionList . "|" . $version['version'];
            }
        }
        return trim($versionList, "|");
    }

    /*
     * get file version
     * @param int $file_id
     * @return string
     */

    public function getVersion($file_id = '') {
        if (empty($file_id)) {
            return false;
        }
        $file_id = esc_sql($file_id);

        $query = "select `version` from " . $this->fileTable . ' where `id` = ' . $file_id;
        return $this->dbobj->get_var($query);
    }

    /*
     * public function to upload file
     * @return bool|array
     */

    public function uploadFile() {
        $target_dir = WFV_UPLOAD_PATH;
        $target_file = $target_dir . basename($_FILES["wfv_file"]["name"]);
        $original_name = pathinfo($target_file, PATHINFO_FILENAME);
        $ext = pathinfo($target_file, PATHINFO_EXTENSION);
        $i = 1;
        while (file_exists($target_file)) {
            $name = $original_name . '-' . $i . '.' . $ext;
            $target_file = $target_dir . $name;
            $i++;
        }
        if (move_uploaded_file($_FILES["wfv_file"]["tmp_name"], $target_file)) {
            $fileDetails = array();
            $fileDetails['size'] = $this->readableFileSize($_FILES["wfv_file"]["size"]);
            $fileDetails['path'] = $target_file;
            $fileDetails['name'] = empty($name) ? basename($_FILES["wfv_file"]["name"]) : $name;
            $fileDetails['type'] = $ext;
            return $fileDetails;
        } else {
            $uploadOk = 0;
            $this->response['errors'][] = "Sorry, there was an error uploading your file.";
        }
        return false;
    }

    /*
     * public function validate
     * @return bool
     */

    public function validateForm() {
        //to do add noonce validation        
        if (!isset($_POST['action']) || $_POST['action'] != 'wfv_add_file') {
            $this->response['errors'][] = 'Invalid file action';
        } elseif (!isset($_POST['wfv_title']) || empty($_POST['wfv_title'])) {
            $this->response['errors'][] = 'Please enter file Title';
        } elseif (!isset($_POST['wfv_version']) || empty($_POST['wfv_version'])) {
            $this->response['errors'][] = 'Please enter file version';
        } elseif (!isset($_POST['wfv_parent']) || $_POST['wfv_parent'] == '' || !$this->isParentExists($_POST['wfv_parent'])) {
            $this->response['errors'][] = 'Invalid Parent';
        } elseif ($_POST['wfv_parent'] != 0 && $this->versionExists('', $_POST['wfv_parent'], $_POST['wfv_version'])) {
            $this->response['errors'][] = 'File version already exists please enter another version';
        } else {
            return true;
        }
        return false;
    }

    /*
     * public function validate update form
     * @return bool
     */

    public function validateUpdateForm() {
        //to do add noonce validation        
        if (!isset($_POST['wfv_edit_file'])) {
            $this->response['errors'][] = 'Invalid file action';
        } elseif (!isset($_POST['wfv_id']) || empty($_POST['wfv_id'])) {
            $this->response['errors'][] = 'Invalid file id';
        } elseif (!isset($_POST['wfv_parent']) || $_POST['wfv_parent'] == '' || !$this->isParentExists($_POST['wfv_parent'])) {
            $this->response['errors'][] = 'Invalid parent';
        } elseif (!isset($_POST['wfv_version']) || empty($_POST['wfv_version'])) {
            $this->response['errors'][] = 'Please enter file version';
        } elseif (isset($_POST['wfv_version']) && $_POST['wfv_version'] != $this->getVersion($_POST['wfv_id']) && $this->versionExists($_POST['wfv_id'], $_POST['wfv_parent'], $_POST['wfv_version'])) {
            $this->response['errors'][] = 'File version already exists please enter another version';
        } else {
            return true;
        }
        return false;
    }

    /*
     * public function to validate file type
     * @return bool
     */

    public function validateFile() {
        //check if file has any upload error
        if (isset($_FILES['wfv_file']['error']) && $_FILES['wfv_file']['error'] > 0) {
            //check error type
            switch ($_FILES['wfv_file']['error']) {
                case 1:
                    $this->response['errors'][] = "The uploaded file exceeds the upload_max_filesize directive in php.ini";
                    break;
                case 2:
                    $this->response['errors'][] = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
                    break;
                case 3:
                    $this->response['errors'][] = "The uploaded file was only partially uploaded";
                    break;
                case 4:
                    $this->response['errors'][] = "No file was uploaded";
                    break;
                case 5:
                    $this->response['errors'][] = "Missing a temporary folder";
                    break;
                case 6:
                    $this->response['errors'][] = "Failed to write file to disk";
                    break;
                case 7:
                    $this->response['errors'][] = "File upload stopped by extension";
                    break;

                default:
                    $this->response['errors'][] = "Unknown upload error";
                    break;
            }
            return false;
        }

        //check if file is uploaded or not
        if (isset($_FILES['wfv_file']) && $_FILES['wfv_file']['size'] > 0) {
            $target_dir = WFV_UPLOAD_PATH;
            $target_file = $target_dir . basename($_FILES["wfv_file"]["name"]);
            //to check file MIME type.
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $FileType = $finfo->file($_FILES['wfv_file']['tmp_name']);

            $allowedType = get_option('wfv_setting_allowed_ftypes');
            $allowedType = empty($allowedType) ? '' : explode("|", $allowedType);
            $allowedType = empty($allowedType) ? '' : array_map('trim', $allowedType);
        } else {
            $this->response['errors'][] = "File is required.";
            return false;
        }


        // Check if file is a actual file or fake file
        if (isset($_POST["wfv_add_file"]) && false === filesize($_FILES["wfv_file"]["tmp_name"])) {
            $this->response['errors'][] = "Invalid File.";
            return false;
        } elseif (!empty($allowedType) && !in_array($FileType, $allowedType)) {
            $this->response['errors'][] = "Sorry, The file type {$FileType} not allowed. allow this file type in allowed file type setting";
            return false;
        } else {
            return true;
        }
    }

    /*
     * public function to get files
     * @param string $where
     * @return array
     */

    public function getFiles($where = '') {
        $return = array();
        $query = '';
        if ($where == '') {
            $query = 'select * from ' . $this->fileTable;
        } else {
            $query = 'select * from ' . $this->fileTable . ' ' . $where;
        }
        $fileData = $this->dbobj->get_results($query, ARRAY_A);

        if (empty($fileData)) {
            return false;
        }

        $dateFormat = get_option('wfv_setting_date_format');
        $dateFormat = empty($dateFormat) ? 'm-d-Y' : $dateFormat;

        foreach ($fileData as $data) {

            $fileDetails = $this->getFileDetailsArray();

            $fileDetails['id'] = $data['id'];
            $fileDetails['title'] = $data['title'];
            $fileDetails['description'] = $data['description'];
            $fileDetails['status'] = $data['status'];
            $fileDetails['active'] = $data['status'];
            $fileDetails['parent'] = $data['parent'];
            $fileDetails['version'] = $data['version'];
            $fileDetails['publish_date'] = date($dateFormat, strtotime($data['publish_date']));
            $fileDetails['last_updated'] = date($dateFormat, strtotime($data['last_updated']));
            $fileDetails['author'] = $data['author'];
            $fileDetails['permission'] = $data['permission'];

            $fileParam = maybe_unserialize($data['file_details']);
            if (!empty($fileParam)) {
                $fileDetails['file_name'] = $fileParam['name'];
                $fileDetails['file_path'] = $fileParam['path'];
                $fileDetails['file_type'] = $fileParam['type'];
                $fileDetails['file_size'] = $fileParam['size'];
            }

            //set file url
            if (false !== ($fileUrl = $this->getFileUrl($fileParam['name']))) {
                $fileDetails['file_url'] = $fileUrl;
            }

            //set download link
            if (false !== ($fileDwLink = $this->getDownloadLink($data['id']))) {
                $fileDetails['file_download_link'] = $fileDwLink;
            }

            //set file Icon Url
            if (false !== ($fileIconUrl = $this->getFileIcon($fileParam['type'], $data['id']))) {
                $fileDetails['file_icon'] = $fileIconUrl;
            }

            $return[] = $fileDetails;
        }

        return $return;
    }

    /*
     * public function to give file details html
     * @param int $file_id, string $field
     * @return bool|string
     */

    public function getFileData($file_id, $field) {
        if (empty($file_id) || empty($field)) {
            return false;
        }
        $file_id = esc_sql($file_id);
        $query = 'select `file_details` from ' . $this->fileTable . ' where `id` = ' . $file_id;
        $fileData = $this->dbobj->get_results($query, ARRAY_A);
        if ($fileData !== false && !empty($fileData)) {
            $fileData = maybe_unserialize($fileData[0]['file_details']);
            return $fileData[$field];
        }
        return false;
    }

    /*
     * public function to get count of all the versions of the file
     * @param int $file_id
     * @return int
     */

    public function getTotalVersions($file_id) {
        $file_id = esc_sql($file_id);
        if (!empty($file_id)) {
            $parent_id = $this->getFileDetail($file_id, 'parent');
            $query = 'select COUNT(*) from ' . $this->fileTable . ' where `parent` = ' . $file_id;
            $query = $parent_id == 0 ? $query : 'select COUNT(*) from ' . $this->fileTable . ' where `parent` = ' . $parent_id;
            $version_count = $this->dbobj->get_var($query);
            return $version_count !== false ? $version_count + 1 : 0;
        }
        return 0;
    }

    /*
     * public function to delete file
     * @param int $file_id
     * @return array
     */

    public function deleteFile($file_id = '') {
        if (empty($file_id)) {
            $this->response['errors'][] = 'Invalid file id.';
        } else {

            //check file is parent file
            if (false !== ($relation = $this->getRelation($file_id))) {
                //if current file is active
                if ($this->getFileDetail($file_id, 'status') == 1 && $this->getTotalVersions($file_id) > 1) {
                    $this->response['errors'][] = 'Cannot delete active version. Make another file as active and then delete this file';
                    return $this->response;
                }
                //file is parent and has childs
                if ($relation == 'Parent' && $this->getTotalVersions($file_id) > 1) {
                    $this->response['errors'][] = 'Cannot delete parent file which has child version. Delete all the child versions first';
                    return $this->response;
                }
            } else {
                $this->response['errors'][] = 'Unable to get file relation type';
                return $this->response;
            }

            $file_id = esc_sql($file_id);
            //get file path before deleting from DB
            $filePath = $this->getFileData($file_id, 'path');

            //delete file details from databse
            $result = $this->dbobj->delete($this->fileTable, array('id' => $file_id), array('%d'));
            //delete file from directory
            if ($result !== false) {
                if (unlink($filePath)) {
                    $this->response['success'][] = 'File deleted successfully.';
                } else {
                    $this->response['errors'][] = 'File details deleted from database, but not from upload directory';
                }
            } else {
                $this->response['errors'][] = 'Unable to delete file details from database';
            }
        }
        return $this->response;
    }

    /*
     * public function to check file is parent or child version
     * @param int $file_id
     * @return bool|string
     */

    public function getRelation($file_id = '') {
        if (empty($file_id)) {
            return false;
        } else {
            $file_id = esc_sql($file_id);
            $query = "select `parent` from " . $this->fileTable . " where `id` = " . $file_id;
            $parent = $this->dbobj->get_var($query);
            if ($parent !== false) {
                return $parent == 0 ? 'Parent' : 'Child';
            }
        }
        return false;
    }

    /*
     * public function to get file size in human redable form
     * @param int $bytes | int $decimals | bool $readable
     * @return string
     */

    public function readableFileSize($bytes, $decimals = 2, $readable = true) {
        $factor = floor((strlen($bytes) - 1) / 3);
        if ($readable) {
            $sz = array(0 => 'Byte', 1 => 'kB', 2 => 'Mb', 3 => 'GB');
            return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . $sz[$factor];
        }
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor));
    }

    /*
     * public static function fileUploadMaxSize to get max upload size
     * @return int
     */

    public static function fileUploadMaxSize() {
        static $max_size = -1;
        if ($max_size < 0) {
            $max_size = self::parseSize(ini_get('post_max_size'));
            $upload_max = self::parseSize(ini_get('upload_max_filesize'));
            if ($upload_max > 0 && $upload_max < $max_size) {
                $max_size = $upload_max;
            }
        }
        return round($max_size / 1024);
    }

    /*
     * public static function parseSize
     * @param float | int $size
     * @return float | int
     */

    public static function parseSize($size) {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
        $size = preg_replace('/[^0-9\.]/', '', $size);
        if ($unit) {
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        } else {
            return round($size);
        }
    }

    /*
     * public function to get file details array
     * @return array
     */

    public function getFileDetailsArray() {
        return array(
            'id' => '',
            'title' => '',
            'description' => '',
            'status' => '',
            'active' => '',
            'version' => '',
            'publish_date' => '',
            'last_updated' => '',
            'author' => '',
            'permission' => '',
            'file_name' => '',
            'file_path' => '',
            'file_url' => '',
            'file_download_link' => '',
            'file_icon' => '',
            'file_type' => '',
            'file_size' => ''
        );
    }

    /*
     * public function to get complete file details by id
     * @param int $file_id
     * @param bool|array
     */

    public function getFile($file_id = '') {

        if (empty($file_id) || !$this->isAccissible($file_id)) {
            return false;
        }

        $file_id = esc_sql($file_id);
        $fileDetails = $this->getFileDetailsArray();

        $dateFormat = get_option('wfv_setting_date_format');
        $dateFormat = empty($dateFormat) ? 'm-d-Y' : $dateFormat;

        $getFileQuery = "select * from " . $this->fileTable . " where `id` = " . $file_id;
        $data = $this->dbobj->get_results($getFileQuery, ARRAY_A);
        if ($data !== false && $file_id == $data[0]['id']) {
            $fileDetails['id'] = $data[0]['id'];
            $fileDetails['title'] = $data[0]['title'];
            $fileDetails['description'] = $data[0]['description'];
            $fileDetails['status'] = $data[0]['status'];
            $fileDetails['active'] = $data[0]['status'];
            $fileDetails['parent'] = $data[0]['parent'];
            $fileDetails['version'] = $data[0]['version'];
            $fileDetails['publish_date'] = date($dateFormat, strtotime($data[0]['publish_date']));
            $fileDetails['last_updated'] = date($dateFormat, strtotime($data[0]['last_updated']));
            $fileDetails['author'] = $data[0]['author'];
            $fileDetails['permission'] = $data[0]['permission'];

            $fileParam = maybe_unserialize($data[0]['file_details']);
            if (!empty($fileParam)) {
                $fileDetails['file_name'] = $fileParam['name'];
                $fileDetails['file_path'] = $fileParam['path'];
                $fileDetails['file_type'] = $fileParam['type'];
                $fileDetails['file_size'] = $fileParam['size'];
            }

            //set file url
            if (false !== ($fileUrl = $this->getFileUrl($fileParam['name']))) {
                $fileDetails['file_url'] = $fileUrl;
            }

            //set download link
            if (false !== ($fileDwLink = $this->getDownloadLink($data[0]['id']))) {
                $fileDetails['file_download_link'] = $fileDwLink;
            }

            //set file Icon Url
            if (false !== ($fileIconUrl = $this->getFileIcon($fileParam['type']))) {
                $fileDetails['file_icon'] = $fileIconUrl;
            }

            return $fileDetails;
        }

        return false;
    }

    /*
     * function to return file url by file name
     * @param string $name
     * @return string
     */

    public function getFileUrl($name = '') {
        if (empty($name)) {
            return false;
        }
        $subPath = WFV_UPLOAD_DIR_NAME . DIRECTORY_SEPARATOR . $name;
        return content_url($subPath);
    }

    /*
     * public function to return file download link
     * @param int $id
     * @return string
     */

    public function getDownloadLink($id) {
        //to do create download link
        $link = esc_url(add_query_arg('_wfv_dwn_key', wp_create_nonce('_wfv_dwn_key'), get_home_url()));
        $link = esc_url(add_query_arg('_file_id', $id, $link));
        return $link;
    }

    /*
     * public function to return file icon url/link
     * @param string $ext, int $file_id
     * @return string
     */

    public function getFileIcon($ext = '') {

        $iconHtml = '<i class="fa fa-file-o" style="font-size:34px;"></i>';
        if (empty($ext)) {
            return $iconHtml;
        }
        $icons = get_option('wfvIcon');
        if (!empty($icons)) {
            foreach ($icons['ext'] as $key => $value) {
                $extions = explode("|", $value);
                $extions = array_map('trim', $extions);
                //compare case insencetive
                $extions = array_map('strtolower', $extions);
                $ext = strtolower($ext);
                if (in_array($ext, $extions)) {
                    //check if custom icon uploaded
                    if (is_numeric($icons['id'][$key])) {
                        $iconHtml = sprintf('<img src="%s" style="width:%s;height:auto">', wp_get_attachment_url($icons['id'][$key]), $icons['size'][$key]);
                    } else {
                        $iconHtml = sprintf('<i class="%s" style="color:%s;font-size:%s"></i>', $icons['id'][$key], $icons['color'][$key], $icons['size'][$key]);
                    }
                }
            }
        }
        return $iconHtml;
    }

    /*
     * public function to check file access permission
     * @param int $file_id
     * @return bool
     */

    public function currentUserCanAccessFile($file_id = '') {
        if (empty($file_id)) {
            return false;
        }
        if (current_user_can('administrator')) {
            return true;
        }
        $file_id = esc_sql($file_id);
        $filePermissions = $this->dbobj->get_var('select `permission` from ' . $this->fileTable . ' where `id` = ' . $file_id);

        if ($filePermissions !== false) {
            //no permission set Open for all 
            if (empty($filePermissions)) {
                return true;
            } else {
                $filePermissions = explode("|", $filePermissions);
                $filePermissions = array_map('trim', $filePermissions);
                foreach ($filePermissions as $permission) {
                    if (current_user_can($permission)) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /*
     * public function to output file content
     * @param int $file_id
     * @return string
     */

    public function getFileTemplate($file_id) {
        if (empty($file_id)) {
            return false;
        }

        $fileDetails = $this->getFile($file_id);
        if (empty($fileDetails)) {
            return false;
        }
        $templateHtml = get_option('wfv_setting_file_tpl');

        foreach ($fileDetails as $key => $value) {
            $tagToReplace = "{" . $key . "}";
            $templateHtml = str_replace($tagToReplace, $value, $templateHtml);
        }
        return $templateHtml;
    }

    /*
     * public function to check if file is plublished
     * @param int $file_id
     * @return bool
     */

    public function isPublished($file_id = '') {
        if (empty($file_id)) {
            return false;
        }
        //checek if file is published and active
        $fileStatus = $this->getFileDetail($file_id, 'status');

        if (!empty($fileStatus) && $fileStatus == 1) {
            return true;
        }

        return false;
    }

    /*
     * public function to check if file is accessible
     * @param int $file_id
     * @return bool
     */

    public function isAccissible($file_id = '') {
        if (empty($file_id)) {
            return false;
        }

        if (is_admin() && current_user_can('administrator')) {
            return true;
        }

        //if current user is admin
        if ($this->isPublished($file_id) && current_user_can('administrator')) {
            return true;
        }

        if ($this->isPublished($file_id) && $this->currentUserCanAccessFile($file_id)) {
            return true;
        }

        return false;
    }

    /*
     * public function to get file details
     * @param int $file_id, string $field
     * @return bool | string
     */

    public function getFileDetail($file_id = '', $field = '') {
        if (empty($file_id) || empty($field)) {
            return false;
        }

        $file_id = esc_sql($file_id);
        $field = esc_sql($field);

        $query = 'select ' . $field . ' from ' . $this->fileTable . ' where `id` = ' . $file_id;

        return $this->dbobj->get_var($query);
    }

    /*
     * public function to return active version by file id
     * @param int $file_id
     * @return bool|int
     */

    public function getActiveFile($file_id) {
        $file_id = esc_sql($file_id);
        if (empty($file_id)) {
            return false;
        }
        //check if file it self is active version
        $isActive = $this->getFileDetail($file_id, 'status');
        $parentId = $this->getFileDetail($file_id, 'parent');
        if ($isActive == 1) {
            return $file_id;
        }
        if ($parentId == 0) {
            $query = 'select `id` from ' . $this->fileTable . ' where `parent` = ' . $file_id . ' and `status` = 1';
        } else {
            $query = 'select `id` from ' . $this->fileTable . ' where (`parent` = ' . $parentId . ' or `id` = ' . $parentId . ') and `status` = 1';
        }
        return $this->dbobj->get_var($query);
    }

    /*
     * public function to check if parent file exists on not
     * @param $parent_id
     * @return bool
     */

    public function isParentExists($parent_id) {
        //for new parent file
        if (empty($parent_id)) {
            return true;
        }
        $parent_id = esc_sql($parent_id);
        $query = "SELECT COUNT(*) FROM " . $this->fileTable . " WHERE `id` = " . $parent_id;

        if ($this->dbobj->get_var($query) > 0) {
            return true;
        }

        return false;
    }

    /*
     * public function to update post meta when switching active version
     * @pararm int $newFile, int $oldFile
     * @return bool
     */

    public function updatePostAttachedFiles($newFile, $oldFile) {        
        if (empty($newFile) || empty($oldFile)) {
            return false;
        }
        $newFile = esc_sql($newFile);
        $oldFile = esc_sql($oldFile);
        //add file select meta box to selected post types
        $allowedPostTypes = get_option('wfv_post_types');

        if (!empty($allowedPostTypes)) {
            $allowedPostTypes = implode("','", $allowedPostTypes);           
            $metaTable = $this->dbobj->postmeta;
            $postTable = $this->dbobj->posts;
            $query = "SELECT $metaTable.meta_value,$metaTable.meta_id FROM $metaTable inner join $postTable on $metaTable.post_id = $postTable.ID where $postTable.post_type IN ('$allowedPostTypes') AND $metaTable.meta_key='wfv_files'";

            $results = $this->dbobj->get_results($query,ARRAY_A);
           
            if ($results !== false && !empty($results)) {
                foreach ($results as $key => $value) {
                    $fileIds = maybe_unserialize($value['meta_value']);
                    $meta_id = $value['meta_id'];
                    if (!empty($fileIds) && false !== ($index = array_search($oldFile, $fileIds))) {
                        $fileIds[$index] = $newFile;
                        $fileIds = maybe_serialize($fileIds);
                        $updateQuery = "UPDATE `$metaTable` SET `meta_value` = '$fileIds' WHERE `meta_id` = $meta_id";
                        $updateResult = $this->dbobj->query($updateQuery);
                        
                        if ($updateResult === false) {
                            return false;
                        }
                    }
                }
            }
        }
    }

    /*
     * public function to set File data
     * @param int $file_id,string $field,string|int $value
     * @return bool
     */

    public function setFileData($file_id, $field, $value) {
        if (empty($file_id) || empty($field)) {
            return false;
        }
        $query = "UPDATE " . $this->fileTable . " SET " . $field . " = '" . $value . "' WHERE `id` = " . $file_id;
        if (false !== $this->dbobj->query($query)) {
            return true;
        }
        return false;
    }

}
