<?php
/**
 * @version     1.0.0
 * @package     com_wbty_payments
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Webity <david@makethewebwork.com> - http://www.makethewebwork.com/
 */

// No direct access
defined('_JEXEC') or die;

class Wbty_paymentsController extends JControllerLegacy
{
	function display() {
		// Make sure we have a default view
		$viewname = JFactory::getApplication()->input->get( 'view', 'cart' );

		// check with the helper to make sure that the user is logged in
		$session =& JFactory::getSession();

		if ($session->get('wbtypayments.require_login')) {
			require_once(JPATH_COMPONENT . '/helpers/wbty_payments.php');
			$viewname = Wbty_paymentsHelper::accessCheck($viewname);
		}

		$view = & $this->getView( $viewname, 'html' );

		$view->setModel( $this->getModel( JFactory::getApplication()->input->get( 'view', 'cart' ) ), true );
		if ($layout = JFactory::getApplication()->input->get('layout')) {
			$view->setLayout($layout);
		}

		$checkout = JFactory::getApplication()->input->get('jform', array(), 'ARRAY');
		if (!empty($checkout)) {
			$view->setLayout('order');
		}
		$view->display(JFactory::getApplication()->input->get( 'tpl' ));
	}

	function process_cc() {
		// Grab our default gateway model.
		$model = $this->defaultGateway();

		// Store most of the customer's data in the session in case their transaction fails and they get kicked back.
		$model->setCustomerSession();

		// Calculate Tax and shipping now that we have a shipping state
		$model->calculateTaxShipping();

		// Process the transaction and see if it is successful!
		if($model->process_cc()) {
			$model->setPaid();
			$this->setRedirect(JRoute::_('index.php?option=com_wbty_payments&view=thankyou', false));
		} else {
			$this->setRedirect(JRoute::_('index.php?option=com_wbty_payments&view=error', false));
		}

	}

    function setGateway() {
        $session = JFactory::getSession();
        $input = JFactory::getApplication()->input;

        $order_id = $session->get('wbtypayments.order_id', 0);

        if ($order_id && $jform = $input->get('jform', array(), 'ARRAY')) {
            $data = new stdClass;
            $data->id = $order_id;
            $data->gateway = $jform['method'];

            JFactory::getDbo()->updateObject('#__wbty_payments_orders', $data, 'id');
        }

        return $this->display();
    }

	function gateway() {
		$model = $this->defaultGateway();
		$subtask = JFactory::getApplication()->input->get('subtask');
		if($model->$subtask()) {
			return true;
		} else {
			return false;
		}
	}

	function defaultGateway() {
		// Get the gateway alias
		$db = JFactory::getDBO();
        $session = JFactory::getSession();
        $gateway = false;

        if ($order_id = $session->get('wbtypayments.order_id', 0)) {
            $query = 'SELECT g.alias FROM #__wbty_payments_orders as o LEFT JOIN #__wbty_payments_gateways as g ON g.id = o.gateway WHERE o.id='.(int)$order_id;
            $db->setQuery($query);
            $gateway = $db->loadResult();
        }

        if (!$gateway) {
    		$query = 'SELECT alias FROM #__wbty_payments_gateways g WHERE default_gateway=1';
    		$db->setQuery($query);
    		$gateway = $db->loadResult();
        }

		// Create the file path and check if the model for the default gateway exists
		if(!JFile::exists(JPATH_COMPONENT."/models/".$gateway.".php")) {
			// Raise error
			echo "Model for default gateway does not exist!";
		}
		require_once(JPATH_COMPONENT."/models/".$gateway.".php");

		if(!class_exists("WBTYPayments".$gateway)) {
			// Raise error
			echo "WBTYPayments".gateway." view not found!";
		}
		$class = "WBTYPayments".$gateway;
		return new $class;
	}

	function remove() {
		$input = JFactory::getApplication()->input;
		$post = $input->post->getArray(array_flip(array_keys($_POST)));

		// Get the database
		$db = JFactory::getDBO();

		$query = "SELECT price, quantity FROM #__wbty_payments_order_items WHERE id=".$post['item_id']." AND order_id=".$post['order_id'];
		$db->setQuery($query);
		$obj = $db->loadObject();
		$price = $obj->price;
		$quantity = $obj->quantity;

		$query = "DELETE FROM #__wbty_payments_order_items WHERE id=".$post['item_id']." AND order_id=".$post['order_id'];
		$db->setQuery($query);
		$db->query();

		$query = "UPDATE #__wbty_payments_orders SET total_amount = total_amount-".(floatval(str_replace(',', '', $price)) * $quantity)." WHERE id=".$post['order_id'];
		$db->setQuery($query);
		$db->query();

		$query = "SELECT total_amount FROM `#__wbty_payments_orders` WHERE id=".$post['order_id'];
		$db->setQuery($query);
		$new_total = $db->loadResult();

		echo number_format((float)$new_total, 2, '.', '');
		exit();
	}

