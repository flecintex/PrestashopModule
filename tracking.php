<?php
    global $smarty;
    global $cookie;
    include('../../config/config.inc.php');
    include('../../header.php');
    include_once 'packlink.php';
    
    // Init the Packlink module.
    $pack = new packlink();

    // -------------------------
    // Get data to Conection
    // -------------------------
    
    $id_lang = $cookie->id_lang;
    $url_packlink        = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = 'url_packlink'");
    $username            = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = 'username'");
    $password            = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = 'password'");
    $apikey              = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = 'apikey'");
    
    // -------------------------
    // WS Connection Client
    // -------------------------
    
    $options  = array('trace' => true, 'exceptions' => true, 'cache_wsdl' => WSDL_CACHE_NONE, 'features' => SOAP_SINGLE_ELEMENT_ARRAYS +  SOAP_USE_XSI_ARRAY_TYPE, 'login' => $apikey, 'password' =>$password, 'soap_version'   => SOAP_1_2, "use"      => SOAP_ENCODED, "style"    => SOAP_DOCUMENT);
    $client   = new SoapClient($url_packlink."/wsdl", $options );
    $iso_lang =  Db::getInstance()->getValue("SELECT iso_code FROM "._DB_PREFIX_."lang WHERE id_lang=$id_lang;");
    $response = $client->setLanguage($iso_lang);
    $response = $client->getShippingTracking($_REQUEST['num']);
    
    $datos  = '<h1 id="shipping_tracking_h1">'.$pack->l('Shipment tracking')." ".$_REQUEST['num'].'</h1>';
    $datos .= '<img id="shipping_tracking_img" alt="Packlink.es" src="http://www.packlink.es/images/'.strtolower($iso_lang)."_".  strtoupper($iso_lang).'/logo.png">';
    $datos .= '<table cellpadding="0" cellspacing="0" border="0" class="display" id="shipping_tracking" >'."\n";
    $dom      = simplexml_load_string(str_replace("]]>", "", str_replace("<![CDATA[", "", $response))); 
    if(count($dom) != 0){
        // -------------------------
        // Processing response
        // -------------------------

        foreach ($dom as $key => $value){
            if($key != "history") $datos .= "<tr><th>".$pack->l(ucwords($key))."</th><td>".$value."</td></tr>"; 
        }
        foreach ($dom->history->status as $key => $value){
            $datos .= '<tr><th>'.$pack->l(ucwords($key)).'</th><td><table cellpadding="0" cellspacing="0" border="0" >'; 
            foreach($value as $s_key => $s_val){
                $datos .= "<tr><th>".$pack->l(ucwords($s_key))."</th><td>".$s_val."</td></tr>";
            }
            $datos .= "</table></td></tr>";
        }
    } else {
        $datos .= "<tr><td>".$response."</td></tr>";
    }
    $datos .= "</table>";
    
    // -------------------------
    // Sending to Smarty
    // -------------------------
    
    $smarty->assign("datos", $datos);
    $smarty->display(dirname(__FILE__) . '/tracking.tpl');

    include('../../footer.php');
?>
