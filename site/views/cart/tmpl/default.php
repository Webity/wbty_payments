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
$doc = JFactory::getDocument();

$jversion = new JVersion();
$above3 = version_compare($jversion->getShortVersion(), '3.0', 'ge');

if ($above3) {
    JHTML::_('jquery.framework');
} else {
	$doc->addScript(JURI::root() . "components/com_wbty_payments/assets/js/jquery-1.7.2.min.js");
}

// Script to delete a note
$script = "
jQuery(document).ready(function($) {
	jQuery('.remove-item').click(function() {
		div_id = jQuery(this).closest('div.item').attr('id');
		item_id = jQuery('#'+div_id).find('.item-id').val();

		var dataString = 'item_id=' + item_id + '&order_id=".(int)$this->order[0]['order_id']."';

		jQuery.ajax({
		  type: \"POST\",
		  url: \"".JRoute::_('index.php?option=com_wbty_payments&view=cart&task=remove', false)."\",
		  data: dataString,
		  success: function(applyData) {
				jQuery('#'+div_id).animate({height: 0, opacity: 0}, 'slow', function() {
					jQuery('#'+div_id).remove();
				});
				jQuery('.order-total').animate({opacity: 100}, 'slow', function() {
					jQuery('.order-total').html(applyData);
				});
                if (jQuery('input.shipping_state').val()) {
                    jQuery('.submit-state').click();
                }
		  }
		});
		return false;
	});

	jQuery('.submit-coupon').click(function() {
        coupon = jQuery('input.coupon').val();
        jQuery('.coupon-discount').html('<img src=\"".JUri::root(true)."/media/wbty_components/img/load.gif\" />');

		var dataString = 'coupon=' + coupon + '&order_id=".(int)$this->order[0]['order_id']."';

		jQuery.ajax({
		  type: \"POST\",
		  url: \"".JRoute::_('index.php?option=com_wbty_payments&view=cart&task=applyCoupon', false)."\",
		  data: dataString,
		  dataType: 'json',
		  success: function(applyData) {
				jQuery('.order-total').animate({opacity: 100}, 'slow', function() {
					jQuery('.order-total').html(applyData.total);
				});
				jQuery('.coupon-discount').animate({opacity: 100}, 'slow', function() {
					jQuery('.coupon-discount').html('-$' + applyData.coupon);
				});
                if (jQuery('input.shipping_state').val()) {
                    jQuery('.submit-state').click();
                }
		  },
          error: function (a, b, c) {
              console.log(a);
              console.log(b);
              console.log(c);
          }
		});
		return false;
	});

    jQuery('.submit-state').click(function() {
        state = jQuery('input.shipping_state').val();
        if (!state) {
            jQuery('.tax-shipping').html('');
            jQuery('.order-total').html(jQuery('.order-subtotal').html());
            return;
        }
        jQuery('.tax-shipping').html('<img src=\"".JUri::root(true)."/media/wbty_components/img/load.gif\" />');

        var dataString = 'shipping_state=' + state + '&order_id=".(int)$this->order[0]['order_id']."';

        jQuery.ajax({
          type: \"POST\",
          url: \"".JRoute::_('index.php?option=com_wbty_payments&view=cart&task=estimateShipping', false)."\",
          data: dataString,
          dataType: 'json',
          success: function(applyData) {
                jQuery('.order-total').animate({opacity: 100}, 'slow', function() {
                    jQuery('.order-total').html(applyData.total);
                });
                jQuery('.tax-shipping').animate({opacity: 100}, 'slow', function() {
                    jQuery('.tax-shipping').html('Shipping: +$' + applyData.shipping + '<br>Tax: +$' + applyData.tax);
                });
          }
        });
        return false;
    });
});
";

$doc->addScriptDeclaration($script);

if (!empty($this->order)) {
?>

    <div id="order">
        <h1>Your order:</h1>
        <?php
		for ($i = 1; $i<=count($this->order); $i++) {
            $item = "<div class='item' id='item$i'>";
            $item .= '<input type="button" class="remove-item readmore" value="Remove">';
            if ($this->order[$i-1]['item_image']) {
                $item .= '<img style="float:left; margin-right:10px; margin-bottom:10px;" class="item-image" src="'.JURI::root(true).'/'.ltrim($this->order[$i-1]['item_image'], '/\\').'" />';
            }
            $item .= '<h3 class="order-item-title">'.$this->order[$i-1]['item_name'].'</h3>';
            $item .= '<p class="order-item-price">Price: $'.$this->order[$i-1]['price'].'</p>';
            $item .= '<p class="order-item-quantity">Quantity: '.$this->order[$i-1]['quantity'].'</p>';

			$content = explode('<hr id="system-readmore" />', $this->order[$i-1]['item_desc']);

            $item .= '<div class="order-item-desc">'.$content[0].'</div>';
			$item .= '<input type="hidden" class="item-id" value="'.$this->order[$i-1]['id'].'">';
            $item .= '<div class="clear"></div></div>';
            echo $item;
        }
        ?>
    </div>

    <form action="<?php echo JRoute::_('index.php?option=com_wbty_payments&view=checkout'); ?>" method="post">
    <div id="payment-method"<?php if (count($this->methods) == 1) echo ' style="display: none;"'; ?>>
		<?php if ($this->methods) :
		foreach ($this->methods as $m) : ?>
			<label for="method_<?php echo $m['id']; ?>" class="radio"><input type="radio" id="method_<?php echo $m['id']; ?>" name="jform[method]" value="<?php echo $m['id']; ?>"<?php if ($m['default_gateway']) { echo ' checked="checked"'; } ?> /><?php echo $m['name']; ?></label>
		<?php endforeach;
		endif; ?>
    <hr />
    </div>
    <div id="order-subtotal">
        <h3>Subtotal: $<span class='order-subtotal'><?= number_format((float)$this->order[0]['total_amount'], 2, '.', ''); ?></span></h3>
    </div>
    <div id="coupon-section">
        <input type="text" class="input-medium coupon" name="coupon" placeholder="Coupon Code" style="margin-bottom:0" />
        <span class="btn submit-coupon">Apply Coupon</span>

        <span class="coupon-discount"></span>
    </div>
    <div id="tax-section">
        <input type="text" class="input-medium shipping_state" name="shipping_state" placeholder="Enter Shipping State" style="margin-bottom:0" />
        <span class="btn submit-state">Calculate Tax & Shipping</span>

        <span class="tax-shipping"></span>
    </div>
    <div id="order-total">
        <h2>Total:</h2>
        <?php echo "<p>$<span class='order-total'>".number_format((float)$this->order[0]['total_amount'], 2, '.', '')."</span></p>"; ?>

        <div class="controls">

        	<?php
			if ($this->params->get('continue_shopping') && !$this->params->get('custom_continue_shopping')) {
				$redirect = JRoute::_('index.php?Itemid='.$this->params->get('continue_shopping'));
			} elseif ($this->params->get('custom_continue_shopping')) {
				$redirect = $this->params->get('custom_continue_shopping');
			}

			if ($redirect) echo '<a href="'.$redirect.'" class="readmore continue-shopping">Continue Shopping</a> <span class="or">or</span>';
			?>
        	<input type="submit" name="jform[checkout]" class="order-checkout readmore" value="Checkout" />
        	<div class="clear"></div>
        </div>
        <div class="clear"></div>
    </div>
    </form>

    <div class="clear"></div>

<?php
} else {
?>

	<div id="order">
        <h1>Your shopping cart is empty!</h1>
    </div>

    <div class="clear"></div>

<?php
}
?>
