<?php defined('_JEXEC') or die('Restricted access');

$app =& JFactory::getApplication();
jimport('wbtypayments.wbtypayments');
$wbtypayments = $app->input->get('wbtypayments', array(), 'ARRAY');

if ($wbtypayments) {
	$order_info = array(
					'amount' => $wbtypayments['price'],
					'item_name' => $wbtypayments['name'],
					'item_desc' => $wbtypayments['description'],
					'item_id' => $wbtypayments['id'],
					'callback_file' => $wbtypayments['callback_file'],
					'callback_function' => $wbtypayments['callback_function'],
					'callback_id' => $wbtypayments['callback_id'],
					'redirect_url' => $wbtypayments['redirect_url'],
					'redirect_text' => $wbtypayments['redirect_text'],
					'recurring' => $wbtypayments['subscription']
					);
					
	if ($wbtypayments['require_login']) {
		$session =& JFactory::getSession();
		$session->set('wbtypayments.require_login',true);
	}
	
	if (WbtyPayments::createOrder($order_info)) {
		$url = WbtyPayments::getCheckoutUrl();
		$app->redirect($url);
	} else {
		$app->enqueueMessage('Error adding item to cart');
	}
}

require JModuleHelper::getLayoutPath('mod_wbty_payments', 'default');

?>