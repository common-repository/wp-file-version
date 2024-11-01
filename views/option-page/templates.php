<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<div class="wrap"><div id="icon-tools" class="icon32"><?php settings_errors(); ?></div>
    <h1>WP File Version settings</h1>
    <form id="wfv-option-form" method="post" action="options.php">       
        <?php settings_fields('wfv-tpl-settings'); ?>
        <?php do_settings_sections('wfv-tp-settings'); ?>        
        <div class="form-group">
            <label for="wfv_setting_file_tpl">Template: select file variables to add with template</label>
            <select id="wfv-file-parameter-select">
                <option value="">Select File Variable</option>
                <?php                
                $fileArray = $this->fileObject->getFileDetailsArray();
                ?>
                <?php if (!empty($fileArray)): ?>
                    <?php foreach ($fileArray as $key => $value): ?>
                        <option value="{<?php echo $key ?>}"><?php echo $key ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            <textarea class="form-control" rows="10" id="wfv_setting_file_tpl" name="wfv_setting_file_tpl"><?php echo esc_attr(get_option('wfv_setting_file_tpl')); ?></textarea>
            <p class="form-text text-muted">Custom File template HTML to display file details on front end</p>
        </div>
        <div class="form-group">
            <label for="wfv_setting_file_css">style/css:</label>
            <textarea class="form-control" rows="10"  id="wfv_setting_file_css" name="wfv_setting_file_css"><?php echo esc_attr(get_option('wfv_setting_file_css')); ?></textarea>
            <p class="form-text text-muted">Custom CSS for file template</p>
        </div>    
        <?php submit_button(); ?>
    </form>
</div>