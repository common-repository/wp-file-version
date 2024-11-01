<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly ?>
<div id="wfvAddFileModel" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">
                    <?php if (isset($_REQUEST['id']) && !empty($_REQUEST['id'])): ?>    
                        Add New Version
                    <?php else: ?>
                        Add New File
                    <?php endif; ?>    
                </h4>
            </div>
            <div class="modal-body">
                <div id="wfv-from-response"></div>                
                <form id="wfv_add_form" method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="form-group has-feedback col-md-6">
                            <label for="file">File <span class="glyphicon glyphicon-asterisk"></span>:</label>
                            <input type="file" class="form-control" id="wfv_file" name="wfv_file">
                            <span class="glyphicon form-control-feedback" id="wfv_file1"></span>
                            <p class="form-text text-muted">Max upload size :<?php 
                            echo $this->fileObject->readableFileSize(round($this->fileObject->fileUploadMaxSize()*1024)); 
                            ?></p>
                        </div>
                        <div class="form-group has-feedback col-md-6">
                            <label for="wfv_title">Title <span class="glyphicon glyphicon-asterisk"></span>:</label>
                            <input type="text" class="form-control" id="wfv_title" name="wfv_title">
                            <span class="glyphicon form-control-feedback" id="wfv_title1"></span>
                        </div>
                    </div>   
                    <div class="row">
                        <div class="form-group has-feedback col-md-12">
                            <label for="wfv_version">File Version <span class="glyphicon glyphicon-asterisk"></span>: </label>
                            <input type="text" class="form-control" id="wfv_version" name="wfv_version">
                            <span class="glyphicon form-control-feedback" id="wfv_version1"></span>
                        </div>                        
                        <div class="form-group col-md-12">
                            <label for="wfv_description">Description: </label>
                            <textarea class="form-control" rows="4" id="wfv_description" name="wfv_description"></textarea>
                        </div>
                    </div>
                     <?php if (isset($_REQUEST['id']) && !empty($_REQUEST['id'])): ?>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="wfv_status">File Status: </label>
                            <div class="checkbox">
                                <label><input type="checkbox" name="wfv_status" value="1" checked>Active</label>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label for="wfv_permission">Access Permission: </label>
                            <div class="row">
                                <?php foreach (get_editable_roles() as $role_name => $role_info): ?>                                    
                                    <div class="col-md-3">
                                        <div class="checkbox">
                                            <label><input type="checkbox" name="wfv_permission[]" value="<?php echo $role_name ?>"><?php echo $role_name ?></label>
                                        </div>
                                    </div>    
                                <?php endforeach; ?>
                            </div>    
                        </div>                        
                    </div>
                    <div class="row">                                                
                        <?php if (isset($_REQUEST['id']) && !empty($_REQUEST['id'])): 
                               $parent_id =  $this->fileObject->getFileDetail($_REQUEST['id'],'parent');
                               $parent_id = $parent_id == 0 ? $_REQUEST['id']:$parent_id;
                        ?><input type="hidden" name="wfv_parent" id="wfv_parent_id" value="<?php echo $parent_id; ?>">
                        <input type="hidden" name="sub_action" id="sub_action" value="wfv_add_file_version">  
                        <?php else: ?>
                            <input type="hidden" name="wfv_parent" value="0">                        
                        <?php endif; ?>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-12">    
                            <input type="hidden" name="action" value="wfv_add_file">
                            <?php wp_nonce_field('wfv_add_file'); ?>
                            <button type="submit" id="wfv_add_button" class="btn btn-primary" name="wfv_add_file">Submit</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">                 
            </div>
        </div>
    </div>   
</div>
