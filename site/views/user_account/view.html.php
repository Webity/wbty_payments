<?php
/**
 * @version     1.0.0
 * @package     com_job_board
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Fritsch Services <david@makethewebwork.us> - http://www.makethewebwork.us
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View to edit
 */
class Wbty_paymentsViewUser_account extends JViewLegacy
{
	protected $state;
	protected $item;
	protected $form;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		// If the user ID is not set, then set it to the logged-in user's
		
		//$this->user = JFactory::getUser();
		//$id = JFactory::getApplication()->input->get('id');
		//$this->isMe = ($this->user->id === $id);

		
		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		parent::display($tpl);
	}
}
