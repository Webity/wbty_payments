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
 * coupon controller class.
 */
class Wbty_paymentsControllerShip extends WbtyControllerForm
{
	protected $view_list = 'shipping';
    protected $view_form = 'ship';
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



}
