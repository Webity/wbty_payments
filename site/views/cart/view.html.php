<?php

/**
 * @version     1.0.0
 * @package     com_wbty_payments
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Webity <david@makethewebwork.com> - http://www.makethewebwork.com/
 */
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View to edit
 */
class Wbty_paymentsViewCart extends JViewLegacy {

    protected $state;
    protected $item;
    protected $params;

    /**
     * Display the view
     */
    public function display($tpl = null) {
        
		$app	= JFactory::getApplication();

        $this->loadHelper('wbty_payments');
        Wbty_paymentsHelper::checkSslWww();
        
		// this check allows the user to skip the cart summary. Redirect is handled in model.
		$this->get('SkipCart');
			
        $this->state 	= $this->get('State');
        $this->item 	= $this->get('Item');
        $this->params 	= $app->getParams('com_wbty_payments');
		
        $this->gateway 	= $this->get('GatewayDetails');
		$this->order	= $this->get('Order');
		$this->methods	= $this->get('Methods');
		
		$model =& $this->getModel();
		$model->updateUser();
		
        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));
            return false;
        }

        parent::display($tpl);
    }
    
}