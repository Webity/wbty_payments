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

JHtml::_('behavior.tooltip');

?>

<div class="cpanel">
	<h2>Main Tasks</h2>
    <div class="icon-wrapper">
		<div class="btn cpanel-btn">
			<a href="index.php?option=com_wbty_payments&view=gateways"><img src="<?php echo JURI::root(); ?>media/wbty_payments/img/gateways.png" alt=""><span>Gateways</span></a>
		</div>
		<div class="btn cpanel-btn">
			<a href="index.php?option=com_wbty_payments&view=orders"><img src="<?php echo JURI::root(); ?>media/wbty_payments/img/orders.png" alt=""><span>Orders</span></a>
		</div>
		<div class="btn cpanel-btn">
			<a href="index.php?option=com_wbty_payments&view=coupons"><img src="<?php echo JURI::root(); ?>media/wbty_payments/img/coupons.png" alt=""><span>Coupons</span></a>
		</div>
        <div class="clr"></div>
    </div>
    <h2 style="clear:left;">Configuration / Settings</h2>
    <div class="icon-wrapper">
		<div class="btn cpanel-btn">
			<a href="index.php?option=com_wbty_payments&view=taxes"><img src="<?php echo JURI::root(); ?>media/wbty_payments/img/taxes.png" alt=""><span>Taxes</span></a>
		</div>
			<div class="btn cpanel-btn">
				<a href="index.php?option=com_wbty_payments&view=shipping"><img src="<?php echo JURI::root(); ?>media/wbty_payments/img/shipping.png" alt=""><span>Shipping</span></a>
			</div>
        <div class="clr"></div>
    </div>
</div>
