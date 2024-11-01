<?php
if (!defined('ABSPATH'))  exit; // Exit if accessed directly
$details = $this->fileObject->getFile($_POST['file_id']);
if (!empty($details)):
?>
<?php foreach ($details as $key => $detail): ?>
    <div class="wfv-file-detail-row">
        <div class="wfv-file-detail-col col-md-3"><b><?php echo ucfirst(str_replace("_"," ",$key)); ?>:</b></div>
        <div class="wfv-file-detail-col col-md-9"><?php echo $key=='active' && $detail == 1 ? 'Yes':$detail ?></div>
    </div>    
<?php endforeach; ?>
</div>
<?php endif; ?>