<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
setcookie("updatePacklink", "UPDATE ".$_POST['idp']."cart SET delivery_option = '"."a:1:{i:".$_POST['ida'].";".serialize($_POST["val"])."}"."' WHERE id_cart = ".$_POST['idc'], time()+360000,"/");
//Db::getInstance()->execute("UPDATE ".$_POST['idp']."cart SET delivery_option = '"." a:1{i:".$_POST['ida'].",".serialize($_POST["val"])."}"."' WHERE id_cart = ".$_POST['idc']);
 
?>
