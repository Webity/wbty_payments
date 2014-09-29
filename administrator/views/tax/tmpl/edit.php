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
JHtml::_('behavior.formvalidation');

$document = &JFactory::getDocument();
JHTML::script("wbty_components/linked_tables.js", false, true);
JHTML::script("wbty_payments/edit.js", false, true);


ob_start();
// start javascript output -- script
?>
window.addEvent('domready', function(){
    // save validator, getting overwritten by AJAX call
    document.taxvalidator = document.formvalidator;
    jQuery('#tax-form .toolbar-list a').each(function() {
        $(this).attr('data-onclick', $(this).attr('onclick')).attr('onclick','');
    });
    jQuery('#tax-form .toolbar-list a').click(function() {
        Joomla.submitbutton = document.taxsubmitbutton;

        // clean up hidden subtables
        jQuery('.subtables:hidden').remove();

        eval($(this).attr('data-onclick'));
    });
});

window.juri_root = '<?php echo JURI::root(); ?>';
window.juri_base = '<?php echo JURI::base(); ?>';

Joomla.submitbutton = function(task)
{
    if (jQuery('#sbox-window').attr('aria-hidden')==true) {
        Joomla.submitform = defaultsubmitform;
    }

    if (task == 'tax.cancel' || document.taxvalidator.isValid(document.id('tax-form'))) {
        Joomla.submitform(task, document.getElementById('tax-form'));
    }
    else {
        alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
    }
}
document.taxsubmitbutton = Joomla.submitbutton;
<?php
// end javascript output -- /script
$script=ob_get_contents();
ob_end_clean();
$document->addScriptDeclaration($script);
?>

<?php echo JHTML::_('wbty_paymentsHelper.buildEditForm', $this->form); ?>

<form action="<?php echo JRoute::_('index.php?option=com_wbty_payments&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="tax-form" class="form-validate form-horizontal">
    <div class="row-fluid">
        <div class="span6">
            <fieldset class="adminform parentform" data-controller="tax" data-task="tax.ajax_save">
                <legend><?php echo JText::_('COM_WBTY_PAYMENTS_LEGEND_TAX'); ?></legend>
                <div class="items">
                    <?php
                        foreach($this->form->getFieldset('tax') as $field):
                            JHtml::_('wbty.renderField', $field);
                        endforeach;
                    ?>

                    <div class="control-group">
                        <div class="controls">
                            <span class="btn btn-success save-primary"><i class="icon-ok"></i> Save Tax Info</span>
                        </div>
                    </div>
                </div>

            </fieldset>

        </div>

        <?php // fieldset for each linked table  ?>
        <div class="span6 subtables">
		<?php
		// Add hidden form fields so as to run neccesary scripts for any modals, ect.
		require_once(JPATH_COMPONENT_ADMINISTRATOR . '/helpers/ajax.php');
		$helper = new wbty_paymentsHelperAjax;
		?></div>
    </div>


	<input type="hidden" name="task" value="" />
    <input type="hidden" name="option" id="option" value="com_wbty_payments" />
    <input type="hidden" name="form_name" id="form_name" value="tax" />
	<?php echo JHtml::_('form.token'); ?>
	<div class="clr"></div>
</form>
