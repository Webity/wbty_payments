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

class Wbty_paymentsModelPurchase extends JModelLegacy {


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
		
		$query = "SELECT oi.*, o.total_amount, o.order_date FROM #__wbty_payments_order_items AS oi LEFT JOIN #__wbty_payments_orders AS o ON oi.order_id=o.id WHERE oi.order_id=$order_id";
		$this->db->setQuery($query);
		$order = $this->db->loadAssocList();
		
		return $order;
	}
	
}