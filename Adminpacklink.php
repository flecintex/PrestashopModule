<?php

//ini_set("display_errors", true);
//error_reporting(E_PARSE^E_ERROR^E_STRICT);
/*class AdminPackLink extends AdminTab
{
  private $module = 'PackLink';

  public function __construct()
  {
    global $cookie, $_LANGADM;
    $langFile = _PS_MODULE_DIR_.$this->module.'/'.Language::getIsoById(intval($cookie->id_lang)).'.php';
    if(file_exists($langFile))
    {
      require_once $langFile;
      foreach($_MODULE as $key=>$value)
        if(substr(strip_tags($key), 0, 5) == 'Admin')
          $_LANGADM[str_replace('_', '', strip_tags($key))] = $value;
    }
    parent::__construct();
  }

  public function display()
  {
    echo $this->l('String 1').'<br /><p class="center">'.Configuration::get('PL_SYNC_METHOD').'</p>';
  }
}*/

class Adminpacklink extends AdminTab{
    private $module="packlink";
    
    public function l($s){
        global $cookie;
        
        $langFile = _PS_MODULE_DIR_.$this->module.'/translations/'.Language::getIsoById(intval($cookie->id_lang)).'.php';
        if(file_exists($langFile)) include $langFile;
        
        $aux = $_MODULE['<{'.$this->module.'}prestashop>packlink_'.md5($s)];
        $aux = is_string($aux)===false?$s:$aux;
        
        return $aux;
    }
    
    public function __construct(){
        global $cookie, $_LANGADM;
        $langFile = _PS_MODULE_DIR_.$this->module.'/translations/'.Language::getIsoById(intval($cookie->id_lang)).'.php';
        
        if(file_exists($langFile)){
            require_once $langFile;
            foreach($_MODULE as $key=>$value){
                
                    $_LANGADM['Admin'.str_replace('_', '', strip_tags($key))] = $value;
                //var_dump('Admin'.str_replace('_', '', strip_tags($key)));
            }
        }
        parent::__construct();
    }
   
    public function postProcess(){
        // Esta función se ejecuta con el botón submit
        // generalmente se usa para guardar los datos en la base de datos

        parent::postProcess();
    }

