<?php
/**
 * @version     0.2.0
 * @package     com_wbty_payments
 * @copyright   Copyright (C) 2012-2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Webity <info@makethewebwork.com> - http://www.makethewebwork.com
 */

// No direct access
defined('_JEXEC') or die;

jimport('wbty_components.controllers.wbtycontrollerform');

/**
 * gateway_field controller class.
 */
class Wbty_paymentsControllerGateway_Field extends WbtyControllerForm
{
	protected $view_list = 'gateway_fields';
    protected $view_form = 'gateway_field';
    protected $com_name = 'wbty_payments';

    function __construct() {
        parent::__construct();
		
		$this->_model = $this->getModel();
    }
	
	function back() {
		$this->setRedirect(
			JRoute::_(
				'index.php?option=' . $this->option . '&view=' . $this->view_list
				. $this->getRedirectToListAppend(), false
			)
		);
	}
	
	
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{
		$append = parent::getRedirectToItemAppend($recordId);
		$append .= '&gateway_id=' . JRequest::getVar('gateway_id');

		return $append;
	}
	
	protected function getRedirectToListAppend()
	{
		$append = parent::getRedirectToListAppend();
		$append .= '&gateway_id=' . JRequest::getVar('gateway_id');

		return $append;
	}
	
}