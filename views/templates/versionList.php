<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
if (isset($_REQUEST['id']) && !empty($_REQUEST['id'])) {    
    $file_id = esc_sql($_REQUEST['id']);
    $parent_id = $this->fileObject->getFileDetail($file_id,'parent');
    $where = 'where parent =' . $file_id . ' or `id`=' . $file_id;
    $where = $parent_id == 0 ? $where : 'where parent =' . $parent_id . ' or `id`=' . $parent_id;
    $files = $this->fileObject->getFiles($where);
}
?>
<table id="wfv-version-table" class="table display" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th>Id</th>            
            <th>Title</th>
            <th>Icon</th>
            <th>Name</th>            
            <th>Size</th>
            <th>Author</th>
            <th>Version</th>            
            <th>Status</th>
            <th>Access</th>
            <th>Date Added</th>
            <th>Action</th>                
        </tr>
    </thead>
    <tbody>
        <?php
        if (!empty($files) && $files !== false):
            foreach ($files as $value) :
                ?>
                <tr>
                    <td title="read more">
                        <span class="glyphicon glyphicon-plus-sign wfv-read-more" data-nonce="<?php echo wp_create_nonce( 'wfv-read-more-nonce_'.$value['id']); ?>" data-id="<?php echo $value['id']; ?>"></span>
                        <span class="glyphicon glyphicon-plus-sign wfv-responsive-read-more"></span>
                        <span class="wfv-file-id-span"><?php echo $value['id']; ?></span>
                    </td>
                    <td data-toggle="tooltip" title="<?php echo $value['title']; ?>"><?php echo $value['title']; ?></td>
                    <td data-toggle="tooltip"><?php echo $this->fileObject->getFileIcon($value['file_type'],$value['id']); ?></td>
                    <td data-toggle="tooltip"><?php echo $value['file_name']; ?></td>                    
                    <td data-toggle="tooltip"><?php echo $value['file_size']; ?></td>
                    <td data-toggle="tooltip"><?php
                        $author_obj = get_user_by('id', $value['author']);
                        echo $author_obj->user_email;
                        ?></td>
                    <td data-toggle="tooltip"><?php echo $value['version']; ?></td>
                    <td data-toggle="tooltip"><?php echo $value['status'] == 1 ? '<span style="color:green" class="glyphicon glyphicon-check"></span>&nbsp;Active':'<span class="glyphicon glyphicon-unchecked"></span>&nbspInactive'; ?></td>                   
                    <td data-toggle="tooltip"><?php echo $value['permission'] == '' ? 'Open' : $value['permission']; ?></td>
                    <td data-toggle="tooltip"><?php echo $value['publish_date']; ?></td>
                    <td><button type="button" title="Edit" class="wfv-edit-file-button" data-id="<?php echo $value['id'] ?>"><span class="glyphicon glyphicon-edit"></span></button>&nbsp;&nbsp;
                        <?php if($this->fileObject->getFileDetail($value['id'],'parent') == 0 && $this->fileObject->getTotalVersions($value['id']) > 1): ?>
                        <button type="button" title="Parent File"><span class="glyphicon glyphicon-lock"></span></button>
                        <?php else: ?>
                        <button type="button" title="Remove" class="wfv-delete-file-button" data-name="<?php echo $value['title']; ?>" data-id="<?php echo $value['id']; ?>"
                       data-pid="<?php echo $value['parent']; ?>"  data-version="<?php echo $value['version']; ?>" class="wfv-delete-file">
                        <span class="glyphicon glyphicon-remove"></span></button>
                        <?php endif; ?>
                       <?php   $wfv_delete_nonce = wp_create_nonce( 'wfv-delete-nonce_'.$value['id'] ); ?>
                        <input type="hidden" id="<?php echo 'wfv-delete-nonce_'.$value['id']  ?>" value="<?php echo $wfv_delete_nonce; ?>"> 
                    </td>
                </tr>                
                <?php
            endforeach;
        endif;
        ?>
    </tbody>
</table>