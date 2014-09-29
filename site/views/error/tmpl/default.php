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

<h2>Oops! Something went wrong with your purchase.</h2>

<?php 
if ($this->order_errors[0]['order_id']) {
	echo "<h3>Error Details: <br><span style='color:red;'>".$this->order_errors[0]['message']."<span></h3>";
} else {
	echo "<h3>".$this->order_errors['default_error'][0]."</h3>";
}
?>

<div class="fancybutton"><a href="<?php echo JRoute::_('index.php?option=com_wbty_payments&view=checkout&order_id='.$this->order_errors[0]['order_id'], false); ?>" class="btn btn-primary">Try purchase again.</a></div>

<?php
if ($this->order_errors[0]['order_id']) {
	echo "<hr>";
	echo "<h5>Additional Details</h5>";
	echo "<ul>";
	echo "<li>Order id: ".$this->order_errors[0]['order_id']."</li>";
	echo "<li>Error number: ".$this->order_errors[0]['number']."</li>";
	foreach ($this->order_errors as $error) {
		echo "<li>".$error['name'].": ".$error['value']."</li>";	
	}
	echo "</ul>";
}
?>