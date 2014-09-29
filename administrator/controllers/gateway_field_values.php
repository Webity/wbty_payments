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
 * Gateway_Field_Values list controller class.
 */
class Wbty_paymentsControllerGateway_Field_Values extends WbtyControllerAdmin
{
	/**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function &getModel($name = 'gateway_field_value', $prefix = 'Wbty_paymentsModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
}