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

jimport('wbty_components.models.wbtymodeladmin');

/**
 * Wbty_payments model.
 */
class Wbty_paymentsModelorder_item extends WbtyModelAdmin
{
	protected $text_prefix = 'com_wbty_payments';
	protected $com_name = 'wbty_payments';
	protected $list_name = 'order_items';

	public function getTable($type = 'order_items', $prefix = 'Wbty_paymentsTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true, $control='jform', $key=0)
	{
		// Initialise variables.
		$app	= JFactory::getApplication();
		
		// Get the form.
		$form = $this->loadForm('com_wbty_payments.order_item.'.$control.'.'.$key, 'order_item', array('control' => $control, 'load_data' => $loadData, 'key'=>$key));
		if (empty($form)) {
			return false;
		}

		return $form;
	}
	
	public function getItems($parent_id, $parent_key) {
		$query = $this->_db->getQuery(true);
		
		$query->select('id, state');
		$query->from($this->getTable()->getTableName());
		$query->where($parent_key . '=' . (int)$parent_id);
		$query->where($parent_key . '!= 0');
		$query->order('state DESC, ordering ASC');
		
		$data = $this->_db->setQuery($query)->loadObjectList();
		if (count($data)) {
			$this->getState();
			$key=0;
			foreach ($data as $key=>$d) {
				$this->data = null;
				$this->setState($this->getName() . '.id', $d->id);
				$return[$d->id] = $this->getForm(array(), true, 'jform', $d->id);
			}
		}
		
		return $return;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		if ($this->data) {
			return $this->data;
		}
		
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_wbty_payments.edit.order_item.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param	integer	The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 * @since	1.6
	 */
	public function getItem($pk = null)
	{
		if ($item['order_item'] = parent::getItem($pk)) {

			//Do any procesing on fields here if needed
			
			
		}

		return $item;
	}

	protected function prepareTable(&$table)
	{
		$user =& JFactory::getUser();
		
		if (JRequest::getVar('order_id')) {
	$table->order_id=JRequest::getVar('order_id');
}

		parent::prepareTable($table);
	}
	
	function save($data) {
		if (!parent::save($data)) {
			return false;
		}
		
		// manage link
		
		
		return $this->table_id;
	}
}