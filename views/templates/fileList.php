<?php
/*
 * This template file will list all the active/parent files
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
$files = $this->fileObject->getFiles('where `status` = 1');
?>
<table id="wfv-file-table" class="table display" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th>Id</th>
            <th>Title</th>
            <th>Name</th>            
            <th>Size</th>
            <th>Author</th>
            <th>Total Versions</th>
            <th>Active Version</th>
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
                    <td title="read more"><span class="glyphicon glyphicon-plus-sign wfv-read-more" data-nonce="<?php echo wp_create_nonce( 'wfv-read-more-nonce_'.$value['id']); ?>" data-id="<?php echo $value['id']; ?>"></span>
                        <span class="glyphicon glyphicon-plus-sign wfv-responsive-read-more"></span>
                        <span class="wfv-file-id-span"><?php echo $value['id']; ?></span>
                    </td>
                    <td><?php echo WFV::wfvExcerpt($value['title'], 30); ?></td>
                    <td><?php echo $value['file_name']; ?></td>                    
                    <td><?php echo $value['file_size']; ?></td>
                    <td><?php $author_obj = get_user_by('id', $value['author']);
                    echo $author_obj->user_email;
                ?></td>
                    <td><?php echo $this->fileObject->getTotalVersions($value['id']); ?></td>
                    <td><?php echo $value['version']; ?></td>
                    <td><?php echo $value['publish_date']; ?></td>
                    <td>                       
                        <a title="add new version" href="<?php echo wp_nonce_url(admin_url('options-general.php?page=wfv_manage_versions&id=' . $value['id']),'wfv-manage-version-nonce_'.$value['id']); ?>"><span class="glyphicon glyphicon-plus-sign"></span></a>
                        &nbsp; &nbsp;<a title="manage versions" href="<?php echo wp_nonce_url(admin_url('options-general.php?page=wfv_manage_versions&id=' . $value['id']),'wfv-manage-version-nonce_'.$value['id']) ?>"><span class="glyphicon glyphicon-edit"></span></a>
                        &nbsp; &nbsp;<a title="delete versions" href="<?php echo wp_nonce_url(admin_url('options-general.php?page=wfv_manage_versions&id=' . $value['id']),'wfv-manage-version-nonce_'.$value['id']) ?>"><span class="glyphicon glyphicon-remove" style="color:#ca2f2f;"></span></a>
                    </td>                    
                </tr>
                <?php
            endforeach;
        endif;
        ?>
    </tbody>
</table>