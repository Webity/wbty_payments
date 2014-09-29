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
?>

<div id="purchaseform">
<h2>Paypal Payments</h2>
<p>You are about to be redirected to Paypal's site to complete the order. If you are not redirected automatically, please click the "Paypal" button below.</p>

<form action="<?php echo ($this->method->type=='1' ? 'https://www.paypal.com/cgi-bin/webscr' : 'https://www.sandbox.paypal.com/cgi-bin/webscr'); ?>" id="paypalForm" method="post">
<input type="hidden" name="cmd" value="_cart">
<input type="hidden" name="upload" value="1">
<input type="hidden" name="business" value="<?php echo $this->gateway['Merchant Email']; ?>">
<input type="hidden" name="invoice" value="<?php echo $this->order[0]['order_id']; ?>">
<input type="hidden" name="notify_url" value="<?php echo JUri::root(); ?>index.php?option=com_wbtypayments&task=ipn" />
<?php
$i=1;
foreach ($this->order as $o) {
	$item = '';
	$item .= '<input type="hidden" name="item_name_'.$i.'" value="'.$o['item_name'].'" />';
	$item .= '<input type="hidden" name="amount_'.$i.'" value="'.$o['price'].'" />';
	$item .= '<input type="hidden" name="quantity_'.$i.'" value="'.(isset($o['quantity']) ? $o['quantity'] : 1) .'" />';
	$item .= '<input type="hidden" name="shipping_'.$i.'" value="0" />';
	if (isset($o['recurring']) && $o['recurring']) {
		$recurring += $o['price'];
	}
	echo $item;
	$i++;
}
if (isset($recurring) && $recurring) : ?>
<input type="hidden" name="a3" value="<?php echo $recurring; ?>">
<input type="hidden" name="p3" value="1">
<input type="hidden" name="t3" value="M">
<?php endif; ?>

<input type="hidden" name="return" value="<?php echo JRoute::_(JURI::root() .'index.php?option=com_wbty_payments&task=thankyou&referenceId=wbtypayments-'.$this->order[0]['order_id']);?>" />
<input type="submit" class="btn btn-primary" value="PayPal">
</form>

</div>

<script>
	document.getElementById('paypalForm').submit();
</script>

<div class="clear"></div>
