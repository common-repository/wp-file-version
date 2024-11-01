<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<tr>
    <td>
        <div class="form-group">
            <select class="form-control wfv-icon-type" name="wfvIcon[type][]">
                <option value="0" <?php echo $icons['type'][$key] == 0 ? 'selected' : ''; ?>>Default</option>
                <option value="1" <?php echo $icons['type'][$key] == 1 ? 'selected' : ''; ?>>Custom</option>
            </select>           
        </div>
    </td>
    <td>
        <div class="form-group wfv-icon-preview">
            <?php if (is_numeric($value)): ?>
                <img class="wfv-icon-image" src="<?php echo wp_get_attachment_url($value); ?>">
            <?php elseif(empty($value)): ?>
                <i class="fa fa-file-o" style="font-size:34px;"></i>
            <?php else: ?>
                <i class="<?php echo $value; ?>" style="font-size:34px;color:<?php echo $icons['color'][$key] ?>"></i>
            <?php endif; ?>   
        </div>
    </td>
    <td>
        <input type="hidden" name="wfvIcon[id][]" class="wfv_icon_id" class="regular-text" value="<?php echo $value ?>">        
        <button type="button" name="upload-btn" class="form-control btn btn-default wfv-upload-icon-btn <?php echo $icons['type'][$key] == 1 ? 'wfv-show' : 'wfv-hide'; ?>">Upload Icon <span class="glyphicon glyphicon-upload"></span></button>
        <div class="wfv-icon-select-container wfv-select-icon <?php echo $icons['type'][$key] == 1 ? 'wfv-hide' : 'wfv-show'; ?>">            
            <div class="dropdown">
                <button class="btn btn-default dropdown-toggle wfv-icon-select-btn" type="button" data-toggle="dropdown">Select Icon
                <span class="caret"></span></button>
                <ul class="dropdown-menu" role="menu" aria-labelledby="menu1">
                    <?php
                        $defaultIconsArray = array(
                            'default Icon'=>'fa fa-file-o',                 
                            'audio'=>'fa fa-file-audio-o',
                            'code'=>'fa fa-file-code-o', 
                            'excel'=>'fa fa-file-excel-o', 
                            'image'=>'fa fa-file-image-o',
                            'video'=>'fa fa-file-movie-o',                    
                            'pdf'=>'fa fa-file-pdf-o',
                            'powerpoint or ppt'=>'fa fa-file-powerpoint-o', 
                            'text'=>'fa fa-file-text-o',
                            'word'=>'fa fa-file-word-o', 
                            'zip/gzip/rar/archive/compressed'=>'fa fa-file-zip-o'
                        );
                        foreach($defaultIconsArray as $iconType=>$iconClass):
                    ?>
                    <li data-icon="<?php echo $iconClass; ?>" data-name="<?php echo $iconType; ?>" role="presentation"><p><i class="<?php echo $iconClass; ?>"></i> <?php echo $iconType; ?></p></li>
                    <?php endforeach;?>                    
                </ul>
            </div>
        </div>
    </td>    
    <td>
        <input type="text" class="form-control" name="wfvIcon[size][]" value="<?php echo $icons['size'][$key]; ?>">
    </td>
    <td>
        <input type="text" class="form-control wfv-icon-color" name="wfvIcon[color][]" value="<?php echo $icons['color'][$key]; ?>">
    </td>
    <td>
        <input type="text" class="form-control" name="wfvIcon[ext][]" value="<?php echo $icons['ext'][$key]; ?>">
    </td>
    <td>
        <button title="remove icon" class="wfv-icon-remove"><span class="glyphicon glyphicon-remove"></span></button>
    </td>
</tr>