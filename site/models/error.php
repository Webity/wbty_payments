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

jimport('joomla.application.component.model');

/**
 * Model
 */
class Wbty_paymentsModelError extends JModelLegacy
{
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
	
	public function getOrderErrors() {
		// Initialize database
		$db = JFactory::getDBO();
		
		// Get order_id from session data	
		$session = JFactory::getSession();
		$order_id = $session->get('wbtypayments.order_id');
		
		if(!empty($order_id)) {
			// Get the errors based on the order_id
			$query = "SELECT e.*, ei.* FROM #__wbty_payments_errors AS e INNER JOIN #__wbty_payments_error_extra_items AS ei ON e.id=ei.error_id WHERE e.order_id=".$db->quote($order_id);
			$db->setQuery($query);
			$errors = $db->loadAssocList();
		} else {
			$errors['default_error'] = array('The system could not find an ID for your order. Please try your purchase again or contact us for assistance.');
		}
		
		return $errors;
	}

}

