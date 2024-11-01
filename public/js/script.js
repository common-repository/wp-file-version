var wfvfileicons = {defaultIcon: {icon: 'fa fa-file-o'},
    audio: {icon: 'fa fa-file-audio-o'},
    code: {icon: 'fa fa-file-code-o'},
    excel: {icon: 'fa fa-file-excel-o'},
    image: {icon: 'fa fa-file-image-o'},
    video: {icon: 'fa fa-file-movie-o'},
    pdf: {icon: 'fa fa-file-pdf-o'},
    powerpoint: {icon: 'fa fa-file-powerpoint-o'},
    text: {icon: 'fa fa-file-text-0'},
    word: {icon: 'fa fa-file-word-o'},
    archive: {icon: 'fa fa-file-zip-o'}
};

jQuery(function ($) {
    $(document).on('click', '.wfv_add_file_btn', function () {
        if ($("form#wfv_add_form").is(":visible")) {
            $("form#wfv_add_form").hide('slow');
        } else {
            $("form#wfv_add_form").show('slow');
        }
    });

    $(document).on('click', '.wfv_can_file_btn', function () {
        $("form#wfv_add_form").hide('slow');
    });

    //show hide file upload field with edit action
    $(document).on('change', 'form#wfv_edit_form .wfv_file_edit_action', function () {
        var val = $(this).val();
        //display file version select field
        if (val == 'switch') {
            $('form#wfv_edit_form >.wfv_select_file_version').show('slow');
            $('form#wfv_edit_form >.wfv_select_version').removeAttr('disabled');
            $('form#wfv_edit_form >.wfv_file_upload_field').hide('slow');
            $('form#wfv_edit_form >.wfv_file').attr('disabled', 'disabled');
        }
        else if (val == 'nothing') {
            $('form#wfv_edit_form >.wfv_file_upload_field').hide('slow');
            $('form#wfv_edit_form >.wfv_file').attr('disabled', 'disabled');
            $('form#wfv_edit_form >.wfv_select_file_version').hide();
            $('form#wfv_edit_form >.wfv_select_version_field').attr('disabled', 'disabled');
        }
        else {
            $('form#wfv_edit_form >.wfv_select_file_version').hide();
            $('form#wfv_edit_form >.wfv_select_version_field').attr('disabled', 'disabled');
            $('form#wfv_edit_form >.wfv_file').removeAttr('disabled');
            $('form#wfv_edit_form >.wfv_file_upload_field').show('slow');
        }
    });
    // this is the id of the form
    function processAddForm(form) {
        $(".wvf_loading").removeClass("wfv-hide");
        var addFormData = new FormData(form);
        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: addFormData,
            cache: false,
            processData: false,
            contentType: false,
            success: function (data)
            {   // alert(data);
                $("#wfv-from-response").html('');

                //check if security check failed
                if (data == -1) {
                    $("#wfv-from-response").append('<div class="error">Security check failed !</div>');
                }

                if (data.errors != null && data.errors.length > 0) {
                    for (var i = 0; i < data.errors.length; i++) {
                        $("#wfv-from-response").append('<div class="error">' + data.errors[i] + '</div>');
                    }
                } else {
                    if (data.success != null && data.success.length > 0) {
                        for (var i = 0; i < data.success.length; i++) {
                            $("#wfv-from-response").append('<div class="updated">' + data.success[i] + '</div>');
                        }

                        var parent_id = addFormData.get('wfv_parent');

                        //make this new version active                        
                        if (addFormData.get('wfv_activate') == 1 && data.file_id[0] != '') {
                            //set new parent id
                            $("form#wfv_add_form > #wfv_parent_id").val(data.file_id[0]);
                            parent_id = data.file_id[0];
                        }

                        if (addFormData.get('sub_action') == 'wfv_add_file_version' && parent_id > 0) {
                            refreshFileList('versionFiles', parent_id);
                        } else {
                            refreshFileList();
                        }
                        //reset all form fields
                        $('form#wfv_add_form').trigger("reset");
                        $('form#wfv_add_form').hide('slow');
                        $('form#wfv_add_form').attr('novalidate', 'novalidate');
                        $('form#wfv_add_form .has-success').removeClass('has-success');
                        $('form#wfv_add_form .glyphicon-ok').removeClass('glyphicon-ok');
                    }
                }
                $(".wvf_loading").addClass("wfv-hide");
            },
            error: function (data)
            {
                $("#wfv-from-response").html('');
                $("#wfv-from-response").append('<div class="error">Some error occured.</div>');
                $(".wvf_loading").addClass("wfv-hide");
            }
        });
    }
    ;

    /*
     * before model form hide do the initial setup and cleanup
     */
    $('#wfvAddFileModel').on('hide.bs.modal', function () {
        $("#wfv-from-response").html('');
        $('form#wfv_add_form').show();
    });


    /*
     * 
     * edit file form model
     */
    $(document).on('click', 'button.wfv-edit-file-button', function () {
        //load model form
        var file_id = $(this).data('id');
        $(".wvf_loading").removeClass("wfv-hide");
        $("#wfv-edit-form-model-container").html();
        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: {'action': 'wfv_get_edit_form', 'id': file_id},
            success: function (data)
            {
                $("#wfv-edit-form-model-container").html(data);
                //show model form
                $("#wfvEditFileModel").modal();
                $(".wvf_loading").addClass("wfv-hide");
            }
        });
    });

    /*
     * Edit file via Ajax
     */
    // this is the id of the form
    function processEditForm(form) {
        var editFromData = new FormData(form);
        $(".wvf_loading").removeClass("wfv-hide");
        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: editFromData,
            cache: false,
            processData: false,
            contentType: false,
            success: function (data)
            {
                $("#wfv-edit-from-response").html('');

                //check if security check failed
                if (data == -1) {
                    $("#wfv-edit-from-response").append('<div class="error">Security check failed !</div>');
                }

                if (data.errors != null && data.errors.length > 0) {
                    for (var i = 0; i < data.errors.length; i++) {
                        $("#wfv-edit-from-response").append('<div class="error">' + data.errors[i] + '</div>');
                    }
                } else {
                    if (data.success != null && data.success.length > 0) {
                        for (var i = 0; i < data.success.length; i++) {
                            $("#wfv-edit-from-response").append('<div class="updated">' + data.success[i] + '</div>');
                        }
                        //make current file as parent
                        if (editFromData.get('wfv_activate') == 1) {
                            parent_id = editFromData.get('wfv_id');
                        } else {
                            var parent_id = editFromData.get('wfv_parent');
                            ;
                            if (parent_id == 0) {
                                parent_id = editFromData.get('wfv_id');
                            }
                        }
                        refreshFileList('versionFiles', parent_id);
                        //reset all form fields
                        $('form#wfv_edit_form').trigger("reset");
                        $('form#wfv_edit_form').hide('slow');
                        $('form#wfv_edit_form').attr('novalidate', 'novalidate');
                        $('form#wfv_edit_form .has-success').removeClass('has-success');
                        $('form#wfv_edit_form .glyphicon-ok').removeClass('glyphicon-ok');
                    }
                }
                $(".wvf_loading").addClass("wfv-hide");
            },
            error: function (data)
            {
                $("#wfv-edit-from-response").html('');
                $("#wfv-edit-from-response").append('<div class="error">Some error occured.</div>');
                $(".wvf_loading").addClass("wfv-hide");
            }
        });
    }

    //front end validation for add new file form
    $('form#wfv_add_form').validate({
        rules: {
            wfv_file: {
                required: true
            },
            wfv_title: {
                required: true
            },
            wfv_version: {
                required: true
            },
            wfv_order: {
                digits: true
            }
        },
        highlight: function (element) {
            var id_attr = "form#wfv_add_form #" + $(element).attr("id") + "1";
            $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
            $(id_attr).removeClass('glyphicon-ok').addClass('glyphicon-remove');
        },
        unhighlight: function (element) {
            var id_attr = "form#wfv_add_form #" + $(element).attr("id") + "1";
            $(element).closest('.form-group').removeClass('has-error').addClass('has-success');
            $(id_attr).removeClass('glyphicon-remove').addClass('glyphicon-ok');
        },
        errorElement: 'span',
        errorClass: 'help-block',
        errorPlacement: function (error, element) {
            if (element.length) {
                error.insertAfter(element);
            } else {
                error.insertAfter(element);
            }
        },
        submitHandler: function (form) {
            processAddForm(form);
            return false;
        }
    });


    //front end validation for add new file form
    $(document).on('click', '#wfv_edit_file', function () {
        $('form#wfv_edit_form').validate({
            rules: {
                wfv_title: {
                    required: true
                },
                wfv_version: {
                    required: true
                },
                wfv_order: {
                    digits: true
                }
            },
            highlight: function (element) {
                var id_attr = "form#wfv_edit_form #" + $(element).attr("id") + "1";
                $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
                $(id_attr).removeClass('glyphicon-ok').addClass('glyphicon-remove');
            },
            unhighlight: function (element) {
                var id_attr = "form#wfv_edit_form #" + $(element).attr("id") + "1";
                $(element).closest('.form-group').removeClass('has-error').addClass('has-success');
                $(id_attr).removeClass('glyphicon-remove').addClass('glyphicon-ok');
            },
            errorElement: 'span',
            errorClass: 'help-block',
            errorPlacement: function (error, element) {
                if (element.length) {
                    error.insertAfter(element);
                } else {
                    error.insertAfter(element);
                }
            },
            submitHandler: function (form) {
                processEditForm(form);
                return false;
            }
        });
    });

    //manage file Icons script
    $(document).on('click', '#wfv-add-more-icon', function () {
        $(".wvf_loading").removeClass("wfv-hide");
        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: {'action': 'wfv_get_icon_form'},
            success: function (data) {
                $("table#wfv-icons-list").append(data);
                $(".wvf_loading").addClass("wfv-hide");
            }
        });
    });

    //remove file icons
    $(document).on('click', '.wfv-icon-remove', function () {
        $(this).parent().parent().remove();
    });

    //show hide icon type option
    $(document).on('change', '.wfv-icon-type', function () {
        var parentObj = $(this).parent().parent().parent();
        if ($(this).val() == 1) {
            $('.wfv-upload-icon-btn', parentObj).show();
            $('.wfv-select-icon', parentObj).hide();
        } else {
            $('.wfv-upload-icon-btn', parentObj).hide();
            $('.wfv-select-icon', parentObj).show();
        }
    });


    //on icon color change
    $(document).on('change', '.wfv-icon-color', function () {
        var iconcolor = $(this).val();
        $('.wfv-icon-preview > .fa', $(this).parent().parent()).css('color', iconcolor);
    });
});

