<?php
/**
 * @version     1.0.0
 * @package     com_wbty_payments
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Webity <david@makethewebwork.com> - http://www.makethewebwork.com/
 */

defined('_JEXEC') or die;

// Include dependancies

$jversion = new JVersion();
$above3 = version_compare($jversion->getShortVersion(), '3.0', 'ge');

JHtml::_('stylesheet', 'wbty_payments/wbty_payments.css', false, true);
if ($above3) {
	JHtml::_('bootstrap.framework');
	JHTML::stylesheet('wbty_components/ui-lightness/jquery-ui-1.10.3.custom.min.css', false, true);
	JHTML::script('wbty_components/jquery-ui-1.10.3.custom.min.js', false, true);
	if (JFactory::getApplication()->isAdmin()) {}
} else {
	JHTML::stylesheet('wbty_components/ui-lightness/jquery-ui-1.10.3.custom.min.css', false, true);
	JHTML::stylesheet('wbty_components/bootstrap.min.css', false, true);
	JHTML::stylesheet('wbty_components/font-awesome.min.css', false, true);
	JHTML::script('wbty_components/jquery-1.10.2.min.js', false, true);
	JHTML::script('wbty_components/jquery-ui-1.10.3.custom.min.js', false, true);
	JHTML::script('wbty_components/bootstrap.min.js', false, true);
}

// Execute the task.
$controller	= JControllerLegacy::getInstance('Wbty_payments');
$controller->execute(JFactory::getApplication()->input->get('task',''));
$controller->redirect();
