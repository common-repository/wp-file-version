<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
?>
<div class="wrap"><div id="icon-tools" class="icon32"><?php settings_errors(); ?></div>
    <h1>WP file version settings</h1>
    <form id="wfv-option-form" method="post" action="options.php">       
        <?php settings_fields('wfv-general-settings'); ?>
<?php do_settings_sections('wfv-general-settings'); ?>
        <div class="form-group">
            <label for="wfv_setting_allowed_ftypes">Allowed File Types MIME</label>
            <textarea class="form-control" rows="5" id="wfv_setting_allowed_ftypes" name="wfv_setting_allowed_ftypes"><?php echo esc_attr(get_option('wfv_setting_allowed_ftypes')); ?></textarea>
            <p class="form-text text-muted">Please enter the allowed file MIME type separated by pipe symbol "|",
                Example: image/jpg | video/3gpp | application/pdf
                <br>Please find more info about MIME type: <a target="_blank" href="http://www.freeformatter.com/mime-types-list.html">Click here</a>
            </p>
        </div>
        <div class="form-group">
            <label for="wfv_setting_date_format">Date Format:</label>
            <input type="text" class="form-control" name="wfv_setting_date_format" value="<?php echo esc_attr(get_option('wfv_setting_date_format')); ?>" />
            <p class="form-text text-muted">Enter date format for file dates (default m-d-Y)</p>
        </div>
        <div class="form-group">
            <label for="wfv_setting_allow_direct_access">
                <input type="checkbox" class="form-control" name="wfv_setting_allow_direct_access" value="1" <?php echo esc_attr(get_option('wfv_setting_allow_direct_access')) == 1 ? 'checked' : ''; ?>/>
                Allow direct URL access
            </label>
            <p class="form-text text-muted">Important security setting. If checked anyone can be downloaded/access file by its URL, If unchecked only
                allowed user roles can download by clicking on the download link only.
            </p>
        </div>
        <div class="form-group">
            <label for="wfv_permission">Allowed Post Types: </label>
            <p class="form-text text-muted">Please select the post types where you want to attach version controllable files</p>
            <div class="row">
                <?php foreach ( get_post_types(array('public'=>true), 'names' ) as $post_type): ?>                       
                    <div class="col-md-3">
                        <div class="checkbox">
                            <label><input type="checkbox" name="wfv_post_types[]" value="<?php echo $post_type ?>" <?php echo is_array(get_option('wfv_post_types')) && in_array($post_type, get_option('wfv_post_types')) ? 'checked' : '' ?>><?php echo $post_type ?></label>
                        </div>
                    </div>    
                <?php endforeach; ?>
            </div>   
        </div>    
        <?php submit_button(); ?>
    </form>
</div>