jQuery(document).ready(function ($) {
    //delete file confirmation dialog
    $(document).on('click', 'button.wfv-delete-file-button', function () {        
        var fileId = $(this).data('id');
        var parentId = $(this).data('pid');        
        $("#wfv-file-delete").show();
        //set up dialog messgae
        $("#delete-confirm-message").html('Are you sure ? you want to delete <br> File:' +
                $(this).data('name') + '<br>Version:' + $(this).data('version'));
        $('#confirm_dialog').modal();
        $(document).on('click', '#wfv-file-delete', function () {
            wfvDeleteFile(fileId, parentId,$("#wfv-delete-nonce_" + fileId).val());            
        });
    });


    //delete file ajax request
    function wfvDeleteFile(file_id, parent_id,securityhash) {        
        if(typeof securityhash == 'undefined' || securityhash == ''){
            return;
        }
        pageLength = $("#wfv-version-table_wrapper select[name='wfv-version-table_length']").val();
        $(".wvf_loading").removeClass("wfv-hide");
        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: {'action': 'wfv_delete_file', 'id': file_id, 'wfv-delete-nonce': securityhash},
            success: function (data)
            {
                $("#delete-confirm-message").html('');
                $("#wfv-file-delete").hide();
                if (data.errors != null && data.errors.length > 0) {
                    for (var i = 0; i < data.errors.length; i++) {
                        $("#delete-confirm-message").append('<div class="error">' + data.errors[i] + '</div>');
                    }
                } else {
                    for (var i = 0; i < data.success.length; i++) {
                        $("#delete-confirm-message").append('<div class="updated">' + data.success[i] + '</div>');
                    }

                    if (parent_id == 0) {
                        parent_id = file_id;
                    }

                    refreshFileList('versionFiles', parent_id);
                }
                $(".wvf_loading").addClass("wfv-hide");
            }
        });
    }
});

