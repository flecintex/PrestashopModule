<?php
    ini_set("display_errors", "on");
    error_reporting(E_ERROR^E_PARSE^E_STRICT);
    global $smarty;
    include('../../config/config.inc.php');
    
    ?>  
        <link type="text/css" rel="stylesheet" href="<?= _MODULE_DIR_?>packlink/css/animations.css" />
        <link type="text/css" rel="stylesheet" href="<?= _MODULE_DIR_?>packlink/css/base.css" />
        <link type="text/css" rel="stylesheet" href="<?= _MODULE_DIR_?>packlink/css/config.css" />
        <link type="text/css" rel="stylesheet" href="<?= _MODULE_DIR_?>packlink/css/front.css" />
        <link type="text/css" rel="stylesheet" href="<?= _MODULE_DIR_?>packlink/css/messages.css" />
        <link type="text/css" rel="stylesheet" href="<?= _MODULE_DIR_?>packlink/css/orders.css" />
        <link type="text/css" rel="stylesheet" href="<?= _MODULE_DIR_?>packlink/css/tables.css" />
        <script type="text/javascript" src="<?= _PS_JS_DIR_.'jquery/jquery-'._PS_JQUERY_VERSION_?>.min.js"></script>
        <script type="text/javascript" src="<?= _MODULE_DIR_?>packlink/js/packlink.js"></script>
    <?php
    
    include 'packlink.php';
    
    // Init the Packlink module.
    $pack = new packlink();
    
    if($_REQUEST['submitPacklink']){
        $sql = "INSERT INTO `ps_packlink_boxes` (`id`, `model`, `description`, `width`, `height`, `depth`, `weight`) VALUES ";
        $sql .= "(NULL, '".$_REQUEST['_MODEL_BOX']."', '".$_REQUEST['_DESCRIPTION_BOX']."', '".$_REQUEST['_WIDTH_BOX']."', '".$_REQUEST['_HEIGHT_BOX']."', '".$_REQUEST['_DEPTH_BOX']."', '".$_REQUEST['_WEIGHT_BOX']."');";
        if (!Db::getInstance()->execute($sql)) $msg = '<span class="msgError">'.($pack->l("An error occurred. Failed to save the definition of the new box")).'</span>';
        else { $msg = '<span class="msgOK">'.$pack->l("Operation performed successfully")."</span>"; }
    }

    // Get the necessary parameters for execute module.
    $url_packlink        = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = 'url_packlink'");
    $username            = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = 'username'");
    $password            = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = 'password'");
    $apikey              = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = 'apikey'");

    // echo $url_packlink."<br>"; echo $username."<br>"; echo $password."<br>"; echo $apikey."<br>";

    ?>
    <table>
    <tr>
    <td>
    <style>h2{font-size:16px; font-family: Arial,Verdana,Helvetica,sans-serif; margin: 0 0 2px; padding: 0 0 0 5px;}</style>
    <h2><?= $pack->l("Choose Box") ?><span class="msgOK" id="msgSelect"></span></h2>        
    <table cellpadding="0" style="width:450px; min-width: 320px; max-width: 450px; height: 296px;" cellspacing="0" border="0" class="table3" id="table<?= $_REQUEST['id'] ?>" >
        <thead>
            <tr>
                <th width="100"><?= $pack->l("Model") ?></th>
                <td colspan="3" style="text-align:left">
                    <span style="width: auto; position:relative;">
                        <select id="_SELECT_MODEL_BOX" name="_MODEL_BOX" style="padding-right:5px">
                            <?php 
                                $query = "SELECT id, 
                                                 model AS '".$pack->l("Model")."',
                                                 description AS '".$pack->l("Description")."',
                                                 width AS '".$pack->l("Width")."',
                                                 weight AS '".$pack->l("Weight")."',
                                                 height AS '".$pack->l("Height")."',
                                                 depth AS '".$pack->l("Depth")."'
                                    FROM `"._DB_PREFIX_.'packlink_boxes` WHERE 1';
                                if ($models = Db::getInstance()->ExecuteS($query)){
                                    foreach ($models as $model){
                                        echo '<option value="'.$model["id"].'">'.$model[$pack->l("Model")].'</option>';
                                        $arr_boxes[$model['id']] = $model;
                                    }
                                }
                                echo "<script>var arrBoxes = '".  serialize($arr_boxes)."';</script>"
                            ?>
                        </select>
                        <span class="step-up" style="left: 323px; top: -7px;"></span>
                        <span class="step-down" style="left: 323px; top: 10px;"></span>
                    </span>
                </td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th><?= $pack->l("Description") ?></th>
                <td colspan="3">
                    <div style="height:141px; width: 100%; display: block; overflow-y: auto" id="_SELECT_DESCRIPTION_BOX"><?= $arr_boxes[1][$pack->l("Description")] ?></div>
                </td>
            </tr>
            <tr>
                <th><?= $pack->l("Width") ?></th>
                <td><div id="_SELECT_WIDTH_BOX"><?= $arr_boxes[1][$pack->l("Width")] ?></div></td>
                <th><?= $pack->l("Height") ?></th>
                <td><div id="_SELECT_HEIGHT_BOX"><?= $arr_boxes[1][$pack->l("Height")] ?></div></td>
            </tr>
            <tr>
                <th><?= $pack->l("Depth") ?></th>
                <td><div id="_SELECT_DEPTH_BOX"><?= $arr_boxes[1][$pack->l("Depth")] ?></div></td>
                <th><?= $pack->l("Maximum Weight") ?></th>
                <td><div id="_SELECT_WEIGHT_BOX"><?= $arr_boxes[1][$pack->l("Weight")] ?></div></td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4"><input type="button" value="<?= $pack->l("Choose") ?>" id="selectPacklink" name="selectPacklink" class="submitButton black"></td>
            </tr>
        </tfoot>
    </table>
   
    
   
    </td>
    <td>
    <h2><?= $pack->l("Add Box")." ".$msg ?></h2>
    <form id="frm2" name="frm2" action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
        <table cellpadding="0" style="width:450px; min-width: 320px; max-width: 450px" cellspacing="0" border="0" class="table3" id="table<?= $_REQUEST['id'] ?>" >
            <thead>
                <tr>
                    <th width="100"><?= $pack->l("Model") ?></th>
                    <td colspan="3" style="text-align:left">
                        <input type="text" id="_MODEL_BOX" name="_MODEL_BOX">
                    </td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th><?= $pack->l("Description") ?></th>
                    <td colspan="3">
                        <textarea type="text" rows="6" id="_DESCRIPTION_BOX" name="_DESCRIPTION_BOX">

                        </textarea>
                    </td>
                </tr>
                <tr>
                    <th><?= $pack->l("Width") ?></th>
                    <td><input type="text" id="_WIDTH_BOX" name="_WIDTH_BOX"></td>
                    <th><?= $pack->l("Height") ?></th>
                    <td><input type="text" id="_HEIGHT_BOX" name="_HEIGHT_BOX"></td>
                </tr>
                <tr>
                    <th><?= $pack->l("Depth") ?></th>
                    <td><input type="text" id="_DEPTH_BOX" name="_DEPTH_BOX"></td>
                    <th><?= $pack->l("Maximum Weight") ?></th>
                    <td><input type="text" id="_WEIGHT_BOX" name="_WEIGHT_BOX"></td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4"><input type="submit" value="<?= $pack->l("Save") ?>" id="submitPacklink" name="submitPacklink" class="submitButton black"></td>
                </tr>
            </tfoot>
        </table>
    </form>
    </td>
    </tr>
    </table>
