<?php

class OrderOpcController extends OrderOpcControllerCore
{
    public function initContent()
    {
            parent::initContent();

            // SHOPPING CART
            $this->_assignSummaryInformations();
            // WRAPPING AND TOS
            $this->_assignWrappingAndTOS();

            $selectedCountry = (int)(Configuration::get('PS_COUNTRY_DEFAULT'));

            if (Configuration::get('PS_RESTRICT_DELIVERED_COUNTRIES'))
                    $countries = Carrier::getDeliveredCountries($this->context->language->id, true, true);
            else
                    $countries = Country::getCountries($this->context->language->id, true);

            // If a rule offer free-shipping, force hidding shipping prices
            $free_shipping = false;
            foreach ($this->context->cart->getCartRules() as $rule)
                    if ($rule['free_shipping'])
                    {
                            $free_shipping = true;
                            break;
                    }

            $this->context->smarty->assign(array(
                    'free_shipping' => $free_shipping,
                    'isGuest' => isset($this->context->cookie->is_guest) ? $this->context->cookie->is_guest : 0,
                    'countries' => $countries,
                    'sl_country' => isset($selectedCountry) ? $selectedCountry : 0,
                    'PS_GUEST_CHECKOUT_ENABLED' => Configuration::get('PS_GUEST_CHECKOUT_ENABLED'),
                    'errorCarrier' => Tools::displayError('You must choose a carrier before', false),
                    'errorTOS' => Tools::displayError('You must accept the Terms of Service before', false),
                    'isPaymentStep' => (bool)(isset($_GET['isPaymentStep']) && $_GET['isPaymentStep']),
                    'genders' => Gender::getGenders(),
            ));
            /* Call a hook to display more information on form */
            self::$smarty->assign(array(
                    'HOOK_CREATE_ACCOUNT_FORM' => Hook::exec('displayCustomerAccountForm'),
                    'HOOK_CREATE_ACCOUNT_TOP' => Hook::exec('displayCustomerAccountFormTop')
            ));
            $years = Tools::dateYears();
            $months = Tools::dateMonths();
            $days = Tools::dateDays();
            $this->context->smarty->assign(array(
                    'years' => $years,
                    'months' => $months,
                    'days' => $days,
            ));

            /* Load guest informations */
            if ($this->isLogged && $this->context->cookie->is_guest)
                    $this->context->smarty->assign('guestInformations', $this->_getGuestInformations());

            if ($this->isLogged)
                    $this->_assignAddress(); // ADDRESS
            // CARRIER
            $this->_assignCarrier();
            // PAYMENT
            $this->_assignPayment();
            Tools::safePostVars();

            $this->context->smarty->assign('newsletter', (int)Module::getInstanceByName('blocknewsletter')->active);

            $this->_processAddressFormat();

            // -------------------------------------------------------------------------------------------------------------------------------------------------
                        // Start of change 2013/03/15. Performance by Pablo Fernández (http://www.packlink.es)
                        // -------------------------------------------------------------------------------------------------------------------------------------------------

                        $this->context->cart->cp_destination = Db::getInstance()->getValue('SELECT `postcode` FROM '._DB_PREFIX_.'address a WHERE a.`id_address` = '.$this->context->cart->id_address_delivery);
                        $this->context->cart->cp_origin      = Db::getInstance()->getValue("SELECT `value` FROM "._DB_PREFIX_."packlink_config a WHERE a.`key` = '_POST_CODE_SHOP'");
                        $this->context->cart->cp_invoice     = Db::getInstance()->getValue('SELECT `postcode` FROM '._DB_PREFIX_.'address a WHERE a.`id_address` = '.$this->context->cart->id_address_invoice);

                        $this->context->cart->id_country_destination = Db::getInstance()->getValue('SELECT `id_country` FROM '._DB_PREFIX_.'address a WHERE a.`id_address` = '.$this->context->cart->id_address_delivery);
                        $this->context->cart->id_country_origin      = Db::getInstance()->getValue("SELECT `value` FROM "._DB_PREFIX_."packlink_config a WHERE a.`key` = '_ID_COUNTRY_SHOP'");
                        $this->context->cart->id_country_invoice     = Db::getInstance()->getValue('SELECT `id_country` FROM '._DB_PREFIX_.'address a WHERE a.`id_address` = '.$this->context->cart->id_address_invoice);

                        $this->context->cart->iso_country_destination = Db::getInstance()->getValue('SELECT `iso_code` FROM '._DB_PREFIX_.'country a WHERE a.`id_country` = '.$this->context->cart->id_country_destination);
                        $this->context->cart->iso_country_origin      = Db::getInstance()->getValue('SELECT `iso_code` FROM '._DB_PREFIX_.'country a WHERE a.`id_country` = '.$this->context->cart->id_country_origin);
                        $this->context->cart->iso_country_invoice     = Db::getInstance()->getValue('SELECT `iso_code` FROM '._DB_PREFIX_.'country a WHERE a.`id_country` = '.$this->context->cart->id_country_invoice);

                        $percentage_adjust = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_PERCENTAGE_ADJUST'");

                        //echo '<script type="text/javascript">'."\n";
                        $aux = $this->context->cart->cp_destination."|". //0
                               $this->context->cart->cp_origin."|".
                               $this->context->cart->cp_invoice."|".
                               $this->context->cart->id_country_destination."|".
                               $this->context->cart->id_country_origin."|".//4
                               $this->context->cart->id_country_invoice."|".
                               $this->context->cart->iso_country_destination."|".
                               $this->context->cart->iso_country_origin."|".
                               $this->context->cart->iso_country_invoice."|".
                               $this->context->cart->id_address_delivery."|".//9
                               Db::getInstance()->getValue('SELECT `value` FROM '._DB_PREFIX_."packlink_config WHERE `key`='url_packlink'")."/get.php?method=quotes|".
                               Db::getInstance()->getValue('SELECT `value` FROM '._DB_PREFIX_."packlink_config WHERE `key`='username'")."|".
                               Db::getInstance()->getValue('SELECT `value` FROM '._DB_PREFIX_."packlink_config WHERE `key`='password'")."|".
                               Db::getInstance()->getValue('SELECT `value` FROM '._DB_PREFIX_."packlink_config WHERE `key`='apikey'")."|".
                               $this->context->cart->id."|".//14
                               _DB_PREFIX_."|".
                               _PS_CLASS_DIR_."|".
                               (Db::getInstance()->getValue('SELECT `value` FROM '._DB_PREFIX_."packlink_config WHERE `key`='_ENABLE_USER_CHOOSE'")=="0"?"false":"true")."|".//17
                               Db::getInstance()->getValue('SELECT `value` FROM '._DB_PREFIX_."packlink_config WHERE `key`='_FREE_SHIPMENT_FROM'");

                        $delivery_option = unserialize(trim(Db::getInstance()->getValue('SELECT `delivery_option` FROM '._DB_PREFIX_.'cart a WHERE a.`id_cart` = '.$this->context->cart->id)));
                        $delivery_option = trim(implode("", $delivery_option));
                        $delivery_option2 = explode(",", $delivery_option);
                        $id_srv_packlink = Db::getInstance()->getValue('SELECT `id_carrier` FROM '._DB_PREFIX_."carrier WHERE `name`='Packlink'"); //$delivery_option2[0];
                        $id_carrier_packlink = $delivery_option2[1];
                        $price_delivery_packlink = $delivery_option2[2];
                        $tax_delivery_packlink = number_format($delivery_option2[2]*$delivery_option2[3], 2);
                        
                        $var_aux = $this->context->cart->getDeliveryOptionList(); 
                        echo "<script type=\"text/javascript\">\n";
                        echo "  var pl_data = '".$var_aux[$this->context->cart->id_address_delivery][$id_srv_packlink.',']['pl_data']."=';\n";
                        echo "  var pl_info = '".base64_encode($aux)."=';\n";
                        echo "  var percentage_adjust = ".$percentage_adjust.";\n";
                        echo "  var opc = ".Configuration::get('PS_ORDER_PROCESS_TYPE').";\n";
                        echo "  var module_dir = '".addslashes(_PS_MODULE_DIR_)."';\n";
                        echo "</script>\n";
                        
                        if(isset($_COOKIE['updatePacklink']) && $_COOKIE['updatePacklink'] != ""){
                            Db::getInstance()->execute(stripslashes($_COOKIE['updatePacklink']));
                            setcookie ("updatePacklink", "", time() - 3600, "/");
                        }

                        // -------------------------------------------------------------------------------------------------------------------------------------------------
                        // End of change 2013/03/15. Performance by Pablo Fernández (http://www.packlink.es)
                        // -------------------------------------------------------------------------------------------------------------------------------------------------



            $this->setTemplate(_PS_THEME_DIR_.'order-opc.tpl');
    }    
    protected function _assignPayment()
    {
            $this->context->smarty->assign(array(
                    'HOOK_TOP_PAYMENT' => ($this->isLogged ? Hook::exec('displayPaymentTop') : ''),
                    'HOOK_PAYMENT' => $this->_getPaymentMethods()
            ));

        $delivery_option = unserialize(Db::getInstance()->getValue('SELECT `delivery_option` FROM '._DB_PREFIX_.'cart a WHERE a.`id_cart` = '.$this->context->cart->id));
        $delivery_option = trim(implode("", $delivery_option));
        $delivery_option2 = explode(",", $delivery_option);
        $id_srv_packlink = $delivery_option2[0];
        $id_carrier_packlink = $delivery_option2[1];
        $price_delivery_packlink = $delivery_option2[2];
        $tax_delivery_packlink = number_format($delivery_option2[2]*$delivery_option2[3], 2);

        /* We may need to display an order summary */
        $this->context->smarty->assign($this->context->cart->getSummaryDetails());
        $this->context->smarty->assign(array(
                'id_address' => $this->context->cart->id_address_delivery,
                'id_srv_packlink' => $id_srv_packlink,
                'id_carrier_packlink' => $id_carrier_packlink,
                'price_delivery_packlink' => $price_delivery_packlink,
                'tax_delivery_packlink' => $tax_delivery_packlink,
                'total_price' => (float)($this->context->cart->getOrderTotal()),
                'taxes_enabled' => (int)(Configuration::get('PS_TAX')),
                'shippingCost' => 0
        ));
        $this->context->cart->checkedTOS = '1';
    }
}