jQuery(document).ready(function ($) {
    var fileTable = $('#wfv-file-table').DataTable({"responsive": true,"order": [[0, "desc"]], "oLanguage": {
            "sEmptyTable": "No file found !"
        }});
    var versionTable = $('#wfv-version-table').DataTable({"responsive": true,"order": [[0, "desc"]], "oLanguage": {
            "sEmptyTable": "No file found !"
        }});
    var iconsTable = $('#wfv-icons-list').DataTable({"responsive": true,"oLanguage": {
            "sEmptyTable": "No Icon found !"
        }, "searching": false});
});


/**
 * refresh file list
 */
function refreshFileList(listType, fileId) {
    var actiondata;
    var pageLength;
    if (listType == 'versionFiles' && fileId != '') {
        actiondata = {'action': 'wfv_refresh_file_list', 'sub_action': 'wfv_add_file_version', 'id': fileId};
        pageLength = jQuery("#wfv-version-table_wrapper select[name='wfv-version-table_length']").val();
    } else {
        actiondata = {'action': 'wfv_refresh_file_list'};
        pageLength = jQuery("#wfv-file-table_wrapper select[name='wfv-file-table_length']").val();
    }

    jQuery.ajax({
        type: "POST",
        url: ajaxurl,
        data: actiondata,
        success: function (data)
        {
            jQuery("#wfv-files-container").html(data);
            var fileTable = jQuery('#wfv-file-table').DataTable({"responsive": true,"order": [[0, "desc"]], "pageLength": 10, "oLanguage": {
                    "sEmptyTable": "No file found !"
                }});
            var versionTable = jQuery('#wfv-version-table').DataTable({"responsive": true,"order": [[0, "desc"]], "pageLength": 10, "oLanguage": {
                    "sEmptyTable": "No file found !"
                }});
        }
    });
}

