<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
?>
<div class="wrap"><div id="icon-tools" class="icon32"></div>
   <h1>All Active Versions/Files</h1> 
   <?php $this->wfvShowLoader(); ?>
   <button data-toggle="modal" data-target="#wfvAddFileModel" class="btn btn-primary" >Add File <span class="glyphicon glyphicon-plus" aria-hidden="true"></span></button>
   <?php include 'templates/form.php' ?>
   <div id="wfv-files-container">
     <?php include 'templates/fileList.php' ?>
   </div>
   <?php include 'templates/fileDetailModel.php' ?>
</div>