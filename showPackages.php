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
<div id="layouts" class="floatLeft" style=" overflow: auto; height:0; display: none; padding: 0 5px">
    <p>Disposición de Paquetes</p>
    <div class="floatLeft packageLayout">
        
    </div>
</div>