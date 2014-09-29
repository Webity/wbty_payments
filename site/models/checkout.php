<?php
/**
 * @version     1.0.0
 * @package     com_wbty_payments
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Webity <david@makethewebwork.com> - http://www.makethewebwork.com/
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');

/**
 * Wbtypayments model.
 */
class Wbty_paymentsModelCheckout extends JModelForm
{

	function __construct() {

		$session = JFactory::getSession(); // load Joomla's session handler to set or retrieve session data. Session data will persist between pages
		$app =& JFactory::getApplication();

		if ($this->_order_id = JFactory::getApplication()->input->get('order_id',0)) { // check if there is a order_id variable set in either GET or POST data, if so set it within the controller
			$session->set('wbtypayments.order_id',$this->_order_id);	// if a order_id variable is set, save it to the session
		} else {
			$this->_order_id = $session->get('wbtypayments.order_id'); // if order_id was not set, load previous order_id from the session.
		}

		if (!$this->_order_id) {
			$app->redirect(JRoute::_('index.php?option=com_wbty_payments&view=cart'), 'No order information was found. Can not checkout.');
		}

        parent::__construct();
    }

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication('site');

		// Load state from the request.
		$pk = JRequest::getInt('id');
		$this->setState('gateway.id', $pk);

		$offset = JRequest::getUInt('limitstart');
		$this->setState('list.offset', $offset);

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);

		// TODO: Tune these values based on other permissions.
		$user		= JFactory::getUser();
		if ((!$user->authorise('core.edit.state', 'com_wbty_payments')) &&  (!$user->authorise('core.edit', 'com_wbty_payments'))){
			$this->setState('filter.published', 1);
			$this->setState('filter.archived', 2);
		}
	}

	/**
	 * Method to get a single record.
	 *
	 * @param	integer	The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 * @since	1.6
	 */
	public function &getItem($pk = null)
	{
		// Initialise variables.
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('gateway.id');

		if ($this->_item === null) {
			$this->_item = array();
		}

		if (!isset($this->_item[$pk])) {

                        $db = $this->getDbo();
                        $query = $db->getQuery(true);

                        $query->select($this->getState(
                                'item.select', 'a.*'
                                )
                        );
                        $query->from('#__wbty_payments_gateways AS a');

                        $query->where('a.id = '. (int) $pk);

                        // Filter by published state.
                        $published = $this->getState('filter.published');
                        $archived = $this->getState('filter.archived');

                        if (is_numeric($published)) {
                                $query->where('(a.state = ' . (int) $published . ' OR a.state =' . (int) $archived . ')');
                        }

                        $db->setQuery($query);

                        $data = $db->loadObject();

                        if ($error = $db->getErrorMsg()) {
                                JError::raiseError(404, $error);
                                return false;
                        }

                        $this->_item[$pk] = $data;

		}

		return $this->_item[$pk];
	}

	protected function loadFormData() {
		$session = JFactory::getSession();

		return $session->get('userData', array());
	}

    function getGatewayDetails(){
		// Initialize database
		$db = JFactory::getDbo();

		if (!$this->_method) {
			if (!$this->getMethod()) {
				return array();
			}
		}

		// Get merchant details from databse based on their selected gateway
		$query = 'SELECT gf.field_name, fv.value
							FROM #__wbty_payments_gateway_fields AS gf
							INNER JOIN #__wbty_payments_gateway_field_values AS fv ON gf.id=fv.gateway_link_id
							WHERE gf.gateway_id = '.$this->_method->id .'';
		//echo str_replace('#_', 'vtc', $query);
		$db->setQuery($query);
		$merchantinfo = $db->loadAssocList('field_name', 'value');

		return $merchantinfo;
	}

	function getOrder() {
		// Get the _order_id that should be set in the session.
		$session = JFactory::getSession();
		$order_id = $session->get('wbtypayments.order_id');

		// Initialize database
		$db = JFactory::getDBO();

		$query = "SELECT oi.*, o.total_amount FROM #__wbty_payments_order_items AS oi LEFT JOIN #__wbty_payments_orders AS o ON oi.order_id=o.id WHERE order_id=$order_id";
		$db->setQuery($query);
		$result = $db->loadAssocList();

		return $result;
	}

	function getMethod() {
		if (isset($this->_method) && $this->_method) {
			return $this->_method;
		}

		// Initialize database
		$db = JFactory::getDBO();

		$jform = JFactory::getApplication()->input->get('jform', array(), 'ARRAY');
		$method = $jform['method'];

		if ($method) {
			$query = "SELECT g.* FROM #__wbty_payments_gateways AS g WHERE g.state=1 AND g.id=".$method." LIMIT 1";
			$db->setQuery($query);
			$method = $db->loadObject();
		}

		if (!$method) {
			$query = "SELECT g.* FROM #__wbty_payments_gateways AS g WHERE g.state=1 AND g.default_gateway=1 LIMIT 1";
			$db->setQuery($query);
			$method = $db->loadObject();
		}
		return $this->_method = $method;
	}

	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app	= JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_wbty_payments.checkout', 'checkout', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}

		return $form;
	}

}
