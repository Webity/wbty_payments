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
class Wbty_paymentsModelorder extends WbtyModelAdmin
{
	protected $text_prefix = 'com_wbty_payments';
	protected $com_name = 'wbty_payments';
	protected $list_name = 'orders';

	public function getTable($type = 'orders', $prefix = 'Wbty_paymentsTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true, $control='jform', $key=0)
	{
		// Initialise variables.
		$app	= JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_wbty_payments.order.'.$control.'.'.$key, 'order', array('control' => $control, 'load_data' => $loadData, 'key'=>$key));
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
		$data = JFactory::getApplication()->getUserState('com_wbty_payments.edit.order.data', array());

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
		if ($item['order'] = parent::getItem($pk)) {

			//Do any procesing on fields here if needed

				$db =& JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->from('#__wbty_payments_orders as a');

				$query->select('gateways.name as gateways_name');
				$query->join('LEFT', '#__wbty_payments_gateways as gateways ON a.gateway=gateways.id');
				$query->select('coupons.name as coupons_name');
				$query->join('LEFT', '#__wbty_payments_coupons as coupons ON a.coupon=coupons.id');

				$query->where('a.id='.(int)$item['order']->id);
				$items = $db->setQuery($query)->loadObject();
				if($items) {
					foreach($items as $key=>$value) {
						if ($value && $key) {
							$item->$key = $value;
						}
					}
				}

				$query->clear()
					->select('*')
					->from('#__wbty_payments_addresses')
					->where('id = ' . (int)$item['order']->billing_address);

				$item['order']->billing_address = $db->setQuery($query)->loadObject();

				$query->clear()
					->select('*')
					->from('#__wbty_payments_addresses')
					->where('id = ' . (int)$item['order']->shipping_address);

				$item['order']->shipping_address = $db->setQuery($query)->loadObject();

				$query->clear()
					->select('*')
					->from('#__wbty_payments_purchasers')
					->where('id = ' . (int)$item['order']->purchaser_id);

				$item['order']->purchaser = $db->setQuery($query)->loadObject();

				$query->clear()
					->select('*')
					->from('#__wbty_payments_order_items')
					->where('order_id = ' . (int)$item['order']->id);

				$item['order']->items = $db->setQuery($query)->loadObjectList();
		}

		return $item;
	}

	protected function prepareTable(&$table)
	{
		$user =& JFactory::getUser();



		parent::prepareTable($table);
	}

	function save($data) {
		if (!parent::save($data)) {
			return false;
		}

		// manage link

		//$order_item = JRequest::getVar('order_item', array(), 'post', 'ARRAY');
		//$this->save_sub('order_item', $order_item, 'order_id');

		return $this->table_id;
	}
}
