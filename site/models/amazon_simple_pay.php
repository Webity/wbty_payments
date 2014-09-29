<?php
/**
 * @package    WBTY Payments
 *
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

require_once(dirname(__FILE__) .DS. "WBTYPaymentsGatewayModel.php");

class WBTYPaymentsAmazon_simple_pay extends WBTYPaymentsGatewayModel {
	
	function __construct($properties = null) {
		
		// Call the function to get the merchant and post data
		$this->info = parent::getGateway();
		
		parent::__construct($properties);
	}
	
	
	function process_cc() {
		return false;
	}
	
	function verify_payment() {
		$referenceId = JFactory::getApplication()->input->get('referenceId', '');
		$order_id = ltrim($referenceId, 'wbtypayments-');
		
		if (!$order_id) {
			return false;
		}
		
		$transactionAmount = JFactory::getApplication()->input->get('transactionAmount', '');
		$amount = ltrim($transactionAmount, 'USD ');
		
		$status = JFactory::getApplication()->input->get('status', '');
		
		if ($status!='SS') {
			return false;
		}
		
		$query = "SELECT id FROM #__wbty_payments_orders WHERE id='$order_id' AND CONVERT(total_amount, DECIMAL(10,2)) = CONVERT('$amount', DECIMAL(10,2)) LIMIT 1";
		$db = JFactory::getDBO();
		$db->setQuery($query);
		$order_id = $db->loadResult();
		
		return $order_id;
	}
	
	function setPaid() {
		parent::setPaid();
	}
	
}