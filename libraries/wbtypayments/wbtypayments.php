<?php
/**
 * @version     1.0.0
 * @package     wbtypayments
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access.
defined('_JEXEC') or die;

/**
 * Groups list controller class.
 */
class WbtyPayments
{
	public static function createOrder($info) {
		static $db;

		if (!$db) {
			// Initialize database
			$db = JFactory::getDbo();
		}

		$user =& JFactory::getUser();

		// Check if there is already an order_id set
		$session = JFactory::getSession();
		$session_order = $session->get('wbtypayments.order_id');

		$object = new stdClass();
		$object->price = $info['amount'];
		$object->quantity = ((int)$info['quantity']) ? (int)$info['quantity'] : 1;

		// If there is no session order, open a new order
		if (empty($session_order)) {
			$query = $db->getQuery(true);
			$columns = array('user_id', 'paid', 'total_amount', 'order_date');
			$values = array($user->id, 0, $db->quote((floatval(str_replace(',', '', $info['amount']))) * $object->quantity), 'NOW()');
			$query
				->insert($db->quoteName('#__wbty_payments_orders'))
				->columns($db->quoteName($columns))
				->values(implode(',', $values));

			$db->setQuery($query)->query();
			$session_order = $db->insertid();

			// Set the order for this session
			$session->set('wbtypayments.order_id',$session_order);

			$update_total = false;

		} else {
			$update_total = true;
		}

		$object->order_id = $session_order;
		$object->item_name = $info['item_name'];
		$object->item_desc = $info['item_desc'];
		$object->item_id = $info['item_id'];
		$object->item_image = $info['item_image'];
		$object->callback_file = $info['callback_file'];
		$object->callback_function = $info['callback_function'];
		$object->callback_id = $info['callback_id'];
		$object->redirect_url = $ingo['redirect_url'];
		$object->redirect_text = $ingo['redirect_text'];

		$db->insertObject('#__wbty_payments_order_items', $object);

		// Update the total in the orders table
		if ($update_total === true) {
			$query = "UPDATE #__wbty_payments_orders SET total_amount = total_amount + ".(floatval(str_replace(',', '', $info['amount'])) * $object->quantity)." WHERE id=".$session_order;
			$db->setQuery($query);
			$db->query();
		}

		return $session_order;
	}

	public function getCheckoutUrl() {
		$session = JFactory::getSession();
		$session_order = $session->get('wbtypayments.order_id');

		return JRoute::_('index.php?option=com_wbty_payments&view=cart&order_id='.$session_order);
	}

	public static function calculateShipping($order_total, $shipping_state) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('shipping_price')
			->from('#__wbty_payments_shipping')
			->where('state=1')
			->where('min_value <= ' . (float)$order_total)
			->where('max_value >= ' . (float)$order_total)
			->where('(narrow_by_state LIKE "" OR narrow_by_state LIKE '.$db->quote($shipping_state).')')
			->order('shipping_price DESC');

		return $db->setQuery($query, 0, 1)->loadResult();
	}

	public static function calculateTax($order_total, $shipping_state) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('percent')
			->from('#__wbty_payments_taxes')
			->where('state=1')
			->where('(applicable_state LIKE "" OR applicable_state LIKE '.$db->quote($shipping_state).')')
			->order('percent DESC');

		return $db->setQuery($query, 0, 1)->loadResult();
	}
}
