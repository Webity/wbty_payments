<?php
/**
 * @version		$Id: users.php 22338 2011-11-04 17:24:53Z github_bot $
 * @package		Joomla.Site
 * @subpackage	com_wbtyprospects
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

abstract class WBTYPaymentsOrders
{
	public static function loadLanguage() {
		$lang =& JFactory::getLanguage();
		$extension = 'com_users';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);
	}
	
	// function to get form elements from com_users for registration
	// $elements will add elements to the end of the form, should be an xml string
	public static function createOrder($info) {
		// Initialize database
		$db = JFactory::getDBO();
		
		// Check if there is already an order_id set
		$session = JFactory::getSession();
		$session_order = $session->get('wbtypayments.order_id');
				
		// If there is no session order, open a new order
		if (empty($session_order)) {
			// Get the default gateway
			$query = "SELECT gateway_id FROM #__wbty_payments_default_gateway";
			$db->setQuery($query);
			$gateway = $db->loadResult();
			
			// Insert the order information and get the order ID back
			$query = "INSERT INTO #__wbty_payments_orders
						(`user_id`,`paid`,`gateway`,`total_amount`,`order_date`)
					VALUES
						('".$info['user_id']."','0','$gateway','".intval(str_replace(',', '', $info['amount']))."','".$info['purchasedate']."')";
			$db->setQuery($query);
			$db->query();
			$session_order = $db->insertid();
			
			// Set the order for this session
			$session->set('wbtypayments.order_id',$session_order);
			
			$update_total = false;
			
		} else {			
			$update_total = true;
		}
		
		// Values to escape so as not to break the query
		$org_char = array("'", "`", ",");
		$new_char = array("\'", "\`", "\,");
		
		// Insert the new item to be purchased into the database
		$query = "INSERT INTO #__wbty_payments_order_items
			(`order_id`, `com_name`, `price`, `item_name`, `item_desc`, `item_id`, `com_order_id`)
		VALUES
			('".$session_order."', '".$info['component']."', '".$info['amount']."', '".str_replace($org_char, $new_char, $info['pkgname'])."', '".str_replace($org_char, $new_char, $info['pkgdesc'])."', '".$info['pkgid']."', '".$info['com_order_id']."')";
		$db->setQuery($query);
		$db->query();
		
		// Update the total in the orders table
		if ($update_total === true) {
			$query = "UPDATE #__wbty_payments_orders SET total_amount = total_amount + ".intval(str_replace(',', '', $info['amount']))." WHERE id=".$session_order;
			$db->setQuery($query);
			$db->query();
		}

		return true;
	}
	
}
