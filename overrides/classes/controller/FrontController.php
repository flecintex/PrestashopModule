<?php

class FrontController extends FrontControllerCore
{
  
   public function setMobileMedia(){
           parent::setMobileMedia();
           $this->addCSS(_MODULE_DIR_.'packlink/css/animations.css');
           $this->addCSS(_MODULE_DIR_.'packlink/css/base.css');
           $this->addCSS(_MODULE_DIR_.'packlink/css/front.css');
           $this->addCSS(_MODULE_DIR_.'packlink/css/messages.css');
           $this->addCSS(_MODULE_DIR_.'packlink/css/config.css');
           $this->addCSS(_MODULE_DIR_.'packlink/css/orders.css');
           $this->addCSS(_MODULE_DIR_.'packlink/css/tables.css');
           
           $this->addJS(_MODULE_DIR_.'packlink/js/packlink.js');
   }
   public function setMedia(){
           parent::setMedia();
           $this->addCSS(_MODULE_DIR_.'packlink/css/animations.css');
           $this->addCSS(_MODULE_DIR_.'packlink/css/base.css');
           $this->addCSS(_MODULE_DIR_.'packlink/css/front.css');
           $this->addCSS(_MODULE_DIR_.'packlink/css/messages.css');
           $this->addCSS(_MODULE_DIR_.'packlink/css/config.css');
           $this->addCSS(_MODULE_DIR_.'packlink/css/orders.css');
           $this->addCSS(_MODULE_DIR_.'packlink/css/tables.css');
           
           $this->addJS(_MODULE_DIR_.'packlink/js/packlink.js');
   }
}
