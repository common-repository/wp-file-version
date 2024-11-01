<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<div class="wrap"><div id="icon-tools" class="icon32"><?php settings_errors(); ?></div>
    <?php $this->wfvShowLoader(); ?>
    <h1>WP file Icons setting</h1>
    <p class="form-text text-muted">Insert file extentions(eq. jpg|png|gif ) seperated by pipe symbol "|". The selected icon will be attached to the specified file types</p>
    <button class="btn btn-primary" id="wfv-add-more-icon">Add Icons <span class="glyphicon glyphicon-plus"></span><i class='fa fa-spinner fa-spin wfv-hide'></i></button>    
    <form id="wfv-option-form" method="post" action="options.php">       
        <?php settings_fields('wfv-icon-settings'); ?>
        <?php do_settings_sections('wfv-icon-settings'); ?>
        <table class="table" id="wfv-icons-list">
            <thead>
                <tr>
                    <th>Icon Type</th>
                    <th>Icon Preview</th>
                    <th>Upload/Select Icon</th>                    
                    <th>Icon size(in px or %)</th>
                    <th>Icon color</th>
                    <th>File Extentions</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $icons = get_option('wfvIcon'); ?>
                <?php if (!empty($icons['id']) && !empty($icons['ext'])): ?>
                <?php foreach ($icons['id'] as $key => $value): ?>
                <?php include(WFV_PLUGIN_PATH . 'views' . DIRECTORY_SEPARATOR . 'option-page' . DIRECTORY_SEPARATOR . 'icon-detail.php'); ?>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <?php submit_button(); ?>
    </form>
</div>