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
?>

<ul class="itemlist">
            
	
					<li><?php echo JText::_('COM_WBTY_PAYMENTS_FORM_LBL_COUPONS_NAME'); ?>: <?php echo $this->item->name; ?></li>
					<li><?php echo JText::_('COM_WBTY_PAYMENTS_FORM_LBL_COUPONS_DISCOUNT'); ?>: <?php echo $this->item->discount; ?></li>

</ul>

<form action="<?php echo JRoute::_('index.php?option=com_wbty_payments{parent_url}&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="coupon-form" class="form-validate form-horizontal">
	<input type="hidden" name="task" value="" />
    <input type="hidden" name="option" id="option" value="com_wbty_payments" />
    <input type="hidden" name="form_name" id="form_name" value="coupon" />
    <?php echo JHtml::_('form.token'); ?>
</form>