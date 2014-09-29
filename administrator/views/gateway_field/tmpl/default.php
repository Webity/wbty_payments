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
            
	
					<li><?php echo JText::_('COM_WBTY_PAYMENTS_FORM_LBL_GATEWAY_FIELDS_FIELD_NAME'); ?>: <?php echo $this->item->field_name; ?></li>

</ul>

<form action="<?php echo JRoute::_('index.php?option=com_wbty_payments&gateway_id='.JRequest::getCmd('gateway_id').'&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="gateway_field-form" class="form-validate form-horizontal">
	<input type="hidden" name="task" value="" />
    <input type="hidden" name="option" id="option" value="com_wbty_payments" />
    <input type="hidden" name="form_name" id="form_name" value="gateway_field" />
    <?php echo JHtml::_('form.token'); ?>
</form>