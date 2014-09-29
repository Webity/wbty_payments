<?php
/**
 * @version     1.0.0
 * @package     com_wbty_payments
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Webity <david@makethewebwork.com> - http://www.makethewebwork.com/
 */

// No direct access.
defined('_JEXEC') or die;

class Wbty_paymentsControllerPurchase extends JControllerLegacy
{
	function add() {
		$app =& JFactory::getApplication();
		jimport('wbtypayments.wbtypayments');
		$session =& JFactory::getSession();
		
		$post = JFactory::getApplication()->input->get('jform', array(), 'ARRAY');
				
		$order_info = array(
						'amount' => $post['price'],
						'item_name' => $post['name'],
						'item_desc' => $post['description'],
						'item_id' => 1,
						'callback_file' => '',
						'callback_function' => '',
						'callback_id' => 0,
						'redirect_url' => '',
						'redirect_text' => ''
						);
						
		if ($post['require_login']) {
			$session->set('wbtypayments.require_login',true);
		}
		
		if (WbtyPayments::createOrder($order_info)) {
			$url = WbtyPayments::getCheckoutUrl();
		} else {
			$app->enqueueMessage('Error Purchasing Package...');
			$url = 'index.php?option=com_wbty_payments';
		}
		
		$app->redirect(JRoute::_($url));
		exit();
	}
}