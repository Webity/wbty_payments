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

class Wbty_paymentsController extends JControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param	boolean			$cachable	If true, the view output will be cached
	 * @param	array			$urlparams	An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return	JController		This object to support chaining.
	 * @since	1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		require_once JPATH_COMPONENT.'/helpers/wbty_payments.php';
		$app = JFactory::getApplication();

		// Load the submenu.
		Wbty_paymentsHelper::addSubmenu($app->input->get('view', 'controlpanel'));

		$view		= $app->input->get('view', 'controlpanel');
        $app->input->set('view', $view);

		parent::display();

		return $this;
	}
}
