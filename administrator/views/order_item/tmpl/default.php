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
            
	
					<li><?php echo JText::_('COM_WBTY_PAYMENTS_FORM_LBL_ORDER_ITEMS_PRICE'); ?>: <?php echo $this->item->price; ?></li>
					<li><?php echo JText::_('COM_WBTY_PAYMENTS_FORM_LBL_ORDER_ITEMS_ITEM_NAME'); ?>: <?php echo $this->item->item_name; ?></li>
					<li><?php echo JText::_('COM_WBTY_PAYMENTS_FORM_LBL_ORDER_ITEMS_ITEM_DESC'); ?>: <?php echo $this->item->item_desc; ?></li>
					<li><?php echo JText::_('COM_WBTY_PAYMENTS_FORM_LBL_ORDER_ITEMS_ITEM_ID'); ?>: <?php echo $this->item->item_id; ?></li>
					<li><?php echo JText::_('COM_WBTY_PAYMENTS_FORM_LBL_ORDER_ITEMS_CALLBACK_FILE'); ?>: <?php echo $this->item->callback_file; ?></li>
					<li><?php echo JText::_('COM_WBTY_PAYMENTS_FORM_LBL_ORDER_ITEMS_CALLBACK_FUNCTION'); ?>: <?php echo $this->item->callback_function; ?></li>
					<li><?php echo JText::_('COM_WBTY_PAYMENTS_FORM_LBL_ORDER_ITEMS_CALLBACK_ID'); ?>: <?php echo $this->item->callback_id; ?></li>
					<li><?php echo JText::_('COM_WBTY_PAYMENTS_FORM_LBL_ORDER_ITEMS_REDIRECT_URL'); ?>: <?php echo $this->item->redirect_url; ?></li>
					<li><?php echo JText::_('COM_WBTY_PAYMENTS_FORM_LBL_ORDER_ITEMS_REDIRECT_TEXT'); ?>: <?php echo $this->item->redirect_text; ?></li>
					<li><?php echo JText::_('COM_WBTY_PAYMENTS_FORM_LBL_ORDER_ITEMS_RECURRING'); ?>: <?php echo $this->item->recurring; ?></li>

</ul>

<form action="<?php echo JRoute::_('index.php?option=com_wbty_payments&order_id='.JRequest::getCmd('order_id').'&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="order_item-form" class="form-validate form-horizontal">
	<input type="hidden" name="task" value="" />
    <input type="hidden" name="option" id="option" value="com_wbty_payments" />
    <input type="hidden" name="form_name" id="form_name" value="order_item" />
    <?php echo JHtml::_('form.token'); ?>
</form>