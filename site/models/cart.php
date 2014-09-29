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

jimport('joomla.application.component.modelitem');

/**
 * Wbtypayments model.
 */
class Wbty_paymentsModelCart extends JModelItem
{
	
	
	function __construct() {
		
		$session = JFactory::getSession(); // load Joomla's session handler to set or retrieve session data. Session data will persist between pages
		
		if ($this->_order_id = JFactory::getApplication()->input->get('order_id',0)) { // check if there is a order_id variable set in either GET or POST data, if so set it within the controller
			$session->set('wbtypayments.order_id',$this->_order_id);	// if a order_id variable is set, save it to the session
		} else {
			$this->_order_id = $session->get('wbtypayments.order_id'); // if order_id was not set, load previous order_id from the session.
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

    function getGatewayDetails(){
		// Initialize database
		$db = JFactory::getDbo();
		
		// Get post details on item to be purchased
		$post = JFactory::getApplication()->input->get('jform', array(), 'ARRAY');
		
		// Get merchant details from databse based on their selected gateway
		$db->setQuery('SELECT gf.field_name, fv.value, gf.gateway_id
							FROM #__wbty_payments_gateway_fields AS gf
							INNER JOIN #__wbty_payments_gateway_field_values AS fv ON gf.gateway_id=fv.gateway_link_id
							');
		$merchantinfo_db = $db->loadAssocList();
		
		// Cycle through the merchant information and set each field name as the key for its respective value
		$merchantinfo = array();
		foreach ($merchantinfo_db as $mi) {
			$mi['field_name'] = str_replace(" ", "_", strtolower($mi['field_name']));
			$array = array($mi['field_name'] => $mi['value'], 'gateway_id' => $mi['gateway_id']);
			$merchantinfo = array_merge((array)$merchantinfo, (array)$array);
		}
		
		$data['post'] = $post;
		$data['merchant'] = $merchantinfo;
		
		return $data;
	}
	
	function getOrder() {
		// Get the _order_id that should be set in the session.
		$session = JFactory::getSession();
		$order_id = $session->get('wbtypayments.order_id');
		
		// Initialize database
		$db = JFactory::getDBO();
		
		$query = "SELECT oi.*, o.total_amount, o.user_id FROM #__wbty_payments_order_items AS oi LEFT JOIN #__wbty_payments_orders AS o ON oi.order_id=o.id WHERE order_id=$order_id";
		$db->setQuery($query);
		$result = $db->loadAssocList();
		
		return $result;
	}
	
	function getMethods($default = false) {
		// Initialize database
		$db = JFactory::getDBO();
		
		$query = "SELECT g.* FROM #__wbty_payments_gateways AS g WHERE g.state=1";
		if ($default) {
			$query .= " AND default_gateway=1";
		}
		$db->setQuery($query);
		$result = $db->loadAssocList();
		
		return $result;
	}
	
	function updateUser() {
		$user =& JFactory::getUser();
		
		$order = $this->getOrder();
		
		if ($order[0]['user_id'] != $user->id) {
			$session = JFactory::getSession();
			$order_id = $session->get('wbtypayments.order_id');
			
			$query = "UPDATE #__wbty_payments_orders SET user_id=".(int)$user->id." WHERE id=".$order_id;
			$this->_db->setQuery($query)->query();
		}
		return true;
	}
	
	public function getSkipCart() {
		$app =& JFactory::getApplication();
		$params = $app->getParams('com_wbty_payments');
		if ($params->get('skip_cart')) {
			// check for a default method before redirecting, since a default method will need to be selected for this to work.
			$method = $this->getMethods(true);
			if ($method) {
				$app->redirect(JRoute::_('index.php?option=com_wbty_payments&view=checkout'));
			}
		}
		return;
	}

}