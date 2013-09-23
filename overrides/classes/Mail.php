<?php

class Mail extends MailCore
{
    const TYPE_HTML = 1;
    const TYPE_TEXT = 2;
    const TYPE_BOTH = 3;
        
    /**
     * Override Send method to avoid default carrier name in order_conf e-mail.
     */
    /**
     * Override Send method to avoid default carrier name in order_conf e-mail.
     */
    public static function Send($id_lang, $template, $subject, $template_vars, $to,
                                $to_name = null, $from = null, $from_name = null, 
                                $file_attachment = null, $mode_smtp = null, 
                                $template_path = _PS_MAIL_DIR_, $die = false, $id_shop = null){

        $orderData = Db::getInstance()->ExecuteS('SELECT orca.*, ord.*, car.* FROM '._DB_PREFIX_.'orders ord            
                                                  JOIN '._DB_PREFIX_.'order_carrier orca ON ord.id_order = orca.id_order
                                                  JOIN '._DB_PREFIX_.'carrier car
                                                  ON car.id_carrier = orca.id_carrier
                                                  WHERE ord.reference = "'.$template_vars["{order_name}"].'"');

        if(strtolower($orderData[0]['name']) == 'packlink'){
            $delivery_option = unserialize(Db::getInstance()->getValue('SELECT `delivery_option` FROM '._DB_PREFIX_.'cart a WHERE a.`id_cart` = '.$orderData[0]['id_cart']));
            $delivery_option = trim(implode("", $delivery_option));
            $delivery_option2 = explode(",", $delivery_option);
            $id_srv_packlink = $delivery_option2[0];
            $id_carrier_packlink = $delivery_option2[1];
            $tax_delivery_packlink = number_format($delivery_option2[2]*$delivery_option2[3], 2);

            // Calcule new values
            $theCart = new Cart($orderData[0]['id_cart']);
            $total_paid_tax_excl = $theCart->getOrderTotal(false);
            $total_paid_tax_incl = $theCart->getOrderTotal(true);
            $total_paid = $total_paid_tax_incl;
            $total_paid_real = $total_paid_tax_incl;
            $total_products_wot = ($total_paid_tax_excl-$delivery_option2[2]);
            $total_products = $total_products_wot;
            $total_products_wt  = $total_paid_tax_incl-$delivery_option2[2]-$tax_delivery_packlink;
            $total_shipping_tax_excl = $delivery_option2[2];
            $total_shipping = $total_shipping_tax_excl;
            $total_shipping_tax_incl = $total_shipping_tax_excl+$tax_delivery_packlink;
            $carrier_tax_rate = $delivery_option2[3]*100;
            $total_wrapping = 0;
            $total_wrapping_tax_excl = 0;
            $total_wrapping_tax_incl = 0;

            // in addition, correct final prices
            $totalNoShipping = $total_paid - $total_shipping;
            $template_vars['{total_shipping}'] = $total_shipping_tax_incl;
            $template_vars['{total_paid}'] = $total_paid;

            // WS Connection Client
            // --------------------
            $carrier_packlink_name = " / ";
            $password         = Db::getInstance()->getValue('SELECT `value` FROM '._DB_PREFIX_."packlink_config WHERE `key`='password'");
            $apikey           = Db::getInstance()->getValue('SELECT `value` FROM '._DB_PREFIX_."packlink_config WHERE `key`='apikey'");
            $url_packlink     = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = 'url_packlink'");
            $options          = array('trace' => true, 'exceptions' => true, 'cache_wsdl' => WSDL_CACHE_NONE, 'features' => SOAP_SINGLE_ELEMENT_ARRAYS +  SOAP_USE_XSI_ARRAY_TYPE, 'login' => $apikey, 'password' =>$password, 'soap_version'   => SOAP_1_2, "use"      => SOAP_ENCODED, "style"    => SOAP_DOCUMENT);
            $client           = new SoapClient($url_packlink."/wsdl", $options );

            $response = $client->getShippers();
            $dom = simplexml_load_string(str_replace("]]>", "", str_replace("<![CDATA[", "", $response))); 
            $break = false;
            foreach ($dom as $shipper){
                foreach($shipper as $service){ 
                    if((int)$service->service_id == $id_carrier_packlink){
                        $carrier_packlink_name = $carrier_packlink_name.(string)$shipper->getName()." ".((string)$service->service);
                        $break = true;
                    }
                    if($break) break;
                }
                if($break) break;
            }

            $template_vars['{carrier}'] = $orderData[0]['name'].$carrier_packlink_name;
        }

        return parent::Send($id_lang, $template, $subject, $template_vars, $to, $to_name, $from, 
                        $from_name, $file_attachment, $mode_smtp, $template_path, $die, 
                        $id_shop);
    }
}

