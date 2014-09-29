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
class Wbty_paymentsModelgateway extends WbtyModelAdmin
{
	protected $text_prefix = 'com_wbty_payments';
	protected $com_name = 'wbty_payments';
	protected $list_name = 'gateways';

	public function getTable($type = 'gateways', $prefix = 'Wbty_paymentsTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true, $control='jform', $key=0)
	{
		// Initialise variables.
		$app	= JFactory::getApplication();
		
		// Get the form.
		$form = $this->loadForm('com_wbty_payments.gateway.'.$control.'.'.$key, 'gateway', array('control' => $control, 'load_data' => $loadData, 'key'=>$key));
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
		$data = JFactory::getApplication()->getUserState('com_wbty_payments.edit.gateway.data', array());

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
		if ($item['gateway'] = parent::getItem($pk)) {

			//Do any procesing on fields here if needed
			
			
		}

		return $item;
	}
	
	public function loadGatewayFields($id = 0)
	{
		
		// Initialize the database
		$db = JFactory::getDbo();
		
		// Select the gateway that matches $id and all of the fields that belong to that gateway
		$query = "SELECT f.field_name, fv.value, f.id, f.gateway_id
					FROM #__wbty_payments_gateway_fields AS f
					LEFT OUTER JOIN #__wbty_payments_gateway_field_values AS fv ON fv.gateway_link_id=f.id
					WHERE f.gateway_id=$id					
					";
		$db->setQuery($query);
		$fields = $db->loadAssocList();

		return $fields;
	}

	protected function prepareTable(&$table)
	{
		$user =& JFactory::getUser();
		
		$db = JFactory::getDbo();
		// Get POST data
		$this->post = JFactory::getApplication()->input->get('jform', array(), 'ARRAY');
		$gate_values = isset($this->post['gate_values']) && is_array($this->post['gate_values']) ? $this->post['gate_values'] : array();
		
		// Get data stored in database
		$db->setQuery('SELECT * FROM #__wbty_payments_gateway_field_values');
		$gate_values_db = $db->loadAssocList();
		
		// Prepare gateway field values
		$this->values_update = array();
		$this->values_insert = array();
		
		// Compare against database for which values to insert or update		
		foreach ($gate_values as $key => $gv) {
			$match = false;
			foreach ($gate_values_db as $gvb) {
				if ($gvb['gateway_link_id'] == $key) {
					$match=true;
					$this->values_update[] = array($key => $gv[0]);
					break 1;
				}
			}
			if ($match==false) {
				$this->values_insert[] = array($key => $gv[0]);
			}
		}
		

		parent::prepareTable($table);

		if ($table->default_gateway) {
			$db->setQuery('UPDATE `#__wbty_payments_gateways` SET default_gateway = 0');
			$db->execute();
		}
	}
	
	function save($data) {
		if (!parent::save($data)) {
			return false;
		}
		
		// manage link
		// Update the gateway field values
		if (isset($this->values_update) && count($this->values_update) > 0) {
			foreach ($this->values_update as $key => $gv) {
			$id = key($gv);
				$query = '
						UPDATE #__wbty_payments_gateway_field_values
						SET value="'.$gv[$id].'" WHERE gateway_link_id="'.$id.'"
						';
				$this->table_db->setQuery($query);
				$this->table_db->query();
			};
		}
		
		// Update the gateway field values
		if (isset($this->values_insert) && count($this->values_insert) > 0) {
			foreach ($this->values_insert as $key => $gv) {
			$id = key($gv);
				$query = '
						INSERT INTO #__wbty_payments_gateway_field_values
						SET gateway_link_id="'.$id.'", value="'.$gv[$id].'"
						';
				$this->table_db->setQuery($query);
				$this->table_db->query();
			};
		}
		
		//$gateway_field = JRequest::getVar('gateway_field', array(), 'post', 'ARRAY');
		//$this->save_sub('gateway_field', $gateway_field, 'gateway_id');
		
		return $this->table_id;
	}
}