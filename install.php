<?php
        error_reporting(E_ALL);
       
        // ---------------------------------------------------------------------
    // Override Classes and Controllers.
        // ---------------------------------------------------------------------
       
        if(str_replace(".", "", _PS_VERSION_) > 1400 ){
            $strOverrideClass = "<?php\n\nclass Cart extends #class#Core\n{\n\n }\n\n";
            foreach (Tools::scandir($this->getLocalPath().'overrides', 'php', '', true) as $file){
                $class = basename($file, '.php');
                $dirName = dirname($file);
                if (!file_exists(_PS_OVERRIDE_DIR_.$dirName."/".$class.".php")) {
                   
                    //copy(_PS_MODULE_DIR_.$this->module_name."/overrides/".$dirName."/".$class.".php", _PS_OVERRIDE_DIR_.$dirName."/".$class.".php");
                    $contentx =@file_get_contents(_PS_MODULE_DIR_.$this->module_name."/overrides/".$dirName."/".$class.".php");
                    $openedfile = fopen(_PS_OVERRIDE_DIR_.$dirName."/".$class.".php", "w");
                    fwrite($openedfile, $contentx);
                    fclose($openedfile);
                    chmod(_PS_OVERRIDE_DIR_.$dirName."/".$class.".php", 0777);
                   
                    $user = get_current_user(); //posix_getpwuid(fileowner(_PS_MODULE_DIR_.$this->module_name."/overrides/".$dirName."/".$class.".php"));
                    chown(_PS_OVERRIDE_DIR_.$dirName."/".$class.".php", $user);
                } else {
                    //rename(_PS_OVERRIDE_DIR_.$dirName."/".$class.".php",_PS_OVERRIDE_DIR_.$dirName."/".$class."_no_pl.php");
                    //chmod(_PS_OVERRIDE_DIR_.$dirName."/".$class."_no_pl.php", 0777);
                    //copy(_PS_MODULE_DIR_.$this->module_name."/overrides/".$dirName."/".$class.".php", _PS_OVERRIDE_DIR_.$dirName."/".$class.".php");
                    //chmod(_PS_OVERRIDE_DIR_.$dirName."/".$class.".php", 0777);
                }
            }
           // recurse_chown_chgrp("/var/web/packlink.de/presta/cache", "webftp", "webftp");
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
            $_DIR_BASE        = _PS_ROOT_DIR_; //substr(_PS_JS_DIR_, 0, stripos(_PS_JS_DIR_, "/", 1)+1);
            $_PS_OVERRIDE_DIR = _PS_OVERRIDE_DIR_;
            $separator        = "/";
        }
        $_DIR_BASE_MODULE     = $_DIR_BASE .$separator.'modules'.$separator.'packlink';
        $_OVERRIDE_CLS        = str_replace($_DIR_BASE, "", $_PS_OVERRIDE_DIR)."classes".$separator;
        $_OVERRIDE_CLS_CTRL   = $_OVERRIDE_CLS.'controller'.$separator;
        $_OVERRIDE_CTRL_FRNT  = str_replace($_DIR_BASE, "", $_PS_OVERRIDE_DIR).'controllers'.$separator.'front'.$separator;
        $_PACKLINK_CARRIER_ID = 0;

        // ---------------------------------------------------------------------
        // Insert new Carrier named Packlink.
        // ---------------------------------------------------------------------

        $aux = Db::getInstance()->executeS("SELECT MAX(id_carrier) AS 'id_carrier' FROM `"._DB_PREFIX_."carrier` WHERE UCASE(name) LIKE '%PACKLINK%' AND `deleted` = 0");
        if(count($aux) != 0 && count($aux[0]) != 0) $_PACKLINK_CARRIER_ID = $aux[0]['id_carrier']; else $_PACKLINK_CARRIER_ID = 0;

        if($_PACKLINK_CARRIER_ID != 0){
            // Delete all carriers less the highest value. After, will update all previous installs.
            
            if ($records = Db::getInstance()->executeS("SELECT DISTINCT id_carrier AS 'id' FROM `"._DB_PREFIX_."carrier` WHERE UCASE(name) LIKE '%PACKLINK%'")){
                foreach ($records as $record){
                    if($record['id'] != $_PACKLINK_CARRIER_ID){
                        if (!Db::getInstance()->execute("UPDATE `"._DB_PREFIX_."packlink_orders` SET id_carrier = ".$_PACKLINK_CARRIER_ID." WHERE id_carrier = '".$record['id']."';")) return false;
                        if (!Db::getInstance()->execute("UPDATE `"._DB_PREFIX_."orders` SET id_carrier = ".$_PACKLINK_CARRIER_ID." WHERE id_carrier = '".$record['id']."';")) return false;
                        if (!Db::getInstance()->execute("UPDATE `"._DB_PREFIX_."orders_carrier` SET id_carrier = ".$_PACKLINK_CARRIER_ID." WHERE id_carrier = '".$record['id']."';")) return false;
                        
                        if (!Db::getInstance()->execute("DELETE FROM `"._DB_PREFIX_."carrier` WHERE id_carrier = '".$record['id']."';")) return false;
                        if (!Db::getInstance()->execute("DELETE FROM `"._DB_PREFIX_."carrier_lang` WHERE id_carrier = '".$record['id']."';")) return false;
                        if (!Db::getInstance()->execute("DELETE FROM `"._DB_PREFIX_."carrier_shop` WHERE id_carrier = '".$record['id']."';")) return false;
                        if (!Db::getInstance()->execute("DELETE FROM `"._DB_PREFIX_."carrier_zone` WHERE id_carrier = '".$record['id']."';")) return false;
                        if (!Db::getInstance()->execute("DELETE FROM `"._DB_PREFIX_."carrier_group` WHERE id_carrier = '".$record['id']."';")) return false;
                        
                        if (!Db::getInstance()->execute("DELETE FROM `"._DB_PREFIX_."delivery` WHERE id_carrier = '".$record['id']."';")) return false;
                        
                        if (!Db::getInstance()->execute("DELETE FROM `"._DB_PREFIX_."range_weight` WHERE id_carrier = '".$record['id']."';")) return false;
                        if (!Db::getInstance()->execute("DELETE FROM `"._DB_PREFIX_."range_price` WHERE id_carrier = '".$record['id']."';")) return false;
                        
                        if (!Db::getInstance()->execute("DELETE FROM `"._DB_PREFIX_."delivery` WHERE id_carrier = '".$record['id']."';")) return false;
                    }
                }
            }
        } else {
            // To do Regular Install

            if (!Db::getInstance()->execute("INSERT INTO `"._DB_PREFIX_."carrier` (`id_reference`, `id_tax_rules_group`, `name`, `url`, `active`, `deleted`, `shipping_handling`, `range_behavior`, `is_module`, `is_free`, `shipping_external`, `need_range`, `external_module_name`, `shipping_method`, `position`, `max_width`, `max_height`, `max_depth`, `max_weight`, `grade`) VALUES (0, 0, 'Packlink', '', 1, 0, 1, 0, 0, 0, 0, 0, '', 2, 2, 0, 0, 0, 0, 0);")) return false;
            $_PACKLINK_CARRIER_ID = Db::getInstance()->executeS("SELECT id_carrier FROM `"._DB_PREFIX_."carrier` WHERE UCASE(name) LIKE '%PACKLINK%';");
            $_PACKLINK_CARRIER_ID = $_PACKLINK_CARRIER_ID[0]['id_carrier'];

            // Refill languages

            if ($shops = Db::getInstance()->executeS("SELECT DISTINCT id_shop AS 'id' FROM `"._DB_PREFIX_.'shop` WHERE 1')){
                foreach ($shops as $shop){
                    if ($langs = Db::getInstance()->executeS("SELECT DISTINCT id_lang AS 'id', iso_code FROM `"._DB_PREFIX_.'lang` WHERE 1')){
                        foreach ($langs as $lang){
                            if($lang['iso_code'] == "es") $message = "Máximo 7 días hábiles";
                            elseif($lang['iso_code'] == "de") $message = "Maximum von 7 Werktagen";
                            elseif($lang['iso_code'] == "fr") $message = "Maximum 7 jours ouvrables";
                            elseif($lang['iso_code'] == "it") $message =  "Numero massimo di 7 giorni lavorativi";
                            elseif($lang['iso_code'] == "ca") $message =  "màxim 7 dies";
                            elseif($lang['iso_code'] == "gl") $message =  "máximos de 7 días";
                            else $message = "Maximum 7 working days";
                            if (!Db::getInstance()->execute("INSERT INTO `"._DB_PREFIX_."carrier_lang` (`id_carrier`, `id_shop`, `id_lang`, `delay`) VALUES (".$_PACKLINK_CARRIER_ID.", ".$shop['id'].", ".$lang['id'].", '".$message."');")) return false;
                        }
                    }

                    if (!Db::getInstance()->execute("INSERT INTO `"._DB_PREFIX_."carrier_shop` (`id_carrier`, `id_shop`) VALUES (".$_PACKLINK_CARRIER_ID.", ".$shop['id'].");")) return false;
                }
            }

            // Refill zones

            if ($zones_ps = Db::getInstance()->executeS("SELECT DISTINCT id_zone AS 'id' FROM `"._DB_PREFIX_.'zone` WHERE 1')){
                foreach ($zones_ps as $zone_ps){
                    if (!Db::getInstance()->execute("INSERT INTO `"._DB_PREFIX_."carrier_zone` (`id_carrier`, `id_zone`) VALUES (".$_PACKLINK_CARRIER_ID.", ".$zone_ps['id'].");")) return false;
                }
            }

            // Refill groups

            if ($groups_ps = Db::getInstance()->executeS("SELECT DISTINCT id_group AS 'id' FROM `"._DB_PREFIX_.'group` WHERE 1')){
                foreach ($groups_ps as $group_ps){
                    if (!Db::getInstance()->execute("INSERT INTO `"._DB_PREFIX_."carrier_group` (`id_carrier`, `id_group`) VALUES (".$_PACKLINK_CARRIER_ID.", ".$group_ps['id'].");")) return false;
                }
            }

            if (!Db::getInstance()->execute("UPDATE `"._DB_PREFIX_."carrier` SET id_reference = '".$_PACKLINK_CARRIER_ID."' WHERE id_carrier = '".$_PACKLINK_CARRIER_ID."'")) return false;
        }
        
        // ---------------------------------------------------------------------
        // Insert States of Spain.
        // ---------------------------------------------------------------------       
       
        if (!Db::getInstance()->execute("UPDATE `"._DB_PREFIX_."country` SET `contains_states` = '1' WHERE `ps_country`.`id_country` =6;")) return false;
        if (!Db::getInstance()->execute("DELETE FROM `"._DB_PREFIX_."state` WHERE id_country = '6';")) return false;
        $query = "INSERT INTO `"._DB_PREFIX_."state` (`id_country`, `id_zone`, `name`, `iso_code`, `tax_behavior`, `active`) VALUES
                 (6, 1, 'ARABA / ÁLAVA', 'ES-VI', 0, 1),
                 (6, 1, 'ALBACETE', 'ES-AB', 0, 1),
                 (6, 1, 'ALICANTE / ALACANT', 'ES-A', 0, 1),
                 (6, 1, 'ALMERÍA', 'ES-AL', 0, 1),
                 (6, 1, 'ÁVILA', 'ES-AV', 0, 1),
                 (6, 1, 'BADAJOZ', 'ES-BA', 0, 1),
                 (6, 1, 'BALEARS, ILLES', 'ES-PM', 0, 1),
                 (6, 1, 'BARCELONA', 'ES-B', 0, 1),
                 (6, 1, 'BURGOS', 'ES-BU', 0, 1),
                 (6, 1, 'CÁCERES', 'ES-CC', 0, 1),
                 (6, 1, 'CÁDIZ', 'ES-CA', 0, 1),
                 (6, 1, 'CASTELLÓN / CASTELLÓ', 'ES-CS', 0, 1),
                 (6, 1, 'CIUDAD REAL', 'ES-CR', 0, 1),
                 (6, 1, 'CÓRDOBA', 'ES-CO', 0, 1),
                 (6, 1, 'CORUÑA, A', 'ES-C', 0, 1),
                 (6, 1, 'CUENCA', 'ES-CU', 0, 1),
                 (6, 1, 'GIRONA', 'ES-GI', 0, 1),
                 (6, 1, 'GRANADA', 'ES-GR', 0, 1),
                 (6, 1, 'GUADALAJARA', 'ES-GU', 0, 1),
                 (6, 1, 'GIPUZKOA', 'ES-SS', 0, 1),
                 (6, 1, 'HUELVA', 'ES-H', 0, 1),
                 (6, 1, 'HUESCA', 'ES-HU', 0, 1),
                 (6, 1, 'JAÉN', 'ES-J', 0, 1),
                 (6, 1, 'LEÓN', 'ES-LE', 0, 1),
                 (6, 1, 'LLEIDA', 'ES-L', 0, 1),
                 (6, 1, 'LA RIOJA', 'ES-LO', 0, 1),
                 (6, 1, 'LUGO', 'ES-LU', 0, 1),
                 (6, 1, 'MADRID', 'ES-M', 0, 1),
                 (6, 1, 'MÁLAGA', 'ES-MA', 0, 1),
                 (6, 1, 'MURCIA', 'ES-MU', 0, 1),
                 (6, 1, 'NAVARRA', 'ES-NA', 0, 1),
                 (6, 1, 'OURENSE', 'ES-OR', 0, 1),
                 (6, 1, 'ASTURIAS', 'ES-O', 0, 1),
                 (6, 1, 'PALENCIA', 'ES-P', 0, 1),
                 (6, 1, 'PALMAS, LAS', 'ES-GC', 0, 1),
                 (6, 1, 'PONTEVEDRA', 'ES-PO', 0, 1),
                 (6, 1, 'SALAMANCA', 'ES-SA', 0, 1),
                 (6, 1, 'SANTA CRUZ DE TENERIFE', 'ES-TF', 0, 1),
                 (6, 1, 'CANTABRIA', 'ES-S', 0, 1),
                 (6, 1, 'SEGOVIA', 'ES-SG', 0, 1),
                 (6, 1, 'SEVILLA', 'ES-SE', 0, 1),
                 (6, 1, 'SORIA', 'ES-SO', 0, 1),
                 (6, 1, 'TARRAGONA', 'ES-T', 0, 1),
                 (6, 1, 'TERUEL', 'ES-TE', 0, 1),
                 (6, 1, 'TOLEDO', 'ES-TO', 0, 1),
                 (6, 1, 'VALENCIA / VALÉNCIA', 'ES-V', 0, 1),
                 (6, 1, 'VALLADOLID', 'ES-VA', 0, 1),
                 (6, 1, 'BIZKAIA', 'ES-BI', 0, 1),
                 (6, 1, 'ZAMORA', 'ES-ZA', 0, 1),
                 (6, 1, 'ZARAGOZA', 'ES-Z', 0, 1),
                 (6, 1, 'CEUTA', 'ES-CE', 0, 1),
                 (6, 1, 'MELILLA', 'ES-ML', 0, 1);";
       
        if (!Db::getInstance()->execute($query)) return false;
       
        // ---------------------------------------------------------------------
        // Insert new Range Weight with 0 to 10000 values.
        // ---------------------------------------------------------------------
       
        if (!Db::getInstance()->execute("DELETE FROM `"._DB_PREFIX_."range_weight` WHERE id_carrier = '".$_PACKLINK_CARRIER_ID."';")) return false;
        if (!Db::getInstance()->execute("INSERT INTO `"._DB_PREFIX_."range_weight` (`id_carrier`, `delimiter1`, `delimiter2`) VALUES ('".$_PACKLINK_CARRIER_ID."', 0.000000, 10000.000000);")) return false;
        $_PACKLINK_RANGE_WEIGHT = Db::getInstance()->Insert_ID();
       
        // ---------------------------------------------------------------------
        // Insert new Range Price with 0 to 10000 values.
        // ---------------------------------------------------------------------
        
        if (!Db::getInstance()->execute("DELETE FROM `"._DB_PREFIX_."range_price` WHERE id_carrier = '".$_PACKLINK_CARRIER_ID."';")) return false;
        if (!Db::getInstance()->execute("INSERT INTO `"._DB_PREFIX_."range_price` (`id_carrier`, `delimiter1`, `delimiter2`) VALUES ('".$_PACKLINK_CARRIER_ID."', 0.000000, 10000.000000);")) return false;
        $_PACKLINK_RANGE_PRICE = Db::getInstance()->Insert_ID();
       
        // ---------------------------------------------------------------------
        // Insert new taxes of Packlink carrier.
        // ---------------------------------------------------------------------
       
        $queryTaxCarrier  = "";
        if ($langs = Db::getInstance()->executeS("SELECT DISTINCT id_lang AS 'id', iso_code FROM `"._DB_PREFIX_.'lang` WHERE 1')){
            foreach ($langs as $lang){
                if (!Db::getInstance()->execute("DELETE FROM `"._DB_PREFIX_."delivery` WHERE id_carrier = '".$_PACKLINK_CARRIER_ID."';")) return false;
                if (!Db::getInstance()->execute("INSERT INTO `"._DB_PREFIX_."delivery` (`id_shop`, `id_shop_group`, `id_carrier`, `id_range_price`, `id_range_weight`, `id_zone`, `price`) VALUES (1, 1, '".$_PACKLINK_CARRIER_ID."', '".$_PACKLINK_RANGE_PRICE."', '".$_PACKLINK_RANGE_WEIGHT."', '".$lang['id']."', '0.000000');")) return false;
            }
        }
       
        // ---------------------------------------------------------------------
        // Update all the products to can to be shipped by Packlink service.
        // ---------------------------------------------------------------------
       
        $aux = Db::getInstance()->executeS("SHOW TABLES LIKE  '"._DB_PREFIX_."product_carrier_no_pl'");
        if(count($aux) != 0 && count($aux[0]) != 0){
            // Not to do anything
        } else {
            $query = "CREATE TABLE `"._DB_PREFIX_."product_carrier_no_pl` LIKE `"._DB_PREFIX_."product_carrier`;";

            if (!Db::getInstance()->execute($query)) return false;
            if (!Db::getInstance()->execute("INSERT INTO `"._DB_PREFIX_."product_carrier_no_pl` SELECT * FROM `"._DB_PREFIX_."product_carrier` ;")) return false;
            if (!Db::getInstance()->execute("DELETE FROM `"._DB_PREFIX_."product_carrier` WHERE 1;")) return false;

            $queries = array();
            if ($products = Db::getInstance()->executeS("SELECT DISTINCT id_product AS 'id' FROM `"._DB_PREFIX_.'product` WHERE 1')){
                foreach ($products as $product){
                    if ($shops = Db::getInstance()->executeS("SELECT DISTINCT id_shop AS 'id' FROM `"._DB_PREFIX_.'shop` WHERE 1')){
                        foreach ($shops as $shop){
                            if (!Db::getInstance()->execute("INSERT INTO `"._DB_PREFIX_."product_carrier` (`id_product`, `id_carrier_reference`, `id_shop`) VALUES (".$product["id"].", ".$_PACKLINK_CARRIER_ID.", ".$shop['id'].");")) return false;
                        }
                    }
                }
            }
            if (!Db::getInstance()->execute("INSERT INTO `"._DB_PREFIX_."product_carrier` SELECT * FROM `"._DB_PREFIX_."product_carrier_no_pl` ;")) return false;
        }
        
        // ---------------------------------------------------------------------
        // Create the orders table of Packlink.
        // ---------------------------------------------------------------------
       
        $query = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."packlink_orders` (
                 `id_order` int(10) unsigned NOT NULL,
                 `reference` varchar(255) DEFAULT NULL,
                 `id_carrier` int(10) unsigned NOT NULL,
                 `price` float(10,2) NOT NULL,
                 `tax` int(5) NOT NULL,
                 `hash` varchar(255) NOT NULL,
                 `created_at` datetime NOT NULL,
                 `updated_at` datetime NOT NULL,
                 `is_ok` int(10) unsigned NOT NULL,
                 `status` decimal(1,0) NOT NULL DEFAULT '1',
                  PRIMARY KEY (`id_order`),
                  KEY `date_add` (`created_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
       
        if (!Db::getInstance()->execute($query)) return false;
       
        // ---------------------------------------------------------------------
        // Create the configuration table of Packlink.
        // ---------------------------------------------------------------------
       
        $query = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."packlink_config` (
                    `key` varchar(50) NOT NULL,
                    `value` varchar(255) NOT NULL,
                    PRIMARY KEY (`key`)
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
       
        if (!Db::getInstance()->execute($query)) return false;
       
        // SOLO PARA PRUEBAS
        $query = "INSERT INTO `"._DB_PREFIX_."packlink_config` (`key`, `value`) VALUES
                ('_POST_CODE_SHOP', ''),
                ('_ID_COUNTRY_SHOP', '  '),
                ('_ADDRESS_SHOP', ''),
                ('_TOWN_SHOP', ''),
                ('_PROVINCE_SHOP', ''),
                ('_LANDLINE_SHOP', ''),
                ('_FAX_SHOP', ''),
                ('_OTHER_PHONE_SHOP', ''),
                ('username', ''),
                ('password', ''),
                ('apikey', ''),
                ('url_packlink', 'http://api.packlink.es'),
                ('_PERCENTAGE_ADJUST', '0'),
                ('_ACTIVE_TAB', '0'),
                ('_ENABLE_TRACKING', '1'),
                ('_ENABLE_DRAGDROP', '1'),
                ('_ENABLE_CTRL_MEASUREMENTS', '1'),
                ('_ENABLE_CTRL_WEIGHTS', '1'),
                ('_ENABLE_ANIMATION', '1'),
                ('_ENABLE_USER_CHOOSE', '1'),
                ('_FREE_SHIPMENT_FROM', '0'),
                ('_INVOICE_POST_CODE_SHOP', ''),
                ('_INVOICE_ID_COUNTRY_SHOP', '0'),
                ('_INVOICE_ADDRESS_SHOP', ''),
                ('_INVOICE_TOWN_SHOP', ''),
                ('_INVOICE_PROVINCE_SHOP', ''),
                ('_INVOICE_LANDLINE_SHOP', ''),
                ('_INVOICE_FAX_SHOP', ''),
                ('_INVOICE_OTHER_PHONE_SHOP', '');";

        if (!Db::getInstance()->execute($query)) return false;    
       
        // ---------------------------------------------------------------------
        // Create the predefined boxes table of Packlink.
        // ---------------------------------------------------------------------
       
        $query = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."packlink_boxes` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `model` varchar(128) CHARACTER SET utf8 NOT NULL,
                    `description` varchar(1024) CHARACTER SET utf8 NOT NULL,
                    `width` decimal(10,1) NOT NULL,
                    `height` decimal(10,1) NOT NULL,
                    `depth` decimal(10,1) NOT NULL,
                    `weight` decimal(10,1) NOT NULL DEFAULT '0.0',
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `Model` (`model`)
                  ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
       
        if (!Db::getInstance()->execute($query)) return false;
       
        // Inserts
        $query = "INSERT INTO `"._DB_PREFIX_."packlink_boxes` (`id`, `model`, `description`, `width`, `height`, `depth`, `weight`) VALUES
                  (1, 'FedEx® 10kg Box', 'Compatible con el servicio FedEx International Priority de hasta 10 kg.', '29.0', '11.0', '9.0', '10.0'),
                  (2, 'FedEx® 25kg Box', 'Compatible con el servicio FedEx International Priority de hasta 25 kg.', '51.0', '33.0', '43.0', '25.0'),
                  (3, 'UPS 10 Kg', 'Utilice esta caja para los envíos de UPS Worldwide Express. Aguanta hasta 10 kg (22 libras). Se incluye la documentación de exportación. Los cargos se basan en la tarifa base y la zona.', '41.0', '33.5', '26.5', '10.0'),
                  (4, 'UPS 25 Kg', 'Utilice esta caja para los envíos de UPS Worldwide Express®. Aguanta hasta 25 kg (55 libras). Se incluye la documentación de exportación. Los cargos se basan en la tarifa base y la zona.', '48.4', '43.3', '35.0', '25.0'),
                  (5, 'UPS Express Tube', 'Nuestro tubo de envío triangular acomoda y protege documentos más grandes que es preferible enrollar en lugar de doblar. Utilice el tubo para enviar cianotipos, cuadros, mapas, dibujos o carteles. Los cargos se basan en el peso facturable y la zona. La documentación de exportación es obligatoria excepto si se trata de envíos domésticos o si se envían bienes en libre circulación dentro de la UE.', '997.0', '19.0', '16.5', '0.0'),
                  (6, 'UPS Express Box', 'UPS Express Box es adecuado para una amplia gama de artículos, incluidos los listados de computador y los componentes electrónicos. Los cargos se basan en el peso y la zona. La documentación de exportación es obligatoria.', '31.5', '9.5', '46.0', '15.0'),
                  (7, 'TROQUELADA-S', 'Caja estándar para envios pequeña', '150.0', '120.0', '60.0', '0.0'),
                  (8, 'TROQUELADA-M', 'Caja estándar para envios mediana', '200.0', '100.0', '60.0', '0.0'),
                  (9, 'TROQUELADA-L', 'Caja estándar para envios grande', '250.0', '180.0', '80.0', '0.0'),
                  (10, 'ARMARIO-S', 'Caja estándar típica de armarios pequeña', '500.0', '340.0', '1000.0', '0.0'),
                  (11, 'ARMARIO-M', 'Caja estándar típica de armarios mediana', '500.0', '500.0', '1000.0', '0.0'),
                  (12, 'ARMARIO-G', 'Caja estándar típica de armarios grande', '500.0', '500.0', '1300.0', '0.0'),
                  (13, 'BOTTLE-1', 'Embalaje especial para enviar una botella de manera protegida. Se adapta a cualquier tipo de botella y es fácil de almacenar y montar.', '12.3', '39.5', '12.3', '0.0'),
                  (14, 'BOTTLE-3', 'Embalaje especial para enviar tres botellas de manera protegida. Se adapta a cualquier tipo de botella y es fácil de almacenar y montar.', '38.5', '39.5', '12.3', '0.0'),
                  (15, 'BOTTLE-6', 'Embalaje especial para enviar seis botellas de manera protegida. Se adapta a cualquier tipo de botella y es fácil de almacenar y montar.', '38.5', '39.5', '26.7', '0.0'),
                  (16, 'MUDANZAS-S', 'Esta caja tiene el tamaño adecuado para el transporte de  vajillas, libros, CDs, y otros elementos de cierto peso.', '400.0', '295.0', '350.0', '2.0'),
                  (17, 'MUDANZAS-M', 'Esta caja tiene el tamaño adecuado para el transporte de ropa, utensilios de cocina voluminosos.', '500.0', '395.0', '450.0', '3.0'),
                  (18, 'MUDANZAS-L', 'Esta caja tiene el tamaño adecuado para el transporte de mantas, edredones, etc.', '600.0', '495.0', '550.0', '5.0');";
                 
        if (!Db::getInstance()->execute($query)) return false;
       
        // ---------------------------------------------------------------------
        // Create the services table of Packlink.
        // ---------------------------------------------------------------------
       
        $query = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."packlink_services` (
                    `shipper` varchar(128) NOT NULL,
                    `service` varchar(128) NOT NULL,
                    `shipper_id` int(11) NOT NULL,
                    `service_id` int(11) NOT NULL,
                    `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`shipper_id`,`service_id`)
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        if (!Db::getInstance()->execute($query)) return false;
       
        // Inserts
        $query = "INSERT INTO `"._DB_PREFIX_."packlink_services` (`shipper`, `service`, `shipper_id`, `service_id`, `last_update`) VALUES
                    ('MRW', 'Urgente 19', 5, 1, '2013-09-10 10:44:45'),
                    ('CHRONOEXPRÉS', 'Internacional', 4, 2, '2013-09-10 10:44:45'),
                    ('SEUR', '24', 1, 3, '2013-09-10 10:44:45'),
                    ('CHRONOEXPRÉS', 'Chrono 24', 4, 4, '2013-09-10 10:44:45'),
                    ('SEUR', 'Classic', 1, 6, '2013-09-10 10:44:45'),
                    ('UPS', 'Express Saver', 2, 13, '2013-09-10 10:44:45'),
                    ('Envialia', '24H', 6, 18, '2013-09-10 10:44:45'),
                    ('Envialia', '48/72H', 6, 19, '2013-09-10 10:44:45'),
                    ('UPS', 'Standard Europa', 2, 20, '2013-09-10 10:44:45'),
                    ('UPS', 'Standard Doméstico', 2, 22, '2013-09-10 10:44:45'),
                    ('ASM', '24', 8, 23, '2013-09-10 10:44:45'),
                    ('ASM', 'Economy', 8, 24, '2013-09-10 10:44:45'),
                    ('UPS', 'Express Intnal', 2, 27, '2013-09-10 10:44:45'),
                    ('GLS', 'Euro Business Parcel', 10, 28, '2013-09-10 10:44:45'),
                    ('GLS', 'Euro Business Parcel', 10, 30, '2013-09-10 10:44:45'),
                    ('CHRONOEXPRÉS', 'Chrono 10', 4, 33, '2013-09-10 10:44:45'),
                    ('CHRONOEXPRÉS', 'Canarias Interislas', 4, 34, '2013-09-10 10:44:45'),
                    ('CHRONOEXPRÉS', 'Baleares Interislas', 4, 35, '2013-09-10 10:44:45'),
                    ('CHRONOEXPRÉS', 'Chrono 24', 4, 36, '2013-09-10 10:44:45'),
                    ('CHRONOEXPRÉS', 'Chrono Marítimo', 4, 37, '2013-09-10 10:44:45'),
                    ('CHRONOEXPRÉS', 'Chrono 24', 4, 38, '2013-09-10 10:44:45'),
                    ('CHRONOEXPRÉS', 'Chrono Marítimo', 4, 39, '2013-09-10 10:44:45'),
                    ('CHRONOEXPRÉS', 'Chrono Portugal', 4, 40, '2013-09-10 10:44:45'),
                    ('GLS', 'Euro Business Parcel', 10, 41, '2013-09-10 10:44:45'),
                    ('TNT', 'Economy Europa', 12, 42, '2013-09-10 10:44:45'),
                    ('TNT', 'Economy Europa', 12, 43, '2013-09-10 10:44:45'),
                    ('TNT', 'Economy Mundo', 12, 44, '2013-09-10 10:44:45'),
                    ('TNT', 'Economy Mundo', 12, 45, '2013-09-10 10:44:45'),
                    ('TNT', 'Express', 12, 46, '2013-09-10 10:44:45'),
                    ('UPS', 'Standard Europa', 2, 47, '2013-09-10 10:44:45'),
                    ('UPS', 'Express Saver', 2, 48, '2013-09-10 10:44:45'),
                    ('UPS', 'Express', 2, 49, '2013-09-10 10:44:45'),
                    ('CORREOS', '48/72H', 13, 50, '2013-09-10 10:44:45'),
                    ('Tourline', '48 Horas', 14, 51, '2013-09-10 10:44:45'),
                    ('CHRONOEXPRÉS', 'Chrono 24', 4, 54, '2013-09-10 10:44:45'),
                    ('Keavo', 'International Priority', 15, 56, '2013-09-10 10:44:45'),
                    ('Tourline', 'Última Hora', 14, 57, '2013-09-10 10:44:45'),
                    ('Keavo', 'Standard', 15, 58, '2013-09-10 10:44:45'),
                    ('Keavo', '48/72h', 15, 59, '2013-09-10 10:44:45'),
                    ('GLS', 'Euro Business', 10, 60, '2013-09-10 10:44:45'),
                    ('GLS', 'Euro Business', 10, 61, '2013-09-10 10:44:45'),
                    ('ZELERIS', '10', 16, 62, '2013-09-10 10:44:45'),
                    ('BUYTRAGO', 'Baleares', 17, 63, '2013-09-10 10:44:45'),
                    ('ZELERIS', '48 horas', 16, 64, '2013-09-10 10:44:45'),
                    ('SEUR', 'Europa Especial Verano 2013', 1, 65, '2013-09-10 10:44:45'),
                    ('GLS', 'Europa Especial Verano 2013', 10, 66, '2013-09-10 10:44:45'),
                    ('ZELERIS', 'Internacional Aéreo', 16, 67, '2013-09-10 10:44:45'),
                    ('BUYTRAGO', '48 H', 17, 68, '2013-09-10 10:44:45'),
                    ('ZELERIS', 'Internacional Terrestre', 16, 69, '2013-09-10 10:44:45'),
                    ('ZELERIS', 'Día Siguiente', 16, 70, '2013-09-10 10:44:45');";
        if (!Db::getInstance()->execute($query)) return false;
       
        // ---------------------------------------------------------------------
        // Create the statuses table of Packlink.
        // ---------------------------------------------------------------------
       
        $query = "CREATE TABLE IF NOT EXISTS `ps_packlink_status` (
                    `id` int(11) NOT NULL,
                    `name` varchar(64) NOT NULL,
                    `lang` varchar(32) NOT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `unique` (`name`,`lang`)
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        if (!Db::getInstance()->execute($query)) return false;
       
        // Inserts
        $query = "INSERT INTO `"._DB_PREFIX_."packlink_status` (`id`, `name`, `lang`) VALUES
                (1, 'Akzeptiert', 'de'),
                (2, 'Accepted', 'en'),
                (3, 'Aceptado', 'es'),
                (4, 'Collaged', 'de'),
                (5, 'Collaged', 'en'),
                (6, 'Encolado', 'es'),
                (7, 'Verarbeitung', 'de'),
                (8, 'In Progress', 'en'),
                (9, 'En Progreso', 'es'),
                (10, 'Abgeschlossen', 'de'),
                (11, 'Completed', 'en'),
                (12, 'Completado', 'es'),
                (13, 'Fehler', 'de'),
                (14, 'Error', 'en'),
                (15, 'Error', 'es');";
        if (!Db::getInstance()->execute($query)) return false;
       
        // ---------------------------------------------------------------------
        // Update Configuration variables.
        // ---------------------------------------------------------------------
       
       
        Configuration::updateValue('PS_SHIPPING_HANDLING_NO_PL', Configuration::get('PS_SHIPPING_HANDLING'));
        Configuration::updateValue('PS_SHIPPING_HANDLING', "1");
        Configuration::updateValue('PS_SHIPPING_METHOD_NO_PL', Configuration::get('PS_SHIPPING_METHOD'));
        Configuration::updateValue('PS_SHIPPING_METHOD', "0");