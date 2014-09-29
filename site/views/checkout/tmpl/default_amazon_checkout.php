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

$document =& JFactory::getDocument();

$document->addScript('https://payments-sandbox.amazon.com/cba/js/PaymentWidgets.js');

// need to generate cart map to sign the request
$map = array();

$i=1;
foreach ($this->order as $o) {
	$map['item_title_'.$i] = $o['item_name'];
	$map['item_price_'.$i] = $o['price'];
	$map['item_quantity_'.$i] = $o['quantity'];
	$map['item_sku_'.$i] = 0;
	$i++;
}
// TODO: map to AWS credentials
$map['item_merchant_id_1'] = '---';
$map['currency_code'] = 'USD';
$map['aws_access_key_id'] = '---';

ksort($map);

$input = '';

foreach ($map as $key => $value) {
	$input = $input . $key . '=' . rawurlencode($value) . '&';
}

$rawHmac = hash_hmac('sha1', $input, '5Edawf/GLWSWFaOn74o5dehz2z+Wm8fLR1c4YnIm', true);

$key =  base64_encode($rawHmac);

?>

<div id="purchaseform">
<h2>Amazon Checkout</h2>
<p>Please use the options below to complete your order.</p>

<form id="CBACartFormId">
<input name="item_merchant_id_1" value="A25UZBBR7BSXLQ<?php //echo $this->gateway['Merchant ID']; ?>" type="hidden" />

<?php
$i=1;
foreach ($this->order as $o) {
	$item .= '<input type="hidden" name="item_title_'.$i.'" value="'.$o['item_name'].'" />';
	$item .= '<input type="hidden" name="item_price_'.$i.'" value="'.$o['price'].'" />';
	$item .= '<input type="hidden" name="item_quantity_'.$i.'" value="'.$o['quantity'].'" />';
	$item .= '<input type="hidden" name="item_sku_'.$i.'" value="0" />';
	echo $item;
	$i++;
}
?>
<input type="hidden" name="return" value="<?php echo JRoute::_(JURI::root() .'index.php?option=com_wbty_payments&view=thankyou');?>" />

<input name="currency_code" value="USD" type="hidden" />
<input name="merchant_signature" value="<?php echo $key; ?>" type="hidden" />
<input name="aws_access_key_id" value="AKIAJPAS4XYNU4H4CWKQ" type="hidden" />
</form>

<div id="cbaButton1"></div>
<script>
if (CBA) {
	new CBA.Widgets.StandardCheckoutWidget({
	merchantId:'A25UZBBR7BSXLQ',
	orderInput: {format: "HTML",
	value: "CBACartFormId"},
	buttonSettings: {size:'large',color:'orange',background:'white'}
	}).render("cbaButton1");
}
</script>

</div>

<script>
	//document.getElementById('paypalForm').submit();
</script>

<div class="clear"></div>
