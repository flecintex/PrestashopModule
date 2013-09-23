<?php 
    ini_set("display_errors", "on");
    error_reporting(E_ERROR^E_PARSE^E_STRICT);
    global $smarty;
    include('../../config/config.inc.php');
    include 'packlink.php';

    // Init the Packlink module.
    $pack = new packlink();
    
    $ErrorMargin = "+1";
    
    $sql = "SELECT o.id_cart, pl.id_product, od.product_quantity AS 'quantity', od.product_name AS 'name', p.price, p.weight, p.width".$ErrorMargin." as width, p.height".$ErrorMargin." as height, p.depth".$ErrorMargin." as depth, pl.description
              FROM "._DB_PREFIX_."product_lang pl, "._DB_PREFIX_."order_detail od, "._DB_PREFIX_."orders o, "._DB_PREFIX_."product p
             WHERE od.product_id = pl.id_product AND pl.id_lang = ".$cookie->id_lang." AND o.id_order = od.id_order AND p.id_product = pl.id_product
               AND o.id_order = ".$_REQUEST['ido'].";";
?>

<div class="floatLeft" style="height:0;">
    <div class="scrollWrapper" id="drag<?= $_REQUEST['ido'] ?>">
        <table class="table1" id="contentOrder<?= $_REQUEST['ido'] ?>" cellspacing="0">
          <tr>
            <th>
                <img id="selectAllPlus<?= $_REQUEST['ido'] ?>" style="cursor:pointer" title = "<?= $pack->l("Select All") ?>" src="<?= _MODULE_DIR_ ?>packlink/images/plus_orange.png" />
                <img id="selectAllLess<?= $_REQUEST['ido'] ?>" style="cursor:pointer; display:none" title = "<?= $pack->l("Unselect All") ?>" src="<?= _MODULE_DIR_ ?>packlink/images/less.png" />
            </th>
            <th></th>
            <th>Producto</th>
          </tr>
          <?php 
            if ($results = Db::getInstance()->ExecuteS($sql)){ 
                foreach ($results as $result){ 
                    for($x = 0; $x < $result['quantity']; $x++){
                        echo '<tr class="packageDown" title = "'.$pack->l("Measurements").": ".number_format($result['width'],2).'x'.number_format($result['height'],2).'x'.number_format($result['depth'],2).' '.number_format($result['weight'], 2).' Kg">';
                        echo '<td></td>';
                        echo '<td>01</td>';
                        echo '<td title="'.strip_tags($result['description'], '').'">'.$result['name'].'<span class="noDisplayPL">'.$result['id_product'].'|'.number_format($result['width'],2).'x'.number_format($result['height'],2).'x'.number_format($result['depth'],2).'|'.number_format($result['weight'], 2).'</span></td>';
                        echo '</tr>';
                    }
                }
            }
          ?>
          
        </table>
    </div>
</div>
<div class="floatLeft package" style="height:0; position: relative;">
    <!--<select multiple>
      <option value="volvo">Ironman</option>
      <option value="saab">Auriculares 360º</option>
      <option value="opel">Gafas 3D</option>
    </select> -->
    <?php if(Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_ENABLE_CTRL_MEASUREMENTS'") == '1'){ ?>
        <dd rel="prettyPhoto" class="view" id="view<?= $_REQUEST['ido'] ?>"></dd>
    <?php } ?>
    
    <span class="measurements-box" id="measurements-box<?= $_REQUEST['ido'] ?>">
        
    </span>
    <div>
        <table id="containerBox<?= $_REQUEST['ido'] ?>">
            
        </table>
        <div id="waitingCBOX<?= $_REQUEST['ido'] ?>" class="waitingCBOX"></div>
    </div>
    <label><?= $pack->l("Current Weight") ?></label>
    <input id="boxCurWeight<?= $_REQUEST['ido'] ?>" type="text" value="0.00 Kg" readonly disabled />
    <label><?= $pack->l("Maximum Weight") ?></label>
    <input id="boxMaxWeight<?= $_REQUEST['ido'] ?>" type="text" value="0.00 Kg" readonly disabled />
    <label><?= $pack->l("Volumetric Weight") ?></label>
    <input id="boxCurVol<?= $_REQUEST['ido'] ?>" type="hidden" value="" />
    <input id="boxMaxVol<?= $_REQUEST['ido'] ?>" type="hidden" value="" />    
    <input id="boxVolWeight<?= $_REQUEST['ido'] ?>" type="text" value="0.00 Kg" readonly disabled />
