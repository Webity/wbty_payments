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

$document = JFactory::getDocument();
$document->addScript("https://js.stripe.com/v2/");
JHtml::_('script', 'wbty_payments/jquery.payment.js', false, true);

// 1 == Live Mode
if ($this->method->type == 1) {
    $publishable_key = $this->gateway['Live Publishable Key'];
} else {
    $publishable_key = $this->gateway['Test Publishable Key'];
}

ob_start(); ?>
        
Stripe.setPublishableKey('<?php echo $publishable_key; ?>');

var stripeResponseHandler = function(status, response) {
    var $form = jQuery('#stripe-registration');
    
    console.log(response);
    
    if (response.error) {
        // Show the errors on the form
        $form.find('.payment-errors').text(response.error.message);
        $form.find('button').prop('disabled', false);
    } else {
        // token contains id, last4, and card type
        var token = response.id;
        // Insert the token into the form so it gets submitted to the server
        $form.append(jQuery('<input type=\"hidden\" name=\"jform[stripeToken]\" />').val(token));
        // and submit
        setTimeout(console.log(''), 4000);
        $form.get(0).submit();
    }
};

jQuery(function($) {
    
    jQuery('#jform_card_number').payment('formatCardNumber');
    jQuery('#jform_cc_exp').payment('formatCardExpiry');
    jQuery('#jform_cvc').payment('formatCardCVC');

    $('#stripe-registration').submit(function(event) {
        var $form = $(this);
        
        $('input').removeClass('invalid');
        $('.validation').removeClass('passed failed');

        var cardType = $.payment.cardType($('.cc-number').val());
        
        var expiration = $('#jform_cc_exp').payment('cardExpiryVal');
        
        $('#jform_card_number').toggleClass('invalid', !$.payment.validateCardNumber($('#jform_card_number').val()));
        $('#jform_cc_exp').toggleClass('invalid', !$.payment.validateCardExpiry(expiration));
        $('#jform_cvc').toggleClass('invalid', !$.payment.validateCardCVC($('#jform_cvc').val(), cardType));
        
        $('#jform_exp_month').val( expiration.month );
        $('#jform_exp_year').val( expiration.year );
        
        if ( $('input.invalid').length ) {
          $('.validation').addClass('failed');
        } else {
          $('.validation').addClass('passed');
        }
        
        // Disable the submit button to prevent repeated clicks
        $form.find('button').prop('disabled', true);
        
        Stripe.card.createToken($form, stripeResponseHandler);
        
        // Prevent the form from submitting with the default action
        return false;
    });
  
});

<?php $script = ob_get_contents();
ob_get_clean();

$document->addScriptDeclaration($script);
?>

<script>
jQuery(document).ready(function($) {
    $('form').submit(function() {
        $(this).find('input[type="submit"]').prop('disabled', true);
    });
});
</script>

<div id="purchaseform">

    <form method="post" action="<?php echo JRoute::_('index.php?option=com_wbty_payments&task=process_cc', false); ?>" class="form-horizontal" id="stripe-registration">
    	<div class="row-fluid">
        <div class="span6">
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
        </div>
        
        <div class="span6">
        <fieldset class="adminform">
            <legend>Payment Information</legend>
            <span class="payment-errors"></span>
            <ul class="purchaseinfo"></ul>


                <li class="control-group stripetext">
                    <div class="control-label">
                        <label id="jform_card_number-lbl" for="jform_card_number" class="hasTooltip invalid" title="" data-original-title="&lt;strong&gt;Card Number *&lt;/strong&gt;&lt;br /&gt;The number of the card you wish to use." aria-invalid="true">Card Number *</label>
                    </div>
                    <div class="controls">
                        <input type="text" id="jform_card_number" value="" class="required invalid" size="20" data-stripe="number" maxlength="20" aria-required="true" required="required" aria-invalid="true">                       
                    </div>
                </li>
                <li class="control-group stripetext">
                    <div class="control-label">
                        <label id="jform_cvc-lbl" for="jform_cvc" class="hasTooltip" title="" data-original-title="&lt;strong&gt;CVC *&lt;/strong&gt;&lt;br /&gt;This 3-4 digit code can usually be found on the back of your card next to the signature strip.">CVC *</label>
                    </div>
                    <div class="controls">
                        <input type="text" id="jform_cvc" value="" class="required" placeholder="CVC" size="4" data-stripe="cvc" maxlength="4" aria-required="true" required="required">                        
                    </div>
                </li>
                <li class="control-group stripetext">
                    <div class="control-label">
                        <label id="jform_cc_exp-lbl" for="jform_cc_exp" class="hasTooltip" title="" data-original-title="&lt;strong&gt;Expires *&lt;/strong&gt;&lt;br /&gt;When does this card expire?">Expires *</label>
                    </div>
                    <div class="controls">
                        <input type="text" id="jform_cc_exp" value="" class="required" placeholder="MM / YYYY" size="9" data-stripe="cc-exp" maxlength="9" aria-required="true" required="required">                       
                    </div>
                </li>

                <input type="hidden" id="jform_exp_month" value="" data-stripe="exp-month">
                <input type="hidden" id="jform_exp_year" value="" data-stripe="exp-year">
        </fieldset>
        </div>
        <div class="clear"></div>
        </div>
        
        <div class="row-fluid">
        <div class="span6">
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
        </div>
        
        <div class="span6">
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
        </div>
        <div class="clear"></div>
        </div>
        
        <fieldset class="adminform">
            <ul class="purchaseinfo" id="submit-purchase">
                
                <li class="control-group">
                    <div class="controls">
                        <input type="Submit" value="Process Payment" class="btn btn-success btn-large">
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