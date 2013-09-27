<form name="packlink_frm" id="packlink_frm" action="<?= $_SERVER['REQUEST_URI'] ?>" method="post" onSubmit="onSubmitPL()">
        <input name="_ACTIVE_TAB" id="_ACTIVE_TAB" type="hidden" value="<?= $_ACTIVE_TAB ?>" />
        <h2 title="<?= $this->l('PackLink Settings') ?>"><img src="http://www.packlink.es/images/logo.png" /></h2>
        
        <div class="clear center"><p>&nbsp;</p></div>
        
        <div id="sectionsPackLink">
            <span <?= $_ACTIVE_TAB=="0"?'class="selected"':"" ?> ><?= $this->l('User Identification') ?></span>
            <span <?= $_ACTIVE_TAB=="1"?'class="selected"':"" ?> ><?= $this->l('Addresses') ?></span>
            <span <?= $_ACTIVE_TAB=="2"?'class="selected"':"" ?>><?= $this->l('Services') ?></span>
            <span <?= $_ACTIVE_TAB=="3"?'class="selected"':"" ?> ><?= $this->l('Add Boxes') ?></span>
            <span <?= $_ACTIVE_TAB=="4"?'class="selected"':"" ?> ><?= $this->l('Other Options') ?></span>
        </div>
        
        <div id="contentSectionsPackLink">
            <fieldset<?= $_ACTIVE_TAB=="0"?'':' style="display:none"'?>>
                    <legend><?= $this->l('User Identification') ?></legend>
                    <span>
                        <label class="labelSection"><?= $this->l('Mode API') ?></label>
                        <table style="width:250px; padding: 5px 10px;">
                            <tr>
                                <td><input style="min-width:auto;" type="radio" disabled="disable" <?= substr($apikey, 0, 2)=="DL"?"checked='checked'":"" ?> name="<?= $this->l('Development') ?>" id="<?= $this->l('Development') ?>" /><label class="labelCheckBox" style="width:auto;" for="<?= $this->l('Development') ?>"><span></span><?= $this->l('Development') ?></label></td>
                                <td><input style="min-width:auto;" type="radio" disabled="disable" <?= substr($apikey, 0, 2)!="DL"?"checked='checked'":"" ?> name="<?= $this->l('Production') ?>" id="<?= $this->l('Production') ?>" /><label class="labelCheckBox" style="width:auto;" for="<?= $this->l('Production') ?>"><span></span><?= $this->l('Production') ?></label></td>
                            </tr>
                        </table>
                    </span>
                    
                    <span>
                        <label class="labelSection"><?= $this->l('User Name') ?></label>
                        <input type="text" name="username" id="username" value="<?= $username ?>" />
                    </span>
                    
                    <span>
                        <label class="labelSection"><?= $this->l('Password') ?></label>
                        <input type="text" name="password" id="password" value="<?= $password ?>" />
                    </span>
                    
                    <span>
                        <label class="labelSection"><?= $this->l('API Key') ?></label>
                        <input type="text" name="apikey"  id="apikey" value="<?= $apikey ?>" />
                    </span>
                    
                    <span>
                        <label class="labelSection"><?= $this->l('Display Name') ?></label>
                        <input type="text" name="_PL_USER_DISPLAY_NAME" style=" background: transparent; border: none" id="_PL_USER_DISPLAY_NAME" value="<?= $_PL_USER_DISPLAY_NAME ?>" />
                    </span>
                    
                    <span>
                        <label class="labelSection"><?= $this->l('Email') ?></label>
                        <input type="text" name="_PL_USER_EMAIL" style=" background: transparent; border: none" id="_PL_USER_EMAIL" value="<?= $_PL_USER_EMAIL ?>" />
                    </span>
                    
                    <span>
                        <label class="labelSection"><?= $this->l('Expiry') ?></label>
                        <input type="text" name="_EXPIRY" style=" background: transparent; border: none" id="_EXPIRY" value="<?= $_EXPIRY ?>" />
                    </span>
            </fieldset>
            
            <fieldset<?= $_ACTIVE_TAB=="1"?'':' style="display:none"'?>>
                    <legend><?= $this->l('Addresses') ?></legend>
                    <legend><?= $this->l('Gathered/Store Address') ?></legend>
                    <div style="color:transparent;">
                        <span>
                            <label class="labelSection"><?= $this->l('Address') ?></label>
                            <input type="text" name="_ADDRESS_SHOP" id="_ADDRESS_SHOP" value="<?= $_ADDRESS_SHOP ?>" />
                        </span>    

                        <span>
                            <label class="labelSection"><?= $this->l('Town') ?></label>
                            <input type="text" name="_TOWN_SHOP" id="_TOWN_SHOP" value="<?= $_TOWN_SHOP ?>" />
                        </span>
                        
                        <span>
                            <label class="labelSection"><?= $this->l('Province') ?></label>
                            <input type="text" name="_PROVINCE_SHOP" id="_PROVINCE_SHOP" value="<?= $_PROVINCE_SHOP ?>" />
                        </span>
                        
                        <span>
                            <label class="labelSection"><?= $this->l('Postal Code') ?></label>
                            <input type="text" name="_POST_CODE_SHOP" id="_POST_CODE_SHOP" value="<?= $_POST_CODE_SHOP ?>" />
                        </span>
                        
                        <span>
                            <label class="labelSection"><?= $this->l('Country') ?></label>
                            <input type="text" name="_COUNTRY_SHOP" id="_COUNTRY_SHOP" value="<?= $_COUNTRY_SHOP ?>" />
                            <select style="display:none" id="_COUNTRY_SHOP_SELECT" name="_COUNTRY_SHOP_SELECT">
                                <?php 
                                    if ($countries = Db::getInstance()->ExecuteS("SELECT DISTINCT id_country AS 'id', name FROM `"._DB_PREFIX_.'country_lang` WHERE id_lang = '.$id_lang)){
                                        foreach ($countries as $country){
                                            ?><option <?= $_ID_COUNTRY_SHOP==$country['id']?'selected="selected"':""  ?>value="<?= $country['id'] ?>"><?= $country['name'] ?></option><?php
                                        }
                                    }
                                ?>
                            </select>
                        </span>
                        
                        <span>
                            <label class="labelSection"><?= $this->l('Telephone') ?></label>
                            <input type="text" name="_LANDLINE_SHOP" id="_LANDLINE_SHOP" value="<?= $_LANDLINE_SHOP ?>" />
                        </span>
                        
                        <span>
                            <label class="labelSection"><?= $this->l('Fax') ?></label>
                            <input type="text" name="_FAX_SHOP" id="_FAX_SHOP" value="<?= $_FAX_SHOP ?>" />
                        </span>
                        <span>
                            <label class="labelSection"><?= $this->l('Other Phone') ?></label>
                            <input type="text" name="_OTHER_PHONE_SHOP" id="_OTHER_PHONE_SHOP" value="<?= $_OTHER_PHONE_SHOP ?>" />
                        </span>
                    </div>
                    
                    <legend><?= $this->l('Invoicing Address') ?></legend>
                    
                    <div style="color:transparent;">
                        <span>
                            <label class="labelSection"><?= $this->l('Address') ?></label>
                            <input type="text" name="_INVOICE_ADDRESS_SHOP" id="_INVOICE_ADDRESS_SHOP" value="<?= $_INVOICE_ADDRESS_SHOP ?>" />
                        </span>    

                        <span>
                            <label class="labelSection"><?= $this->l('Town') ?></label>
                            <input type="text" name="_INVOICE_TOWN_SHOP" id="_INVOICE_TOWN_SHOP" value="<?= $_INVOICE_TOWN_SHOP ?>" />
                        </span>
                        
                        <span>
                            <label class="labelSection"><?= $this->l('Province') ?></label>
                            <input type="text" name="_INVOICE_PROVINCE_SHOP" id="_INVOICE_PROVINCE_SHOP" value="<?= $_INVOICE_PROVINCE_SHOP ?>" />
                        </span>
                        
                        <span>
                            <label class="labelSection"><?= $this->l('Postal Code') ?></label>
                            <input type="text" name="_INVOICE_POST_CODE_SHOP" id="_INVOICE_POST_CODE_SHOP" value="<?= $_INVOICE_POST_CODE_SHOP ?>" />
                        </span>
                        
                        <span>
                            <label class="labelSection"><?= $this->l('Country') ?></label>
                            <input type="text" name="_INVOICE_COUNTRY_SHOP" id="_INVOICE_COUNTRY_SHOP" value="<?= $_INVOICE_COUNTRY_SHOP ?>" />
                            <select style="display:none" id="_INVOICE_COUNTRY_SHOP_SELECT" name="_INVOICE_COUNTRY_SHOP_SELECT">
                                <?php 
                                    if ($countries = Db::getInstance()->ExecuteS("SELECT DISTINCT id_country AS 'id', name FROM `"._DB_PREFIX_.'country_lang` WHERE id_lang = '.$id_lang)){
                                        foreach ($countries as $country){
                                            ?><option <?= $_INVOICE_ID_COUNTRY_SHOP==$country['id']?'selected="selected"':""  ?>value="<?= $country['id'] ?>"><?= $country['name'] ?></option><?php
                                        }
                                    }
                                ?>
                            </select>
                        </span>
                        
                        <span>
                            <label class="labelSection"><?= $this->l('Telephone') ?></label>
                            <input type="text" name="_INVOICE_LANDLINE_SHOP" id="_INVOICE_LANDLINE_SHOP" value="<?= $_INVOICE_LANDLINE_SHOP ?>" />
                        </span>
                        
                        <span>
                            <label class="labelSection"><?= $this->l('Fax') ?></label>
                            <input type="text" name="_INVOICE_FAX_SHOP" id="_INVOICE_FAX_SHOP" value="<?= $_INVOICE_FAX_SHOP ?>" />
                        </span>
                        <span>
                            <label class="labelSection"><?= $this->l('Other Phone') ?></label>
                            <input type="text" name="_INVOICE_OTHER_PHONE_SHOP" id="_INVOICE_OTHER_PHONE_SHOP" value="<?= $_INVOICE_OTHER_PHONE_SHOP ?>" />
                        </span>
                    </div>
            </fieldset>
            
            <fieldset<?= $_ACTIVE_TAB=="2"?'':' style="display:none"'?>>
                <legend><?= $this->l('Services') ?></legend>
                <?= $services_html; ?>
            </fieldset>
            
            <fieldset<?= $_ACTIVE_TAB=="3"?'':' style="display:none"'?>>
                <legend><?= $this->l('Add Box') ?></legend>
                <span style="margin-left:172px; width: auto; position:relative;">
                    <select id="_SELECT_MODEL_BOX" name="_MODEL_BOX" style="padding-right:5px; float: left; height: 32px; width: auto; min-width: auto">
                        <?php 
                            $query = "SELECT id, 
                                             model AS '".$this->l("Model")."',
                                             description AS '".$this->l("Description")."',
                                             width AS '".$this->l("Width")."',
                                             weight AS '".$this->l("Weight")."',
                                             height AS '".$this->l("Height")."',
                                             depth AS '".$this->l("Depth")."'
                                FROM `"._DB_PREFIX_.'packlink_boxes` WHERE 1';
                            if ($models = Db::getInstance()->ExecuteS($query)){
                                foreach ($models as $model){
                                    echo '<option value="'.$model["id"].'">'.$model[$this->l("Model")].'</option>';
                                    $arr_boxes[$model['id']] = $model;
                                }
                            }
                            echo "<script>var arrBoxes = '".  serialize($arr_boxes)."';</script>"
                        ?>
                    </select>
                    <span class="step-up"></span>
                    <span class="step-down"></span>
                    <input type="hidden" val="" id="_BOX_ID" name="_BOX_ID" />
                </span>
                <input type="reset" value="Nuevo"  id="submitpacklink" name="submitpacklink" class="submitButton stylePacklink2" onClick="$('#_BOX_ID').val('')">
                <?= $messageBoxes ?>
                <span>
                    <label class="labelSection"><?= $this->l("Model") ?></label>
                    <input type="text" id="_MODEL_BOX" name="_MODEL_BOX">
                </span>

                <span>
                    <label class="labelSection"><?= $this->l("Description") ?></label>
                    <textarea type="text" rows="6" id="_DESCRIPTION_BOX" name="_DESCRIPTION_BOX"></textarea>
                </span>
                <span>
                    <label class="labelSection"><?= $this->l("Width") ?></label>
                    <input type="text" id="_WIDTH_BOX" name="_WIDTH_BOX">
                </span>

                <span>
                    <label class="labelSection"><?= $this->l("Height") ?></label>
                    <input type="text" id="_HEIGHT_BOX" name="_HEIGHT_BOX">
                </span>

                <span>
                    <label class="labelSection"><?= $this->l("Depth") ?></label>
                    <input type="text" id="_DEPTH_BOX" name="_DEPTH_BOX">
                </span>

                <span>
                    <label class="labelSection"><?= $this->l("Weight") ?></label>
                    <input type="text" id="_WEIGHT_BOX" name="_WEIGHT_BOX">
                </span>
            </fieldset>
            
            <fieldset<?= $_ACTIVE_TAB=="4"?'':' style="display:none"'?>>
                <legend><?= $this->l('Other Options') ?></legend>
                <span>
                    <label class="labelSection"><?= $this->l('Tracking') ?></label>
                    <table style="width:calc(100% - 200px); padding: 5px 10px;">
                        <tr>
                            <td style="width:110px"><input style="min-width:auto;" type="radio" <?= $_ENABLE_TRACKING=="1"?'checked="checked"':"" ?> name="_ENABLE_TRACKING" id="enableTrackingOn" value = "1" /><label class="labelCheckBox" style="width:auto;" for="enableTrackingOn"><span></span><?= $this->l('Enable') ?></label></td>
                            <td><input style="min-width:auto;" type="radio" <?= $_ENABLE_TRACKING=="0"?'checked="checked"':"" ?> name="_ENABLE_TRACKING" id="enableTrackingOff" value="0" /><label class="labelCheckBox" style="width:auto;" for="enableTrackingOff"><span></span><?= $this->l('Disable') ?></label></td>
                        </tr>
                    </table>

                    <label class="labelSection"><?= $this->l('Drag & Drop') ?></label>
                    <table style="width:calc(100% - 200px); padding: 5px 10px;">
                        <tr>
                            <td style="width:110px"><input style="min-width:auto;" type="radio" <?= $_ENABLE_DRAGDROP=="1"?'checked="checked"':"" ?> name="_ENABLE_DRAGDROP" id="enableDragDropOn" value = "1" /><label class="labelCheckBox" style="width:auto;" for="enableDragDropOn"><span></span><?= $this->l('Enable') ?></label></td>
                            <td><input style="min-width:auto;" type="radio" <?= $_ENABLE_DRAGDROP=="0"?'checked="checked"':"" ?> name="_ENABLE_DRAGDROP" id="enableDragDropOff" value="0" /><label class="labelCheckBox" style="width:auto;" for="enableDragDropOff"><span></span><?= $this->l('Disable') ?></label></td>
                        </tr>
                    </table>
                    
                    <label class="labelSection"><?= $this->l('Weights Control') ?></label>
                    <table style="width:calc(100% - 200px); padding: 5px 10px;">
                        <tr>
                            <td style="width:110px"><input style="min-width:auto;" type="radio" <?= $_ENABLE_CTRL_WEIGHTS=="1"?'checked="checked"':"" ?> name="_ENABLE_CTRL_WEIGHTS" id="enableCtrlWeightsOn" value = "1" /><label class="labelCheckBox" style="width:auto;" for="enableCtrlWeightsOn"><span></span><?= $this->l('Enable') ?></label></td>
                            <td><input style="min-width:auto;" type="radio" <?= $_ENABLE_CTRL_WEIGHTS=="0"?'checked="checked"':"" ?> name="_ENABLE_CTRL_WEIGHTS" id="enableCtrlWeightsOff" value="0" /><label class="labelCheckBox" style="width:auto;" for="enableCtrlWeightsOff"><span></span><?= $this->l('Disable') ?></label></td>
                        </tr>
                    </table>
                    
                    <label class="labelSection"><?= $this->l('Measurements Control') ?></label>
                    <table style="width:calc(100% - 200px); padding: 5px 10px;">
                        <tr>
                            <td style="width:110px"><input style="min-width:auto;" type="radio" <?= $_ENABLE_CTRL_MEASUREMENTS=="1"?'checked="checked"':"" ?> name="_ENABLE_CTRL_MEASUREMENTS" id="enableCtrlMeasurementsOn" value = "1" /><label class="labelCheckBox" style="width:auto;" for="enableCtrlMeasurementsOn"><span></span><?= $this->l('Enable') ?></label></td>
                            <td><input style="min-width:auto;" type="radio" <?= $_ENABLE_CTRL_MEASUREMENTS=="0"?'checked="checked"':"" ?> name="_ENABLE_CTRL_MEASUREMENTS" id="enableCtrlMeasurementsOff" value="0" /><label class="labelCheckBox" style="width:auto;" for="enableCtrlMeasurementsOff"><span></span><?= $this->l('Disable') ?></label></td>
                        </tr>
                    </table>
                    
                    <label class="labelSection"><?= $this->l('Enable Animations') ?></label>
                    <table style="width:calc(100% - 200px); padding: 5px 10px;">
                        <tr>
                            <td style="width:110px"><input style="min-width:auto;" type="radio" <?= $_ENABLE_ANIMATION=="1"?'checked="checked"':"" ?> name="_ENABLE_ANIMATION" id="enableAnimationOn" value = "1" /><label class="labelCheckBox" style="width:auto;" for="enableAnimationOn"><span></span><?= $this->l('Enable') ?></label></td>
                            <td><input style="min-width:auto;" type="radio" <?= $_ENABLE_ANIMATION=="0"?'checked="checked"':"" ?> name="_ENABLE_ANIMATION" id="enableAnimationOff" value="0" /><label class="labelCheckBox" style="width:auto;" for="enableAnimationOff"><span></span><?= $this->l('Disable') ?></label></td>
                        </tr>
                    </table>
                    
                    <label style="top:0" class="labelSection"><?= $this->l('Allow the user to choose the carrier service') ?></label>
                    <table style="width:calc(100% - 200px); padding: 5px 10px;">
                        <tr>
                            <td style="width:110px"><input style="min-width:auto;" type="radio" <?= $_ENABLE_USER_CHOOSE=="1"?'checked="checked"':"" ?> name="_ENABLE_USER_CHOOSE" id="enableUserChooseOn" value = "1" /><label class="labelCheckBox" style="width:auto;" for="enableUserChooseOn"><span></span><?= $this->l('Enable') ?></label></td>
                            <td><input style="min-width:auto;" type="radio" <?= $_ENABLE_USER_CHOOSE=="0"?'checked="checked"':"" ?> name="_ENABLE_USER_CHOOSE" id="enableUserChooseOff" value="0" /><label class="labelCheckBox" style="width:auto;" for="enableUserChooseOff"><span></span><?= $this->l('Disable') ?></label></td>
                        </tr>
                    </table>
                    
                    <span>
                        <label class="labelSection"><?= $this->l('Percentage Adjust') ?></label>
                        <input type="text" name="_PERCENTAGE_ADJUST" id="_PERCENTAGE_ADJUST" value="<?= $_PERCENTAGE_ADJUST ?>" />
                    </span>
                    
                    <span>
                        <label style="top:0" class="labelSection"><?= $this->l('Free Shipment From')."(".$this->l('Fixed Price').")"  ?></label>
                        <input type="text" name="_FREE_SHIPMENT_FROM" id="_FREE_SHIPMENT_FROM" value="<?= $_FREE_SHIPMENT_FROM ?>" />
                    </span>
                </span>
            </fieldset>
        </div>
        
        <input class="submitButton stylePacklink2" type="submit" name="submitpacklink" id="submitpacklink" value="Actualizar" />
        
        <script class="jsbin" src="<?= _MODULE_DIR_ ?>packlink/js/jquery.dataTables.nightly.js"></script>
        <script>
            $('#sectionsPackLink span').click(function (){
                var index = $(this).index();
                $('#sectionsPackLink span').each(function (i){
                    $(this).removeClass('selected');
                    
                    if(index == i){
                        $("#contentSectionsPackLink fieldset:nth-child("+(i+1)+")", $(this).parent().parent()).css("display", "");
                        $('#_ACTIVE_TAB').val(i);
                    } else {
                        $("#contentSectionsPackLink fieldset:nth-child("+(i+1)+")", $(this).parent().parent()).css("display", "none");
                    }
                });
                $(this).addClass('selected');
            });
            
            $('#_COUNTRY_SHOP').focus(function (){
                $('#_COUNTRY_SHOP_SELECT').show().live("click");
                $('#_COUNTRY_SHOP').hide()
            });
            
            $('#_COUNTRY_SHOP').mouseenter(function (){
                $('#_COUNTRY_SHOP_SELECT').show().live("click");
                $('#_COUNTRY_SHOP').hide()
            });
            
            $('#_COUNTRY_SHOP_SELECT').focusout(function (){
                $('#_COUNTRY_SHOP_SELECT').hide()
                $('#_COUNTRY_SHOP').val($('#_COUNTRY_SHOP_SELECT option:selected').text());
                $('#_COUNTRY_SHOP').show();
            });
            
            $('#_COUNTRY_SHOP_SELECT').mouseleave(function (){
                if(!$("select").is(":focus")){
                    $('#_COUNTRY_SHOP_SELECT').hide()
                    $('#_COUNTRY_SHOP').val($('#_COUNTRY_SHOP_SELECT option:selected').text());
                    $('#_COUNTRY_SHOP').show();
                }
            });
            
            $('#_INVOICE_COUNTRY_SHOP').focus(function (){
                $('#_INVOICE_COUNTRY_SHOP_SELECT').show().live("click");
                $('#_INVOICE_COUNTRY_SHOP').hide()
            });
            
            $('#_INVOICE_COUNTRY_SHOP').mouseenter(function (){
                $('#_INVOICE_COUNTRY_SHOP_SELECT').show().live("click");
                $('#_INVOICE_COUNTRY_SHOP').hide()
            });
            
            $('#_INVOICE_COUNTRY_SHOP_SELECT').focusout(function (){
                $('#_INVOICE_COUNTRY_SHOP_SELECT').hide()
                $('#_INVOICE_COUNTRY_SHOP').val($('#_INVOICE_COUNTRY_SHOP_SELECT option:selected').text());
                $('#_INVOICE_COUNTRY_SHOP').show();
            });
            
            $('#_INVOICE_COUNTRY_SHOP_SELECT').mouseleave(function (){
                if(!$("select").is(":focus")){
                    $('#_INVOICE_COUNTRY_SHOP_SELECT').hide()
                    $('#_INVOICE_COUNTRY_SHOP').val($('#_INVOICE_COUNTRY_SHOP_SELECT option:selected').text());
                    $('#_INVOICE_COUNTRY_SHOP').show();
                }
            });
                        
            $(document).ready(function(){
                $('#services').dataTable({
                    "oLanguage": {
                                    "sProcessing":"<?=  $this->l('sProcessing'); ?>",
                                    "sLengthMenu":"<?=  $this->l('sLengthMenu');?>",
                                    "sZeroRecords":"<?=  $this->l('sZeroRecords');?>",
                                    "sInfo":"<?=  $this->l('sInfo');?>",
                                    "sInfoEmpty":"<?=  $this->l('sInfoEmpty');?>",
                                    "sInfoFiltered":"<?=  $this->l('sInfoFiltered');?>",
                                    "sInfoPostFix":"<?=  $this->l('sInfoPostFix');?>",
                                    "sSearch":"<?=  $this->l('sSearch');?>",
                                    "sUrl":"<?=  $this->l('sUrl');?>",
                                    "oPaginate": {
                                        "sFirst":"<?=  $this->l('sFirst');?>",
                                        "sPrevious":"<?=  $this->l('sPrevious');?>",
                                        "sNext":"<?=  $this->l('sNext');?>",
                                        "sLast":"<?=  $this->l('sLast');?>"
                                    },
                                    "oAria": {
                                        "sSortAscending": "<?=  $this->l('sSortAscending');?>",
                                        "sSortDescending":"<?=  $this->l('sSortDescending');?>"
                                    }
                                }
                });
                $('#services input:checkbox').click(function (){
                    /*$('#services tr').each(function(i){
                        $(this).removeClass("disable"); 
                    });*/
                   if($(this).is(':checked'))
                       $(this).parent().parent().removeClass("disable");
                   else 
                       $(this).parent().parent().addClass("disable"); 
                });
                
                $(window).load(function() {
                    adjustInputs();
                    $(window).resize(function() {
                        adjustInputs();
                    });
                    $('#sectionsPackLink span').click(function(){
                        adjustInputs(); 
                    });
                });
            });
            
            function adjustInputs(){
                $('#contentSectionsPackLink input').each(function(){
                    //$('input').prev().width();
                    if($(this).prop("class").substr(0, 6) == "adjust"){
                        var val = parseFloat($(this).prop("class").replace("adjust", ''))/100;
                        $(this).width((($('#contentSectionsPackLink').width()-230)*parseFloat(val))+"px");
                        $(this).css("min-width","auto");
                    } else if(!$(this).hasClass("noAdjust")){
                        $(this).width(($('#contentSectionsPackLink').width()-230)+"px");
                        $(this).css("min-width","auto");
                    }
                });
                
                $('#contentSectionsPackLink textarea').each(function(){
                    //$('input').prev().width();
                    if($(this).prop("class").substr(0, 6) == "adjust"){
                        var val = parseFloat($(this).prop("class").replace("adjust", ''))/100;
                        $(this).width((($('#contentSectionsPackLink').width()-230)*parseFloat(val))+"px");
                        $(this).css("min-width","auto");
                    } else if(!$(this).hasClass("noAdjust")){
                        $(this).width(($('#contentSectionsPackLink').width()-230)+"px");
                        $(this).css("min-width","auto");
                    }
                });
                
                $('#_COUNTRY_SHOP_SELECT').width(($('#contentSectionsPackLink').width()-218)+"px");
                $('#_COUNTRY_SHOP_SELECT').css("min-width","auto");
                $('#_INVOICE_COUNTRY_SHOP_SELECT').width(($('#contentSectionsPackLink').width()-218)+"px");
                $('#_INVOICE_COUNTRY_SHOP_SELECT').css("min-width","auto");
                
            }
            function onSubmitPL(){
                $('#services input:checkbox').each(function(){
                    if(!$(this).is(":checked")){
                        $(this).val($(this).val()*(-1));
                        $(this).attr("checked", true);
                    }
                    $(this).hide();
                });
            }
            
            $('span[class^="step-"]').click(function() {
                var item      = $('#_SELECT_MODEL_BOX'),
                selected  = item[0].selectedIndex;

                var op = $(this).prop("class").split("-");
                var index = (op[1] == 'up' ? -1 : 1) + selected;
                if (item.find('option')[index]) {
                    item[0].selectedIndex = index;
                }
                var aux = unserialize(arrBoxes);
                $('#_SELECT_MODEL_BOX').change();
            });
            
            $('#_SELECT_MODEL_BOX').change(function(){
                var aux = unserialize(arrBoxes);
                $('#_BOX_ID').val(aux[$('#_SELECT_MODEL_BOX').val()]['id']);
                $('#_MODEL_BOX').val(aux[$('#_SELECT_MODEL_BOX').val()]['<?= $this->l("Model") ?>']);
                $('#_DESCRIPTION_BOX').val(aux[$('#_SELECT_MODEL_BOX').val()]['<?= $this->l("Description") ?>']);
                $('#_WIDTH_BOX').val(aux[$('#_SELECT_MODEL_BOX').val()]['<?= $this->l("Width") ?>']);
                $('#_HEIGHT_BOX').val(aux[$('#_SELECT_MODEL_BOX').val()]['<?= $this->l("Height") ?>']);
                $('#_DEPTH_BOX').val(aux[$('#_SELECT_MODEL_BOX').val()]['<?= $this->l("Depth") ?>']);
                $('#_WEIGHT_BOX').val(aux[$('#_SELECT_MODEL_BOX').val()]['<?= $this->l("Weight") ?>']);
             });
       </script>
</form>
