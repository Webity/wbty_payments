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
class Wbty_paymentsViewCheckout extends JViewLegacy {

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
		
        $this->state 	= $this->get('State');
		
		$this->method	= $this->get('Method');
		$this->form		= $this->get('Form');
		
		$this->params 	= $app->getParams('com_wbty_payments');
		
        $this->gateway 	= $this->get('GatewayDetails');
		$this->order	= $this->get('Order');
		
		if (file_exists(dirname(__FILE__).'/tmpl/default_'.$this->method->alias.'.php')) {
			$tpl = $this->method->alias;
		}
		
        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));
            return false;
        }

        parent::display($tpl);
    }
    
}