    public function display(){
        global $cookie;
        $id_lang = $cookie->id_lang;
        
        ?>
        <link type="text/css" rel="stylesheet" href="<?= _MODULE_DIR_?>packlink/css/animations.css" />
        <link type="text/css" rel="stylesheet" href="<?= _MODULE_DIR_?>packlink/css/base.css" />
        <link type="text/css" rel="stylesheet" href="<?= _MODULE_DIR_?>packlink/css/config.css" />
        <link type="text/css" rel="stylesheet" href="<?= _MODULE_DIR_?>packlink/css/front.css" />
        <link type="text/css" rel="stylesheet" href="<?= _MODULE_DIR_?>packlink/css/messages.css" />
        <link type="text/css" rel="stylesheet" href="<?= _MODULE_DIR_?>packlink/css/orders.css" />
        <link type="text/css" rel="stylesheet" href="<?= _MODULE_DIR_?>packlink/css/tables.css" />
        <script type="text/javascript" src="<?= _MODULE_DIR_ ?>packlink/js/packlink.js"></script>
        <script type="text/javascript" src="<?= _MODULE_DIR_ ?>packlink/js/jquery-ui-1.8.16.custom.min.js"></script>
        <script type="text/javascript" src="<?= _MODULE_DIR_ ?>packlink/js/jquery.ui.datepicker-es.js"></script>
        <script class="jsbin" src="<?= _MODULE_DIR_ ?>packlink/js/jquery.dataTables.nightly.js"></script>
        <link href="<?= _MODULE_DIR_ ?>packlink/css/jquery-ui-1.8.1.custom.css" rel="stylesheet" type="text/css" /> <!-- 1.8.16 -->
        <link href="<?= _MODULE_DIR_.$this->module ?>/css/jquery.lightbox.css" rel="stylesheet" type="text/css" media="all" />
        <script class="jsbin" src="<?= _MODULE_DIR_.$this->module ?>/js/jquery.lightbox.js"></script>
        <?php

        // WS Conection Read-Only Queries
        // ------------------------------
        $url_packlink        = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = 'url_packlink'");
        $username            = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = 'username'");
        $password            = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = 'password'");
        $apikey              = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = 'apikey'");

        // WS Connection Client
        // --------------------
        function createParam($element, $name){
            if(is_array($element)){
                $soapstruct = new SoapVar($element, SOAP_ENC_OBJECT, $name, "");
                return new SoapParam($soapstruct, $name);
            } else {
                return new SoapParam($element, $name);
            }
        }
        
        $options = array('trace' => true, 'exceptions' => true, 'cache_wsdl' => WSDL_CACHE_NONE, 'features' => SOAP_SINGLE_ELEMENT_ARRAYS +  SOAP_USE_XSI_ARRAY_TYPE, 'login' => $apikey, 'password' =>$password, 'soap_version'   => SOAP_1_2, "use"      => SOAP_ENCODED, "style"    => SOAP_DOCUMENT);
        $client     = new SoapClient($url_packlink."/wsdl", $options );
        $iso_lang =  Db::getInstance()->getValue("SELECT iso_code FROM "._DB_PREFIX_."lang WHERE id_lang=$id_lang;");
        $response = $client->setLanguage($iso_lang);
        
        foreach ($xml as $service){
            $factors[] = array($this->l('Shipper ID')=>(string)$service->shipper_id, $this->l('Service ID')=>(string)$service->service_id, $this->l('Fixed Price')=>(string)$service->volumetric_factor);
        }

        try{
            // Recover orders data
            
            // Recover status
            if(!isset($_GET['status']) || $_GET['status'] == 0) $addCond = "AND pl.status != -1 AND pl.status != 3 AND pl.status != 4";
            elseif(isset($_GET['status'])) $addCond = "AND status = ".$_GET['status'];
            
            // If status no is active disable animations and events
            if(isset($_GET['status']) && ($_GET['status'] == 3 || $_GET['status'] == -1 || $_GET['status'] == 4)){
                echo '<style> .animation:hover { transform: none; } </style>';
            }
            
            $sql = "SELECT o.`id_order` AS '".$this->l("Nº Order")."', o.`id_customer` AS '".$this->l("Nº Customer")."', 
                            (SELECT CONCAT(address1, ' ', address2, ', ', postcode, ' ', city, ' ', id_country) FROM "._DB_PREFIX_."address WHERE id_address = o.`id_address_delivery`) AS '".$this->l("Delivery Address")."', 
                            '' AS '".$this->l("Shipper")."',
                            pl.id_carrier AS 'ID\'hidden',
                            o.`payment` AS '".$this->l("Method Of Payment")."', 
                            o.`total_paid` AS 'Total', o.`date_upd` AS `".$this->l("Date")."`,
                            pl.status AS '".$this->l("Status")."',
                            pl.`reference` AS '".$this->l("Reference")."'
                       FROM `"._DB_PREFIX_."orders` o, `"._DB_PREFIX_."packlink_orders` pl
                       WHERE o.id_order = pl.id_order
                         $addCond";

            if ($results = Db::getInstance()->ExecuteS($sql)){ 
                foreach ($results as $order){
                    $order[$this->l("Shipper")] = Db::getInstance()->getValue("SELECT CONCAT(shipper, ' ', service) FROM ps_packlink_services WHERE service_id = ".$order['ID\'hidden']);
                    if($order[$this->l("Shipper")] == '') $order[$this->l("Shipper")] = "PACKLINK";
                    foreach($order as $key => $value){
                        if($key == $this->l("Status")){
                            if($order['Reference'] != "") {
                                $p_filter = array("packlink_ref"=>$order['Reference'], "return"=>"status"); 
                                $param    = createParam($p_filter, "shipments");
                                $response = $client->getShipments($param);
                                $response =  simplexml_load_string(str_replace("]]>", "", str_replace("<![CDATA[", "", $response))); 

                                if($response->order->status_id != "" && $response->order->status_id != NULL) {
                                    Db::getInstance()->execute("UPDATE "._DB_PREFIX_."packlink_orders SET status = '".$response->order->status_id."' WHERE `reference` = '".$order['Reference']."'");
                                
                                    if($response->order->status_id == 0) $iconStatus = _MODULE_DIR_.'packlink/images/statuses/accepted.png';
                                    elseif($response->order->status_id == 1) $iconStatus = _MODULE_DIR_.'packlink/images/statuses/collage.png';
                                    elseif($response->order->status_id == 2) $iconStatus = _MODULE_DIR_.'packlink/images/statuses/inprogress.png';
                                    elseif($response->order->status_id == 3) $iconStatus = _MODULE_DIR_.'packlink/images/statuses/completed.png';
                                    elseif($response->order->status_id == 4) $iconStatus = _MODULE_DIR_.'packlink/images/statuses/error.png';
                                    else $iconStatus = _MODULE_DIR_.'packlink/images/statuses/canceled.png';

                                    $selectStatus = '<span style="background:url('.$iconStatus.') no-repeat scroll 0 -1px transparent; padding-left:20px;">'.ucwords($response->order->status)."</span>";
                                }
                            } else {
                                $iconStatus = _MODULE_DIR_.'packlink/images/statuses/noprocessed.png';
                                
                                $status = "Sin procesar";

                                $selectStatus = '<span style="background:url('.$iconStatus.') no-repeat scroll 0 -1px transparent; padding-left:20px;">'.ucwords($status)."</span>";
                            }
                           
                            $orders[$order[$this->l("Nº Order")]][$key] = $selectStatus;
                        } else {
                            if($key == "ID'hidden"){
                                $response = $client->getVolumetricFactor($value);
                                $value = $value."|".strip_tags($response);
                            }
                            $orders[$order[$this->l("Nº Order")]][$key] = $value;
                            if($key == $this->l("Reference")){
                                $ref = $orders[$order[$this->l("Nº Order")]][$this->l("Reference")];
                                $orders[$order[$this->l("Nº Order")]][$this->l("Reference")] = '<a href="/modules/packlink/tracking.php?num='.$ref.'" target="_blank">'.$ref.'</a>';
                            }
                        }
                        $sql_quotes = "SELECT   (SELECT postcode FROM "._DB_PREFIX_."address WHERE id_address = o.`id_address_delivery`) as 'PCD',
                                            (SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_POST_CODE_SHOP') AS 'PCO',
                                            (SELECT postcode FROM "._DB_PREFIX_."address WHERE id_address = o.`id_address_invoice`) as 'PCI',
                                            (SELECT id_country FROM "._DB_PREFIX_."address WHERE id_address = o.`id_address_delivery`) as 'IDCD',
                                            (SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_ID_COUNTRY_SHOP') AS 'IDCO',
                                            (SELECT id_country FROM "._DB_PREFIX_."address WHERE id_address = o.`id_address_invoice`) as 'IDCI',
                                            (SELECT iso_code FROM "._DB_PREFIX_."country WHERE id_country = (SELECT id_country FROM "._DB_PREFIX_."address WHERE id_address = o.`id_address_delivery`)) AS 'ISOD',
                                            (SELECT iso_code FROM "._DB_PREFIX_."country WHERE id_country = (SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_ID_COUNTRY_SHOP')) AS 'ISOO',
                                            (SELECT iso_code FROM "._DB_PREFIX_."country WHERE id_country = (SELECT id_country FROM "._DB_PREFIX_."address WHERE id_address = o.`id_address_invoice`)) AS 'ISOI',
                                            o.`id_address_delivery` AS 'IDADD',
                                            CONCAT((SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = 'url_packlink'), '/get.php?method=quotes') AS 'URI',
                                            (SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = 'username') AS 'user',
                                            (SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = 'password') AS 'pwd',
                                            (SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = 'apikey') AS 'key'
                                    FROM "._DB_PREFIX_."address a, "._DB_PREFIX_."orders o
                                    WHERE id_address = o.`id_address_delivery` and id_order = ".$order[$this->l("Nº Order")];
                        if ($results = Db::getInstance()->ExecuteS($sql_quotes)){ 
                            foreach ($results as $order_quotes){
                                $b64Orders[$order[$this->l("Nº Order")]] = implode(",", $order_quotes);
                            }
                        }
                    }
                }
            }

            $orders_html = self::toHTML($orders);

            if($orders == NULL) {
                $orders_html  = '<table cellpadding="0" cellspacing="0" border="0" class="display" id="order_nodata" > <thead> <tr> <th></th> <th>Nº Pedido</th> <th>Nº Cliente</th> <th>Dirección de Entrega</th> <th>Transportista</th> <th style="display:none">ID\'hidden</th> <th>Forma de Pago</th> <th>Total</th> <th>Fecha</th> <th>Estado</th> <th>Referencia</th> </tr> </thead>';
                $orders_html .= '<tbody><tr class="odd"><td style="padding:8px 5px" colspan="10"><b>No hay datos disponibles</b></td></tr></tbody></table>';
            }
            
        } catch (SoapFault $exp) {
            $_EXPIRY = $exp->faultstring."<br />";
        }
        
        if(!isset($_GET['status']) || ($_GET['status'] != 3 && $_GET['status'] != -1 && $_GET['status'] != 4)) $_ACTIVE_TAB_ORDERS = "0";
        elseif(isset($_GET['status']) && $_GET['status'] == 3) $_ACTIVE_TAB_ORDERS = "1";
        elseif(isset($_GET['status']) && $_GET['status'] == -1) $_ACTIVE_TAB_ORDERS = "2";
        elseif(isset($_GET['status']) && $_GET['status'] == 4) $_ACTIVE_TAB_ORDERS = "3";
        ?>
        
        <form name="packlink_frm" id="packlink_frm" action="<?= $_SERVER['REQUEST_URI'] ?>" method="post" onSubmit="onSubmitPL()">
            <script>var pl_info = '<?= base64_encode(serialize($b64Orders)) ?>';</script>
            <h2 title="PackLink Settings"><img src="<?= _MODULE_DIR_ ?>packlink/images/logo_text.png"></h2>
            <div id="sectionsPackLink">
                <span <?= $_ACTIVE_TAB_ORDERS=="0"?'class="selected"':"" ?> ><?= $this->l('Active Orders') ?></span>
                <span <?= $_ACTIVE_TAB_ORDERS=="1"?'class="selected"':"" ?> ><?= $this->l('Completed Orders') ?></span>
                <span <?= $_ACTIVE_TAB_ORDERS=="2"?'class="selected"':"" ?> ><?= $this->l('Canceled Orders') ?></span>
                <span <?= $_ACTIVE_TAB_ORDERS=="3"?'class="selected"':"" ?> ><?= $this->l('Faulted Orders') ?></span>
            </div>
            
            <div id="contentSectionsPackLink">
                <fieldset>
                    <?php 
                        if($_ACTIVE_TAB_ORDERS == 0) $s = 'Active Orders';
                        elseif($_ACTIVE_TAB_ORDERS == 1) $s = 'Completed Orders';
                        elseif($_ACTIVE_TAB_ORDERS == 2) $s = 'Canceled Orders';
                        elseif($_ACTIVE_TAB_ORDERS == 3) $s = 'Faulted Orders';
                    ?>
                    <legend><?= $this->l($s) ?></legend>
                    <?= $orders_html ?>
                </fieldset>
                
            </div>
            <script type="text/javascript" src="<?= _MODULE_DIR_.$this->module ?>/js/drag-min.js"></script>
        </form>
            
        <script>
            var startBoxes           = 3;
            var rd                   = new Array();
            var curCol               = 0;
            var curRow               = 0;
            var itemDrag             = null;
            var layoutItem           = new Array();
            var ordSerial            = "";
            var datePickerResponse   = "";
            var datePickerResponseTS = new Array();
            var modifiedPage         = false;
            
            function enable_drag(id){
                // Inicializamos los contenedores
                rd[id] = REDIPS.drag;
                
                $('#orders .table1').each(function(){
                    // Referenciamos a la clase REDIPS.drag
                    rd[id].init($(this).parent().attr('id'));
                });    
                // Si pulsamos la tecla CRTL los elementos se clonarán
                rd[id].hover.colorTd = '#9BB3DA';
                rd[id].dropMode = 'single';
                
                rd[id].event.changed = function(cc) {
                    var pos = rd[id].getPosition();
                    curRow = pos[4]+1;
                    curCol = pos[5]+1;
                    itemDrag = $('th:nth-child('+(curCol)+')', $(cc).parent().parent());
                    itemDrag.html('<img src="<?= _MODULE_DIR_.$this->module ?>/images/trash-orange.png" style="padding:0" width="20" />')
                }
                
                // Cuando se realize un evento de DROP, refrescamos
                rd[id].event.droppedBefore = function () {
                    itemDrag.html(pad(curCol-startBoxes, 2));
                    var pos = rd[id].getPosition();
                    if(pos[1] == 0 && pos[2] == curCol-1){
                        $($('tr:nth-child('+curRow+') td:nth-child('+(curCol)+')', $(itemDrag).parent().parent())).children().remove();
                        $($('tr:nth-child('+curRow+')', $(itemDrag).parent().parent())).removeClass("packageUp");
                        $($('tr:nth-child('+curRow+')', $(itemDrag).parent().parent())).addClass("packageDown");
                        
                        $('.options select').each(function(){
                            $(this).change();
                        });
                        
                        return false;
                    } else if(pos[1] != pos[4] || pos[2] < startBoxes || pos[2] >= (startBoxes+parseInt($('select#selectBox'+id+' option').length+1))){
                        return false;
                    }
                };
                rd[id].event.dropped = function () {
                    $('.options select').each(function(){
                        $(this).change();
                    });
                    //itemDoesntFit($('#contentOrder'+id+' tr:first-child td.selectedColumn'), id);
                };
               
               return rd;
            } 
            
            function enableDatePicker(id){
                $.ajax({
                    url:ordSerial[id].split(',')[10].replace("quotes", "laboralDays"),
                    crossDomain:true,
                    async:false,
                    dataType:"jsonp",
                    contentType: "application/json; charset=utf-8",
                    cache: 'false', 
                    data:{
                        username: ordSerial[id].split(',')[11], password:ordSerial[id].split(',')[12], 
                        apikey:ordSerial[id].split(',')[13], request_format:"json", response_format:"json",
                        charset:"UTF-8", language:"es", query:"get/laboralDays",
                        data:'{"iso_source":"es", "iso_target":"es", "cp_source":"28033", "cp_target":"28029", "current_time":"true", "excludeSaturdays":"true", "excludeSundays":"true", "service_id":"'+$('td:nth-child(6)', $('#order'+id).prev()).html().split("|")[0]+'", "numOfDays":"10", "formatDate":"Y-n-j"}'
                    },
                    success:function (data){
                        datePickerResponse = data;
                        var obj = $.parseJSON(datePickerResponse);
                        var dt_allow = obj['laboral_days'].split(";");
                        
                        var allow_dates = "";
                        var allow_ts    = new Array();
                        var aux_ant     = 0;
                        var numMonths   = 0;

                        for(var x=0; x < dt_allow.length; x++){
                            var item = dt_allow[x].substr(0, dt_allow[x].indexOf("[")).replace(/\s/g, '');
                            allow_dates   += '"'+item+'":1,';
                            var idx = Math.floor(Math.abs(new Date() - new Date(item.replace(/-/g,'/')))/(86400000));
                            allow_ts[idx] = dt_allow[x].substring(dt_allow[x].indexOf("[")+1, dt_allow[x].indexOf("]")); 
                            if(aux_ant    != dt_allow[x].split("-")[1] ){ aux_ant = dt_allow[x].split("-")[1]; numMonths++; }
                        }
                        
                        allow_dates = "{"+allow_dates.substr(0, allow_dates.length-1)+"}";
                        datePickerResponseTS = allow_ts;
                        
                        $("#gathered_date"+id).datepicker({
                            showOn: 'both',
                            buttonImage: '<?= _MODULE_DIR_ ?>packlink/images/calendar.png',
                            buttonImageOnly: true,
                            changeYear: false,
                            numberOfMonths: numMonths,
                            onSelect: function(dateText) {
                                var aux = this.value.split("/");
                                aux = aux[2]+"/"+aux[1]+"/"+aux[0];
                                var idx = Math.floor(Math.abs(new Date() - new Date(aux))/(86400000));
                                var ts = datePickerResponseTS[idx].split(",");
                                $('#scheduleOpt1').val('').attr("checked", false).parent().css("display", "none");
                                $('#scheduleOpt2').val('').attr("checked", false).parent().css("display", "none");
                                for(var x = 0; x < ts.length; x++){
                                    $('label', $('#scheduleOpt'+(x+1)).parent()).html('<span style="border:none"></span>'+ts[x]).css({"font": "normal 12px Arial","text-align":"left","width":"100%"});
                                    $('#scheduleOpt'+(x+1)).val(ts[x]).parent().css("display", "");
                                }
                            },
                            beforeShowDay: function(date) {
                                
                                var dates_allowed = $.parseJSON(allow_dates);
                                var date_str = [
                                     date.getFullYear(),
                                     date.getMonth() + 1,
                                     date.getDate()
                                 ].join('-');

                                 if (dates_allowed[date_str]==1) {
                                     return [true, 'good_date', '<?= $this->l("You can select this day") ?>'];
                                 } else {
                                     return [false, 'bad_date', '<?= $this->l("You can not select this day") ?>'];
                                 }
                             }
                        });
                        $("#gathered_date"+id).datepicker($.datepicker.regional['en']);
                        $("#gathered_date"+id).val("");
                    },
                    fail: function(e, t){
                        alert(JSON.stringify(e)+" "+t);
                    }
                });
                
                

                
            }
            
            $(document).ready(function(){
                $('#orders').dataTable({
                    "oLanguage": {
                        "sProcessing":"<?=  $this->l('sProcessing'); ?>",
                        "sLengthMenu":"<?=  $this->l('sLengthMenu');?>",
                        "sZeroRecords":"<?=  $this->l('sZeroRecords');?>",
                        "sInfo":"<?=  $this->l('sInfo');?>",
                        "sInfoEmpty":"<?=  $this->l('sInfoEmpty');?>",
                        "sInfoFiltered":"<?=  $this->l('sInfoFiltered');?>",
                        "sInfoPostFix":"<?=  $this->l('sInfoPostFix');?>",
                        "sSearch":"<?=  $this->l('sSearch');?>",
                        "sUrl":"<?=  $this->l('sUrl');?>",
                        "oPaginate": {
                            "sFirst":"<?=  $this->l('sFirst');?>",
                            "sPrevious":"<?=  $this->l('sPrevious');?>",
                            "sNext":"<?=  $this->l('sNext');?>",
                            "sLast":"<?=  $this->l('sLast');?>"
                        },
                        "oAria": {
                            "sSortAscending": "<?=  $this->l('sSortAscending');?>",
                            "sSortDescending":"<?=  $this->l('sSortDescending');?>"
                        }
                    }
                });
                $.fn.dataTableExt.oApi.fnSortOnOff  = function ( oSettings, aiColumns, bOn ){
                    var cols = typeof aiColumns == 'string' && aiColumns == '_all' ? oSettings.aoColumns : aiColumns;

                    for ( var i = 0, len = cols.length; i < len; i++ ) {
                        oSettings.aoColumns[ i ].bSortable = bOn;
                    }
                }
                
                /* ******************************************* 
                   Event to expand or collape of main orders   
                   ******************************************* */
                // If status no is active disable animations and events
                
                <?php if(!isset($_GET['status']) || $_GET['status'] == 0){ ?>
                    $('#orders tr td:first-child').click(function(){
                        if($('td:last-child', $(this).parent()).html() == ""){
                            modifiedPage = true;
                            var item  = $(this).parent();
                            var contentThis = $(this);
                            var id = contentThis.next().html();
                            if(contentThis.hasClass("expand")){
                                var color = 'rgb(255,255,255)'; 
                                $.ajax({
                                    type: "POST",
                                    url: "<?= _MODULE_DIR_.$this->module ?>/getTemplateProducts.php",
                                    data: { ido: $(this).next().html(), ubase: '<?= _MODULE_DIR_.$this->module ?>'},
                                    dataType: "html",
                                    async : true,
                                    beforeSend: function ( xhr ) {
                                        contentThis.removeClass("expand");
                                        contentThis.addClass("loading");
                                    }
                                }).done(function( html ) {
                                    html = html.replace('<dd class="view"', '<dd class="view" style="display:none"');

                                    item.after('<tr id="order'+id+'" style="background-color:'+color+'"><td colspan="18">'+html+'</td></tr>');
                                    $(".floatLeft", item.next()).each(function(){
                                    <?php if(Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_ENABLE_ANIMATION'") == '1'){ ?>
                                        $(this).animate({height:320},1000, function () {  $('.package > .view', contentThis.parent().next()).css("display","block"); });
                                    <?php } else {?>
                                        $(this).height(320);
                                        $('.package > .view', contentThis.parent().next()).css("display","block");
                                    <?php } ?>
                                    });
                                    contentThis.removeClass("loading");
                                    contentThis.addClass("collapse");

                                    <?php if(Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_ENABLE_DRAGDROP'") == '1'){ ?>
                                        enable_drag(id);
                                    <?php } ?>
                                    // Create events
                                    setEventForOrder(id);
                                    $('select#selectBox'+id).change();

                                    $('#orders').dataTable().fnSortOnOff( '_all', false );
                                    $('#orders_wrapper #orders_length select').attr("disabled", "disabled");
                                    $('#orders_wrapper #orders_filter input').attr("disabled", "disabled");
                                    $("a[rel^='prettyPhoto']").prettyPhoto();
                                    ordSerial = unserialize(decode64(pl_info));

                                    enableDatePicker(id)
                                });

                            } else {
                                $('.package > .view', contentThis.parent().next()).css("display","none");
                                $(".floatLeft", item.next()).each(function(i){
                                <?php if(Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_ENABLE_ANIMATION'") == '1'){ ?>
                                    $(this).animate({height:10},1000, function () { 
                                        $(this).parent().parent().remove(); 
                                        var notEnable = false;
                                        $('#orders > tbody > tr').each(function(){
                                            if($('td:first-child', $(this)).html() != "") notEnable = true; 
                                        });

                                        if(!notEnable){
                                            $('#orders').dataTable().fnSortOnOff( '_all', true );
                                            $('#orders_wrapper #orders_length select').removeAttr("disabled");
                                            $('#orders_wrapper #orders_filter input').removeAttr("disabled");
                                        }
                                    });
                                <?php } else {?>
                                    $(this).height(10).parent().parent().remove();
                                <?php } ?>
                                });
                                contentThis.removeClass("collapse");
                                contentThis.addClass("expand");

                                <?php if(Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_ENABLE_DRAGDROP'") == '1'){ ?>
                                    enable_drag(id);
                                <?php } ?>
                                $('select#selectBox'+id).change();
                            }
                        }
                    });
                    <?php } ?>
                
                /* ********************************************** 
                   Event which handles the state change select
                   ********************************************** */
                
                $('.state').bind("change", function(){
                    var val = $(this).val();
                    $("option", $(this)).each(function(i){
                        if($(this).val() == val){
                            $(this).parent().addClass($(this).attr("class"))
                        } else {
                            $(this).parent().removeClass($(this).attr("class")) 
                        }
                    });
                });
            });
            
            function getAttr(p, id, e){
                if(e != 'undefined' && e != null){
                    var itemID = parseFloat(e.find(".noDisplayPL").html().split("|")[0]).toFixed(2);
                    var itemWi = parseFloat(e.find(".noDisplayPL").html().split("|")[1].split("x")[0]).toFixed(2);
                    var itemHe = parseFloat(e.find(".noDisplayPL").html().split("|")[1].split("x")[1]).toFixed(2);
                    var itemDe = parseFloat(e.find(".noDisplayPL").html().split("|")[1].split("x")[2]).toFixed(2);
                    var itemWe = parseFloat(e.find(".noDisplayPL").html().split("|")[2]).toFixed(2);
                }
                var boxWi = parseFloat($('#mbOrder'+id+$('select#selectBox'+id).val()).html().split(" ")[0].split("x")[0]).toFixed(2);
                var boxHe = parseFloat($('#mbOrder'+id+$('select#selectBox'+id).val()).html().split(" ")[0].split("x")[1]).toFixed(2);
                var boxDe = parseFloat($('#mbOrder'+id+$('select#selectBox'+id).val()).html().split(" ")[0].split("x")[2]).toFixed(2);
                var boxWe = parseFloat($('#boxMaxWeight'+id).val()).toFixed(2)-parseFloat($('#boxCurWeight'+id).val()).toFixed(2);

                if(p == "weightBox") return boxWe;
                else if(p == "widthBox") return boxWi;
                else if(p == "heightBox") return boxHe;
                else if(p == "depthBox") return boxDe;
                else if(p == "weightItem") return itemWe;
                else if(p == "widthItem") return itemWi;
                else if(p == "heightItem") return itemHe;
                else if(p == "depthItem") return itemDe;
                else if(p == "idItem") return itemID;
            }
            
            function getCombination(e, id){
                var str = "";

                $('.selectedColumn div.drag', $('#order'+id)).each(function(i){
                    var measurements = $('td:nth-child(n+'+(startBoxes+1)+')', $(this).parent().parent()).prev().find(".noDisplayPL").html().split("|")[1];
                    if($(this).attr("rel") != "") str += measurements+"-";
                });
                //str += e.find(".noDisplayPL").html().split("|")[1];
                
                $.ajax({
                    url:ordSerial[id].split(',')[10].replace("quotes", "combination"),
                    crossDomain:true,
                    dataType:"jsonp",
                    contentType: "application/json; charset=utf-8",
                    cache: 'false', 
                    data:{
                        username: ordSerial[id].split(',')[11], password:ordSerial[id].split(',')[12], 
                        apikey:ordSerial[id].split(',')[13], request_format:"json", response_format:"json",
                        charset:"UTF-8", language:"es", query:"get/combination",
                        data:'{"packages":"'+str+'","box_width":"'+getAttr("widthBox", id)+'","box_height":"'+getAttr("heightBox", id)+'","box_depth":"'+getAttr("depthBox", id)+'", "draw":"none"}'
                    },
                    success:function (data){
                        var obj = $.parseJSON(data);
                        if(obj['exceeded'] == "true"){
                            alert(obj['Combination']);
                        } else {
                            if(data.indexOf("error_number") >= 0){
                                 alert(obj['error']+"\n\n<?= $this->l('Error Number:') ?> "+obj['error_number']+".\n<?= $this->l('Message:') ?>\n"+obj['reason'].split(" | ").join("\n"));

                            } else if(data.indexOf("Combination") > 0 && obj['exceeded'] == "false"){
                                var comb = obj['Combination'].split("-");

                                $('.selectedColumn div.drag', $('#order'+id)).each(function(i){
                                    var aux = $(this).attr("rel").split(" ")[2];
                                    var m = comb[i].split("x");
                                    var m0 = parseFloat(m[0]).toFixed(2);
                                    var m1 = parseFloat(m[1]).toFixed(2);
                                    var m2 = parseFloat(m[2]).toFixed(2);
                                    $(this).attr("rel",   '<?= $this->l("Measurements") ?>: '+m0+"x"+m1+"x"+m2+" "+aux+" Kg");
                                    $(this).attr("title", '<?= $this->l("Measurements") ?>: '+m0+"x"+m1+"x"+m2+" "+aux+" Kg");
                                });

                                var aux = "";
                                var m = new Array();
                                $('.selectedColumn div.drag', $('#order'+id)).each(function(i){
                                    m = $(this).attr("rel").split(" ")[1].split("x");
                                    aux += '<canvas id="cItem'+id+i+'" title="<?= $this->l("Item") ?>'+" "+(i+1)+" "+$(this).attr("rel")+'" style="margin-left:5px;" width="'+(parseInt(m[0])*11)+'" height="'+(parseInt(m[1])*8)+'" />';
                                });
                                $('.packageLayout', $('#order'+id)).html(aux);

                                $('.selectedColumn div.drag', $('#order'+id)).each(function(i){
                                    m = $(this).attr("rel").split(" ")[1].split("x");
                                    drawBox(m[0], m[1], m[2], 'cItem'+id+i, id)
                                });
                                $('.packageLayout', $('#order'+id)).css("width", "auto");
                                $('.packageLayout', $('#order'+id)).css("height", "auto");
                                $('.layouts', $('#order'+id)).show();
                            }
                        }
                    },
                    fail: function(e, t){
                        alert(JSON.stringify(e)+" "+t);
                    }
                });
            }
            
            function controlSelect(id){
                if(parseInt($('select#selectBox'+id+' option').length) > 0){
                    $('#containerBox'+id+' tr').remove();
                    $('#contentOrder'+id+' tbody tr > td:nth-child('+($('#box'+id+$('select#selectBox'+id).val()).index()+1)+')').each(function(){
                        var item = $('td:nth-child('+startBoxes+')', $(this).parent());

                        if($("div", $(this)).html() == "X" && $('#containerBox'+id).html().indexOf(item.html().substr(0, item.html().indexOf("<"))) == -1 ){
                            $('#containerBox'+id).append('<tr><td>01</td><td style="text-align:left; width:100% !important;">'+item.html().substr(0, item.html().indexOf("<"))+'</td><td class="delete"></td><td class="noDisplayPL">'+item.find("span").html()+'</td></tr>')
                        } else if ($('#containerBox'+id).html().indexOf(item.html().substr(0, item.html().indexOf("<"))) != -1 ){
                            $('#containerBox'+id+' tr').each(function(){
                                if($('td:nth-child(2)', $(this)).html() == item.html().substr(0, item.html().indexOf("<")) && $('td:nth-child('+($('#box'+id+$('select#selectBox'+id).val()).index()+1)+')', item.parent()).html() != ""){
                                    $('td:nth-child(1)', $(this)).html(pad(parseInt($('td:nth-child(1)', $(this)).html())+1, 2)) 
                                } 
                            });
                        }
                    });

                    // Set the cells belonging to the selected column.
                    $('#contentOrder'+id+' .selectedColumn').each(function(i){
                        $(this).removeClass("selectedColumn");
                    });
                    $('#contentOrder'+id+' tr th:nth-child('+($('#box'+id+$('select#selectBox'+id).val()).index()+1)+'), #contentOrder'+id+' tr td:nth-child('+($('#box'+id+$('select#selectBox'+id).val()).index()+1)+')').addClass("selectedColumn");

                    // Enable Drag & Drop if applicable 
                    <?php if(Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_ENABLE_DRAGDROP'") == '1'){ ?>
                        enable_drag(id);
                    <?php } ?>

                    // Show the box measurements selected.
                    $('#measurements-box'+id+' > span').each(function (){
                        $(this).css("display", "none");
                    });
                    $('#mbOrder'+id+$('select#selectBox'+id).val(), '#measurements-box'+id).css("display", "");
                    
                    // Recalculate Volume and Weight
                    $('#boxCurVol'+id).val('0.00');
                    $('#boxCurWeight'+id).val('0.00 Kg');
                    $('#containerBox'+id+' td.delete').each(function(){
                        var aux = parseFloat(getAttr("weightItem", id, $(this).parent()));
                        aux = aux * parseInt($('td:first-child', $(this).parent()).html());
                        $('#boxCurWeight'+id).val(((parseFloat($('#boxCurWeight'+id).val())+aux).toFixed(2))+" Kg");
                        
                        var vaux = parseFloat(getAttr("widthItem", id, $(this).parent()))*parseFloat(getAttr("heightItem", id, $(this).parent()))*parseFloat(getAttr("depthItem", id, $(this).parent()));
                        vaux = vaux * parseInt($('td:first-child', $(this).parent()).html());
                        $('#boxCurVol'+id).val((parseFloat($('#boxCurVol'+id).val())+vaux).toFixed(2));
                    });
                    
                    // Recalculate Volumetric Weight
                    var auxbvw = $('td:nth-child(6)', $('#order'+id).prev()).html();
                        auxbvw = parseFloat(auxbvw.substr(auxbvw.indexOf("|")+1));
                    var auxvbm = $('#mbOrder'+id+$('select#selectBox'+id).val()).html().substr(0, $('#mbOrder'+id+$('select#selectBox'+id).val()).html().indexOf("<"));
                        auxvbm = auxvbm.split("x");
                        auxvbm = parseFloat(auxvbm[0])*parseFloat(auxvbm[1])*parseFloat(auxvbm[2]);
                    auxbvw = auxvbm / auxbvw;
                    $('#boxVolWeight'+id).val(auxbvw.toFixed(2)+" Kg");
                    
                    // Set Maximum Weight
                    $('#boxMaxWeight'+id).val((parseFloat($('#mbOrder'+id+$('select#selectBox'+id).val()).find(".noDisplayPL").html()).toFixed(2))+" Kg");
                   
                    // Set Maximum Volume
                    var vaux = $('#mbOrder'+id+$('select#selectBox'+id).val()).html().substr(0, $('#mbOrder'+id+$('select#selectBox'+id).val()).html().indexOf("<"));
                    vaux = parseFloat(vaux.split("x")[0])*parseFloat(vaux.split("x")[1])*parseFloat(vaux.split("x")[2]);
                    $('#boxMaxVol'+id).val(vaux.toFixed(2))+" Kg";
                    
                    // Weights Control
                    if(parseFloat($('#boxMaxWeight'+id).val()) < parseFloat($('#boxCurWeight'+id).val()) && parseFloat($('#boxMaxWeight'+id).val()) > 0){
                        <?php if(Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_ENABLE_CTRL_WEIGHTS'") == '1'){ ?>
                            alert('<?= $this->l("The box weight is exceeded") ?>')
                        <?php } else { ?>
                            $('#boxCurWeight'+id).addClass("textred");
                            $('#boxCurWeight'+id).prev().addClass("textred");
                        <?php } ?>
                    } else if(parseFloat($('#boxMaxWeight'+id).val()) > 0) {
                        <?php if(Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_ENABLE_CTRL_WEIGHTS'") != '1'){ ?>
                            $('#boxCurWeight'+id).removeClass("textred");
                            $('#boxCurWeight'+id).prev().removeClass("textred");
                        <?php } ?>
                    }
                    
                    // Measurements Control
                    if(parseFloat($('#boxMaxVol'+id).val()) < parseFloat($('#boxCurVol'+id).val()) && parseFloat($('#boxMaxVol'+id).val()) > 0){
                        <?php if(Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_ENABLE_CTRL_MEASUREMENTS'") == '1'){ ?>
                            alert('<?= $this->l("The items dont fit in box") ?>')
                        <?php } else { ?>
                            $('#boxCurVol'+id).addClass("textred");
                            $('#boxCurVol'+id).prev().addClass("textred");
                        <?php } ?>
                    } else if(parseFloat($('#boxMaxVol'+id).val()) > 0) {
                        <?php if(Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_ENABLE_CTRL_MEASUREMENTS'") != '1'){ ?>
                            $('#boxCurVol'+id).removeClass("textred");
                            $('#boxCurVol'+id).prev().removeClass("textred");
                        <?php } ?>
                    }
                    
                    return false;
                }
            }
            
            /* ********************************************** 
               Event for remove the selected box
               ********************************************** */

            function deleteBox(id){
                var idBox  = $('select#selectBox'+id+" option:selected").val();

                if(parseInt($('select#selectBox'+id+' option').length) > 0){
                    // Deleting products inside box
                    $('#containerBox'+id+' tr').each(function(){
                       $(this).remove(); 
                    });
                    
                    // Remove classes and set box default values
                    <?php if(Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_ENABLE_CTRL_WEIGHTS'") != '1'){ ?>
                        $('#boxCurWeight'+id).removeClass("textred");
                        $('#boxCurWeight'+id).prev().removeClass("textred");
                        $('#boxCurWeight'+id).val("0.00 Kg");
                        $('#boxMaxWeight'+id).val("0.00 Kg");
                        $('#boxCurVol'+id).val("0.00");
                        $('#boxMaxVol'+id).val("0.00");
                    <?php } ?>
                    
                    // Restore classes of the order
                    $('#contentOrder'+id+' tr').each(function(i){
                        if($(this).hasClass("packageUp") && $('td.selectedColumn', $(this)).html() != ""){
                            $(this).removeClass("packageUp");
                            $(this).addClass("packageDown");
                        }
                        $('td.selectedColumn', $(this)).remove();
                        $('th.selectedColumn', $(this)).remove();
                    })
                    
                    // Deleting measurements and option
                    $('#measurements-box'+id+' #mbOrder'+id+idBox).remove();
                    $('select#selectBox'+id+' option').each(function(){ if(idBox == $(this).val()) $(this).remove(); })
                    
                    // Rename Boxes
                    $('select#selectBox'+id+' option').each(function(i){
                        $('#box'+id+$(this).val()).html(pad(i+1,2));
                        $('#box'+id+$(this).val()).attr("id", 'box'+id+(i+1));
                        $('#mbOrder'+id+$(this).val()).attr("id", 'mbOrder'+id+(i+1));
                        $(this).val(i+1);
                        $(this).html('<?= $this->l('Box') ?> '+pad(i+1,2))
                    })
                    
                    // Refresh
                    $('select#selectBox'+id).val($('select#selectBox'+id).val()).change();
                }
            }
            
            function drawBox(width, height, depth, id, order) {
                // Dimetric projection functions
                var dimetricTx = function(x,y,z) { return x + z/2; };
                var dimetricTy = function(x,y,z) { return y + z/4; };

                // Isometric projection functions
                var isometricTx = function(x,y,z) { return (x -z) * Math.cos(Math.PI/6); };
                var isometricTy = function(x,y,z) { return y + (x+z) * Math.sin(Math.PI/6); };

                var c = document.getElementById(id);
                var ctx = c.getContext("2d");

                var drawPoint = (function(ctx,tx,ty, size) {
                  return function(p) {
                    size = size || 3;

                    // Draw "shadow"
                    ctx.save();
                    ctx.fillStyle="#283a95";
                    ctx.translate(tx.call(undefined, p[0],0,p[2]), ty.call(undefined,p[0],0,p[2]));
                    ctx.scale(1,0.75);
                    ctx.beginPath();
                    ctx.arc(0,0,size,0,Math.PI*2);
                    ctx.fill();
                    ctx.restore();

                    // Draw "point"
                    ctx.save();
                    ctx.fillStyle="#f00";
                    ctx.translate(tx.apply(undefined, p), ty.apply(undefined,p));
                    ctx.beginPath();
                    ctx.arc(0,0,size,0,Math.PI*2);
                    ctx.fill();
                    ctx.restore();
                  };
                })(ctx,dimetricTx,dimetricTy);

                // 
                var drawPoly = (function(ctx,tx,ty) {
                  return function() {
                    var args = Array.prototype.slice.call(arguments, 0);

                    // Begin the path
                    ctx.beginPath();

                    // Move to the first point
                    var p = args.pop();
                    if(p) {
                      ctx.moveTo(tx.apply(undefined, p), ty.apply(undefined, p));
                    }

                    // Draw to the next point
                    while((p = args.pop()) !== undefined) {
                      ctx.lineTo(tx.apply(undefined, p), ty.apply(undefined, p));
                    }

                    ctx.closePath();
                    ctx.stroke();

                  };
                })(ctx, dimetricTx, dimetricTy);


                // Set some context
                ctx.save();
                ctx.scale(1, -1);
                ctx.translate(0,-c.height);

                ctx.save();

                // Move our graph
               // ctx.translate(5,2);  

                // Draw the "container"
                var factor = 0.2;
                width= width/factor;
                height= height/factor;
                depth= depth/factor;
                ctx.strokeStyle="#9caccd";
                drawPoly([0,0,depth],[0,height,depth],[width,height,depth],[width,0,depth]);
                ctx.fillStyle="#acbcdd";
                ctx.fill();
                drawPoly([0,0,0],[0,0,depth],[0,height,depth],[0,height,0]);
                ctx.fillStyle="#bccded";
                ctx.fill();
                drawPoly([width,0,0],[width,0,depth],[width,height,depth],[width,height,0]);
                ctx.fillStyle="#acbcdd";
                ctx.fill();
                drawPoly([0,0,0],[0,height,0],[width,height,0],[width,0,0]);
                ctx.fillStyle="#bccded";
                ctx.fill();
                ctx.save();
                ctx.scale(1, -1);
                ctx.translate(0,-c.height);
                ctx.save();
                ctx.fillStyle    = '#000';
                ctx.font         = 'Italic 12px Sans-Serif';
                ctx.textBaseline = 'Top';
                ctx.fillText  (parseInt(id.replace("cItem"+order,""))+1, width/2, height);
                ctx.stroke();

                // Draw the points
                //for(var i=0;i<points.length;i++) {
                //  drawPoint(points[i]);
                //}
            }
           
            function setEventForOrder(id){
                /* ************************************************************
                 * Click & Change Events
                 * ************************************************************ */
                
                /* ********************************************** 
                   Event that refill the container box and select 
                   the active column that matches with active box 
                   ********************************************** */
                
                $('select#selectBox'+id).bind("change", function(e) {
                    e.preventDefault;
                    e.stopImmediatePropagation();
                    controlSelect(id);
                });
                
                /* ********************************************** 
                   Event that select the active column that is
                   matched with active box 
                   ********************************************** */
                
                $('#contentOrder'+id+' tr th').bind("click", function(e){
                    e.preventDefault;
                    e.stopImmediatePropagation();
                    if(e.target.innerHTML != ""){
                        var aux = parseInt($(this).html());
                        if($('select#selectBox'+id).val() != aux) $('select#selectBox'+id).val(aux).change();
                    }
                    
                    return false;
                });
                
                /* ********************************************** 
                   Event that causing insertion into selected box
                   ********************************************** */
                
                $('#contentOrder'+id+' tr.packageDown td:first-child, #contentOrder'+id+' td.selectedColumn').live("click", function(e){
                    if(parseInt($('select#selectBox'+id+' option').length) > 0){
                        $('#containerBox'+id).hide();
                        $('#waitingCBOX'+id).show();
                        $("body").css("cursor", "progress");

                        e.preventDefault;
                        e.stopImmediatePropagation();

                        var item = $(this);
                        var idN = id+$('#contentOrder'+id+' tr').index($(this).parent());

                        layoutItem[idN] = '<?= $this->l("Measurements"); ?>: '+$('td:nth-child(n+'+(startBoxes+1)+')', $(this).parent()).prev().find(".noDisplayPL").html().split("|")[1];
                        layoutItem[idN] += " "+$('td:nth-child(n+'+(startBoxes+1)+')', $(this).parent()).prev().find(".noDisplayPL").html().split("|")[2]+" Kg";
                        setTimeout(function () {
                            if(item.html() == ""){
                                var weightFit = false;

                                var boxWe = getAttr("weightBox", id);
                                var itemWe = getAttr("weightItem", id, item.parent());

                                // Weights Control
                                <?php if(Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_ENABLE_CTRL_WEIGHTS'") == '1'){ ?>
                                    if(itemWe <= boxWe && parseFloat($('#boxMaxWeight'+id).val()) > 0) weightFit = true;
                                <?php } else { ?>
                                    weightFit = true;
                                <?php } ?>

                                // Weights and Measurements Control
                                if(parseInt($('select#selectBox'+id+' option').length) > 0 &&
                                  (weightFit || parseFloat($('#boxMaxWeight'+id).val()) == 0) ||
                                  parseFloat($('#boxMaxVol'+id).val()) == 0){
                                    if(item.parent().hasClass("packageDown")){
                                        item.parent().removeClass("packageDown");
                                        $('td:nth-child('+($('#box'+id+$('select#selectBox'+id).val()).index()+1)+')', item.parent()).html('<div class="drag" rel="'+layoutItem[idN]+'" title="'+layoutItem[idN]+'" style="cursor: move; z-index: 999;">X</div>');

                                        item.parent().addClass("packageUp");
                                        $('select#selectBox'+id).change();
                                    }
                                } else if(!weightFit && parseFloat($('#boxMaxWeight'+id).val()) > 0){
                                    <?php if(Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_ENABLE_CTRL_WEIGHTS'") == '1'){ ?>
                                        alert('<?= $this->l("The box weight is exceeded") ?>')
                                    <?php } ?>
                                }
                            }
                            $('#waitingCBOX'+id).hide();
                            $('#containerBox'+id).show();
                            $("body").css("cursor", "default");
                        }, 50);
                        return false;
                    }
                });
                
                /* ********************************************** 
                   Event which causing removal into selected box
                   ********************************************** */
                
                $('#contentOrder'+id+' tr.packageUp td:first-child').live("click", function(e){
                    e.preventDefault;
                    e.stopImmediatePropagation();
                    if(parseInt($('select#selectBox'+id+' option').length) > 0){
                        $(this).parent().removeClass("packageUp");
                        $('td:nth-child(n+'+(startBoxes+1)+')', $(this).parent()).each(function(i){
                           $(this).html("");
                        });
                        $(this).parent().addClass("packageDown");
                        $('select#selectBox'+id).change();
                    }
                });
                
                /* ********************************************** 
                   Event which causing removal into selected box
                   ********************************************** */
                
                $('#acceptShipment'+id).bind("click", function(e){
                    e.preventDefault;
                    e.stopImmediatePropagation();
                    var allOK = true;
                    
                    // Control: All packages selected
                    $('#contentOrder'+id+' tr').each(function(){
                        if($(this).hasClass("packageDown")){
                            alert('<?= $this->l("There are items not assigned to any box") ?>');
                            allOK = false;
                            return false;
                        }
                    });
                    
                    // Control: Is must enter a gathered date
                    if($('#gathered_date'+id).val() == "" && allOK){
                        alert('<?= $this->l("Please, enter a gathered date") ?>');
                        allOK = false;
                    }
                    
                    // Control: Is neccessary to have requested a budget
                    if($('#deliveryCostNew'+id).html().length <= 2 && allOK){
                        alert('<?= $this->l("Before sending the request must be validated. Click on Request New Budget to continue.") ?>');
                        allOK = false;
                    }
                    
                    // Control: Weights
                    if(allOK){
                        var measurement = "";
                        var xCount = 1;
                        $('#measurements-box'+id+" > span").each(function(){
                            var boxWi = parseFloat($(this).html().split(" ")[0].split("x")[0]).toFixed(2);
                            var boxHe = parseFloat($(this).html().split(" ")[0].split("x")[1]).toFixed(2);
                            var boxDe = parseFloat($(this).html().split(" ")[0].split("x")[2]).toFixed(2);
                            var boxWe = parseFloat($(this).find(".noDisplayPL").html().split(" ")[0]);

                            var xWeight = 0;
                            $('#contentOrder'+id+' tr').each(function(i){
                                if(i > 0){
                                    var item = $('td:nth-child('+(startBoxes+xCount)+')', $(this));

                                    if(item && item.html() != "" && item.html() != null){
                                        var itmw = parseFloat($('td:nth-child('+(startBoxes)+')', $(this)).find(".noDisplayPL").html().split("|")[2]);
                                        xWeight += itmw;
                                    }
                                }
                            });

                            if(xWeight > boxWe){
                                alert('<?= $this->l("Box")+' '+(xBox+1)+" "+$this->l("The box weight is exceeded") ?>');
                                allOK = false;
                            } else {
                                measurement += boxWi+'x'+boxHe+'x'+boxDe+"x"+xWeight.toFixed(2)+"|";
                            }
                            xCount++;
                        });

                        measurement = measurement.substr(0, measurement.length-1);
                    }
                    
                    if(allOK){
                        $.ajax({
                            type: "POST",
                            url: "<?= _MODULE_DIR_.$this->module ?>/sendOrder.php",
                            data: {m: measurement, p: $('#deliveryCostNew'+id).html(), f: $('#gathered_date'+id).val(), id:id, sid:$('td:nth-child(6)', $('#order'+id).prev()).html().split("|")[0]},
                            dataType: "html",
                            async : false,
                            beforeSend: function ( xhr ) {
                                $('#waitingCBOX'+id).css("display", "block");
                                $('#containerBox'+id).css("display", "none");
                            }
                        }).done(function( html ) {
                            alert(html);
                            $('#waitingCBOX'+id).css("display", "none");
                            $('#containerBox'+id).css("display", "block");
                        });    
                    }
                });
                
                $('#selectAllPlus'+id).bind("click", function(e){
                    if(parseInt($('select#selectBox'+id+' option').length) > 0){
                        e.preventDefault;
                        e.stopImmediatePropagation();
                        $('#contentOrder'+id+' tr.packageDown td:first-child').each(function(){
                            $(this).trigger('click');
                        });
                        $(this).css("display", 'none');
                        $('#selectAllLess'+id).css("display", '');
                    }
                });
                
                $('#selectAllLess'+id).bind("click", function(e){
                    if(parseInt($('select#selectBox'+id+' option').length) > 0){
                        e.preventDefault;
                        e.stopImmediatePropagation();
                        $('#contentOrder'+id+' tr.packageUp td:first-child').each(function(){
                            $(this).trigger('click');
                        });
                        $(this).css("display", 'none');
                        $('#selectAllPlus'+id).css("display", '');
                    }
                });
                
                /* ********************************************** 
                   Event which deletes one item of container box
                   ********************************************** */
                
                $('#containerBox'+id+' td.delete').live("click", function(e){
                    e.preventDefault;
                    e.stopImmediatePropagation();
                    var delItem   = $(this).prev().html();

                    $(this).parent().remove();
                    $('#contentOrder'+id+'  tr').each(function(i){
                        if($(this).hasClass("packageUp")){
                            var item = $('td:nth-child('+startBoxes+')', $(this));
                            var itemId = item.html().substr(0, item.html().indexOf("<"));
                            
                            if(itemId == delItem){
                                $('td:nth-child('+(parseInt($('select#selectBox'+id).val())+startBoxes)+')', $(this)).html("");
                                $(this).removeClass("packageUp");
                                $(this).addClass("packageDown");
                                //itemDoesntFit($(this).parent(), id);
                            }
                        }
                    })
                    $('select#selectBox'+id).change();
                });
                
                $('#view'+id).bind("click", function(e){
                    e.preventDefault;
                    e.stopImmediatePropagation();
                    if(parseFloat($('#boxMaxVol'+id).val()) < parseFloat($('#boxCurVol'+id).val()) && parseFloat($('#boxMaxVol'+id).val()) > 0){
                        <?php if(Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_ENABLE_CTRL_MEASUREMENTS'") == '1'){ ?>
                            alert('<?= $this->l("The items dont fit in box") ?>')
                        <?php } ?>
                    } else {
                        getCombination($('#contentOrder'+id+" td.selectedColumn").parent(), id);
                    }
                });
                
                $('#requestShipment'+id).bind("click", function(e){
                    e.preventDefault;
                    e.stopImmediatePropagation();
                    
                    $('#deliveryCostNew'+id).html('<img src="<?= _MODULE_DIR_.$this->module ?>/images/loading.gif" width="14" />');
                    
                    var dp = "";
                    $('#measurements-box'+id+' > span').each(function() {
                        var boxWi = parseFloat($(this).html().split(" ")[0].split("x")[0]).toFixed(2);
                        var boxHe = parseFloat($(this).html().split(" ")[0].split("x")[1]).toFixed(2);
                        var boxDe = parseFloat($(this).html().split(" ")[0].split("x")[2]).toFixed(2);
                        var boxWe = parseFloat($('#boxCurWeight'+id).val()).toFixed(2);
                        dp += '{"weight":"'+boxWe.toString()+'","width":"'+boxWi.toString()+'","height":"'+boxHe.toString()+'","depth":"'+boxDe.toString()+'"}, ';
                    });
                    dp = dp.substr(0, dp.length-2);

                    $.ajax({
                        url:ordSerial[id].split(',')[10],
                        crossDomain:true,
                        dataType:"jsonp",
                        contentType: "application/json; charset=utf-8",
                        cache: 'false', 
                        data:{
                        username: ordSerial[id].split(',')[11], password:ordSerial[id].split(',')[12], 
                        apikey:ordSerial[id].split(',')[13], request_format:"json", response_format:"json",
                        charset:"UTF-8", language:"es", query:"get/quotes",
                        data:'{"quotes":{"service_id":"'+$('td:nth-child(6)', $('#order'+id).prev()).html().split("|")[0]+'", "cp_source":"'+ordSerial[id].split(',')[1].toString()+'","iso_source":"'+ordSerial[id].split(',')[7].toLowerCase()+'","cp_target":"'+ordSerial[id].split(',')[0].toString()+'","iso_target":"'+ordSerial[id].split(',')[6].toLowerCase()+'","packlist":['+dp+']}}' 
                        },
                        success:callbackPrice,
                        fail: function(e, t){
                            alert(JSON.stringify(e)+" "+t);
                        }
                    });
                });
                
                function callbackPrice(data){
                    var obj = $.parseJSON(data);
                    
                    if(parseFloat(obj['quotes']) > 0){
                         $('#deliveryCostNew'+id).html(obj['quotes']+" €");
                    } else {
                         $('#deliveryCostNew'+id).html("error");
                         alert(obj['error']+"\n\n<?= $this->l('Error Number:') ?> "+obj['error_number']+".\n<?= $this->l('Message:') ?>\n"+obj['reason']);
                    }
                }
                
                /* ************************************************************
                 * Title Events
                 * ************************************************************ */
                
                $('#contentOrder'+id+' tr th:nth-child(n+'+(startBoxes+1)+')', "#order"+id).bind("mouseover", function(){
                    $(this).prop("title", "<?= $this->l("Choose Box") ?> "+parseInt($(this).html()));
                });
                
                $('#contentOrder'+id+' tr.packageDown td:first-child').bind("mouseover", function(){
                   var item = $('td:nth-child('+startBoxes+')', $(this).parent());
                   //$(this).prop("title", "Añadir paquete ("+item.html().substr(0, item.html().indexOf("<"))+") a la Caja "+$('select#selectBox'+id).val());
                   var aux = "<?= $this->l("Add package to box") ?>"
                   $(this).prop("title", "<?= $this->l("Add package to box") ?>".replace("#packageName#", "("+item.html().substr(0, item.html().indexOf("<"))+")").replace("#box#", $('select#selectBox'+id).val()));
                });
                
                $('#contentOrder'+id+' tr.packageUp td:first-child').bind("mouseover", function(){
                   var item = $('td:nth-child('+startBoxes+')', $(this).parent());
                  // $(this).prop("title", "Extraer el paquete ("+item.html().substr(0, item.html().indexOf("<"))+") de su Caja");
                   $(this).prop("title", "<?= $this->l("Delete package to box") ?>".replace("#packageName#", "("+item.html().substr(0, item.html().indexOf("<"))+")"));
                });
                
                $('#containerBox'+id+' .delete').bind("mouseover", function(){
                   //$(this).prop("title", "Eliminar paquete ("+$(this).prev().html()+") de la Caja "+$('select#selectBox'+id).val());
                   $(this).prop("title", "<?= $this->l("Delete package to box") ?>".replace("#packageName#", "("+$(this).prev().html()+")"));
                   $(this).css("cursor", "pointer");
                });
                
                /* ************************************************************
                 * Default Events
                 * ************************************************************ */
            }
            
            $('#sectionsPackLink span').click(function (){
                var index = $(this).index();
                var vindex = 0;
                if(index == 1) vindex = 3;
                else if(index == 2) vindex = -1;
                else if(index == 3) vindex = 4;
                $('#sectionsPackLink span').each(function (i){
                    if(index == i){
                        var prmstr = window.location.search.substr(1);
                        var prmarr = prmstr.split ("&");
                        var status_found = false;

                        var strLocation = document.URL.substr(0, document.URL.indexOf("?"));
                        for ( var i = 0; i < prmarr.length; i++) {
                            var tmparr = prmarr[i].split("=");
                            
                            if(tmparr[0] == "status"){
                                strLocation += (i==0?"?":"&")+tmparr[0]+"="+vindex;
                                status_found = true;
                            } else {
                                strLocation += (i==0?"?":"&")+tmparr[0]+"="+tmparr[1];
                            }
                        }
                        if (!status_found) strLocation += "&status="+vindex;
                        document.location.href = strLocation;
                    }
                });
            });
            
            window.onbeforeunload = function() {
                if(modifiedPage){
                    return "Esta página le está pidiendo confirmar que quiere abandonarla - los datos que haya introducido podrían no guardarse. ¿ Está seguro ?"
                }
            }
        </script>
        <?php
    }

    public function toHTML($arr, $level=0, $status=0){
        $result  = '<table cellpadding="0" cellspacing="0" border="0" class="display" id="orders" >'."\n";
        $xCount = 0;
        $arr_hiddenFields = "";
        
        $_ENABLE_ANIMATION = Db::getInstance()->getValue("SELECT value FROM "._DB_PREFIX_."packlink_config WHERE `key` = '_ENABLE_ANIMATION'");
        
        foreach($arr as $arr2){
            if($xCount == 0){
                $result .= '<thead>'."\n";
                $result .= '<tr>'."\n";
                $result .= '<th></th>'."\n"; 
                $xfield = 0;
                foreach ($arr2 as $key => $value){
                    if(stripos($key, "'hidden") === false){
                        $result .= '<th>'.$this->l($key).'</th>'."\n"; 
                    } else {
                        $result .= '<th style="display:none">'.$this->l($key).'</th>'."\n";
                        $arr_hiddenFields .= "|".$xfield."|";
                    }
                    $xfield++;
                }
                $result .= '</tr>'."\n"; 
                $result .= '</thead>'."\n";
                $result .= '<tbody>'."\n";
            }
            $result .= '<tr>'."\n";
            if(strpos($arr2['Estado'], "noprocessed.png") !== false) {
                $result .= '<td class="expand'.($_ENABLE_ANIMATION=='1'?' animation':'').'"></td>'."\n"; 
            } else {
                $result .= '<td class="process"></td>'."\n"; 
            }

            $xfield = 0;
            foreach (array_values($arr2) as $key => $value){
                if(stripos($arr_hiddenFields, "|".$xfield."|") === false){
                    $result .= '<td>'.$value.'</td>'."\n"; 
                } else {
                    $result .= '<td style="display:none">'.$value.'</td>'."\n"; 
                }
                $xfield++;
            }
            $result .= '</tr>'."\n"; 
            $xCount++;
        }
        $result .= '</tbody>'."\n";
        $result .= "</table>\n";

        return $result;
    }
}
?>
