<?php
/**
 * @version     0.2.0
 * @package     com_wbty_payments
 * @copyright   Copyright (C) 2012-2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Webity <info@makethewebwork.com> - http://www.makethewebwork.com
 */

// No direct access.
defined('_JEXEC') or die;

jimport('wbty_components.controllers.wbtycontrolleradmin');

/**
 * Gateways list controller class.
 */
class Wbty_paymentsControllerGateways extends WbtyControllerAdmin
{
	/**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function &getModel($name = 'gateway', $prefix = 'Wbty_paymentsModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

	public function changeDefault() {
		$app = &JFactory::getApplication();
		// no matter what we are going to redirect here
		$redirecturl = 'index.php?option=com_wbty_payments&view=gateways';
		$gateway = $app->input->get('id');
		
		if (!$gateway) {
			JError::raiseWarning( 100, 'No gateway id set to change default gateway');
			$app->redirect($redirecturl);
			return false;
		}
		
		$db =& JFactory::getDBO();
		
		$query = "UPDATE #__wbty_payments_gateways SET default_gateway = 0";
		$db->setQuery($query);
		$result = $db->query();
		
		
		$query = "UPDATE #__wbty_payments_gateways SET default_gateway = 1 WHERE id=".$db->quote($gateway);
		$db->setQuery($query);
		$result = $db->query();
		
		if (!$result) {
			JError::raiseWarning( 103, 'Error setting gateway as default gateway' );
			$app->redirect($redirecturl);
			return false;
		}
		
		$app->enqueueMessage( 'Default table successfully updated' );
		$app->redirect($redirecturl);
		return true;
	}
}