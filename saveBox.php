<?php
    global $smarty;
    global $cookie;
    include('../../config/config.inc.php');
    include 'packlink.php';
    
    // Init the Packlink module.
    $pack = new packlink();

    $sql = "INSERT INTO `ps_packlink_boxes` (`model`, `description`, `width`, `height`, `depth`, `weight`) VALUES ";
    $sql .= "('".$_REQUEST['_MODEL_BOX']."', '".$_REQUEST['_DESCRIPTION_BOX']."', ".$_REQUEST['_WIDTH_BOX'].", ".$_REQUEST['_HEIGHT_BOX'].", ".$_REQUEST['_DEPTH_BOX'].", ".$_REQUEST['_WEIGHT_BOX'].");";
    if (!Db::getInstance()->execute($sql)){
        echo "0|".$pack->l("An error occurred. Failed to save the definition of the new box");
    } else {
        echo Db::getInstance()->Insert_ID()."|".$pack->l("Operation performed successfully"); 
    }
?>
