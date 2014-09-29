<?php
/**
 * @version     0.2.0
 * @package     com_wbty_payments
 * @copyright   Copyright (C) 2012-2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Webity <info@makethewebwork.com> - http://www.makethewebwork.com
 */

// no direct access
defined('_JEXEC') or die;

$item = $this->item['order'];

$total = $item->total_amount;
if ($item->paid) {
    $ship_state = $item->billing_address->address_state;
    jimport('wbtypayments.wbtypayments');
    $shipping = WbtyPayments::calculateShipping($total, $ship_state);
    $tax = WbtyPayments::calculateTax($total, $ship_state);

    $tax = (($tax/100) * $total);
    $total = $total + $shipping + $tax;
}
?>

<h3>Order #<?= $item->id ?>: <?= $item->purchaser->first_name . ' ' . $item->purchaser->last_name; ?></h3>

<ul class="itemlist">
    <!--<li><?php echo JText::_('COM_WBTY_PAYMENTS_FORM_LBL_ORDERS_USER_ID'); ?>: <?php echo $item->user_id; ?></li>-->
    <li><?php echo JText::_('COM_WBTY_PAYMENTS_FORM_LBL_ORDERS_PAID'); ?>: <strong><?php echo $item->paid ? 'Yes' : 'No'; ?></strong></li>
    <!--<li><?php echo JText::_('COM_WBTY_PAYMENTS_FORM_LBL_ORDERS_GATEWAY'); ?>: <?php echo $item->gateways_name; ?></li>-->
    <li><?php echo JText::_('COM_WBTY_PAYMENTS_FORM_LBL_ORDERS_TOTAL_AMOUNT'); ?>: <strong>$<?= number_format($total, 2); ?></strong></li>
    <li>Tax: <strong>$<?= number_format($tax, 2); ?></strong></li>
    <li>Shipping: <strong>$<?= number_format($shipping, 2); ?></strong></li>
    <li><?php echo JText::_('COM_WBTY_PAYMENTS_FORM_LBL_ORDERS_ORDER_DATE'); ?>: <strong><?php echo strftime('%b %d, %Y %H:%M', strtotime($item->order_date)); ?></strong></li>
    <li><?php echo JText::_('COM_WBTY_PAYMENTS_FORM_LBL_ORDERS_COUPON'); ?>: <strong><?php echo $item->coupon_name; ?></strong></li>
    <li><?php echo JText::_('COM_WBTY_PAYMENTS_FORM_LBL_ORDERS_COUPON_AMOUNT'); ?>: <strong><?php echo $item->coupon_amount; ?></strong></li>
</ul>

<div class="row-fluid">
    <div class="span4">
        <h3>Order Items</h3>
        <?php foreach ($item->items as $i) : ?>
            <h4><?= $i->item_name; ?></h4>
            <div>Quantity: <strong><?= $i->quantity ?></strong></div>
            <div>Price: <strong>$<?= number_format($i->price, 2) ?></strong></div>
        <?php endforeach; ?>
    </div>
    <div class="span4">
        <h3>Shipping Address</h3>
        <p>
            <?= $item->shipping_address->address_line_1; ?><br>
            <?= $item->shipping_address->address_line_2; ?>
            <?= $item->shipping_address->address_line_2 ? '<br>' : ''; ?>
            <?= $item->shipping_address->city; ?>,
            <?= $item->shipping_address->address_state; ?>
            <?= $item->shipping_address->zip; ?>
        </p>
    </div>
    <div class="span4">
        <h3>Billing Address</h3>
        <p>
            <?= $item->billing_address->address_line_1; ?><br>
            <?= $item->billing_address->address_line_2; ?>
            <?= $item->billing_address->address_line_2 ? '<br>' : ''; ?>
            <?= $item->billing_address->city; ?>,
            <?= $item->billing_address->address_state; ?>
            <?= $item->billing_address->zip; ?>
        </p>
    </div>
</div>

<form action="<?php echo JRoute::_('index.php?option=com_wbty_payments&layout=edit&id='.(int) $item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal">
	<input type="hidden" name="task" value="" />
    <input type="hidden" name="option" id="option" value="com_wbty_payments" />
    <input type="hidden" name="form_name" id="form_name" value="order" />
    <?php echo JHtml::_('form.token'); ?>
</form>
