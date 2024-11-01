<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
//get file details
$file_id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$file_id = esc_sql($file_id);
$fileDetails = $this->fileObject->getFiles('where `id`=' . $file_id);
if (!empty($fileDetails)):
    $access = $fileDetails[0]['permission'];
    $access = explode('|', $access);
?><div id="wfvEditFileModel" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Edit File</h4>
                </div>
                <div class="modal-body">
                    <div id="wfv-edit-from-response"></div>
                    <form id="wfv_edit_form" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="wfv_current_file_details">
                                    <div class="wfv_file_detail">
                                        <b>Current File details</b><br>
                                        <span class="wfv-edit-file-icon"><?php echo $this->fileObject->getFileIcon($fileDetails[0]['file_type'], $fileDetails[0]['id']); ?></span>
                                        <span><b>Name </b><?php echo $fileDetails[0]['file_name'] ?></span>
                                        <span><b>Size </b><?php echo $fileDetails[0]['file_size'] ?></span>                                        
                                    </div>    
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6">                                
                                <label for="wfv_file">Upload New File:</label>
                                <input type="file" class="form-control" id="wfv_file" name="wfv_file">
                                <p class="form-text text-muted">Max upload size :<?php 
                            echo $this->fileObject->readableFileSize(round($this->fileObject->fileUploadMaxSize()*1024)); 
                            ?></p>
                            </div>
                            <div class="form-group has-feedback col-md-6">
                                <label for="wfv_title">Title: </label>
                                <input type="text" class="form-control" id="wfv_title" name="wfv_title" value="<?php echo $fileDetails[0]['title'] ?>">
                                <span class="glyphicon form-control-feedback" id="wfv_name1"></span>
                            </div>
                        </div>
                        <div class="row">    
                            <div class="form-group has-feedback col-md-12">
                                <label for="wfv_version">File Version: </label>
                                <input type="text" class="form-control" id="wfv_version" name="wfv_version" value="<?php echo $fileDetails[0]['version'] ?>">
                                <span class="glyphicon form-control-feedback" id="wfv_version1"></span>                                
                            </div>
                            <div class="form-group col-md-12">
                                <label for="wfv_description">Description: </label>
                                <textarea class="form-control" rows="5" id="wfv_description" name="wfv_description"><?php echo $fileDetails[0]['description'] ?></textarea>
                            </div>
                        </div>
                        <div class="row">             
                            <div class="form-group col-md-6">
                                <label for="wfv_status">File Status: </label>
                                <div class="checkbox">
                                    <label><input type="checkbox" name="wfv_status" value="1" <?php echo $fileDetails[0]['status'] == 1 ? 'checked onclick="return false;" onkeydown="return false;"' : ''; ?>>Active</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-12">                                
                                <label for="wfv_permission">Access Permission: </label>
                                <div class="row">
                                    <?php foreach (get_editable_roles() as $role_name => $role_info): ?>
                                        <div class="col-md-3">
                                            <div class="checkbox">
                                                <label><input type="checkbox" name="wfv_permission[]" value="<?php echo $role_name ?>" <?php echo in_array($role_name, $access) ? 'checked' : '' ?>>
                                                    <?php echo $role_name ?></label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>                        
                        <div class="row">
                            <div class="form-group col-md-12"> 
                                <input type="hidden" name="action" value="wfv_edit_file">
                                <input type="hidden" name="wfv_id" id="wfv_id" value="<?php echo $fileDetails[0]['id']; ?>">
                                <input type="hidden" name="wfv_parent" id="wfv_parent" value="<?php echo $fileDetails[0]['parent']; ?>">
                                <?php wp_nonce_field('wfv-edit-nonce_' . $fileDetails[0]['id']); ?>
                                <button type="submit" class="btn btn-primary" name="wfv_edit_file" id="wfv_edit_file">Update</button>
                                <button type="button" class="btn btn-default" data-dismiss="modal" name="wfv_can_file_btn">Cancel</button>
                            </div>
                        </div>  
                    </form>
                </div>
                <div class="modal-footer">                 
                </div>                
            </div>
        </div>
    </div>    
<?php endif; ?>