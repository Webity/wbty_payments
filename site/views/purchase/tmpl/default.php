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
?>

    <h1>Add item to cart</h1>
    <fieldset>
        <form name="wbty_payments" action="<?php echo JRoute::_('index.php?option=com_wbty_payments&task=purchase.add'); ?>" method="post">
            <div class="control-group">
                <label class="control-label" for="input_name">Item Name: </label>
                <div class="controls">
                  	<input type="text" id="input_name" name="jform[name]" value="" />
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="input_description">Item Description: </label>
                <div class="controls">
                  	<input type="text" id="input_description" name="jform[description]" value="" />
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="input_price">Item Price: </label>
                <div class="controls">
                  	<input type="text" id="input_price" name="jform[price]" value="" />
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="input_quantity">Item Quantity: </label>
                <div class="controls">
                  	<input type="text" id="input_quantity" name="jform[quantity]" value="" />
                </div>
            </div>
            <div class="control-group">
            	<div class="controls">
                	<input type="submit" value="Add Item" class="btn btn-primary" />
                </div>
            </div>
        </form>
    </fieldset>