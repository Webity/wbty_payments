<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_footer
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>
<form action="" method="post" class="mod_wbtypayments form-horizontal">
	<input type="hidden" name="wbtypayments[name]" value="<?php echo $params->get('name'); ?>" />
    <?php if ($params->get('price')) : ?>
	<input type="hidden" name="wbtypayments[price]" value="<?php echo $params->get('price'); ?>" />
    <?php else: ?>
    <div class="control-group">
	<label class="control-label">Price</label>
    <div class="controls"><input type="text" class="wbtypayments price" name="wbtypayments[price]" value="" /></div>
    </div>
    <?php endif; ?>
    <?php if ($params->get('allow_subscription')) : ?>
    <div class="control-group">
        <div class="controls">
        <label class="checkbox"><input type="checkbox" name="wbtypayments[subscription]" value="1"> Automatic Monthly Payment</label>
        </div>
    </div>
    <?php endif; ?>
	<input type="hidden" name="wbtypayments[description]" value="<?php echo $params->get('description'); ?>" />
	<input type="hidden" name="wbtypayments[id]" value="<?php echo $params->get('id'); ?>" />
	<input type="hidden" name="wbtypayments[callback_file]" value="<?php echo $params->get('callback_file'); ?>" />
	<input type="hidden" name="wbtypayments[callback_function]" value="<?php echo $params->get('callback_function'); ?>" />
	<input type="hidden" name="wbtypayments[callback_id]" value="<?php echo $params->get('callback_id'); ?>" />
	<input type="hidden" name="wbtypayments[redirect_url]" value="<?php echo $params->get('redirect_url'); ?>" />
	<input type="hidden" name="wbtypayments[redirect_text]" value="<?php echo $params->get('redirect_text'); ?>" />
	<input type="hidden" name="wbtypayments[require_login]" value="<?php echo $params->get('require_login'); ?>" />
    <div class="control-group">
    <div class="controls">
    <input type="submit" class="btn btn-success" value="<?php echo $params->get('button'); ?>" />
    </div>
    </div>
</form>