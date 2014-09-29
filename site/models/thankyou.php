<?php
/**
 * @package    WBTY Payments
 *
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.model');

class Wbty_paymentsModelThankyou extends JModelLegacy {


	public function __construct($properties = null)
	{
		
		if (!($this->doc)) {
			$this->doc =& JFactory::getDocument();
		}
		
		if (!($this->db)) {
			$this->db =& JFactory::getDBO();
		}
		
		parent::__construct($properties);
	}
	
	/** 
	* Get the order information based on $order_id
	*/
	function getOrderData($order_id) {
		$query = $this->db->getQuery(true);
		
		$query
			->select('oi.*, o.total_amount, o.order_date')
			->from('#__wbty_payments_order_items AS oi')
			->join('LEFT', '#__wbty_payments_orders AS o ON oi.order_id=o.id')
			->where('oi.order_id='.$order_id);
		
		$this->db->setQuery($query);
		$order['items'] = $this->db->loadAssocList();
		
		// Get associated addresses
		$query = $this->db->getQuery(true);
		
		$query
			->select('a.*, p.first_name, p.last_name, p.email')
			->from('#__wbty_payments_addresses AS a')
			->join('LEFT','#__wbty_payments_purchasers AS p ON p.id=a.purchaser_id')
			->join('LEFT', '#__wbty_payments_orders AS o ON o.purchaser_id=p.id')
			->where('o.id='.$order_id);
		
		$this->db->setQuery($query);
		$order['addresses'] = $this->db->loadAssocList();
		
		return $order;
	}
	
}