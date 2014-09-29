<?php
/**
 * @package    WBTY Payments
 *
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

class WBTYPaymentsGatewayModel extends JObject {

	protected $doc = null;

	protected $db = null;

	public function __construct($properties = null)
	{

		if (!($this->doc)) {
			$this->doc &= JFactory::getDocument();
		}

		if (!($this->db)) {
			$this->db &= JFactory::getDBO();
		}

		parent::__construct($properties);
	}

	public function process_cc() {
		return false;
	}

	public function verify_payment() {
		return false;
	}

	public function getShipInfoForm() {
		return false;
	}

	public function insertOrder($order_info) {

	}

	public function getGateway(){
		// Initialize database and open the session
		$db = JFactory::getDbo();
		$session = JFactory::getSession();
		$gateway = false;

		if ($order_id = $session->get('wbtypayments.order_id', 0)) {
			$query = 'SELECT g.id FROM #__wbty_payments_orders as o LEFT JOIN #__wbty_payments_gateways as g ON g.id = o.gateway WHERE o.id='.(int)$order_id;
			$db->setQuery($query);
			$gateway = $db->loadResult();
		}

		// Get merchant details from databse based on their selected gateway
		$query = $db->getQuery(true)
			->select('gf.field_name, fv.value, g.id, g.type')
			->from('#__wbty_payments_gateway_fields as gf')
			->leftjoin('#__wbty_payments_gateway_field_values AS fv ON gf.id=fv.gateway_link_id')
			->leftjoin('#__wbty_payments_gateways AS g ON g.id=gf.gateway_id');

		if ($gateway) {
			$query->where('g.id = '.$gateway);
		} else {
			$query->where('g.default_gateway=1');
		}

		$db->setQuery($query);
		$merchantinfo_db = $db->loadAssocList();

		// Cycle through the merchant information and set each field name as the key for its respective value
		$merchantinfo = array();
		foreach ($merchantinfo_db as $mi) {
			$mi['field_name'] = str_replace(" ", "_", strtolower($mi['field_name']));
			$array = array($mi['field_name'] => $mi['value'], 'gateway_id' => $mi['gateway_id'], 'type' => $mi['type']);
			$merchantinfo = array_merge((array)$merchantinfo, (array)$array);
		}

		$order_id = $session->get('wbtypayments.order_id');

		// Get the order information based on the session _order_id
		$db->setQuery('SELECT total_amount FROM #__wbty_payments_orders WHERE id='.$order_id);
		$order_total = $db->loadResult();

		// Get post data for the order
		$post = JFactory::getApplication()->input->get('jform', array(), 'ARRAY');

		$data['post'] = $post;
		// Set the [amount] element of the post data to the stored order total in case the user edited the html form
		$data['post']['amount'] = $order_total;
		$data['merchant'] = $merchantinfo;

		return $data;
	}

	public function setCustomerSession() {
		// Get our post data! Hooray!
		$post = JFactory::getApplication()->input->get('jform', array(), 'ARRAY');

		// Let us filter the data like a nice soup stock.
		$userData = array();
		foreach ($post as $key => $value) {
			if ($key != 'creditCardType' && $key != 'creditCardNumber' && $key != 'expDateMonth' && $key != 'expDateYear' && $key != 'cvv2Number') $userData[$key] = $value;
		}

		// Open up a session and start storing that data, baby!
		$session 	= JFactory::getSession();
		$userData 	= $session->set('userData', $userData);
	}

	public function calculateTaxShipping() {
		// Get our post data! Hooray!
		$post = JFactory::getApplication()->input->get('jform', array(), 'ARRAY');

		$state = $post['shipping_different'] ? $post['shipping_state'] : $post['state'];
		$total = $this->info['post']['amount'];

		jimport('wbtypayments.wbtypayments');
		$shipping = WbtyPayments::calculateShipping($total, $state);
		$tax = WbtyPayments::calculateTax($total, $state);

		$this->info['post']['amount'] = $total + $shipping + (($tax/100) * $total);
	}

	public function setPaid($order_id = 0, $user_id = 0, $clear = true) {
		// Grab the session
		$session = JFactory::getSession();
		$db = JFactory::getDBO();
		$user = JFactory::getUser($user_id);

		// Grab the _order_id for the last time
		if (!$order_id) {
			$order_id = $session->get('wbtypayments.order_id');
		}

		// Set the order in WBTYPayments to paid
		$query = "UPDATE #__wbty_payments_orders SET paid=1 WHERE id=$order_id";
		$db->setQuery($query);
		$db->query();

		// Get the order items
		$query = "SELECT oi.*, o.total_amount, o.order_date FROM #__wbty_payments_order_items AS oi LEFT JOIN #__wbty_payments_orders AS o ON oi.order_id=o.id WHERE oi.order_id=$order_id";
		$db->setQuery($query);
		$order = $db->loadAssocList();

		// Store and connect the user to the order
		$post = JFactory::getApplication()->input->get('jform', array(), 'ARRAY');
		$ids = $this->storeCustomer($post);
		$query = "UPDATE `#__wbty_payments_orders` SET purchaser_id=".$db->quote($ids['purchaser_id']).", billing_address=".$db->quote($ids['billing_address']).", shipping_address=".$db->quote($ids['shipping_address'])." WHERE id = ".$order_id;
		$db->setQuery($query);
		$db->query();

		foreach ($order as $o) {
			// process callback functions to tell other components that everything is paid!
			$o['callback_file'] = trim(str_replace('JPATH_BASE', JPATH_BASE, $o['callback_file']), ' \'');
			if ($o['callback_file'] && file_exists($o['callback_file'])) {
				require_once($o['callback_file']);
				if ($func = $o['callback_function']) {
					$class='';
					if (strpos($func, '::')!==false) {
						$f = explode('::',$func);
						$class = $f[0];
						$func = $f[1];
					}
					if ($class) {
						if (class_exists($class)) {
							if (is_callable($class, $func)) {
								call_user_func(array($class, $func), 1, $o['callback_id'], $user->id);
							}
						}
					} else {
						if (method_exists($func)) {
							$func($user->id, 1, $o['callback_id']);
						}
					}
				}
			}
		}

		if ($clear) {
			/* ================================================================
			   Create an email confirmation for the customer
		    ================================================================ */
			$mailer = JFactory::getMailer();

			$config = JFactory::getConfig();

			$jversion = new JVersion();
			$above3 = version_compare($jversion->getShortVersion(), '3.0', 'ge');

			if ($above3) {
				$sitename = $config->get( 'sitename' );
				$mailfrom = $config->get( 'mailfrom' );
				$fromname = $config->get( 'fromname' );
			} else {
				$sitename = $config->getValue( 'config.sitename' );
				$mailfrom = $config->getValue( 'config.mailfrom' );
				$fromname = $config->getValue( 'config.fromname' );
			}

			$sender = array($mailfrom, $fromname);

			$mailer->setSender($sender);

			$mailer->addRecipient($post['email']);
			$mailer->setSubject('Thank you for your order from '.$sitename);

			$body = $intro = '';

			$intro  = '<h1>Thank you for your order from '.$sitename.'</h1>';
			$intro .= '<h3>Your order receipt and details are as follows:</h3><p>';

			if ($order[0]['order_id'])  	$body .= 'Order ID: <strong>'.$order[0]['order_id'].'</strong><br>';
			if ($order[0]['order_date']) 	$body .= 'Date: <strong>'.$order[0]['order_date'].'</strong>';

			$body .= '</p><hr><h4>Items:</h4>';

			foreach ($order as $o) {
				$body .= '<p>'.$o['item_name'].'<br>';
				$body .= 'Price: $'.$o['price'].'<br>';
				$body .= $o['item_desc'].'</p>';
				$body .= '<hr />';
			}

			$body .= '<h3>Order total: $'.$order[0]['total_amount'].'</h3><hr>';

			$billing = '<p><strong>'.$post['firstName'].' '.$post['lastName'].'</strong><br>';
			$billing .= $post['email'].'</p>';
			$billing .= '<p>'.$post['address1'].'<br>';
				if ($post['address2']) $billing .= $post['address2'].'<br>';
			$billing .= $post['city'].', '.$post['state'].' '.$post['zip'].'<br>';
			$billing .= $post['country'];

			if ($post['shipping_address1']) {

				$shipping = '<p><strong>'.$post['firstName'].' '.$post['lastName'].'</strong><br>';
				$shipping .= $post['email'].'</p>';
				$shipping .= '<p>'.$post['shipping_address1'].'<br>';
					if ($post['shipping_address2']) $shipping .= $post['shipping_address2'].'<br>';
				$shipping .= $post['shipping_city'].', '.$post['shipping_state'].' '.$post['shipping_zip'].'<br>';
				$shipping .= $post['shipping_country'];

			} else {
				$shipping = $billing;
			}

			$body .= '<h3>Billing Information:</h3> '.$billing.'<h3>Shipping Information:</h3> '.$shipping;

			$mailer->isHTML(true);
			$mailer->Encoding = 'base64';
			$mailer->setBody($intro.$body);

			$send = $mailer->Send();


			// Now send emails to relevant administrators
			jimport('joomla.application.component.helper');
			$com_params = JComponentHelper::getParams('com_wbty_payments'); // Our componenet parameters!
			if ( $com_params->get('email_notifications') ) {

				$mailer->ClearAllRecipients();
				$mailer->addRecipient(explode( ',', $com_params->get('email_notifications') )); // Extra emails set in the global configuration

				$mailer->setSubject('New order on '.$sitename);

				$intro = '<h1>New order on '.$sitename.'</h1>';
				$intro .= '<h3>The order details are:</h3>';

				$mailer->setBody($intro.$body);

				$send = $mailer->Send();
			}


			// Insert $order_id into a new session variable and unset _order_id
			$session->set('_completed_order_id', $order_id);
			$session->clear('wbtypayments.order_id');
		}
	}

	public function storeCustomer($post) {
		if (empty($post)) $post = JFactory::getApplication()->input->get('jform', array(), 'ARRAY');

		// Grab that beautiful database object, and extract a new query object from it
		$db = JFactory::getDbo();
		$query	= $db->getQuery(true);

		// Store the purchaser
		$cols	= array(
			'first_name',
			'last_name',
			'email'
		);

		$vals	= array(
			$db->quote($post['firstName']),
			$db->quote($post['lastName']),
			$db->quote($post['email'])
		);

		$query
			->insert($db->quoteName('#__wbty_payments_purchasers'))
			->columns($db->quoteName($cols))
			->values(implode(',', $vals));

		$db->setQuery($query)->query();

		// Get the ID of the freshly inserted row
		$purchaserID = $db->insertid();

		// Store the address(s)
		$query->clear();

		$cols	= array(
			'type',
			'address_line_1',
			'address_line_2',
			'city',
			'address_state',
			'zip',
			'country',
			'purchaser_id'
		);

		$query
			->insert($db->quoteName('#__wbty_payments_addresses'))
			->columns($db->quoteName($cols));

		// If the shipping address is different from billing, set the address type and store the shipping address first
		if ($post['shipping_different'] == 1) {
			$type = 2; // Shipping

			$vals = array(
				$db->quote($type),
				$db->quote($post['shipping_address1']),
				$db->quote($post['shipping_address2']),
				$db->quote($post['shipping_city']),
				$db->quote($post['shipping_state']),
				$db->quote($post['shipping_zip']),
				$db->quote($post['shipping_country']),
				$purchaserID
			);

			$query
			->values(implode(',', $vals));

			$db->setQuery($query)->query();

			$shipping_id = $db->insertid();

			// Clear the values and prepare the query and type for the next insertion
			$query->clear('values');
			$type = 1; // Billing
		} else {
			$type = 3; // Shipping & Billing
		}

		$vals = array(
			$db->quote($type),
			$db->quote($post['address1']),
			$db->quote($post['address2']),
			$db->quote($post['city']),
			$db->quote($post['state']),
			$db->quote($post['zip']),
			$db->quote($post['country']),
			$purchaserID
		);

		$query
			->values(implode(',', $vals));

		$db->setQuery($query)->query();

		$billing_id = $db->insertid();

		if ($type == 3) {
			$shipping_id = $billing_id;
		}

		return array(
			'purchaser_id' => $purchaserID,
			'billing_address' => $billing_id,
			'shipping_address' => $shipping_id
		);
	}

}
