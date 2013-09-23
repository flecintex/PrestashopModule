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
                    file_put_contents(_PS_OVERRIDE_DIR_.$dirName."/".$class.".php", str_replace("#class#", $class, $strOverrideClass));
                    chmod(_PS_OVERRIDE_DIR_.$dirName."/".$class.".php", 0777);
                } else {
                    $contentOvr = file_get_contents(_PS_OVERRIDE_DIR_.$dirName."/".$class.".php");
                    $contentMod = file_get_contents(_PS_MODULE_DIR_.$this->module_name."/overrides/".$dirName."/".$class.".php");
                    
                    // Deleting header of class
                    $posi = strpos($contentOvr, $class."Core\n{")+strlen($class."Core\n{");
                    $contentOvr = substr($contentOvr, $posi);
                    $posi = strpos($contentMod, $class."Core\n{")+strlen($class."Core\n{");
                    $contentMod = substr($contentMod, $posi);
                    
                    // Deleting footer of class
                    $contentOvr = substr($contentOvr, 0, strrpos($contentOvr, "}"));
                    $contentMod = substr($contentMod, 0, strrpos($contentMod, "}"));
                    
                    $contentOvr_arr = explode("function", $contentOvr);
                    $contentMod_arr = explode("function", $contentMod);
                    for($x = 0; $x < count($contentOvr_arr); $x++){
                        $function = trim(substr($contentMod_arr[$x], 0, strpos($contentMod_arr[$x], "(")));
                        if(strpos($contentOvr, $function) !== false){
                            $dfFnOvr = $this->getDefinitionFN($contentOvr, $function);
                            $cnFnOvr = $this->getContentFN($contentOvr_arr[$x]);
                            $dfFnMod = $this->getDefinitionFN($contentMod, $function);
                            $cnFnMod = $this->getContentFN($contentMod_arr[$x]);
                            
                            // Habría que poner condiciones concretas en cada función que se parseé para que funcione fino 
                            
                            // Now create the new spec.
                            $codeIf    = "\n\t\t// --------------------------------------------------------------------------";
                            $codeIf   .= "\n\t\t// - Start modify of Packlink -----------------------------------------------";
                            $codeIf   .= "\n\t\t// --------------------------------------------------------------------------";
                            $codeIf   .= "\n\t\t".'if ($id_carrier == packlink::getPacklinkID() ){';
                            $codeElse  = "\n\t\t// --------------------------------------------------------------------------";
                            $codeElse .= "\n\t\t// - End modify of Packlink -------------------------------------------------";
                            $codeElse .= "\n\t\t// --------------------------------------------------------------------------";
                            $codeElse .= "\n\t\t".'} else {'."\n";
                            
                            $fn = "\t".$dfFnOvr."{\t\t".$codeIf."\n".$cnFnMod.$codeElse.$cnFnOvr."\n\t\t}\n}\n";
                            echo $fn;
                            echo "--------------------------------------------------------------------------------------------------------------\n";
                            echo "--------------------------------------------------------------------------------------------------------------\n";
                            echo "--------------------------------------------------------------------------------------------------------------\n";
                        }
                    }
                }
            }
            die();
            if (!is_dir($this->getLocalPath().'overrides'))
                    return true;

            $result = true;
            foreach (Tools::scandir($this->getLocalPath().'overrides', 'php', '', true) as $file)
            {
                    $class = basename($file, '.php');
                    var_dump($class);
                    if (Autoload::getInstance()->getClassPath($class.'Core'))
                            $result &= $this->doOverride($class);
            }

           
        }
        
        /*
        // Rename old files overriden
        rename(_PS_OVERRIDE_DIR_."classes/Cart.php", _PS_OVERRIDE_DIR_."classes/Cart_no_pl.php");
        rename(_PS_OVERRIDE_DIR_."classes/Mail.php", _PS_OVERRIDE_DIR_."classes/Mail_no_pl.php");
        rename(_PS_OVERRIDE_DIR_."classes/PaymentModule.php", _PS_OVERRIDE_DIR_."classes/PaymentModule_no_pl.php");
        
        // Rights of old files
        chmod(_PS_OVERRIDE_DIR_."classes/Cart_no_pl.php", 0777);
        chmod(_PS_OVERRIDE_DIR_."classes/Mail_no_pl.php", 0777);
        chmod(_PS_OVERRIDE_DIR_."classes/PaymentModule_no_pl.php", 0777);
        
        // Coping the new files
        copy(_PS_MODULE_DIR_.$this->module_name."/overrides/classes/Cart.php", _PS_OVERRIDE_DIR_."classes/Cart.php");
        copy(_PS_MODULE_DIR_.$this->module_name."/overrides/classes/Mail.php", _PS_OVERRIDE_DIR_."classes/Mail.php");
        copy(_PS_MODULE_DIR_.$this->module_name."/overrides/classes/PaymentModule.php", _PS_OVERRIDE_DIR_."classes/PaymentModule.php");
        
        // Rights of new files
        chmod(_PS_OVERRIDE_DIR_."classes/Cart.php", 0777);
        chmod(_PS_OVERRIDE_DIR_."classes/Mail.php", 0777);
        chmod(_PS_OVERRIDE_DIR_."classes/PaymentModule.php", 0777);
        */
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
      /* var_dump($_DIR_BASE);
       var_dump($_DIR_BASE_MODULE);
       var_dump($_OVERRIDE_CLS);
       var_dump($_OVERRIDE_CLS_CTRL);
       var_dump($_OVERRIDE_CTRL_FRNT);
       die();*/
	        
        // ---------------------------------------------------------------------
	// Insert new Carrier named Packlink.
        // ---------------------------------------------------------------------

        if (!Db::getInstance()->execute("INSERT INTO `"._DB_PREFIX_."carrier` (`id_reference`, `id_tax_rules_group`, `name`, `url`, `active`, `deleted`, `shipping_handling`, `range_behavior`, `is_module`, `is_free`, `shipping_external`, `need_range`, `external_module_name`, `shipping_method`, `position`, `max_width`, `max_height`, `max_depth`, `max_weight`, `grade`) VALUES (0, 0, 'Packlink', '', 1, 0, 1, 0, 0, 0, 0, 0, '', 2, 2, 0, 0, 0, 0, 0);")) return false;
        $_PACKLINK_CARRIER_ID = Db::getInstance()->getValue("SELECT id_carrier FROM `"._DB_PREFIX_."carrier` WHERE UCASE(name) LIKE '%PACKLINK%';");

        // Refill languages
        
        if ($shops = Db::getInstance()->ExecuteS("SELECT DISTINCT id_shop AS 'id' FROM `"._DB_PREFIX_.'shop` WHERE 1')){
            foreach ($shops as $shop){
                if ($langs = Db::getInstance()->ExecuteS("SELECT DISTINCT id_lang AS 'id', iso_code FROM `"._DB_PREFIX_.'lang` WHERE 1')){
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
        
        if ($zones_ps = Db::getInstance()->ExecuteS("SELECT DISTINCT id_zone AS 'id' FROM `"._DB_PREFIX_.'zone` WHERE 1')){
            foreach ($zones_ps as $zone_ps){
                if (!Db::getInstance()->execute("INSERT INTO `"._DB_PREFIX_."carrier_zone` (`id_carrier`, `id_zone`) VALUES (".$_PACKLINK_CARRIER_ID.", ".$zone_ps['id'].");")) return false;
            }
        }
        
        // Refill groups
        
        if ($groups_ps = Db::getInstance()->ExecuteS("SELECT DISTINCT id_zone AS 'id' FROM `"._DB_PREFIX_.'zone` WHERE 1')){
            foreach ($groups_ps as $$group_ps){
                if (!Db::getInstance()->execute("INSERT INTO `"._DB_PREFIX_."carrier_group` (`id_carrier`, `id_group`) VALUES (".$_PACKLINK_CARRIER_ID.", ".$group_ps['id'].");")) return false;
            }
        }
        
        if (!Db::getInstance()->execute("UPDATE `"._DB_PREFIX_."carrier` SET id_reference = '".$_PACKLINK_CARRIER_ID."' WHERE id_carrier = '".$_PACKLINK_CARRIER_ID."'")) return false;
        
        // ---------------------------------------------------------------------
	// Insert States of Spain.
        // ---------------------------------------------------------------------        
        
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
        
        if (!Db::getInstance()->execute("INSERT INTO `"._DB_PREFIX_."range_weight` (`id_carrier`, `delimiter1`, `delimiter2`) VALUES ('".$_PACKLINK_CARRIER_ID."', 0.000000, 10000.000000);")) return false;
        
        // ---------------------------------------------------------------------
	// Insert new Range Price with 0 to 10000 values.
        // ---------------------------------------------------------------------
        
        if (!Db::getInstance()->execute("INSERT INTO `"._DB_PREFIX_."range_price` (`id_carrier`, `delimiter1`, `delimiter2`) VALUES ('".$_PACKLINK_CARRIER_ID."', 0.000000, 10000.000000);")) return false;
        
        // ---------------------------------------------------------------------
	// Update all the products to can to be shipped by Packlink service.
        // ---------------------------------------------------------------------
        
        $query = "CREATE TABLE `"._DB_PREFIX_."product_carrier_no_pl` LIKE `"._DB_PREFIX_."product_carrier`;";
        
        if (!Db::getInstance()->execute($query)) return false;
        if (!Db::getInstance()->execute("INSERT INTO `"._DB_PREFIX_."product_carrier_no_pl` SELECT * FROM `"._DB_PREFIX_."product_carrier` ;")) return false;
        if (!Db::getInstance()->execute("DELETE FROM `"._DB_PREFIX_."product_carrier` WHERE 1;")) return false;

        $queries = array();
        if ($products = Db::getInstance()->ExecuteS("SELECT DISTINCT id_product AS 'id' FROM `"._DB_PREFIX_.'product` WHERE 1')){
            foreach ($products as $product){
                if ($shops = Db::getInstance()->ExecuteS("SELECT DISTINCT id_shop AS 'id' FROM `"._DB_PREFIX_.'shop` WHERE 1')){
                    foreach ($shops as $shop){
                        if (!Db::getInstance()->execute("INSERT INTO `"._DB_PREFIX_."product_carrier` (`id_product`, `id_carrier_reference`, `id_shop`) VALUES (".$product["id"].", ".$_PACKLINK_CARRIER_ID.", ".$shop['id'].");")) return false;
                    }
                }
            }
        }
        if (!Db::getInstance()->execute("INSERT INTO `"._DB_PREFIX_."product_carrier` SELECT * FROM `"._DB_PREFIX_."product_carrier_no_pl` ;")) return false;
        
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
                    `value` varchar(255) NOT NULL
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        
        if (!Db::getInstance()->execute($query)) return false;
        
        // SOLO PARA PRUEBAS
        $query = "INSERT INTO `"._DB_PREFIX_."packlink_config` (`key`, `value`) VALUES
                    ('_POST_CODE_SHOP', '08080'),
                    ('_ID_COUNTRY_SHOP', '6'),
                    ('_ADDRESS_SHOP', 'Plaza Mayor 3 - Local 1'),
                    ('_TOWN_SHOP', 'Barcelona'),
                    ('_PROVINCE_SHOP', 'Barcelona'),
                    ('_LANDLINE_SHOP', '933235689'),
                    ('_FAX_SHOP', '933235688'),
                    ('_OTHER_PHONE_SHOP', '631002003'),
                    ('username', 'root'),
                    ('password', 'c7576eef44d51ac9800e77d9a47cfa7db014dc3c'),
                    ('apikey', 'DL-11851411360748425-8189224'),
                    ('url_packlink', 'http://api.packlink.es'),
                    ('_PERCENTAGE_ADJUST', '5'),
                    ('_ACTIVE_TAB', '4'),
                    ('_ENABLE_TRACKING', '1'),
                    ('_ENABLE_DRAGDROP', '1'),
                    ('_ENABLE_CTRL_MEASUREMENTS', '1'),
                    ('_ENABLE_CTRL_WEIGHTS', '1'),
                    ('_ENABLE_ANIMATION', '1'),
                    ('_ENABLE_USER_CHOOSE', '1');";

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
                    PRIMARY KEY (`id`)
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
                  (12, 'ARMARIO-M', 'Caja estándar típica de armarios grande', '500.0', '500.0', '1300.0', '0.0'),
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
                    `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
";
        if (!Db::getInstance()->execute($query)) return false; 
        
        // Inserts
        $query = "INSERT INTO `"._DB_PREFIX_."packlink_services` (`shipper`, `service`, `shipper_id`, `service_id`, `last_update`) VALUES
                ('MRW', 'Urgente 19', 5, 1, '2013-04-30 11:32:10'),
                ('CHRONOEXPRÉS', 'Internacional', 4, 2, '2013-04-30 11:32:10'),
                ('SEUR', '24', 1, 3, '2013-04-30 11:32:10'),
                ('CHRONOEXPRÉS', 'Chrono 24', 4, 4, '2013-04-30 11:32:10'),
                ('SEUR', '24 Aéreo', 1, 5, '2013-04-30 11:32:10'),
                ('SEUR', 'Classic', 1, 6, '2013-04-30 11:32:10'),
                ('SEUR', 'Courier', 1, 7, '2013-04-30 11:32:10'),
                ('Fedex', 'International Priority', 3, 9, '2013-04-30 11:32:10'),
                ('Fedex', 'International Economy', 3, 10, '2013-04-30 11:32:10'),
                ('Fedex', 'International Economy', 3, 11, '2013-04-30 11:32:10'),
                ('UPS', 'Express Saver', 2, 13, '2013-04-30 11:32:10'),
                ('UPS', 'Expedited', 2, 15, '2013-04-30 11:32:10'),
                ('Fedex', 'International Priority', 3, 17, '2013-04-30 11:32:10'),
                ('Envialia', '24H', 6, 18, '2013-04-30 11:32:10'),
                ('Envialia', '48/72H', 6, 19, '2013-04-30 11:32:10'),
                ('UPS', 'Standard Europa', 2, 20, '2013-04-30 11:32:10'),
                ('UPS', 'Standard Domestico', 2, 22, '2013-04-30 11:32:10'),
                ('ASM', '24', 8, 23, '2013-04-30 11:32:10'),
                ('ASM', 'Economy', 8, 24, '2013-04-30 11:32:10'),
                ('MEX', 'MEX Express', 9, 25, '2013-04-30 11:32:10'),
                ('UPS', 'Express Intl', 2, 27, '2013-04-30 11:32:10'),
                ('UPS', 'Express Intnal.', 2, 27, '2013-04-30 11:32:10'),
                ('GLS', 'Euro Business', 10, 28, '2013-04-30 11:32:10'),
                ('OCHOA', 'Nacional', 11, 29, '2013-04-30 11:32:10'),
                ('GLS', 'Euro Business', 10, 30, '2013-04-30 11:32:10'),
                ('OCHOA', 'Euro', 11, 32, '2013-04-30 11:32:10'),
                ('CHRONOEXPRÉS', 'Chrono 10', 4, 33, '2013-04-30 11:32:10'),
                ('CHRONOEXPRÉS', 'Canarias Interislas', 4, 34, '2013-04-30 11:32:10'),
                ('CHRONOEXPRÉS', 'Baleares Interislas', 4, 35, '2013-04-30 11:32:10'),
                ('CHRONOEXPRÉS', 'Chrono 24', 4, 36, '2013-04-30 11:32:10'),
                ('CHRONOEXPRÉS', 'Chrono Marítimo', 4, 37, '2013-04-30 11:32:10'),
                ('CHRONOEXPRÉS', 'Chrono 24', 4, 38, '2013-04-30 11:32:10'),
                ('CHRONOEXPRÉS', 'Chrono Marítimo', 4, 39, '2013-04-30 11:32:10'),
                ('CHRONOEXPRÉS', 'Chrono Portugal', 4, 40, '2013-04-30 11:32:10'),
                ('GLS', 'Euro Business', 10, 41, '2013-04-30 11:32:10'),
                ('TNT', 'Economy Europa', 12, 42, '2013-04-30 11:32:10'),
                ('TNT', 'Economy Europa', 12, 43, '2013-04-30 11:32:10'),
                ('TNT', '', 12, 44, '2013-04-30 11:32:10'),
                ('TNT', 'Economy Mundo', 12, 44, '2013-04-30 11:32:10'),
                ('TNT', 'Economy Mundo', 12, 45, '2013-04-30 11:32:10'),
                ('TNT', 'Express', 12, 46, '2013-04-30 11:32:10'),
                ('UPS', 'Standard Europa', 2, 47, '2013-04-30 11:32:10'),
                ('UPS', 'Express Saver', 2, 48, '2013-04-30 11:32:10'),
                ('UPS', 'Express', 2, 49, '2013-04-30 11:32:10'),
                ('CORREOS', '48/72H', 13, 50, '2013-04-30 11:32:10'),
                ('Tourline', 'Tourline 48 Horas', 14, 51, '2013-04-30 11:32:10'),
                ('Envialia', '24H', 6, 52, '2013-04-30 11:32:10'),
                ('Envialia', '48/72H', 6, 53, '2013-04-30 11:32:10'),
                ('CHRONOEXPRÉS', 'Chrono 24', 4, 54, '2013-04-30 11:32:10');";
        if (!Db::getInstance()->execute($query)) return false; 
        
        // ---------------------------------------------------------------------
	// Create the statuses table of Packlink.
        // ---------------------------------------------------------------------
        
        $query = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."packlink_status` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `name` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_mysql500_ci NOT NULL,
                    `color` varchar(32) CHARACTER SET utf8 NOT NULL,
                    PRIMARY KEY (`id`)
                  ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
        if (!Db::getInstance()->execute($query)) return false; 
        
        // Inserts
        $query = "INSERT INTO `"._DB_PREFIX_."packlink_status` (`id`, `name`, `color`) VALUES
                 (1, 'Pendiente', 'red'),
                 (2, 'Recogido', 'blue'),
                 (3, 'En Reparto', 'violet'),
                 (4, 'En Arrastre', 'brown'),
                 (5, 'Finalizado', 'green');";
        if (!Db::getInstance()->execute($query)) return false;
        
        // ---------------------------------------------------------------------
	// Override Classes and Controllers.
        // ---------------------------------------------------------------------
        
        /*if(str_replace(".", "", _PS_VERSION_) > 1400 ){
            foreach (Tools::scandir($this->getLocalPath().'overrides', 'php', '', true) as $file){
                $class = basename($file, '.php');
                var_dump($file);
                if (Autoload::getInstance()->getClassPath($class.'Core'))
                        $this->addOverride($class);
            }
        }*/
        
        // ---------------------------------------------------------------------
	// Update Configuration variables.
        // ---------------------------------------------------------------------
        
        
        Configuration::updateValue('PS_SHIPPING_HANDLING_NO_PL', Configuration::get('PS_SHIPPING_HANDLING'));
        Configuration::updateValue('PS_SHIPPING_HANDLING', "1");
        Configuration::updateValue('PS_SHIPPING_METHOD_NO_PL', Configuration::get('PS_SHIPPING_METHOD'));
        Configuration::updateValue('PS_SHIPPING_METHOD', "0");

