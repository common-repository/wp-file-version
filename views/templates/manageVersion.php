<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<!-- this template will list the all the versions of a file and its managing options -->
<div class="wrap"><div id="icon-tools" class="icon32"></div>    
    <?php $this->wfvShowLoader(); ?>
<?php
    $file_id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
    if(!wp_verify_nonce( $_REQUEST['_wpnonce'], 'wfv-manage-version-nonce_'.$file_id)){
        wp_die('Security check failed !');
    }    
?><h1>All Versions of <?php echo $this->fileObject->getFileDetail($file_id,'title');?></h1> 
    <button data-toggle="modal" data-target="#wfvAddFileModel" class="btn btn-primary" >Add New Version <span class="glyphicon glyphicon-plus" aria-hidden="true"></span></button>
    <a class="btn btn-default" href="<?php echo admin_url('options-general.php?page=wfv_manage_files'); ?>"><span class="glyphicon glyphicon-arrow-left"></span> Back</a>    
    <?php include 'form.php' ?>
    <div id="wfv-files-container">
        <?php include 'versionList.php' ?>
    </div>
    <a class="btn btn-default" href="<?php echo admin_url('options-general.php?page=wfv_manage_files'); ?>"><span class="glyphicon glyphicon-arrow-left"></span> Back</a>    
    <div id="confirm_dialog" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Delete File</h4>
                </div>
                <div id="delete-confirm-message" class="modal-body">                        
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="wfv-file-delete">Delete</button>
                    <button type="button" data-dismiss="modal" class="btn">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    <div id="wfv-edit-form-model-container">
    </div>
    <?php include 'fileDetailModel.php' ?>
</div>    