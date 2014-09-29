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

$merchant = $this->gateway['merchant'];
$post = $this->gateway['post'];
$user = JFactory::getUser();
$order = $this->stufwbty[0]; print_r($order);
?>

<script>
jQuery(document).ready(function($) {
    $('form').submit(function() {
        $(this).find('input[type="submit"]').prop('disabled', true);
    });
});
</script>

<div id="purchaseform">

    <form method="post" action="<?php echo JRoute::_('index.php?option=com_wbty_payments&task=process_cc', false); ?>" class="form-horizontal">
        <fieldset class="adminform">
            <legend>Personal Information</legend>
            <ul class="purchaseinfo">
                
                <li class="control-group">
                    <?php echo str_replace('<label', '<label class="control-label"', $this->form->getLabel('firstName')); ?>
                    <div class="controls">
                        <?php echo $this->form->getInput('firstName'); ?>
                    </div>
                </li>
                
                <li class="control-group">
                    <?php echo str_replace('<label', '<label class="control-label"', $this->form->getLabel('lastName')); ?>
                    <div class="controls">
                        <?php echo $this->form->getInput('lastName'); ?>
                    </div>
                </li>
                
                <li class="control-group">
                    <?php echo str_replace('<label', '<label class="control-label"', $this->form->getLabel('email')); ?>
                    <div class="controls">
                        <?php echo $this->form->getInput('email'); ?>
                    </div>
                </li>
        
            </ul>
        </fieldset>
        
        <fieldset class="adminform">
            <legend>Payment Information</legend>
            <ul class="purchaseinfo">
                
                <li class="control-group">
                    <?php echo str_replace('<label', '<label class="control-label"', $this->form->getLabel('creditCardType')); ?>
                    <div class="controls">
                        <?php echo $this->form->getInput('creditCardType'); ?>
                    </div>
                </li>
                
                <li class="control-group">
                    <?php echo str_replace('<label', '<label class="control-label"', $this->form->getLabel('creditCardNumber')); ?>
                    <div class="controls">
                        <?php echo $this->form->getInput('creditCardNumber'); ?>
                    </div>
                </li>
                
                <li class="control-group">
                    <?php echo str_replace('<label', '<label class="control-label"', $this->form->getLabel('expDateMonth')); ?>
                    <div class="controls">
                        <?php echo $this->form->getInput('expDateMonth'); ?>
                    </div>
                </li>
                
                <li class="control-group">
                    <?php echo str_replace('<label', '<label class="control-label"', $this->form->getLabel('expDateYear')); ?>
                    <div class="controls">
                        <?php echo $this->form->getInput('expDateYear'); ?>
                    </div>
                </li>
                
                <li class="control-group">
                    <?php echo str_replace('<label', '<label class="control-label"', $this->form->getLabel('cvv2Number')); ?>
                    <div class="controls">
                        <?php echo $this->form->getInput('cvv2Number'); ?>
                    </div>
                </li>
        
            </ul>
        </fieldset>
        
        <fieldset class="adminform">
            <legend>Payment Address</legend>
            <ul class="purchaseinfo">
                
                <li class="control-group">
                    <?php echo str_replace('<label', '<label class="control-label"', $this->form->getLabel('address1')); ?>
                    <div class="controls">
                        <?php echo $this->form->getInput('address1'); ?>
                    </div>
                </li>
                
                <li class="control-group">
                    <?php echo str_replace('<label', '<label class="control-label"', $this->form->getLabel('address2')); ?>
                    <div class="controls">
                        <?php echo $this->form->getInput('address2'); ?>
                    </div>
                </li>
                
                <li class="control-group">
                    <?php echo str_replace('<label', '<label class="control-label"', $this->form->getLabel('city')); ?>
                    <div class="controls">
                        <?php echo $this->form->getInput('city'); ?>
                    </div>
                </li>
                
                <li class="control-group">
                    <?php echo str_replace('<label', '<label class="control-label"', $this->form->getLabel('state')); ?>
                    <div class="controls">
                        <?php echo $this->form->getInput('state'); ?>
                    </div>
                </li>
                
                <li class="control-group">
                    <?php echo str_replace('<label', '<label class="control-label"', $this->form->getLabel('zip')); ?>
                    <div class="controls">
                        <?php echo $this->form->getInput('zip'); ?>
                    </div>
                </li>
                
                <li class="control-group">
                    <?php echo str_replace('<label', '<label class="control-label"', $this->form->getLabel('country')); ?>
                    <div class="controls">
                        <?php echo $this->form->getInput('country'); ?>
                    </div>
                </li>
        
            </ul>
        </fieldset>
        
        <fieldset class="adminform shipping_information">
            <legend>Shipping Information</legend>
            <ul class="purchaseinfo">
                
                <li class="control-group">
                    <?php echo str_replace('<label', '<label class="control-label"', $this->form->getLabel('shipping_different')); ?>
                    <div class="controls">
                        <?php echo $this->form->getInput('shipping_different'); ?>
                    </div>
                </li>
                
            </ul>
                
            <ul class="purchaseinfo" id="shipping_information">
            
                <li class="control-group">
                    <?php echo str_replace('<label', '<label class="control-label"', $this->form->getLabel('shipping_address1')); ?>
                    <div class="controls">
                        <?php echo $this->form->getInput('shipping_address1'); ?>
                    </div>
                </li>
                
                <li class="control-group">
                    <?php echo str_replace('<label', '<label class="control-label"', $this->form->getLabel('shipping_address2')); ?>
                    <div class="controls">
                        <?php echo $this->form->getInput('shipping_address2'); ?>
                    </div>
                </li>
                
                <li class="control-group">
                    <?php echo str_replace('<label', '<label class="control-label"', $this->form->getLabel('shipping_city')); ?>
                    <div class="controls">
                        <?php echo $this->form->getInput('shipping_city'); ?>
                    </div>
                </li>
                
                <li class="control-group">
                    <?php echo str_replace('<label', '<label class="control-label"', $this->form->getLabel('shipping_state')); ?>
                    <div class="controls">
                        <?php echo $this->form->getInput('shipping_state'); ?>
                    </div>
                </li>
                
                <li class="control-group">
                    <?php echo str_replace('<label', '<label class="control-label"', $this->form->getLabel('shipping_zip')); ?>
                    <div class="controls">
                        <?php echo $this->form->getInput('shipping_zip'); ?>
                    </div>
                </li>
                
                <li class="control-group">
                    <?php echo str_replace('<label', '<label class="control-label"', $this->form->getLabel('shipping_country')); ?>
                    <div class="controls">
                        <?php echo $this->form->getInput('shipping_country'); ?>
                    </div>
                </li>
        
            </ul>
        </fieldset>
        
        <fieldset class="adminform">
            <ul class="purchaseinfo">
                
                <li class="control-group">
                    <div class="controls">
                        <input type="Submit" value="Process Payment">
                    </div>
                </li>
        
            </ul>
            <input type="hidden" name="jform[amount]" id="amount" value="<?php echo $this->order[0]['total_amount']?>">
        </fieldset>
    </form>

</div>

<!--<div id="checkout-order">
	<h1>Your order:</h1>
	<?php
	foreach ($this->order as $o) {
		$item = "<div class='item'>";
		$item .= "<p class='checkout-item-title'>".$o['item_name']."</p>";
		$item .= "<p class='checkout-item-price'>".$o['price']."</p>";
		$item .= "</div><hr />";
		echo $item;
	}
	echo "<h2>Total:</h2>";
	echo "<p class='checkout-total'>".$this->order[0]['total_amount']."</p>";
	?>
</div>-->

<div class="clear"></div>

<script type="text/javascript">
	document.getElementById('jform_shipping_different0').onclick = function () {
		document.getElementById('shipping_information').style.display = "none";
	}

	document.getElementById('jform_shipping_different1').onclick = function () {
		document.getElementById('shipping_information').style.display = "block";
	}
	
	if (document.getElementById('jform_shipping_different1').checked = true) {
		document.getElementById('shipping_information').style.display = "block";
	}
</script>