	function applyCoupon() {
		$input = JFactory::getApplication()->input;
		$post = $input->getArray(array_flip(array_keys($_POST)));

		// Get the database
		$db = JFactory::getDBO();

		$query = "SELECT price, quantity FROM `#__wbty_payments_order_items` WHERE order_id=".$post['order_id']." AND state=1";
		$db->setQuery($query);
		$objs = $db->loadObjectList();

		$total = 0;
		foreach ($objs as $obj) {
		    $price = $obj->price;
		    $quantity = $obj->quantity;

		    $total += (floatval(str_replace(',', '', $price)) * $quantity);
		}

		$query = "SELECT id, discount, discount_type FROM `#__wbty_payments_coupons` WHERE state=1 AND name LIKE ".$db->quote($post['coupon']);
		$db->setQuery($query);
		$coupon = $db->loadObject();

		$coupon_value = 0;
		if ($coupon) {
		    switch ($coupon->discount_type) {
		        case 1: // fixed amount
		            $coupon_value = $coupon->discount;
		            $total -= $coupon_value;
		            break;
		        case 2: // percent off
		            $coupon_value = $coupon->discount/100 * $total;
		            $total -= $coupon_value;
		            break;
		    }
		}

		$query = "UPDATE `#__wbty_payments_orders` SET total_amount = ".($total).", coupon = ".$db->quote($coupon->id).", coupon_amount=".$db->quote($coupon_value)." WHERE id=".$post['order_id'];

		$db->setQuery($query);
		$db->query();

		$query = "SELECT total_amount FROM #__wbty_payments_orders WHERE id=".$post['order_id'];
		$db->setQuery($query);
		$new_total = $db->loadResult();

		echo json_encode(array('total' => number_format((float)$new_total, 2, '.', ''), 'coupon'=>number_format((float)$coupon_value, 2, '.', '')));
		exit();
	}

	function estimateShipping() {
		$input = JFactory::getApplication()->input;
		$post = $input->getArray(array_flip(array_keys($_POST)));

		// Get the database
		$db = JFactory::getDBO();

		$query = "SELECT price, quantity FROM `#__wbty_payments_order_items` WHERE order_id=".$post['order_id']." AND state=1";
		$db->setQuery($query);
		$objs = $db->loadObjectList();

		$total = 0;
		foreach ($objs as $obj) {
			$price = $obj->price;
			$quantity = $obj->quantity;

			$total += (floatval(str_replace(',', '', $price)) * $quantity);
		}

		jimport('wbtypayments.wbtypayments');
		$shipping = WbtyPayments::calculateShipping($total, $post['shipping_state']);
		$tax = WbtyPayments::calculateTax($total, $post['shipping_state']);

		echo json_encode(array(
			'total' => number_format((float)($total + $shipping + ($tax/100 * $total)), 2, '.', ''),
			'shipping' => number_format((float)$shipping, 2, '.', ''),
			'tax' => number_format((float)($tax/100 * $total), 2, '.', '')
		));
		exit();
	}

	public function thankyou() {
		$model = $this->defaultGateway();
		if($model->verify_payment()) {
			$model->setPaid();
			$this->setRedirect(JRoute::_('index.php?option=com_wbty_payments&view=thankyou', false));
		} else {
			$this->setRedirect(JRoute::_('index.php?option=com_wbty_payments&view=error', false));
		}
	}

    public function ipn() {
        ob_start();
        $raw_post_data = file_get_contents('php://input');
        $raw_post_array = explode('&', $raw_post_data);

        $myPost = array();
        foreach ($raw_post_array as $keyval) {
          $keyval = explode ('=', $keyval);
          if (count($keyval) == 2)
             $myPost[$keyval[0]] = urldecode($keyval[1]);
        }

        // read the IPN message sent from PayPal and prepend 'cmd=_notify-validate'
        $req = 'cmd=_notify-validate';
        if(function_exists('get_magic_quotes_gpc')) {
           $get_magic_quotes_exists = true;
        }
        foreach ($myPost as $key => $value) {
           if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
                $value = urlencode(stripslashes($value));
           } else {
                $value = urlencode($value);
           }
           $req .= "&$key=$value";
        }


        // Step 2: POST IPN data back to PayPal to validate

        $ch = curl_init('https://www.paypal.com/cgi-bin/webscr');
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

        // In wamp-like environments that do not come bundled with root authority certificates,
        // please download 'cacert.pem' from "http://curl.haxx.se/docs/caextract.html" and set
        // the directory path of the certificate as shown below:
        // curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/cacert.pem');
        if( !($res = curl_exec($ch)) ) {
            echo ("Got " . curl_error($ch) . " when processing IPN data");
            curl_close($ch);
            $output = ob_get_clean();
            file_put_contents('output.txt', $output);
            exit;
        }
        curl_close($ch);

        $order_id = (int)$myPost['invoice'];
        $order_id = 82;

        $session = JFactory::getSession();
        $session->set('wbtypayments.order_id', $order_id);

        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('user_id')
            ->from('#__wbtypayments_orders')
            ->where('id = ' . $order_id);

        $user_id = $db->setQuery($query)->loadResult();

        $model = $this->defaultGateway($order_id);
        $model->setPaid($order_id, $user_id);
    }
}
