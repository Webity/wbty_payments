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
    document.gatewayvalidator = document.formvalidator;
    jQuery('#gateway-form .toolbar-list a').each(function() {
        $(this).attr('data-onclick', $(this).attr('onclick')).attr('onclick','');
    });
    jQuery('#gateway-form .toolbar-list a').click(function() { 
        Joomla.submitbutton = document.gatewaysubmitbutton;
        
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
    
    if (task == 'gateway.cancel' || document.gatewayvalidator.isValid(document.id('gateway-form'))) {
        Joomla.submitform(task, document.getElementById('gateway-form'));
    }
    else {
        alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
    }
}
document.gatewaysubmitbutton = Joomla.submitbutton;
<?php
// end javascript output -- /script
$script=ob_get_contents();
ob_end_clean();
$document->addScriptDeclaration($script);
?>

<?php echo JHTML::_('wbty_paymentsHelper.buildEditForm', $this->form); ?>

<form action="<?php echo JRoute::_('index.php?option=com_wbty_payments&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="gateway-form" class="form-validate form-horizontal">
    <fieldset class="adminform parentform" data-controller="gateway" data-task="gateway.ajax_save">
        <div class="row-fluid">
            <div class="span6">
                <fieldset>
                    <legend><?php echo JText::_('COM_WBTY_PAYMENTS_LEGEND_GATEWAY'); ?></legend>
                    <div class="items">
                        <?php 
                            foreach($this->form->getFieldset('gateway') as $field):
                                JHtml::_('wbty.renderField', $field);
                            endforeach; 
                        ?>
                    </div>

                </fieldset>
                
            </div>
                
            <?php // fieldset for each linked table  ?>
            <div class="span6 subtables">
                <fieldset>
                    <legend>Gateway Settings</legend>
        		<?php
        		// Add hidden form fields so as to run neccesary scripts for any modals, ect.
                    foreach($this->gate_fields as $field) {
                        $input = '<li>';
                        $input .= '<label>'.$field['field_name'].'</label>';
                        $input .= '<input type="text" size="30" name="jform[gate_values]['.$field['id'].'][]" id="'.$field['field_name'].'"';
                        if (!empty($field['value'])) {
                            $input .= 'value="'.$field['value'].'"';
                        }
                        $input .= ' /></li>';
                        echo $input;
                    };
                    ?>
                </fieldset>
            </div>
        </div>


        <div class="control-group"> 
            <div class="controls">
                <span class="btn btn-success save-primary"><i class="icon-ok"></i> Save Gateway Info</span>
            </div>
        </div>
    </fieldset>
	
    
	<input type="hidden" name="task" value="" />
    <input type="hidden" name="option" id="option" value="com_wbty_payments" />
    <input type="hidden" name="form_name" id="form_name" value="gateway" />
	<?php echo JHtml::_('form.token'); ?>
	<div class="clr"></div>
</form>