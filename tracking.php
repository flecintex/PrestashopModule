<?php
global $smarty;
include('../../config/config.inc.php');
include('../../header.php');

$url = 'http://www.packlink.es/es/seguimiento-envios/';
$fields = array('num'=>urlencode($_POST['num']));

//url-ify the data for the POST
foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
rtrim($fields_string,'&');

//connection
$ch = curl_init();
    curl_setopt($ch,CURLOPT_URL, $url);
    curl_setopt($ch,CURLOPT_POST, count($fields));
    curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $datos = curl_exec($ch);
curl_close($ch);

$posi1 = stripos($datos,'<div id="contenedorDerecha"');
$posi2 = stripos($datos,'<div id="cajaLogosPie">');
$datos = substr($datos, $posi1 , $posi2-$posi1);
$styles = '
    <style>
        #contenedorDerecha .TrackBulNum { padding: 10px 0; }
        #contenedorDerecha .packLinkTrackTexto { display:none; }
        #contenedorDerecha .packLinkTrackCaja { display:none; }
        #contenedorDerecha #formTrackSubmit { display:none; }
        #contenedorDerecha .tPar b { background-color: #f0f0f0; font-size: 1.1em; line-height: 16px; padding-top: 8px; padding-left: 5px; padding-right: 5px; }
        #contenedorDerecha .tImpar b { background-color: #ffffff; font-size: 1.1em; line-height: 16px; padding-top: 8px; padding-left: 5px; padding-right: 5px; }
        #contenedorDerecha .tPar span { padding: 0 5px 0 0; color: #555555; }
        #contenedorDerecha .tImpar span { padding: 0 5px 0 0; color: #555555; }
        #contenedorDerecha .tPar div { padding-bottom: 5px; float: right; }
        #contenedorDerecha .tImpar div { padding-bottom: 5px; float: right; }
        #contenedorDerecha b { display: block; padding-bottom: 5px; }
        #contenedorDerecha h2 { padding-top:10px; }
        #contenedorDerecha .textoBarraAzulFunciona { display:none }
        #contenedorDerecha .infoPack > br { display: none; }
    </style>';

//$datos = htmlspecialchars(substr($datos, 0, strlen($datos)-21));
$datos = $styles. str_replace("</h1>", "</h2>", str_replace("<h1", "<h2", substr($datos, 0, strlen($datos)-21)));
$smarty->assign("datos", $datos);
$smarty->assign("dat_sdiv", $dat_ediv);
$smarty->assign("dat_ediv", $dat_ediv);
$smarty->display(dirname(__FILE__) . '/tracking.tpl');
     
include('../../footer.php');
?>