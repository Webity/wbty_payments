<?php defined('_JEXEC') or die('Restricted access');

JHtml::stylesheet('wbty_payments/wbty_payments_cart.css', false, true);

$session = JFactory::getSession();
$order_id = $session->get('wbtypayments.order_id');

// Initialize database
$db = JFactory::getDBO();

if ($order_id) {
	$query = "SELECT oi.*, o.total_amount, o.user_id FROM #__wbty_payments_order_items AS oi LEFT JOIN #__wbty_payments_orders AS o ON oi.order_id=o.id WHERE order_id=$order_id";
	$db->setQuery($query);
	$items = $db->loadObjectList();
}

jimport('wbtypayments.wbtypayments');
$cart_url = WbtyPayments::getCheckoutUrl();

require JModuleHelper::getLayoutPath('mod_wbtypayments_cart', 'default');

?>