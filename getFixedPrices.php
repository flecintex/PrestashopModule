<?php
    include('../../config/config.inc.php');
  
    function toHTML($arr, $level=0){
        $result  = '<table cellpadding="0" style="width:100%" cellspacing="0" border="0" class="table3" id="table'.$_REQUEST['id'].'" >'."\n";
        $result .= '<thead>'."\n";
        $result .= '<tr>'."\n";
        foreach (array_keys($arr[0]) as $key){
            $result .= '<th align="center">'.$key.'</th>'."\n"; 
        }
        $result .= '</tr>'."\n"; 
        $result .= '</thead>'."\n";
        $result .= '<tbody>'."\n";
        foreach (array_values($arr) as $value){
            $aux = implode("\n", $value);
            $aux = stripos($aux, 'checked="checked"')!==false?true:false;
            $result .= '<tr class="'.($aux==false?'disable':'').'">'."\n";
            foreach ($value as $value2){
                $result .= '<td align="center">'.$value2.'</td>'."\n"; 
            }
            $result .= '</tr>'."\n"; 
        }

        $result .= '</tbody>'."\n";
        $result .= "</table>\n";

        return $result;
    }
    
    // Get needed data for web service
    // ------------------------------
    $url_packlink        = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = 'url_packlink'");
    $username            = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = 'username'");
    $password            = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = 'password'");
    $apikey              = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = 'apikey'");
    $secret              = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = 'secret'");

    // WS Connection Client
    $options = array('trace' => true, 'exceptions' => true, 'cache_wsdl' => WSDL_CACHE_NONE, 'features' => SOAP_SINGLE_ELEMENT_ARRAYS + SOAP_USE_XSI_ARRAY_TYPE, 'login' => $username, 'soap_version' => SOAP_1_2, "use" => SOAP_ENCODED, "style" => SOAP_DOCUMENT);
    $client     = new SoapClient($url_packlink."/wsdl", $options );

    // Set seed and authentication
    $ctimeSeed    = microtime();
    $response = $client->auth($apikey, sha1($password.$secret.$ctimeSeed), $ctimeSeed);
    $header   = new SoapHeader("APIPacklink","token",$response);
    $client->__setSoapHeaders($header);

    // Identified user, is proceed to do the requests
    $l = unserialize(base64_decode($_REQUEST['l']));

    $response = $client->setLanguage($l['l']);
    
    $response = $client->getFixedPriceByService($_REQUEST['id']);
    $dom = simplexml_load_string($response); 
   
    foreach ($dom as $price){ 
        $prices[] = array($l["from"]=>$price->from." Kg", $l["to"]=>$price->to." Kg", $l["value"]=>$price->value." €"); //, $l["location"]=>$price->location);
    }
   ?>
        <link type="text/css" rel="stylesheet" href="<?= _MODULE_DIR_?>packlink/css/animations.css" />
        <link type="text/css" rel="stylesheet" href="<?= _MODULE_DIR_?>packlink/css/base.css" />
        <link type="text/css" rel="stylesheet" href="<?= _MODULE_DIR_?>packlink/css/config.css" />
        <link type="text/css" rel="stylesheet" href="<?= _MODULE_DIR_?>packlink/css/front.css" />
        <link type="text/css" rel="stylesheet" href="<?= _MODULE_DIR_?>packlink/css/messages.css" />
        <link type="text/css" rel="stylesheet" href="<?= _MODULE_DIR_?>packlink/css/orders.css" />
        <link type="text/css" rel="stylesheet" href="<?= _MODULE_DIR_?>packlink/css/tables.css" />
    <?php
    
    echo toHTML($prices);
    