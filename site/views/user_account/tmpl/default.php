<?php
/**
 * @version     1.0.0
 * @package     com_job_board
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Fritsch Services <david@makethewebwork.us> - http://www.makethewebwork.us
 */

// no direct access
defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
// Import CSS
?>
<div class="row-fluid">
    <div id="login" class="span6">
    <?php
    require_once(JPATH_COMPONENT . '/' . "helpers" . '/' . "users.php");
    $login = JHtmlComUsers::buildUserLogin();
    $form = JHtmlComUsers::defaultLogin($login, 'com_wbty_payments', 'login');
    
	 
	$search_array = array('<legend',
						  'type="submit"',
						  '</form>'
						  );
	$replace_array = array('<legend class="jobs-header"',
						   'type="submit" class="btn btn-primary"', 
						   '</form>'
						   );
	
    echo str_replace($search_array, $replace_array,$form);
    ?>
    </div>
    
    <div id="signup" class="span6">
    <?php
    require_once(JPATH_COMPONENT . '/' . "helpers" . '/' . "users.php");
    $registration = JHtmlComUsers::buildUserForm();
    $form = JHtmlComUsers::defaultTemplate($registration, 'com_wbty_payments', 'register');
    
	$search_array = array('<legend',
						  'type="submit"',
						  'title="Cancel"',
						  '</form>'
						  );
	$replace_array = array('<legend class="jobs-header"',
						   'type="submit" class="btn btn-primary"', 
						   'class="btn btn-error" title="Cancel"',
						   '</form>'
						   );
	
    echo str_replace($search_array, $replace_array, $form);
    ?>
    </div>
</div>