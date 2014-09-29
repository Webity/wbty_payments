<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_footer
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die; ?>

<div id="wbty-cart<?php echo $module->id; ?>" class="wbty-cart">

	<?php
	$total = 0;
	$count = 0;
	if ($items) {
		foreach($items as $item) {
			$count++;
			$total += $item->price * $item->quantity;
		}
	}
	?>

    <div class="total-surround">

        <div class="cart">
        	<a href="<?php echo $cart_url; ?>"><i class="icon-cart"></i></a>
        </div>

        <div class="total">
        	<p><a href="<?php echo $cart_url; ?>">
				<?php if ($count > 0) {
					echo $count.' item'.($count==1 ? '' : 's').' - $'.number_format($total, 2);
				} else {
					echo $count.' item'.($count==1 ? '' : 's').' in your cart.';
				} ?>
            </a></p>
        </div>

        <div class="clear"></div>

    </div>

    <div class="checkout">
    	<a href="<?php echo $cart_url; ?>" class="checkout-button">Checkout <i class="icon-chevron-right"></i></a>
    </div>

</div>
