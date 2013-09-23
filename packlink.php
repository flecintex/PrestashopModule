<?php
/*
*  @author Pablo Fernández para PackLink
*  @version  Release: 1.0b
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

if (!defined('_PS_VERSION_'))
	exit;

class packlink extends Module {
    var $module_name 		 = "packlink";
    var $module_displayName 	 = 'Módulo de PackLink';
    var $module_version 	 = "1.0b";
	
    var $module_adminName 	 = "AdminPackLink";
    var $module_adminDisplayName = 'Configuración PackLink';
    var $module_adminDescription = 'Panel de Configuración de PackLink.';

    var $module_author 		 = "PackLink (Pablo E. Fernández Casado)";
    var $assignTab	   	 = "shipping_logistics";
    var $module_description 	 = 'Módulo para buscador de envíos de paquetería de PackLink.es.<br/><div>
        <h4>Ventajas de contratar con PackLink.es</h4>
            <ul>
                    <li><b>Comparar los precios de las empresas de transporte</b> más fiables como SEUR, UPS, FedEx, Nacex, MRW, ChronoExprés, Envialia, ASM, MEX, GLS, OCHOA y Tourline.</li>
                    <li>Proceso de contratación <b>sencillo y rápido</b></li>
                    <li>Puede solicitar la <b>recogida para el mismo día</b></li>
                    <li><b>Cobertura gratuita incluida en el precio</b> y posibilidad de contratar cobertura adicional</li>
                    <li><b>Seguimiento online</b> gratuito con actualizaciones regulares, para su tranquilidad</li>
                    <li><b>Pago 100% seguro</b> a través de tarjeta o PayPal</li>
            </ul>
            <br>
            Todos nuestros servicios tienen importantes descuentos para que realice sus envíos de paquetería con los precios más baratos del mercado.

            </div><br />Obtenga su presupuesto gratuito al instante y contrate el servicio en 3 sencillos pasos. Sólo tiene que introducir el peso y las medidas del bulto en el formulario azul “Buscar el mejor precio” y benefíciese de las mejores ofertas para enviar un paquete en la web.
            <h4>Soporte Técnico</h4><br/>Puede contactar con nosotros a través de nuestra Ayuda On-Line o de nuestro Formulario de Contacto pulsando <a style=color:#2698df target=_blank href=http://www.packlink.es/es/contacte/><strong>aquí</strong></a>.';
	
	
	
	function getBrowser() { 
		$u_agent = $_SERVER['HTTP_USER_AGENT']; 
		$bname = 'Unknown';
		$platform = 'Unknown';
		$version= "";
	
		//First get the platform?
		if (preg_match('/linux/i', $u_agent)) {
			$platform = 'linux';
		}
		elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
			$platform = 'mac';
		}
		elseif (preg_match('/windows|win32/i', $u_agent)) {
			$platform = 'windows';
		}
		
		// Next get the name of the useragent yes seperately and for good reason
		if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) { 
			$bname = 'Internet Explorer'; 
			$ub = "MSIE"; 
			$prefix = "-ms-";
		} elseif(preg_match('/Firefox/i',$u_agent)){ 
			$bname = 'Mozilla Firefox'; 
			$ub = "Firefox"; 
			$prefix = "-moz-";
		} elseif(preg_match('/Chrome/i',$u_agent)) { 
			$bname = 'Google Chrome'; 
			$ub = "Chrome"; 
			$prefix = "-webkit-";
		} elseif(preg_match('/Safari/i',$u_agent)) { 
			$bname = 'Apple Safari'; 
			$ub = "Safari"; 
			$prefix = "-webkit-";
		} elseif(preg_match('/Opera/i',$u_agent)) { 
			$bname = 'Opera'; 
			$ub = "Opera"; 
			$prefix = "-o-";
		} elseif(preg_match('/Netscape/i',$u_agent)) { 
			$bname = 'Netscape'; 
			$ub = "Netscape"; 
			$prefix = "-khtml-";
		} 
		
		// finally get the correct version number
		$known = array('Version', $ub, 'other');
		$pattern = '#(?<browser>' . join('|', $known) .
		')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
		if (!preg_match_all($pattern, $u_agent, $matches)) {
			// we have no matching number just continue
		}
		
		// see how many we have
		$i = count($matches['browser']);
		if ($i != 1) {
			//we will have two since we are not using 'other' argument yet
			//see if version is before or after the name
			if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
				$version= $matches['version'][0];
			}
			else {
				$version= $matches['version'][1];
			}
		}
		else {
			$version= $matches['version'][0];
		}
		
		// check if we have a number
		if ($version==null || $version=="") {$version="?";}
		
		return array(
			'userAgent' => $u_agent,
			'name'      => $bname,
			'version'   => $version,
			'platform'  => $platform,
			'pattern'    => $pattern
		);
	} 
	
	public function __construct(){
		$this->name = $this->module_name;
		$this->tab = $this->assignTab;
		$this->version = $this->module_version;
		$this->author = $this->module_author;

		parent::__construct();

		$this->displayName = $this->l($this->module_displayName);
		$this->description = $this->l($this->module_description);
		$this->confirmUninstall = $this->l('Are you sure ?');
	}

	public function install(){
                include(dirname(__FILE__).'/install.php');
		
		if(!parent::install()     
                    || !$this->registerHook('processCarrier') 
                    || !$this->registerHook('updateCarrier')
                    || !$this->registerHook('adminOrder')
                    || !$this->registerHook('rightColumn')
                    || !$this->installModuleTab('Admin'.$this->module_name, $this->module_name, Db::getInstance()->getValue('SELECT id_tab FROM `'._DB_PREFIX_.'tab` WHERE `class_name`="AdminParentOrders";'))
                    | !Db::getInstance()->execute("UPDATE "._DB_PREFIX_."tab_lang SET `name` = '".ucwords($this->module_name)."' WHERE `name` = '".$this->module_name."'")
                ) return false;
                
                return true;
	}
        
	public function uninstall(){
		include(dirname(__FILE__).'/uninstall.php');
		return(
                   parent::uninstall()
                   && $this->unregisterHook('rightColumn')
                   && $this->unregisterHook('processCarrier')
                   && $this->unregisterHook('updateCarrier')
                   && $this->unregisterHook('adminOrder')
                   && $this->uninstallModuleTab('Admin'.$this->module_name));
	}

	public function hookRightColumn($params){
            global $smarty;
            if(Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_ENABLE_TRACKING'"))
                return $this->display(__FILE__, $this->module_name.'.tpl');
        }
	
	public function getContent(){
            global $cookie;
 
            $id_lang = $cookie->id_lang;
            if($_REQUEST['_ACTIVE_TAB'] != "") Db::getInstance()->execute("UPDATE "._DB_PREFIX_."packlink_config SET value = '".$_REQUEST['_ACTIVE_TAB']."' WHERE `key` = '_ACTIVE_TAB'");

            $url_packlink        = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = 'url_packlink'");
            $username            = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = 'username'");
            $password            = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = 'password'");
            $apikey              = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = 'apikey'");
            
            // WS Connection Client
            // --------------------
            $options = array('trace' => true, 'exceptions' => true, 'cache_wsdl' => WSDL_CACHE_NONE, 'features' => SOAP_SINGLE_ELEMENT_ARRAYS +  SOAP_USE_XSI_ARRAY_TYPE, 'login' => $apikey, 'password' =>$password, 'soap_version'   => SOAP_1_2, "use"      => SOAP_ENCODED, "style"    => SOAP_DOCUMENT);
            $client     = new SoapClient($url_packlink."/wsdl", $options );
            $iso_lang =  Db::getInstance()->getValue("SELECT iso_code FROM "._DB_PREFIX_."lang WHERE id_lang=$id_lang;");
            if($username != "" && $password != "" && $apikey != "") $response = $client->setLanguage($iso_lang);
            
            if(Tools::isSubmit('submit'.$this->module_name)){
                Db::getInstance()->execute("UPDATE "._DB_PREFIX_."packlink_config SET value = '".$_REQUEST['_POST_CODE_SHOP']."' WHERE `key` = '_POST_CODE_SHOP'");
                Db::getInstance()->execute("UPDATE "._DB_PREFIX_."packlink_config SET value = '".$_REQUEST['_COUNTRY_SHOP_SELECT']."' WHERE `key` = '_ID_COUNTRY_SHOP'");
                Db::getInstance()->execute("UPDATE "._DB_PREFIX_."packlink_config SET value = '".$_REQUEST['_ADDRESS_SHOP']."' WHERE `key` = '_ADDRESS_SHOP'");
                Db::getInstance()->execute("UPDATE "._DB_PREFIX_."packlink_config SET value = '".$_REQUEST['_TOWN_SHOP']."' WHERE `key` = '_TOWN_SHOP'");
                Db::getInstance()->execute("UPDATE "._DB_PREFIX_."packlink_config SET value = '".$_REQUEST['_PROVINCE_SHOP']."' WHERE `key` = '_PROVINCE_SHOP'");
                Db::getInstance()->execute("UPDATE "._DB_PREFIX_."packlink_config SET value = '".$_REQUEST['_LANDLINE_SHOP']."' WHERE `key` = '_LANDLINE_SHOP'");
                Db::getInstance()->execute("UPDATE "._DB_PREFIX_."packlink_config SET value = '".$_REQUEST['_FAX_SHOP']."' WHERE `key` = '_FAX_SHOP'");
                Db::getInstance()->execute("UPDATE "._DB_PREFIX_."packlink_config SET value = '".$_REQUEST['_OTHER_PHONE_SHOP']."' WHERE `key` = '_OTHER_PHONE_SHOP'");
                
                Db::getInstance()->execute("UPDATE "._DB_PREFIX_."packlink_config SET value = '".$_REQUEST['_INVOICE_POST_CODE_SHOP']."' WHERE `key` = '_INVOICE_POST_CODE_SHOP'");
                Db::getInstance()->execute("UPDATE "._DB_PREFIX_."packlink_config SET value = '".$_REQUEST['_INVOICE_COUNTRY_SHOP_SELECT']."' WHERE `key` = '_INVOICE_ID_COUNTRY_SHOP'");
                Db::getInstance()->execute("UPDATE "._DB_PREFIX_."packlink_config SET value = '".$_REQUEST['_INVOICE_ADDRESS_SHOP']."' WHERE `key` = '_INVOICE_ADDRESS_SHOP'");
                Db::getInstance()->execute("UPDATE "._DB_PREFIX_."packlink_config SET value = '".$_REQUEST['_INVOICE_TOWN_SHOP']."' WHERE `key` = '_INVOICE_TOWN_SHOP'");
                Db::getInstance()->execute("UPDATE "._DB_PREFIX_."packlink_config SET value = '".$_REQUEST['_INVOICE_PROVINCE_SHOP']."' WHERE `key` = '_INVOICE_PROVINCE_SHOP'");
                Db::getInstance()->execute("UPDATE "._DB_PREFIX_."packlink_config SET value = '".$_REQUEST['_INVOICE_LANDLINE_SHOP']."' WHERE `key` = '_INVOICE_LANDLINE_SHOP'");
                Db::getInstance()->execute("UPDATE "._DB_PREFIX_."packlink_config SET value = '".$_REQUEST['_INVOICE_FAX_SHOP']."' WHERE `key` = '_INVOICE_FAX_SHOP'");
                Db::getInstance()->execute("UPDATE "._DB_PREFIX_."packlink_config SET value = '".$_REQUEST['_INVOICE_OTHER_PHONE_SHOP']."' WHERE `key` = '_INVOICE_OTHER_PHONE_SHOP'");

                Db::getInstance()->execute("UPDATE "._DB_PREFIX_."packlink_config SET value = '".$_REQUEST['username']."' WHERE `key` = 'username'");
                Db::getInstance()->execute("UPDATE "._DB_PREFIX_."packlink_config SET value = '".$_REQUEST['password']."' WHERE `key` = 'password'");
                Db::getInstance()->execute("UPDATE "._DB_PREFIX_."packlink_config SET value = '".$_REQUEST['apikey']."' WHERE `key` = 'apikey'");
                Db::getInstance()->execute("UPDATE "._DB_PREFIX_."packlink_config SET value = '".$_REQUEST['_PERCENTAGE_ADJUST']."' WHERE `key` = '_PERCENTAGE_ADJUST'");
                Db::getInstance()->execute("UPDATE "._DB_PREFIX_."packlink_config SET value = '".$_REQUEST['_ENABLE_TRACKING']."' WHERE `key` = '_ENABLE_TRACKING'");
                Db::getInstance()->execute("UPDATE "._DB_PREFIX_."packlink_config SET value = '".$_REQUEST['_ENABLE_DRAGDROP']."' WHERE `key` = '_ENABLE_DRAGDROP'");
                Db::getInstance()->execute("UPDATE "._DB_PREFIX_."packlink_config SET value = '".$_REQUEST['_ENABLE_CTRL_WEIGHTS']."' WHERE `key` = '_ENABLE_CTRL_WEIGHTS'");
                Db::getInstance()->execute("UPDATE "._DB_PREFIX_."packlink_config SET value = '".$_REQUEST['_ENABLE_CTRL_MEASUREMENTS']."' WHERE `key` = '_ENABLE_CTRL_MEASUREMENTS'");
                Db::getInstance()->execute("UPDATE "._DB_PREFIX_."packlink_config SET value = '".$_REQUEST['_ENABLE_ANIMATION']."' WHERE `key` = '_ENABLE_ANIMATION'");
                Db::getInstance()->execute("UPDATE "._DB_PREFIX_."packlink_config SET value = '".$_REQUEST['_ENABLE_USER_CHOOSE']."' WHERE `key` = '_ENABLE_USER_CHOOSE'");
                Db::getInstance()->execute("UPDATE "._DB_PREFIX_."packlink_config SET value = '".$_REQUEST['_FREE_SHIPMENT_FROM']."' WHERE `key` = '_FREE_SHIPMENT_FROM'");
                
                // Save boxes Section
                // ------------------
                $messageBoxes = "";
                if(isset($_REQUEST['_MODEL_BOX'])       && $_REQUEST['_MODEL_BOX'] != ""        &&
                   isset($_REQUEST['_DESCRIPTION_BOX']) && $_REQUEST['_DESCRIPTION_BOX'] != ""  &&
                   isset($_REQUEST['_WIDTH_BOX'])       && $_REQUEST['_WIDTH_BOX'] != ""        &&
                   isset($_REQUEST['_HEIGHT_BOX'])      && $_REQUEST['_HEIGHT_BOX'] != ""       &&
                   isset($_REQUEST['_DEPTH_BOX'])       && $_REQUEST['_DEPTH_BOX'] != ""        &&
                   isset($_REQUEST['_WEIGHT_BOX'])      && $_REQUEST['_WEIGHT_BOX'] != ""){
                    
                    if(isset($_REQUEST['_BOX_ID']) && $_REQUEST['_BOX_ID'] != ""){
                        $sql_set = "SET `model` = '".$_REQUEST['_MODEL_BOX']."', `description` = '".$_REQUEST['_DESCRIPTION_BOX']."', ";
                        $sql_set.= "`width` = '".$_REQUEST['_WIDTH_BOX']."', `height` = '".$_REQUEST['_HEIGHT_BOX']."', ";
                        $sql_set.= "`depth` = '".$_REQUEST['_DEPTH_BOX']."', `weight` = '".$_REQUEST['_WEIGHT_BOX']."'";
                        $sql     = "UPDATE "._DB_PREFIX_."packlink_boxes` ".$sql_set." WHERE `ps_packlink_boxes`.`id` = ".$_REQUEST['_BOX_ID'].";";
                    } else {
                        $sql  = "INSERT INTO `"._DB_PREFIX_."packlink_boxes` (`model`, `description`, `width`, `height`, `depth`, `weight`) VALUES ";
                        $sql .= "('".$_REQUEST['_MODEL_BOX']."', '".$_REQUEST['_DESCRIPTION_BOX']."', '".$_REQUEST['_WIDTH_BOX']."', '".$_REQUEST['_HEIGHT_BOX']."', '".$_REQUEST['_DEPTH_BOX']."', '".$_REQUEST['_WEIGHT_BOX']."')";
                    }
                   
                    Db::getInstance()->execute($sql);
                    
                    $messageBoxes = '<span><label class="labelSection">&nbsp;</label><div class="msgOK" style=" width: calc(100% - 175px);">'.$this->l("Operation performed successfully")."</div></span>";
                } else {
                    $messageBoxes = '<span><label class="labelSection">&nbsp;</label><div class="msgError" style="width: calc(100% - 175px);">'.$this->l("An error occurred. Failed to save the definition of the new box")."</div></span>";
                }
                
                // WS Connection Write Queries
                // ---------------------------
                
                // Update Ignore Listed, margins and transits
                // ------------------------------------------
                try{
                    $servicesListed = array();
                    $servicesListedMargin = array();
                    $servicesListedTransit = array();
                    $servicesListedFSFrom = array();
                    foreach($_REQUEST as $name => $value){
                        if(substr($name, 0, 12) == "actSrvMargin"){
                            $servicesListedMargin[] = str_replace("actSrvMargin", "", $name)."=>".number_format($value, 2);
                        } elseif(substr($name, 0, 13) == "actSrvTransit"){
                            $servicesListedTransit[] = str_replace("actSrvTransit", "", $name)."=>".abs($value);
                        } elseif(substr($name, 0, 12) == "actSrvFSFrom"){
                            $servicesListedFSFrom[] = str_replace("actSrvFSFrom", "", $name)."=>".abs($value);
                        } elseif(substr($name, 0, 6) == "actSrv"){
                            $servicesListed[] = $value;
                        }
                    }
                    $servicesListed = implode(",", $servicesListed);
                    $servicesListedMargin = implode(",", $servicesListedMargin);
                    $servicesListedTransit = implode(",", $servicesListedTransit);
                    $servicesListedFSFrom = implode(",", $servicesListedFSFrom);
                    $response = $client->updateStateServices($servicesListed);
                    $response = $client->updateMarginServices($servicesListedMargin);
                    $response = $client->updateFreeShipmentFromServices($servicesListedFSFrom);
                    //$response = $client->updateTransitServices($servicesListedTransit);
                } catch (SoapFault $exp) {
                    $html .= $this->displayConfirmation($exp->faultstring);
                }
                
                $html .= $this->displayConfirmation($this->l('Successfully saved configuration.'));
            }
            
            $username            = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = 'username'");
            $password            = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = 'password'");
            $apikey              = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = 'apikey'");
            
            $_POST_CODE_SHOP            = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_POST_CODE_SHOP'");
            $_ID_COUNTRY_SHOP           = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_ID_COUNTRY_SHOP'");
            $_COUNTRY_SHOP              = Db::getInstance()->getValue("SELECT name  FROM "._DB_PREFIX_."country_lang WHERE `id_country` = ".$_ID_COUNTRY_SHOP." AND id_lang = ".$id_lang);
            $_ADDRESS_SHOP              = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_ADDRESS_SHOP'");
            $_TOWN_SHOP                 = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_TOWN_SHOP'");
            $_PROVINCE_SHOP             = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_PROVINCE_SHOP'");
            $_LANDLINE_SHOP             = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_LANDLINE_SHOP'");
            $_FAX_SHOP                  = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_FAX_SHOP'");
            $_OTHER_PHONE_SHOP          = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_OTHER_PHONE_SHOP'");
            
            $_INVOICE_POST_CODE_SHOP    = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_INVOICE_POST_CODE_SHOP'");
            $_INVOICE_ID_COUNTRY_SHOP   = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_INVOICE_ID_COUNTRY_SHOP'");
            $_INVOICE_COUNTRY_SHOP      = Db::getInstance()->getValue("SELECT name  FROM "._DB_PREFIX_."country_lang WHERE `id_country` = ".$_INVOICE_ID_COUNTRY_SHOP." AND id_lang = ".$id_lang);
            $_INVOICE_ADDRESS_SHOP      = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_INVOICE_ADDRESS_SHOP'");
            $_INVOICE_TOWN_SHOP         = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_INVOICE_TOWN_SHOP'");
            $_INVOICE_PROVINCE_SHOP     = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_INVOICE_PROVINCE_SHOP'");
            $_INVOICE_LANDLINE_SHOP     = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_INVOICE_LANDLINE_SHOP'");
            $_INVOICE_FAX_SHOP          = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_INVOICE_FAX_SHOP'");
            $_INVOICE_OTHER_PHONE_SHOP  = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_INVOICE_OTHER_PHONE_SHOP'");
            
            $_PERCENTAGE_ADJUST         = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_PERCENTAGE_ADJUST'");
            $_ACTIVE_TAB                = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_ACTIVE_TAB'");
            $_ENABLE_TRACKING           = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_ENABLE_TRACKING'");
            $_ENABLE_DRAGDROP           = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_ENABLE_DRAGDROP'");
            $_ENABLE_CTRL_WEIGHTS       = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_ENABLE_CTRL_WEIGHTS'");
            $_ENABLE_CTRL_MEASUREMENTS  = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_ENABLE_CTRL_MEASUREMENTS'");
            $_ENABLE_ANIMATION          = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_ENABLE_ANIMATION'");
            $_ENABLE_USER_CHOOSE        = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_ENABLE_USER_CHOOSE'");
            $_FREE_SHIPMENT_FROM        = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_FREE_SHIPMENT_FROM'");
            
            // WS Conection Read-Only Queries
            // ------------------------------
            try{
                // User Information
                $response = $client->getUserInfo();
                $xml = simplexml_load_string($response); 
                $_EXPIRY = $xml->expired;
                $_PL_USER_EMAIL = $xml->email;
                $_PL_USER_DISPLAY_NAME = $xml->display_name;

                // Service Listed
                if($username != "" && $password != "" && $apikey != ""){
                    $response = $client->getShippers();
                    $dom = simplexml_load_string(str_replace("]]>", "", str_replace("<![CDATA[", "", $response))); 

                    $l = base64_encode(serialize(array('l'=>$iso_lang, "from"=>$this->l("from"), "to"=>$this->l("to"), "value"=>$this->l("value")))); //, "location"=>$this->l("location"))));

                    foreach ($dom as $shipper){
                        foreach($shipper as $service){ 
                            $fixed_price_tmpl = '<a title="'.(string)$service->service.'" rel="prettyPhoto[iframes]" href="'._MODULE_DIR_.'packlink/getFixedPrices.php?id='.(int)$service->service_id.'&l='.$l.'&iframe=true&width=500&height=80%"><img src="'._MODULE_DIR_.'packlink/images/pin_blue.png'.'" width="20" alt="'.$this->l('Table of values ​​for')." ".(string)$shipper->getName()." / ".(string)$service->service.'" title="'.$this->l('The service has fixed prices').'" /></a>';
                            $fixed_price = $service->fixed_price==$this->l('Yes')?$fixed_price_tmpl:"";
                            $shippers[]= array($this->l('Active')=>'<input class="activeService" type="checkbox" name="actSrv'.(int)$service->service_id.'" id="actSrv'.(int)$service->service_id.'" value="'.(int)$service->service_id.'" '.((string)$service->ignore==""?'checked="checked"':"").' /><label class="labelCheckBox" for="actSrv'.(int)$service->service_id.'"><span></span></label>',
                                              // $this->l('Shipper ID')=>(int)$service->shipper_id,
                                              // $this->l('Service ID')=>(int)$service->service_id,
                                               $this->l('Shipper Name')=>(string)$shipper->getName(),
                                               $this->l('Service Name')=>(string)$service->service,
                                               $this->l('Fixed Price')=>(string)$fixed_price,
                                               $this->l('Description')=>(string)$service->description,
                                               $this->l('Margin|adjust')." %"=>'<input class="noAdjust transitMargin" name="actSrvMargin'.(int)$service->service_id.'" id="actSrvMargin'.(int)$service->service_id.'" value="'.(string)$service->margin.'" />',
                                               $this->l('Free Shipment From|adjust') => '<input class="noAdjust transitMargin" name="actSrvFSFrom'.(int)$service->service_id.'" id="actSrvFSFrom'.(int)$service->service_id.'" value="'.(string)$service->free_shipment_from.'" />');
                                               //$this->l('Transit')=>'<input class="noAdjust transitMargin" name="actSrvTransit'.(int)$service->service_id.'" id="actSrvTransit'.(int)$service->service_id.'" value="'.(string)$service->transit.'" />');
                        }
                    }
                    $services_html = self::toHTML($shippers);
                }
            } catch (SoapFault $exp) {
                $_EXPIRY = $exp->faultstring."<br />";
            }

            $browser = $this->getBrowser();
            ob_start();
            include (_PS_MODULE_DIR_.$this->module_name."/adminPanel.php");
            $html = ob_get_contents();
            ob_end_clean();

            return $html;
	}
        
        //$this->l('Show Prices')=>'<img src="'._MODULE_DIR_.'/packlink/images/expand.png'.'" width="20" alt="expand.png" title="Mostrar los precios para este servicio" />');
        //$this->l('Prices')=>$this->getPrices($service->service_id, $client));
        private function getPrices($service_id, $cliente){
            global $cookie;
 
            $url_packlink        = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = 'url_packlink'");
            $username            = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = 'username'");
            $password            = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = 'password'");
            $apikey              = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = 'apikey'");
            
            // WS Connection Client
            // --------------------
            $options = array('trace' => true, 'exceptions' => true, 'cache_wsdl' => WSDL_CACHE_NONE, 'features' => SOAP_SINGLE_ELEMENT_ARRAYS +  SOAP_USE_XSI_ARRAY_TYPE, 'login' => $apikey, 'password' =>$password, 'soap_version'   => SOAP_1_2, "use"      => SOAP_ENCODED, "style"    => SOAP_DOCUMENT);
            $client     = new SoapClient($url_packlink."/wsdl", $options );
            $iso_lang =  Db::getInstance()->getValue("SELECT iso_code FROM "._DB_PREFIX_."lang WHERE id_lang=".$cookie->id_lang);
           
            if($username != "" && $password != "" && $apikey != ""){
                $response = $client->setLanguage($iso_lang);

                $response = $client->getFixedPriceByService($service_id);
                $dom = simplexml_load_string(str_replace("]]>", "", str_replace("<![CDATA[", "", $response))); 
                foreach ($dom as $shipper){
                    foreach($shipper as $service){ 
                        foreach($service->prices as $price){ 
                            $prices[] = array("from"=>$price->from, "to"=>$price->to, "value"=>$price->value); //, "location"=>$price->location);
                        }
                    }
                }
            }
            return self::toHTML($prices);
        }
      
        /* 	**************************************************************************************************************************************************************************************************
                FUNCIÓN PARA CONVERTIR UN ARRAY ASOCIATIVO EN FORMATO CSV HTML.
                PARÁMETROS:
                -----------
                $data 			--> ES EL ARRAY FUENTE
                $level			--> NO CAMBIAR. INDICA EL NODO DÓNDE ESTA. SE USA PARA EL PROCESO RECURSIVO.
                ***************************************************************************************************************************************************************************************************/
        /**
         * Function for convert an array associative at SOAP messages. Only used with Web Service connection.
         * @param string $data The source array.
         * @param int Indicates recursive level. 
         * @return string Returns a string in HTML Format table.
         */

        public static function toHTML($arr, $level=0){
                $result  = '<table cellpadding="0" cellspacing="0" border="0" class="display" id="services" >'."\n";
                $result .= '<thead>'."\n";
                $result .= '<tr>'."\n";
                $arrAttr = array();
                $xCount = 0;
                foreach (array_keys($arr[0]) as $key){
                    if(stripos($key, "|") !== false){
                        if(stripos($key, "|adjust") !== false) $aux = "adjust";
                        $key = str_replace("|".$aux, "", $key);
                        $arrAttr[$xCount] = $aux;
                    }
                    
                    if($arrAttr[$xCount] == "adjust")
                        $result .= '<th style="text-align:center">'.$key.'</th>'."\n"; 
                    else
                        $result .= '<th>'.$key.'</th>'."\n"; 
                    $xCount++;
                }
                $result .= '</tr>'."\n"; 
                $result .= '</thead>'."\n";
                $result .= '<tbody>'."\n";
                foreach (array_values($arr) as $value){
                    $aux = implode("\n", $value);
                    $aux = stripos($aux, 'checked="checked"')!==false?true:false;
                    $result .= '<tr class="'.($aux==false?'disable':'').'">'."\n";
                    $xCount = 0;
                    foreach ($value as $value2){
                        if($arrAttr[$xCount] == "adjust"){
                            $result .= '<td class="'.$arrAttr[$xCount].'">'.$value2.'</td>'."\n"; 
                        } else {
                            $result .= '<td>'.$value2.'</td>'."\n"; 
                        }
                        $xCount++;
                    }
                    $result .= '</tr>'."\n"; 
                }
                
                $result .= '</tbody>'."\n";
                $result .= "</table>\n";

                return $result;
        }
	
	private function installModuleTab($tabClass, $tabName, $idTabParent){
		@copy(_PS_MODULE_DIR_.strtolower($this->name).'/images/logo.gif', _PS_IMG_DIR_.'t/'.$tabClass.'.gif');
		$tab = new Tab();
		$tab->name = array(1 => $tabName, 2 => $tabName);
		$tab->class_name = $tabClass;
		$tab->module = $this->name;
		$tab->id_parent = $idTabParent;

                if(!$tab->save()) return false;
		
		return true;
	} 
	
	private function uninstallModuleTab($tabClass){
		$idTab = Tab::getIdFromClassName($tabClass);
		if($idTab != 0){
		    $tab = new Tab($idTab);
		    $tab->delete();
		    return true;
		}
		
		return false;
	} 
}