//upload icon image script in option page
jQuery(document).ready(function ($) {
    $(document).on('click', '.wfv-upload-icon-btn', function (e) {
        var obj = $(this);
        e.preventDefault();
        var image = wp.media({
            title: 'Upload Image',
            multiple: false
        }).open()
                .on('select', function (e) {
                    var uploaded_image = image.state().get('selection').first();
                    var attachment_id = uploaded_image.toJSON().id;
                    var attachment_url = uploaded_image.toJSON().url;
                    $(obj).prev(".wfv_icon_id").val(attachment_id);
                    var parObj = obj.parent().parent();
                    $('.wfv-icon-preview', parObj).html('<img class="wfv-icon-image" src="' + attachment_url + '">');
                });
    });

    //when icon is selected from drop down list
    $(document).on('click', '.wfv-select-icon ul li', function () {
        var iconClass = $(this).data('icon');
        var iconName = $(this).data('name');
        //var parObj = $(this).parent().parent().parent();
        $('.wfv-icon-preview', $(this).parent().parent().parent().parent().parent()).html('<i class="' + iconClass + '" style="font-size:34px;"></i>');
        $('.wfv-icon-select-btn', $(this).parent().parent().parent()).html(iconName + ' <span class="caret"></span>');
        $('.wfv_icon_id', $(this).parent().parent().parent().parent()).val(iconClass);
    });

});

jQuery(document).ready(function ($) {
//auto insert file filed in template html field
    jQuery("select#wfv-file-parameter-select").on('change', function () {
        var $txt = jQuery("#wfv_setting_file_tpl");
        var caretPos = $txt[0].selectionStart;
        var textAreaTxt = $txt.val();
        var txtToAdd = $(this).val();
        $txt.val(textAreaTxt.substring(0, caretPos) + txtToAdd + textAreaTxt.substring(caretPos));
    });

    //append select icon
    $('.wfv-select-icon-container').append(getfileIconSelectList());
});


//function to create file icon select dropdown
function getfileIconSelectList() {
    var iconSelectOptions = '<select name="wfvIcon[icon][]" class="wfv-select-icon">';
    for (var key in wfvfileicons) {
        if (wfvfileicons.hasOwnProperty(key)) {
            iconSelectOptions += '<option value="' + wfvfileicons[key].icon + '"><i class="' + wfvfileicons[key].icon + '"></i> ' + key + '</option>';
        }
    }
    return iconSelectOptions + '</select>';
}

//file list click on read more button
jQuery(document).ready(function ($) {
    $(document).on('click', 'span.wfv-read-more', function () {
        $(".wvf_loading").removeClass("wfv-hide");
        $('#wfvFileDetailModal .modal-body').html('');
        actiondata = {'action': 'wfvshowFile', 'file_id': $(this).data('id'), 'wfv_rm_nonce': $(this).data('nonce')};
        jQuery.ajax({
            type: "POST",
            url: ajaxurl,
            data: actiondata,
            success: function (data)
            {
                $('#wfvFileDetailModal .modal-body').html(data);
                $('#wfvFileDetailModal').modal('show');
                $(".wvf_loading").addClass("wfv-hide");
            },
            error: function () {
                $(".wvf_loading").addClass("wfv-hide");
            }
        });
    });
    
    $(document).on('click','.wfv-responsive-read-more',function(){
        if($(this).hasClass('glyphicon-plus-sign')){
            $(this).removeClass('glyphicon-plus-sign');
            $(this).addClass('glyphicon-minus-sign');
            $(this).css('color','red');
        }else{
           $(this).removeClass('glyphicon-minus-sign'); 
           $(this).addClass('glyphicon-plus-sign');
           $(this).css('color','green');
        }
    });
    
});