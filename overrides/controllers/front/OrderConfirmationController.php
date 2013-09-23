<?php

class OrderConfirmationController extends OrderConfirmationControllerCore
{
    
    public $php_self = 'order-confirmation';
    public $id_cart;
    public $id_module;
    public $id_order;
    public $reference;
    public $secure_key;
    public function init()
    {
        parent::init();

        $this->id_cart = (int)(Tools::getValue('id_cart', 0));
        $is_guest = false;

        /* check if the cart has been made by a Guest customer, for redirect link */
        if (Cart::isGuestCartByCartId($this->id_cart))
        {
                $is_guest = true;
                $redirectLink = 'index.php?controller=guest-tracking';
        }
        else
                $redirectLink = 'index.php?controller=history';

        $this->id_module = (int)(Tools::getValue('id_module', 0));
        $this->id_order = Order::getOrderByCartId((int)($this->id_cart));
        $this->secure_key = Tools::getValue('key', false);
        $order = new Order((int)($this->id_order));
        if ($is_guest)
        {
                $customer = new Customer((int)$order->id_customer);
                $redirectLink .= '&id_order='.$order->reference.'&email='.urlencode($customer->email);
        }
        if (!$this->id_order || !$this->id_module || !$this->secure_key || empty($this->secure_key))
                Tools::redirect($redirectLink.(Tools::isSubmit('slowvalidation') ? '&slowvalidation' : ''));
        $this->reference = $order->reference;
        if (!Validate::isLoadedObject($order) || $order->id_customer != $this->context->customer->id || $this->secure_key != $order->secure_key)
                Tools::redirect($redirectLink);
        $module = Module::getInstanceById((int)($this->id_module));
        if ($order->payment != $module->displayName)
                Tools::redirect($redirectLink);
    }
    public function initContent()
    {
            parent::initContent();

            $this->context->smarty->assign(array(
                    'is_guest' => $this->context->customer->is_guest,
                    'HOOK_ORDER_CONFIRMATION' => $this->displayOrderConfirmation(),
                    'HOOK_PAYMENT_RETURN' => $this->displayPaymentReturn()
            ));

            if ($this->context->customer->is_guest)
            {
                    $this->context->smarty->assign(array(
                            'id_order' => $this->id_order,
                            'reference_order' => $this->reference,
                            'id_order_formatted' => sprintf('#%06d', $this->id_order),
                            'email' => $this->context->customer->email
                    ));
                    /* If guest we clear the cookie for security reason */
                    $this->context->customer->mylogout();
            }

            // PackLink Changes ----------------------------------------------------------------------------------------------------------------------------------------
           
            // ----------------------
            // Update Packlink Orders
            // ----------------------
            $delivery_option = unserialize(Db::getInstance()->getValue('SELECT `delivery_option` FROM '._DB_PREFIX_.'cart a WHERE a.`id_cart` = '.$this->id_cart));
            $delivery_option = trim(implode("", $delivery_option));
            $delivery_option2 = explode(",", $delivery_option);
            $id_srv_packlink = $delivery_option2[0];
            $id_carrier_packlink = $delivery_option2[1];
            $tax_delivery_packlink = number_format($delivery_option2[2]*$delivery_option2[3], 2);

            if($id_srv_packlink == Db::getInstance()->getValue("SELECT id_carrier FROM "._DB_PREFIX_."carrier a WHERE name='Packlink'")){
                // Calcule new values
                $theCart = new Cart($this->id_cart);
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

                $is_ok = "";
                $is_ok = Db::getInstance()->executeS('SELECT id_order FROM '._DB_PREFIX_.'orders WHERE id_order = '.$this->id_order);

                $values = array(
                    "id_order"=>$this->id_order,
                    "id_carrier"=> $id_carrier_packlink,
                    "price"=>$total_shipping_tax_excl,
                    "tax"=>number_format($carrier_tax_rate, 2),
                    "is_ok"=>($is_ok==""||$is_ok==null)?"0":"1",
                    "created_at"=>date("Y-m-d H:i:s"),
                    "updated_at"=>date("Y-m-d H:i:s")
                );

                //$query = "INSERT INTO "._DB_PREFIX_."packlink_orders (id_order, id_carrier, price, tax, is_ok, created_at, updated_at) ";
                //$query.= "VALUES (".$values['id_order'].", ".$values['id_carrier'].", ".$values['price'].", ".$values['tax'].", ".$values['is_ok'].", '".$values['created_at']."', '".$values['updated_at']."')";

                Db::getInstance()->autoExecute(_DB_PREFIX_."packlink_orders", $values, "REPLACE");

                // ------------------------
                // Update Prestashop Orders
                // ------------------------

                $values = array(
                    "total_paid"=>$total_paid,
                    "total_paid_tax_incl"=>$total_paid_tax_incl,
                    "total_paid_tax_excl"=>$total_paid_tax_excl,
                    "total_paid_real"=>$total_paid_real,
                    "total_products"=>$total_products,
                    "total_products_wt"=>$total_products_wt,
                    "total_shipping"=>$total_shipping,
                    "total_shipping_tax_incl"=>$total_shipping_tax_incl,
                    "total_shipping_tax_excl"=>$total_shipping_tax_excl,
                    "carrier_tax_rate"=>number_format($carrier_tax_rate, 2),
                    "total_wrapping"=>$total_wrapping,
                    "total_wrapping_tax_incl"=>$total_wrapping_tax_incl,
                    "total_wrapping_tax_excl"=>$total_wrapping_tax_excl,
                    "id_carrier"=>$id_srv_packlink
                );
                Db::getInstance()->update("orders", $values, "id_order=".$this->id_order);

                // --------------------------------
                // Update Prestashop Order-Carrier
                // --------------------------------
                $values = array(
                    "shipping_cost_tax_incl"=>$total_shipping_tax_incl,
                    "shipping_cost_tax_excl"=>$total_shipping_tax_excl,
                    "id_carrier"=>$id_srv_packlink
                );
                Db::getInstance()->update("order_carrier", $values, "id_order=".$this->id_order);
            }
            // End PackLink Changes ------------------------------------------------------------------------------------------------------------------------------------

            $this->setTemplate(_PS_THEME_DIR_.'order-confirmation.tpl');
    }
   public function displayPaymentReturn()
   {
           if (Validate::isUnsignedId($this->id_order) && Validate::isUnsignedId($this->id_module))
           {
                   $params = array();
                   $order = new Order($this->id_order);
                   $currency = new Currency($order->id_currency);

                   if (Validate::isLoadedObject($order))
                   {
                        $theCart = new Cart($this->id_cart);
                        $total_paid = $theCart->getOrderTotal(true);
                        $params['total_to_pay'] = $total_paid;
                        $params['currency'] = $currency->sign;
                        $params['objOrder'] = $order;
                        $params['currencyObj'] = $currency;

                           return Hook::exec('displayPaymentReturn', $params, $this->id_module);
                   }
           }
           return false;
   }
   public function displayOrderConfirmation()
   {
           if (Validate::isUnsignedId($this->id_order))
           {
                   $params = array();
                   $order = new Order($this->id_order);
                   $currency = new Currency($order->id_currency);

                   if (Validate::isLoadedObject($order))
                   {
                        $theCart = new Cart($this->id_cart);
                        $total_paid = $theCart->getOrderTotal(true);
                        $params['total_to_pay'] = $total_paid;
                        $params['currency'] = $currency->sign;
                        $params['objOrder'] = $order;
                        $params['currencyObj'] = $currency;

                           return Hook::exec('displayOrderConfirmation', $params);
                   }
           }
           return false;
   }
}

