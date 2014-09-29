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
            
	
					<li><?php echo JText::_('COM_WBTY_PAYMENTS_FORM_LBL_GATEWAY_FIELD_VALUES_GATEWAY_LINK_ID'); ?>: <?php echo $this->item->gateway_link_id; ?></li>
					<li><?php echo JText::_('COM_WBTY_PAYMENTS_FORM_LBL_GATEWAY_FIELD_VALUES_VALUE'); ?>: <?php echo $this->item->value; ?></li>

</ul>

<form action="<?php echo JRoute::_('index.php?option=com_wbty_payments{parent_url}&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="gateway_field_value-form" class="form-validate form-horizontal">
	<input type="hidden" name="task" value="" />
    <input type="hidden" name="option" id="option" value="com_wbty_payments" />
    <input type="hidden" name="form_name" id="form_name" value="gateway_field_value" />
    <?php echo JHtml::_('form.token'); ?>
</form>