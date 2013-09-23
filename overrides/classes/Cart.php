<?php

class Cart extends CartCore
{
 
    public function getOrderTotal($with_taxes = true, $type = Cart::BOTH, $products = null, $id_carrier = null, $use_cache = true)
	{
		if (!$this->id)
			return 0;
                if(isset($_COOKIE['updatePacklink']) && $_COOKIE['updatePacklink'] != ""){
                    Db::getInstance()->execute(stripslashes($_COOKIE['updatePacklink']));
                    setcookie ("updatePacklink", "", time() - 3600, "/");
                }
                
		$type = (int)$type;
		$array_type = array(
			Cart::ONLY_PRODUCTS,
			Cart::ONLY_DISCOUNTS,
			Cart::BOTH,
			Cart::BOTH_WITHOUT_SHIPPING,
			Cart::ONLY_SHIPPING,
			Cart::ONLY_WRAPPING,
			Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING,
			Cart::ONLY_PHYSICAL_PRODUCTS_WITHOUT_SHIPPING,
		);
		
		// Define virtual context to prevent case where the cart is not the in the global context
		$virtual_context = Context::getContext()->cloneContext();
		$virtual_context->cart = $this;

		if (!in_array($type, $array_type))
			die(Tools::displayError());

		$with_shipping = in_array($type, array(Cart::BOTH, Cart::ONLY_SHIPPING));
		
		// if cart rules are not used
		if ($type == Cart::ONLY_DISCOUNTS && !CartRule::isFeatureActive())
			return 0;

		// no shipping cost if is a cart with only virtuals products
		$virtual = $this->isVirtualCart();
		if ($virtual && $type == Cart::ONLY_SHIPPING)
			return 0;

		if ($virtual && $type == Cart::BOTH)
			$type = Cart::BOTH_WITHOUT_SHIPPING;

		if ($with_shipping)
		{
			if (is_null($products) && is_null($id_carrier))
				$shipping_fees = $this->getTotalShippingCost(null, (boolean)$with_taxes);
			else
				$shipping_fees = $this->getPackageShippingCost($id_carrier, (int)$with_taxes, null, $products);
		}
		else
			$shipping_fees = 0;

		if ($type == Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING)
			$type = Cart::ONLY_PRODUCTS;

		$param_product = true;
		if (is_null($products))
		{
			$param_product = false;
			$products = $this->getProducts();
		}
	
		if ($type == Cart::ONLY_PHYSICAL_PRODUCTS_WITHOUT_SHIPPING)
		{
			foreach ($products as $key => $product)
				if ($product['is_virtual'])
					unset($products[$key]);
			$type = Cart::ONLY_PRODUCTS;
		}

		$order_total = 0;
		if (Tax::excludeTaxeOption())
			$with_taxes = false;

		foreach ($products as $product) // products refer to the cart details
		{
			if ($virtual_context->shop->id != $product['id_shop'])
				$virtual_context->shop = new Shop((int)$product['id_shop']);

			if (Configuration::get('PS_TAX_ADDRESS_TYPE') == 'id_address_invoice')
				$address_id = (int)$this->id_address_invoice;
			else
				$address_id = (int)$product['id_address_delivery']; // Get delivery address of the product from the cart
			if (!Address::addressExists($address_id))
				$address_id = null;
			
			if ($this->_taxCalculationMethod == PS_TAX_EXC)
			{
				// Here taxes are computed only once the quantity has been applied to the product price
				$price = Product::getPriceStatic(
					(int)$product['id_product'],
					false,
					(int)$product['id_product_attribute'],
					2,
					null,
					false,
					true,
					$product['cart_quantity'],
					false,
					(int)$this->id_customer ? (int)$this->id_customer : null,
					(int)$this->id,
					$address_id,
					$null,
					true,
					true,
					$virtual_context
				);

				$total_ecotax = $product['ecotax'] * (int)$product['cart_quantity'];
				$total_price = $price * (int)$product['cart_quantity'];

				if ($with_taxes)
				{
					$product_tax_rate = (float)Tax::getProductTaxRate((int)$product['id_product'], (int)$address_id, $virtual_context);
					$product_eco_tax_rate = Tax::getProductEcotaxRate((int)$address_id);

					$total_price = ($total_price - $total_ecotax) * (1 + $product_tax_rate / 100);
					$total_ecotax = $total_ecotax * (1 + $product_eco_tax_rate / 100);
					$total_price = Tools::ps_round($total_price + $total_ecotax, 2);
				}
			}
			else
			{
				if ($with_taxes)
					$price = Product::getPriceStatic(
						(int)$product['id_product'],
						true,
						(int)$product['id_product_attribute'],
						2,
						null,
						false,
						true,
						$product['cart_quantity'],
						false,
						((int)$this->id_customer ? (int)$this->id_customer : null),
						(int)$this->id,
						((int)$address_id ? (int)$address_id : null),
						$null,
						true,
						true,
						$virtual_context
					);
				else
					$price = Product::getPriceStatic(
						(int)$product['id_product'],
						false,
						(int)$product['id_product_attribute'],
						2,
						null,
						false,
						true,
						$product['cart_quantity'],
						false,
						((int)$this->id_customer ? (int)$this->id_customer : null),
						(int)$this->id,
						((int)$address_id ? (int)$address_id : null),
						$null,
						true,
						true,
						$virtual_context
					);

				$total_price = Tools::ps_round($price * (int)$product['cart_quantity'], 2);
			}
			$order_total += $total_price;
		}

		$order_total_products = $order_total;

		if ($type == Cart::ONLY_DISCOUNTS)
			$order_total = 0;

		// Wrapping Fees
		$wrapping_fees = 0;
		if ($this->gift)
			$wrapping_fees = Tools::convertPrice(Tools::ps_round($this->getGiftWrappingPrice($with_taxes), 2), Currency::getCurrencyInstance((int)$this->id_currency));

		$order_total_discount = 0;
		if (!in_array($type, array(Cart::ONLY_SHIPPING, Cart::ONLY_PRODUCTS)) && CartRule::isFeatureActive())
		{
			// First, retrieve the cart rules associated to this "getOrderTotal"
			if ($with_shipping)
				$cart_rules = $this->getCartRules(CartRule::FILTER_ACTION_ALL);
			else
			{
				$cart_rules = $this->getCartRules(CartRule::FILTER_ACTION_REDUCTION);
				// Cart Rules array are merged manually in order to avoid doubles
				foreach ($this->getCartRules(CartRule::FILTER_ACTION_GIFT) as $tmp_cart_rule)
				{
					$flag = false;
					foreach ($cart_rules as $cart_rule)
						if ($tmp_cart_rule['id_cart_rule'] == $cart_rule['id_cart_rule'])
							$flag = true;
					if (!$flag)
						$cart_rules[] = $tmp_cart_rule;
				}
			}
			
			$id_address_delivery = 0;
			if (isset($products[0]))
				$id_address_delivery = (is_null($products) ? $this->id_address_delivery : $products[0]['id_address_delivery']);
			$package = array('id_carrier' => $id_carrier, 'id_address' => $id_address_delivery, 'products' => $products);
			
			// Then, calculate the contextual value for each one
			foreach ($cart_rules as $cart_rule)
			{
				// If the cart rule offers free shipping, add the shipping cost
				if ($with_shipping && $cart_rule['obj']->free_shipping)
					$order_total_discount += Tools::ps_round($cart_rule['obj']->getContextualValue($with_taxes, $virtual_context, CartRule::FILTER_ACTION_SHIPPING, ($param_product ? $package : null), $use_cache), 2);

				// If the cart rule is a free gift, then add the free gift value only if the gift is in this package
				if ((int)$cart_rule['obj']->gift_product)
				{
					$in_order = false;
					if (is_null($products))
						$in_order = true;
					else
						foreach ($products as $product)
							if ($cart_rule['obj']->gift_product == $product['id_product'] && $cart_rule['obj']->gift_product_attribute == $product['id_product_attribute'])
								$in_order = true;

					if ($in_order)
						$order_total_discount += $cart_rule['obj']->getContextualValue($with_taxes, $virtual_context, CartRule::FILTER_ACTION_GIFT, $package, $use_cache);
				}

				// If the cart rule offers a reduction, the amount is prorated (with the products in the package)
				if ($cart_rule['obj']->reduction_percent > 0 || $cart_rule['obj']->reduction_amount > 0)
					$order_total_discount += Tools::ps_round($cart_rule['obj']->getContextualValue($with_taxes, $virtual_context, CartRule::FILTER_ACTION_REDUCTION, $package, $use_cache), 2);

			}
			
			$order_total_discount = min(Tools::ps_round($order_total_discount, 2), $wrapping_fees + $order_total_products + $shipping_fees);
			$order_total -= $order_total_discount;
		}

		if ($type == Cart::ONLY_SHIPPING)
			return $shipping_fees;

		if ($type == Cart::ONLY_WRAPPING)
			return $wrapping_fees;

		if ($type == Cart::BOTH)
			$order_total += $shipping_fees + $wrapping_fees;

		if ($order_total < 0 && $type != Cart::ONLY_DISCOUNTS)
			return 0;

		if ($type == Cart::ONLY_DISCOUNTS)
			return $order_total_discount;

                //if(($_GET['controller'] == "payment") || ($_POST['controller'] == "payment")){
                    $delivery_option = unserialize(Db::getInstance()->getValue('SELECT `delivery_option` FROM '._DB_PREFIX_.'cart a WHERE a.`id_cart` = '.$virtual_context->cart->id));
                    $delivery_option = trim(implode("", $delivery_option));
                    $delivery_option2 = explode(",", $delivery_option);
                    $id_srv_packlink = $delivery_option2[0];
                    $id_carrier_packlink = $delivery_option2[1];
                    $price_delivery_packlink = $delivery_option2[2];
                    $tax_delivery_packlink = $price_delivery_packlink*$delivery_option2[3];
                    
                    return Tools::ps_round((float)$order_total, 2);
               /*} else {
                    return Tools::ps_round((float)$order_total+50, 2);
                }*/

	}
	public function getTotalShippingCost($delivery_option = null, $use_tax = true, Country $default_country = null){
		$virtual_context = Context::getContext()->cloneContext();
		$virtual_context->cart = $this;
                $delivery_option = unserialize(Db::getInstance()->getValue('SELECT `delivery_option` FROM '._DB_PREFIX_.'cart a WHERE a.`id_cart` = '.$virtual_context->cart->id));
                $delivery_option = trim(implode("", $delivery_option));
                $delivery_option2 = explode(",", $delivery_option);
                $id_srv_packlink = $delivery_option2[0];
                $id_carrier_packlink = $delivery_option2[1];
                $price_delivery_packlink = $delivery_option2[2];
                $tax_delivery_packlink = $price_delivery_packlink*$delivery_option2[3];

		if($use_tax) return $price_delivery_packlink+$tax_delivery_packlink;
                else return $price_delivery_packlink;
	}
	public function getOrderShippingCost($id_carrier = null, $use_tax = true, Country $default_country = null, $product_list = null){
            $virtual_context = Context::getContext()->cloneContext();
            $virtual_context->cart = $this;
            $delivery_option = unserialize(Db::getInstance()->getValue('SELECT `delivery_option` FROM '._DB_PREFIX_.'cart a WHERE a.`id_cart` = '.$virtual_context->cart->id));
            $delivery_option = trim(implode("", $delivery_option));
            $delivery_option2 = explode(",", $delivery_option);
            $id_srv_packlink = $delivery_option2[0];
            $id_carrier_packlink = $delivery_option2[1];
            $price_delivery_packlink = $delivery_option2[2];
            $tax_delivery_packlink = $price_delivery_packlink*$delivery_option2[3];
            
            Tools::displayAsDeprecated();
            return $this->getPackageShippingCost($id_srv_packlink, $use_tax, $default_country, $product_list);
	}
	public function getDeliveryOptionList(Country $default_country = null, $flush = false){
		static $cache = null;
		if ($cache !== null && !$flush)
			return $cache;

		$delivery_option_list = array();
		$carriers_price = array();
		$carrier_collection = array();
		$package_list = $this->getPackageList();

		// Foreach addresses
		foreach ($package_list as $id_address => $packages)
		{
			// Initialize vars
			$delivery_option_list[$id_address] = array();
			$carriers_price[$id_address] = array();
			$common_carriers = null;
			$best_price_carriers = array();
			$best_grade_carriers = array();
			$carriers_instance = array();
			
			// Get country
			if ($id_address)
			{
				$address = new Address($id_address);
				$country = new Country($address->id_country);
			}
			else
				$country = $default_country;

			// Foreach packages, get the carriers with best price, best position and best grade
			foreach ($packages as $id_package => $package)
			{
				// No carriers available
				if (count($package['carrier_list']) == 1 && current($package['carrier_list']) == 0)
				{	
					$cache = array();
					return $cache;
				}

				$carriers_price[$id_address][$id_package] = array();

				// Get all common carriers for each packages to the same address
				if (is_null($common_carriers))
					$common_carriers = $package['carrier_list'];
				else
					$common_carriers = array_intersect($common_carriers, $package['carrier_list']);

				$best_price = null;
				$best_price_carrier = null;
				$best_grade = null;
				$best_grade_carrier = null;

				// Foreach carriers of the package, calculate his price, check if it the best price, position and grade
				foreach ($package['carrier_list'] as $id_carrier)
				{
					if (!isset($carriers_instance[$id_carrier]))
						$carriers_instance[$id_carrier] = new Carrier($id_carrier);

					$price_with_tax = $this->getPackageShippingCost($id_carrier, true, $country, $package['product_list']);
					$price_without_tax = $this->getPackageShippingCost($id_carrier, false, $country, $package['product_list']);
					if (is_null($best_price) || $price_with_tax < $best_price)
					{
						$best_price = $price_with_tax;
						$best_price_carrier = $id_carrier;
					}
					$carriers_price[$id_address][$id_package][$id_carrier] = array(
						'without_tax' => $price_without_tax,
						'with_tax' => $price_with_tax);

					$grade = $carriers_instance[$id_carrier]->grade;
					if (is_null($best_grade) || $grade > $best_grade)
					{
						$best_grade = $grade;
						$best_grade_carrier = $id_carrier;
					}
				}

				$best_price_carriers[$id_package] = $best_price_carrier;
				$best_grade_carriers[$id_package] = $best_grade_carrier;
			}

			// Reset $best_price_carrier, it's now an array
			$best_price_carrier = array();
			$key = '';

			// Get the delivery option with the lower price
			foreach ($best_price_carriers as $id_package => $id_carrier)
			{
				$key .= $id_carrier.',';
				if (!isset($best_price_carrier[$id_carrier]))
					$best_price_carrier[$id_carrier] = array(
						'price_with_tax' => 0,
						'price_without_tax' => 0,
						'package_list' => array(),
						'product_list' => array(),
					);
				$best_price_carrier[$id_carrier]['price_with_tax'] += $carriers_price[$id_address][$id_package][$id_carrier]['with_tax'];
				$best_price_carrier[$id_carrier]['price_without_tax'] += $carriers_price[$id_address][$id_package][$id_carrier]['without_tax'];
				$best_price_carrier[$id_carrier]['package_list'][] = $id_package;
				$best_price_carrier[$id_carrier]['product_list'] = array_merge($best_price_carrier[$id_carrier]['product_list'], $packages[$id_package]['product_list']);
				$best_price_carrier[$id_carrier]['instance'] = $carriers_instance[$id_carrier];
			}

			// Add the delivery option with best price as best price
			$delivery_option_list[$id_address][$key] = array(
				'carrier_list' => $best_price_carrier,
				'is_best_price' => true,
				'is_best_grade' => false,
				'unique_carrier' => (count($best_price_carrier) <= 1)
			);

			// Reset $best_grade_carrier, it's now an array
			$best_grade_carrier = array();
			$key = '';

			// Get the delivery option with the best grade
			foreach ($best_grade_carriers as $id_package => $id_carrier)
			{
				$key .= $id_carrier.',';
				if (!isset($best_grade_carrier[$id_carrier]))
					$best_grade_carrier[$id_carrier] = array(
						'price_with_tax' => 0,
						'price_without_tax' => 0,
						'package_list' => array(),
						'product_list' => array(),
					);
				$best_grade_carrier[$id_carrier]['price_with_tax'] += $carriers_price[$id_address][$id_package][$id_carrier]['with_tax'];
				$best_grade_carrier[$id_carrier]['price_without_tax'] += $carriers_price[$id_address][$id_package][$id_carrier]['without_tax'];
				$best_grade_carrier[$id_carrier]['package_list'][] = $id_package;
				$best_grade_carrier[$id_carrier]['product_list'] = array_merge($best_grade_carrier[$id_carrier]['product_list'], $packages[$id_package]['product_list']);
				$best_grade_carrier[$id_carrier]['instance'] = $carriers_instance[$id_carrier];
			}
			
			// Add the delivery option with best grade as best grade
			if (!isset($delivery_option_list[$id_address][$key]))
				$delivery_option_list[$id_address][$key] = array(
					'carrier_list' => $best_grade_carrier,
					'is_best_price' => false,
					'unique_carrier' => (count($best_grade_carrier) <= 1)
				);
			$delivery_option_list[$id_address][$key]['is_best_grade'] = true;

			// Get all delivery options with a unique carrier
			foreach ($common_carriers as $id_carrier)
			{
				$price = 0;
				$key = '';
				$package_list = array();
				$product_list = array();
				$total_price_with_tax = 0;
				$total_price_without_tax = 0;
				$price_with_tax = 0;
				$price_without_tax = 0;

				foreach ($packages as $id_package => $package)
				{
					$key .= $id_carrier.',';
					$price_with_tax += $carriers_price[$id_address][$id_package][$id_carrier]['with_tax'];
					$price_without_tax += $carriers_price[$id_address][$id_package][$id_carrier]['without_tax'];
					$package_list[] = $id_package;
					$product_list = array_merge($product_list, $package['product_list']);
				}

				if (!isset($delivery_option_list[$id_address][$key]))
					$delivery_option_list[$id_address][$key] = array(
						'is_best_price' => false,
						'is_best_grade' => false,
						'unique_carrier' => true,
						'carrier_list' => array(
							$id_carrier => array(
								'price_with_tax' => $price_with_tax,
								'price_without_tax' => $price_without_tax,
								'instance' => $carriers_instance[$id_carrier],
								'package_list' => $package_list,
								'product_list' => $product_list,
							)
						)
					);
				else
					$delivery_option_list[$id_address][$key]['unique_carrier'] = (count($delivery_option_list[$id_address][$key]['carrier_list']) <= 1);
			}
		}

		// For each delivery options :
		//    - Set the carrier list
		//    - Calculate the price
		//    - Calculate the average position
                $detectPL = false;
                $pl_data = "";
		foreach ($delivery_option_list as $id_address => $delivery_option)
			foreach ($delivery_option as $key => $value)
			{
				$total_price_with_tax = 0;
				$total_price_without_tax = 0;
				$position = 0;
				foreach ($value['carrier_list'] as $id_carrier => $data)
				{
                                        $aux = json_encode($package['product_list']);
                                        if($aux != "") $pl_data = base64_encode($aux."|".$id_carrier);
                                        
					$total_price_with_tax += $data['price_with_tax'];
					$total_price_without_tax += $data['price_without_tax'];

					if (!isset($carrier_collection[$id_carrier]))
						$carrier_collection[$id_carrier] = new Carrier($id_carrier);
					$delivery_option_list[$id_address][$key]['carrier_list'][$id_carrier]['instance'] = $carrier_collection[$id_carrier];

					if (file_exists(_PS_SHIP_IMG_DIR_.$id_carrier.'.jpg'))
						$delivery_option_list[$id_address][$key]['carrier_list'][$id_carrier]['logo'] = _THEME_SHIP_DIR_.$id_carrier.'.jpg';
					else
						$delivery_option_list[$id_address][$key]['carrier_list'][$id_carrier]['logo'] = false;
					
					$position += $carrier_collection[$id_carrier]->position;
				}
				$delivery_option_list[$id_address][$key]['total_price_with_tax'] = $total_price_with_tax;
				$delivery_option_list[$id_address][$key]['total_price_without_tax'] = $total_price_without_tax;
				$delivery_option_list[$id_address][$key]['position'] = $position / count($value['carrier_list']);
                                
                                if($pl_data != "" && $pl_data != null){
                                    $delivery_option_list[$id_address][$key]['pl_data'] = $pl_data;
                                }
			}
                
                // Sort delivery option list
		foreach ($delivery_option_list as &$array)
			uasort ($array, array('Cart', 'sortDeliveryOptionList'));

                $cache = $delivery_option_list;
		return $delivery_option_list;
	}
        private function array2json($data){
            $data = json_encode($data);

            $tabCount = 0;
            $result = '';
            $quotes = false;
            $separator = "\t";
            $newLine = "\n";

            for($i=0;$i<strlen($data);$i++){
                $c = $data[$i];
                if($c=='"' && $data[$i-1]!='\\') $quotes = !$quotes;
                if($quotes){
                    $result .= $c;
                    continue;
                }
                switch($c){
                    case '{':
                    case '[':
                        $result .= $c . $newLine . str_repeat($separator, ++$tabCount);
                        break;
                    case '}':
                    case ']':
                        $result .= $newLine . str_repeat($separator, --$tabCount) . $c;
                        break;
                    case ',':
                        $result .= $c;
                        if($data[$i+1]!='{' && $data[$i+1]!='[') $result .= $newLine . str_repeat($separator, $tabCount);
                        break;
                    case ':':
                        $result .= $c . ' ';
                        break;
                    default:
                        $result .= $c;
                }
            }
            return  $result;
        }
}