</div>
<div class="floatLeft options" style="height:0;">
    <a class="submitButton black buttonPackageAdd" title="<?= $pack->l("Add / Choose Box") ?>" rel="prettyPhoto[iframes]" href="<?= $_REQUEST['ubase'].'/boxes_add.php?ido='.$_REQUEST['ido'].'&iframe=true&width=925&height=350'; ?>" >&nbsp;</a>
    <a class="submitButton black buttonPackageDel" id="buttonPackageDel<?= $_REQUEST['ido'] ?>" title="Eliminar Caja" onclick="deleteBox('<?= $_REQUEST['ido'] ?>')"></a>

    <select name="selectBox<?= $_REQUEST['ido'] ?>" id="selectBox<?= $_REQUEST['ido'] ?>">
    </select>
    <input type="button" id="requestShipment<?= $_REQUEST['ido'] ?>" name="requestShipment<?= $_REQUEST['ido'] ?>" value="<?= $pack->l("Request New Budget") ?>" class="submitButton black request" style="102px !important" />


    <span>
        <table cellspacing="5" >
            <tr>
                <td><?= $pack->l("Gathered Date") ?></td>
                <td width="115">
                    <input type="text" readonly="readonly" name="gathered_date<?= $_REQUEST['ido'] ?>" id="gathered_date<?= $_REQUEST['ido'] ?>" class="gathered_date">
                </td>
            </tr>
            <tr>
                <td style="display:none"><input type="radio" name="scheduleOpt" id="scheduleOpt1" /><label class="labelCheckBox" for="scheduleOpt1"></label></td>
                <td style="display:none"><input type="radio" name="scheduleOpt" id="scheduleOpt2" /><label class="labelCheckBox" for="scheduleOpt2"></label></td>
            </tr>
            <tr>
                <td><?= $pack->l("New Budget") ?></td>
                <td id="deliveryCostNew<?= $_REQUEST['ido'] ?>" width="60"> €</td>
            </tr>
            <tr>
                <td><?= $pack->l("Paid by Customer") ?></td>
                <td><?= Db::getInstance()->getValue("SELECT ROUND(price*(1+(tax/100)), 2) FROM "._DB_PREFIX_."packlink_orders WHERE id_order = ".$_REQUEST['ido']) ?> €</td>
            </tr>
        </table>
        <input type="button" id="acceptShipment<?= $_REQUEST['ido'] ?>" name="acceptShipment<?= $_REQUEST['ido'] ?>" value="<?= $pack->l("Accept User Submission"); ?>" class="submitButton black accept" style="102px !important" />
    </span>
    
    <!--
    <fieldset style="height:116px; padding: 0 5px">
        <legend>?= $pack->l("Gathered Date") ?></legend>
        
        <table cellspacing="1" >
            <tr>
                <td style="width:48%">
                    <input type="text" readonly="readonly" name="gathered_date?= $_REQUEST['ido'] ?>" id="gathered_date?= $_REQUEST['ido'] ?>" class="gathered_date">
                </td>
                <td><input type="button" id="requestShipment?= $_REQUEST['ido'] ?>" name="requestShipment?= $_REQUEST['ido'] ?>" value="?= $pack->l("New Budget") ?>" class="submitButton black request" style="102px !important" /></td>
            </tr>
            <tr>
                <td style="display:none"><input type="radio" name="scheduleOpt" id="scheduleOpt1" /><label class="labelCheckBox" for="scheduleOpt1"></label></td>
                <td style="display:none"><input type="radio" name="scheduleOpt" id="scheduleOpt2" /><label class="labelCheckBox" for="scheduleOpt2"></label></td>
            </tr>
        </table>
    </fieldset>
    
    <fieldset>
        <legend>?= $pack->l("Presupuestos y Aceptar Envíos") ?></legend>
        <table cellspacing="5" width="100%" >
            <tr>
                <td style="70%">?= $pack->l("New Budget") ?></td>
                <td style="30%" id="deliveryCostNew?= $_REQUEST['ido'] ?>" width="60"> €</td>
            </tr>
            <tr>
                <td>?= $pack->l("Paid by Customer") ?></td>
                <td>?= Db::getInstance()->getValue("SELECT price FROM "._DB_PREFIX_."packlink_orders WHERE id_order = ".$_REQUEST['ido']) ?> €</td>
            </tr>
        </table>
        <input type="button" id="acceptShipment?= $_REQUEST['ido'] ?>" name="acceptShipment?= $_REQUEST['ido'] ?>" value="?= $pack->l("Accept User Submission"); ?>" class="submitButton black accept" style="102px !important" />
    </fieldset>
    -->
</div>

<div class="floatLeft layouts" style=" overflow: auto; height:0; display: none; padding: 0 5px">
    <p>Disposición de Paquetes</p>
    <div class="floatLeft packageLayout">
        
    </div>
</div>
