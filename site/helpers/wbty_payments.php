<?php
/**
 * @version     1.0.0
 * @package     com_wbty_payments
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Webity <david@makethewebwork.com> - http://www.makethewebwork.com/
 */

abstract class Wbty_paymentsHelper
{
	
	public function accessCheck($viewname = 'cart') {
		$user =& JFactory::getUser();
		
		if ($user->guest) {
			
			$app =& JFactory::getApplication();
			
			// check first if form has been submitted to register or login
			if (JFactory::getApplication()->input->get('username')) {
				require_once(JPATH_COMPONENT . '/' . "helpers" . '/' . "users.php");
				
				if (JFactory::getApplication()->input->get('email1')) {
					// create user
					if (!JHtmlComUsers::saveUserForm()) {
						JError::raiseWarning( 100, 'User could not be created.' );
					}
					
					// check for redirect data
					if ($redirect = JFactory::getApplication()->input->get('redirect','')) {
						$app->redirect(JRoute::_($redirect, false));
						return false;
					}
				} else {
					// log user in
					if (JHtmlComUsers::processLogin() !== true) {
						JError::raiseWarning( 100, 'Login failed.' );
					}
					
				}
			}
			
			// recheck user
			$user =& JFactory::getUser();
			if ($user->guest) {
				// user needs to be logged in
				$app->enqueueMessage('Please login or create an account before making a purchase.');
				$viewname = 'user_account';
			}
		}
		return $viewname;
	}

	public function checkSslWww() {
		$app = JFactory::getApplication();
		$params = $app->getParams('com_wbty_payments');
		$force_ssl = $params->get('force_ssl', 0);
		$force_www = $params->get('force_www', 0);

		$redirect = false;

	    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
	    $www = (strpos($host, 'www.') === 0);

	    if ($force_www) {
	    	if ($force_www == 1 && !$www) {
	    		$redirect = true;
	    		$host = 'www.' . $host;
	    	} elseif ($force_www == 2 && $www) {
	    		$redirect = true;
	    		$host = substr($host, 4);
	    	}
	    }

		$ssl = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? true :false;
	    $sp = strtolower($_SERVER['SERVER_PROTOCOL']);
    	$protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '') . '://';

		if ($force_ssl && !$ssl) {
			$redirect = true;
			$protocol = 'https://';
		}

		if ($redirect) {
			$app->redirect($protocol . $host . $_SERVER['REQUEST_URI']);
		}

		return true;
	}

}

