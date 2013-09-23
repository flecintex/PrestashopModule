<?php
        // ---------------------------------------------------------------------
	// Override Classes and Controllers.
        // ---------------------------------------------------------------------
        
        if(str_replace(".", "", _PS_VERSION_) > 1400 ){
            $strOverrideClass = "<?php\n\nclass Cart extends #class#Core\n{\n\n }\n\n";
            foreach (Tools::scandir($this->getLocalPath().'overrides', 'php', '', true) as $file){
                $class = basename($file, '.php');
                $dirName = dirname($file);
                if (file_exists(_PS_OVERRIDE_DIR_.$dirName."/".$class.".php")) {
                    @unlink(_PS_OVERRIDE_DIR_.$dirName."/".$class.".php");
                } 
            }
        }
        
        // ---------------------------------------------------------------------
	// Get variables and necessary data for the installation.
        // ---------------------------------------------------------------------
        
        $system = strtolower(substr(PHP_OS, 0, 3));
        if($system == "win"){
            $_DIR_BASE        =  str_replace("/", "\\", _PS_ROOT_DIR_); //substr(_PS_JS_DIR_, 0, stripos(_PS_JS_DIR_, "/", 1)+1);
            $_PS_OVERRIDE_DIR =  str_replace("/", "\\", _PS_OVERRIDE_DIR_);
            $separator        = "\\";
        } else {
            $_DIR_BASE        = _PS_MODULE_DIR_; //substr(_PS_JS_DIR_, 0, stripos(_PS_JS_DIR_, "/", 1)+1);
            $_PS_OVERRIDE_DIR = _PS_OVERRIDE_DIR_;
            $separator        = "/";
        }
        $_DIR_BASE_MODULE     = _PS_MODULE_DIR_.'packlink';
        $_OVERRIDE_CLS        = str_replace($_DIR_BASE, "", _PS_OVERRIDE_DIR_)."classes".$separator;
        $_OVERRIDE_CLS_CTRL   = $_OVERRIDE_CLS.'controller'.$separator;
        $_OVERRIDE_CTRL_FRNT  = str_replace($_DIR_BASE, "", _PS_OVERRIDE_DIR_).'controllers'.$separator.'front'.$separator;
        $_PACKLINK_CARRIER_ID = Db::getInstance()->getValue("SELECT MAX(id_carrier) FROM `"._DB_PREFIX_."carrier` WHERE UCASE(name) LIKE '%PACKLINK%' AND `deleted` = 0", false);
        
	// ---------------------------------------------------------------------
	// Delete Carrier
        // ---------------------------------------------------------------------
        
         if ($records = Db::getInstance()->ExecuteS("SELECT DISTINCT id_carrier AS 'id' FROM `"._DB_PREFIX_."carrier` WHERE UCASE(name) LIKE '%PACKLINK%'")){
            foreach ($records as $record){
                if (!Db::getInstance()->execute("DELETE FROM `"._DB_PREFIX_."carrier` WHERE id_carrier = '".$record['id']."';")) return false;
                if (!Db::getInstance()->execute("DELETE FROM `"._DB_PREFIX_."carrier_lang` WHERE id_carrier = '".$record['id']."';")) return false;
                if (!Db::getInstance()->execute("DELETE FROM `"._DB_PREFIX_."carrier_shop` WHERE id_carrier = '".$record['id']."';")) return false;
                if (!Db::getInstance()->execute("DELETE FROM `"._DB_PREFIX_."carrier_zone` WHERE id_carrier = '".$record['id']."';")) return false;
                if (!Db::getInstance()->execute("DELETE FROM `"._DB_PREFIX_."carrier_group` WHERE id_carrier = '".$record['id']."';")) return false;
                if (!Db::getInstance()->execute("DELETE FROM `"._DB_PREFIX_."state` WHERE id_country = '6';")) return false;
                if (!Db::getInstance()->execute("UPDATE `"._DB_PREFIX_."country` SET `contains_states` = '0' WHERE `ps_country`.`id_country` =6;")) return false;
                // ---------------------------------------------------------------------
                // Delete Range Weight
                // ---------------------------------------------------------------------

                if (!Db::getInstance()->execute("DELETE FROM `"._DB_PREFIX_."range_weight` WHERE id_carrier = '".$record['id']."';")) return false;

                // ---------------------------------------------------------------------
                // Delete new Range
                // ---------------------------------------------------------------------

                if (!Db::getInstance()->execute("DELETE FROM `"._DB_PREFIX_."range_price` WHERE id_carrier = '".$record['id']."';")) return false;
                
                // ---------------------------------------------------------------------
                // Delete taxes delivery
                // ---------------------------------------------------------------------

                if (!Db::getInstance()->execute("DELETE FROM `"._DB_PREFIX_."delivery` WHERE id_carrier = '".$record['id']."';")) return false;
            }
         }
        
        // ---------------------------------------------------------------------
        // Update all the products to can to be shipped by Packlink service.
        // ---------------------------------------------------------------------

        if (!Db::getInstance()->execute("DROP TABLE  `"._DB_PREFIX_."product_carrier`;")) return false;
        if (!Db::getInstance()->execute("RENAME TABLE `"._DB_PREFIX_."product_carrier_no_pl` TO `"._DB_PREFIX_."product_carrier`")) return false;        

        // ---------------------------------------------------------------------
	// Drop tables
        // ---------------------------------------------------------------------
        
        
        if (!Db::getInstance()->execute("DROP TABLE  `"._DB_PREFIX_."packlink_boxes`;")) return false;
        if (!Db::getInstance()->execute("DROP TABLE  `"._DB_PREFIX_."packlink_config`;")) return false;
        if (!Db::getInstance()->execute("DROP TABLE  `"._DB_PREFIX_."packlink_services`;")) return false;
        if (!Db::getInstance()->execute("DROP TABLE  `"._DB_PREFIX_."packlink_status`;")) return false;
        
        
        // ---------------------------------------------------------------------
	// Update Configuration variables.
        // ---------------------------------------------------------------------
        
        Configuration::updateValue('PS_SHIPPING_HANDLING', Configuration::get('PS_SHIPPING_HANDLING_NO_PL'));
        Configuration::deleteByName('PS_SHIPPING_HANDLING_NO_PL');
        Configuration::updateValue('PS_SHIPPING_METHOD', Configuration::get('PS_SHIPPING_METHOD_NO_PL'));
        Configuration::deleteByName('PS_SHIPPING_METHOD_NO_PL');
        
        Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'tab WHERE module = "Packlink" OR  class_name = "Packlink"');
        Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'tab_lang WHERE name = "Packlink"');

