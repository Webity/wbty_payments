<?php
/**
 * @version     1.0.0
 * @package     com_wbty_payments
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Webity <david@makethewebwork.com> - http://www.makethewebwork.com/
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.helper');
$params = JComponentHelper::getParams('com_wbty_payments');

if (!empty($this->order['items'])) : ?>

    <h1>Thank you for your order!</h1>

    <?php
    // need to run these first to get the address for the shipping/tax calculation
    $billing = '';
    $shipping = '';

    foreach($this->order['addresses'] as $address) {

        $html  = '<p><strong>'.$address['first_name'].' '.$address['last_name'].'</strong><br>';
        $html .= $address['email'].'</p>';
        $html .= '<p>'.$address['address_line_1'].'<br>';
        if ($address['address_line_2']) $html .= $address['address_line_2'].'<br>';
        $html .= $address['city'].', '.$address['address_state'].' '.$address['zip'].'<br>';
        $html .= $address['country'];

        switch($address['type']) {

            case 1: // Billing
                $billing = $html;
            break;

            case 2: // Shipping
                $shipping = $html;
                $ship_state = $address['address_state'];
            break;

            case 3: // Both!
                $billing = $shipping = $html;
                $ship_state = $address['address_state'];
            break;
        }

    } ?>

    <div id="complete-order">
        <h3>Your order receipt:</h3>
        <p>
        <?php
        if ($this->order['items'][0]['order_id'])  echo "<span class='order-item-id'>Order ID: <strong>".$this->order['items'][0]['order_id']."</strong></span><br>";
        if ($this->order['items'][0]['order_date']) echo "<span class='order-item-date'>Date: <strong>".$this->order['items'][0]['order_date']."</strong></span>";
		echo "</p><hr><h4>Items:</h4>";
        foreach ($this->order['items'] as $o) {
            $item = "<div class='item'>";
            $item .= "<p><span class='order-item-title'>".$o['item_name']."</span><br>";
            $item .= "<span class='order-item-price'>Price: $".$o['price']."</span><br>";
            $item .= "<span class='order-item-desc'>".$o['item_desc']."</span></p>";
            if ($o['redirect_url'] && $o['redirect_text']) {
				$item .= "<a href='".JRoute::_($o['redirect_url'])."' class='btn btn-success'>".$o['redirect_text']."</a>";
			}
            $item .= "</div><hr />";
            echo $item;
        }

        $total = $this->order['items'][0]['total_amount'];
        jimport('wbtypayments.wbtypayments');
        $shipping_cost = WbtyPayments::calculateShipping($total, $ship_state);
        $tax = WbtyPayments::calculateTax($total, $ship_state);

        $total = $total + $shipping_cost + (($tax/100) * $total);

        echo '<h4>Shipping: $'.number_format($shipping_cost, 2).'</h4>';
        echo '<h4>Tax: $'.number_format(($tax/100) * $total, 2).'</h4>';
		echo "<h3 class='order-total'>Order total: $".number_format($total, 2)."</h3><hr>";
        ?>
    </div>
    <?php if ($this->order['addresses']) : ?>
    <div id="address" class="row-fluid">

    	<div class="span5 offset1">
        	<h3>Billing Info</h3>
            <?php echo $billing; ?>
        </div>

        <div class="span5 offset1">
        	<h3>Shipping Info</h3>
            <?php echo $shipping; ?>
        </div>
    </div>
    <?php endif; ?>

<?php else : ?>
	<h2>No order information is available.</h2>
<?php endif; ?>

<?php
if ($params->get('menu_redirect') && !$params->get('custom_redirect')) {
	$redirect = JRoute::_('index.php?Itemid='.$params->get('menu_redirect'));
} elseif ($params->get('custom_redirect')) {
	$redirect = $params->get('custom_redirect');
} else {
	$redirect = JRoute::_('index.php');
}
?>
<hr />
<div id="continue">
	<div><a class="btn" href="<?php echo $redirect; ?>">Continue Browsing</a></div>
</div>