<?php 
    include('../../footer.php');
?>
<script>
    function pad (n, length){
        var str = (n > 0 ? n : -n) + "";
        var zeros = "";
        for (var i = length - str.length; i > 0; i--)
            zeros += "0";
        zeros += str;
        return n >= 0 ? zeros : "-" + zeros;
    }
    
    $('#_SELECT_MODEL_BOX').change(function(){
       var aux = unserialize(arrBoxes);
       $('#_SELECT_DESCRIPTION_BOX').html(aux[$('#_SELECT_MODEL_BOX').val()]['<?= $pack->l("Description") ?>']);
       $('#_SELECT_WIDTH_BOX').html(aux[$('#_SELECT_MODEL_BOX').val()]['<?= $pack->l("Width") ?>']);
       $('#_SELECT_HEIGHT_BOX').html(aux[$('#_SELECT_MODEL_BOX').val()]['<?= $pack->l("Height") ?>']);
       $('#_SELECT_DEPTH_BOX').html(aux[$('#_SELECT_MODEL_BOX').val()]['<?= $pack->l("Depth") ?>']);
       $('#_SELECT_WEIGHT_BOX').html(aux[$('#_SELECT_MODEL_BOX').val()]['<?= $pack->l("Weight") ?>']);
    });
    $('#selectPacklink').click(function(){
        $('#msgSelect').html('...');
        var dest = window.parent.document;
        var n_boxes = $('select#selectBox<?= $_REQUEST['ido'] ?> > option', dest).length+1;
        var aux = unserialize(arrBoxes);
        
        $("#contentOrder<?= $_REQUEST['ido'] ?> tr:eq(0)", dest).append('<th id="box<?= $_REQUEST['ido'] ?>'+n_boxes+'" title="<?= $pack->l("Choose Box") ?> '+pad(n_boxes, 2)+'">'+pad(n_boxes, 2)+'</th>');
        $("#contentOrder<?= $_REQUEST['ido'] ?> tr:gt(0)", dest).append('<td></td>');
        $('select#selectBox<?= $_REQUEST['ido'] ?>', dest).append('<option value="'+n_boxes+'"><?= $pack->l("Box") ?> '+pad(n_boxes, 2)+'</option>');
        $('#measurements-box<?= $_REQUEST['ido'] ?>', dest).append('<span style="display:none;" id="mbOrder<?= $_REQUEST['ido'] ?>'+n_boxes+'">'+aux[$('#_SELECT_MODEL_BOX').val()]['<?= $pack->l("Width") ?>']+'x'+aux[$('#_SELECT_MODEL_BOX').val()]['<?= $pack->l("Height") ?>']+'x'+aux[$('#_SELECT_MODEL_BOX').val()]['<?= $pack->l("Depth") ?>']+'cm <span class="noDisplayPL">'+parseFloat(aux[$('#_SELECT_MODEL_BOX').val()]['<?= $pack->l("Weight") ?>']).toFixed(2)+' Kg</span>');
        $('#boxMaxWeight<?= $_REQUEST['ido'] ?>', dest).val(parseFloat(aux[$('#_SELECT_MODEL_BOX').val()]['<?= $pack->l("Weight") ?>']).toFixed(2)+' Kg');
        $('#boxMaxVol<?= $_REQUEST['ido'] ?>', dest).val(parseFloat(aux[$('#_SELECT_MODEL_BOX').val()]['<?= $pack->l("Width") ?>'] * aux[$('#_SELECT_MODEL_BOX').val()]['<?= $pack->l("Height") ?>'] * aux[$('#_SELECT_MODEL_BOX').val()]['<?= $pack->l("Depth") ?>']).toFixed(2));
        $('#boxCurVol<?= $_REQUEST['ido'] ?>', dest).val('0.00');
        $('select#selectBox<?= $_REQUEST['ido'] ?>', dest).val($('select#selectBox<?= $_REQUEST['ido'] ?> option:last', dest).val());
        window.parent.setEventForOrder('<?= $_REQUEST['ido'] ?>');
        window.parent.controlSelect('<?= $_REQUEST['ido'] ?>');
        $('#msgSelect').html('<?= $pack->l("Operation performed successfully") ?>');
    });
    
    $('#submitPacklink').click(function(){
        $('#frm2').submit();
    });
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
    
    $(document).ready(function(){
        $('.pp_gallery').css("display", 'none');
    });
    $(window).load(function(){
        $('.pp_gallery').css("display", 'none');
    });

    
</script>