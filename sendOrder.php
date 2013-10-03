<?php
    global $smarty;
    include '../../config/config.inc.php';
    include 'packlink.php';
    
    function createParam($element, $name, $pathServer){
        if(is_array($element)){
                $soapstruct = new SoapVar($element, SOAP_ENC_OBJECT, $name, $pathServer."schema.xsd");
                return 	new SoapParam($soapstruct, $name);
        } else {
                return 	new SoapParam($element, $name);
        }
    }
    
    // Init the Packlink module.
    $pack = new packlink();
    
    // Get the necessary parameters for execute module.
    $url_packlink        = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = 'url_packlink'");
    $username            = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = 'username'");
    $password            = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = 'password'");
    $apikey              = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = 'apikey'");
    $secret              = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = 'secret'");
    
    // Shop Address
    $_POST_CODE_SHOP            = Db::getInstance()->getValue("SELECT value    FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_POST_CODE_SHOP'");
    $_ID_COUNTRY_SHOP           = Db::getInstance()->getValue("SELECT value    FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_ID_COUNTRY_SHOP'");
    $_COUNTRY_ISO_SHOP          = Db::getInstance()->getValue("SELECT iso_code FROM "._DB_PREFIX_."country WHERE `id_country` = ".$_ID_COUNTRY_SHOP);
    $_ADDRESS_SHOP              = Db::getInstance()->getValue("SELECT value    FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_ADDRESS_SHOP'");
    $_TOWN_SHOP                 = Db::getInstance()->getValue("SELECT value    FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_TOWN_SHOP'");
    $_PROVINCE_SHOP             = Db::getInstance()->getValue("SELECT value    FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_PROVINCE_SHOP'");
    $_LANDLINE_SHOP             = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_LANDLINE_SHOP'");
    $_OTHER_PHONE_SHOP          = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_OTHER_PHONE_SHOP'");
    
    $_INVOICE_POST_CODE_SHOP    = Db::getInstance()->getValue("SELECT value    FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_INVOICE_POST_CODE_SHOP'");
    $_INVOICE_ID_COUNTRY_SHOP   = Db::getInstance()->getValue("SELECT value    FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_INVOICE_ID_COUNTRY_SHOP'");
    $_INVOICE_COUNTRY_ISO_SHOP  = Db::getInstance()->getValue("SELECT iso_code FROM "._DB_PREFIX_."country WHERE `id_country` = ".$_INVOICE_ID_COUNTRY_SHOP);
    $_INVOICE_ADDRESS_SHOP      = Db::getInstance()->getValue("SELECT value    FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_INVOICE_ADDRESS_SHOP'");
    $_INVOICE_TOWN_SHOP         = Db::getInstance()->getValue("SELECT value    FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_INVOICE_TOWN_SHOP'");
    $_INVOICE_PROVINCE_SHOP     = Db::getInstance()->getValue("SELECT value    FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_INVOICE_PROVINCE_SHOP'");
    
    $_PS_SHOP_NAME              = Db::getInstance()->getValue("SELECT value    FROM "._DB_PREFIX_."configuration   WHERE `name` = 'PS_SHOP_NAME'");
    $_PS_SHOP_EMAIL             = Db::getInstance()->getValue("SELECT value    FROM "._DB_PREFIX_."configuration   WHERE `name` = 'PS_SHOP_EMAIL'");
    
    $_ID_ADDRESS_DELIVERY       = Db::getInstance()->getValue("SELECT id_address_delivery FROM "._DB_PREFIX_."orders WHERE `id_order` = '".$_REQUEST['id']."'");
    $_ID_ADDRESS_INVOICE        = Db::getInstance()->getValue("SELECT id_address_invoice FROM "._DB_PREFIX_."orders WHERE `id_order` = '".$_REQUEST['id']."'");
    $_INVOICE_PHONE_1           = Db::getInstance()->getValue("SELECT phone        FROM "._DB_PREFIX_."address WHERE `id_address` = '".$_ID_ADDRESS_INVOICE."'");
    $_INVOICE_PHONE_2           = Db::getInstance()->getValue("SELECT phone_mobile FROM "._DB_PREFIX_."address WHERE `id_address` = '".$_ID_ADDRESS_INVOICE."'");
    $_DELIVERY_PHONE_1          = Db::getInstance()->getValue("SELECT phone        FROM "._DB_PREFIX_."address WHERE `id_address` = '".$_ID_ADDRESS_DELIVERY."'");
    $_DELIVERY_PHONE_2          = Db::getInstance()->getValue("SELECT phone_mobile FROM "._DB_PREFIX_."address WHERE `id_address` = '".$_ID_ADDRESS_DELIVERY."'");
    $_INVOICE_PHONE             = $_INVOICE_PHONE_1;
    $_DELIVERY_PHONE            = $_DELIVERY_PHONE_1;
    $_GATHERED_PHONE            = $_LANDLINE_SHOP;
    
    if($_DELIVERY_PHONE_1 == "") $_DELIVERY_PHONE = $_DELIVERY_PHONE_2;
    if($_INVOICE_PHONE_1 == "")  $_INVOICE_PHONE = $_INVOICE_PHONE_2;
    if($_LANDLINE_SHOP == "")    $_GATHERED_PHONE  = $_OTHER_PHONE_SHOP;
    
    if($_REQUEST['p'] && $_REQUEST['m'] && $_REQUEST['f']){
        $sql = ("SELECT o.`id_order` AS 'order_id',
                        (SELECT (SELECT firstname  FROM "._DB_PREFIX_."customer c WHERE c.`id_customer` = psa.id_customer) FROM "._DB_PREFIX_."address psa WHERE psa.id_address = o.`id_address_delivery`) AS 'customer_firstname', 
                        (SELECT (SELECT lastname  FROM "._DB_PREFIX_."customer c WHERE c.`id_customer` = psa.id_customer) FROM "._DB_PREFIX_."address psa WHERE psa.id_address = o.`id_address_delivery`) AS 'customer_lastname',
                        '$_ADDRESS_SHOP'        AS 'gathered_address', 
                        '$_POST_CODE_SHOP'      AS 'gathered_pc',
                        '$_TOWN_SHOP'           AS 'gathered_town',
                        '$_PROVINCE_SHOP'       AS 'gathered_province',
                        '".strtolower($_COUNTRY_ISO_SHOP)."'    AS 'gathered_iso_code', 
                        '$_INVOICE_ADDRESS_SHOP'        AS 'invoice_address', 
                        '$_INVOICE_POST_CODE_SHOP'      AS 'invoice_pc',
                        '$_INVOICE_TOWN_SHOP'           AS 'invoice_town',
                        '$_INVOICE_PROVINCE_SHOP'       AS 'invoice_province',
                        '".strtolower($_INVOICE_COUNTRY_ISO_SHOP)."'    AS 'invoice_iso_code', 
                        (SELECT CONCAT(psa.address1,'|',psa.address2) FROM "._DB_PREFIX_."address psa WHERE psa.id_address = o.`id_address_delivery`) AS 'delivery_address', 
                        (SELECT psa.postcode            FROM "._DB_PREFIX_."address psa WHERE psa.id_address = o.`id_address_delivery`) AS 'delivery_pc',
                        (SELECT psa.city                FROM "._DB_PREFIX_."address psa WHERE psa.id_address = o.`id_address_delivery`) AS 'delivery_town',
                        (SELECT name                    FROM "._DB_PREFIX_."state WHERE id_state = (SELECT id_state FROM `"._DB_PREFIX_."address` psa WHERE psa.id_address = o.`id_address_delivery`)) AS 'delivery_province',
                        (SELECT (SELECT LOWER(iso_code) FROM "._DB_PREFIX_."country cl  WHERE cl.`id_country` = psa.id_country) FROM "._DB_PREFIX_."address psa WHERE psa.id_address = o.`id_address_delivery`) AS 'delivery_iso_code', 
                        '' AS 'shipper',
                        pl.id_carrier AS 'shipper_id',
                        o.`total_paid` AS 'merchandise_value'
                   FROM `"._DB_PREFIX_."orders` o, `"._DB_PREFIX_."packlink_orders` pl
                  WHERE o.id_order = pl.id_order AND pl.id_order = ".$_REQUEST['id']);

        $right = true;
        if ($results = Db::getInstance()->ExecuteS($sql)){ 
            foreach ($results as $order){
                $order["shipper"] = Db::getInstance()->getValue("SELECT CONCAT(shipper, ' ', service) FROM ps_packlink_services WHERE service_id = ".$order['shipper_id']);
                $mearurements = explode("|", $_REQUEST['m']);
                for($xCount = 0; $xCount < count($mearurements); $xCount++){
                    $m = explode("x", $mearurements[$xCount]);
                    if($m[0] != "" && $m[1] != "" && $m[2] != "" && $m[3] != ""){
                        $auxM[$xCount]['width']  = $m['0'];
                        $auxM[$xCount]['height'] = $m['1'];
                        $auxM[$xCount]['depth']  = $m['2'];
                        $auxM[$xCount]['weight'] = $m['3'];
                    } else {
                        $right = false;
                    }
                }
                if($order["delivery_province"] == "null" || $order["delivery_province"] == "") $order["delivery_province"] = $order["delivery_town"];
                if(substr(trim($order['delivery_address']), strlen(trim($order['delivery_address']))-1, 1) == "|") {
                    $order['delivery_address'] = substr(trim($order['delivery_address']), 0, strlen(trim($order['delivery_address']))-1);
                }
                
                $order["shop_email"]     = $_PS_SHOP_EMAIL;
                $order["description"]    = $pack->l("Items of Shop").$_PS_SHOP_NAME;
                $order["gathered_date"]  = str_replace("/", "-", $_REQUEST['f']);
                $order["packlist"]       = $auxM;
                $order['service_id']     = $_REQUEST['sid'];
                $order['shop_name']      = $_PS_SHOP_NAME;
                $order['gathered_phone'] = $_GATHERED_PHONE;
                $order['invoice_phone']  = $_INVOICE_PHONE;
                $order['delivery_phone'] = $_DELIVERY_PHONE;
                $order['test'] = "noSave";
                //echo json_encode($order, true);
                ksort($order);
                
                global $cookie;
                $pathServer = $url_packlink."/";
                $iso_lang =  Db::getInstance()->getValue("SELECT iso_code FROM "._DB_PREFIX_."lang WHERE id_lang=$cookie->id_lang;");
                
                // WS Connection Client
                $options = array('trace' => true, 'exceptions' => true, 'cache_wsdl' => WSDL_CACHE_NONE, 'features' => SOAP_SINGLE_ELEMENT_ARRAYS + SOAP_USE_XSI_ARRAY_TYPE, 'login' => $username, 'soap_version' => SOAP_1_2, "use" => SOAP_ENCODED, "style" => SOAP_DOCUMENT);
                $client     = new SoapClient($url_packlink."/wsdl", $options );

                // Set seed and authentication
                $ctimeSeed    = microtime();
                $response = $client->auth($apikey, sha1($password.$secret.$ctimeSeed), $ctimeSeed);
                $header   = new SoapHeader("APIPacklink","token",$response);
                $client->__setSoapHeaders($header);

                // Identified user, is proceed to do the requests
                try {
                    $response = $client->setLanguage($iso_lang);
                    //$param    = createParam($order, "order", "");
                    $response = $client->sendShipment($order);
                    $response =  simplexml_load_string(str_replace("]]>", "", str_replace("<![CDATA[", "", $response))); 
                    if($response == ""){
                        echo $pack->l("An error occurred.");
                        die();
                    }
                    
                    Db::getInstance()->getValue("UPDATE "._DB_PREFIX_."packlink_orders SET reference = '".$response."' WHERE id_order = ".$_REQUEST['id']);
                    Db::getInstance()->getValue("UPDATE "._DB_PREFIX_."orders SET shipping_number = '".$response."' WHERE id_order = ".$_REQUEST['id']);
                    Db::getInstance()->getValue("UPDATE "._DB_PREFIX_."order_carrier SET tracking_number = '".$response."' WHERE id_order = ".$_REQUEST['id']);
                    
                } catch (SoapFault $exp) {
                    echo "Message: ".$exp->faultstring."<br />";
                    echo "Error Code: ".$exp->faultcode."<br />";
                    echo "Line: ".$exp->getLine()."<br />";
                    echo "Detail:<pre>".$exp->xdebug_message."</pre>";
                    //echo "Trc: ".print_r($exp->getTrace())."<br />";
                    echo "Trace:<pre>".$exp->getTraceAsString()."</pre>";

                }
                
                echo $pack->l("Operation Completed Successfully");
            }
        }
    }


?>
