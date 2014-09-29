<?php
/**
 * @version     0.2.0
 * @package     com_wbty_payments
 * @copyright   Copyright (C) 2012-2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Webity <info@makethewebwork.com> - http://www.makethewebwork.com
 */

defined('_JEXEC') or die;

jimport('wbty_components.models.wbtymodellist');

/**
 * Methods supporting a list of Wbty_payments records.
 */
class Wbty_paymentsModelorder_items extends WbtyModelList
{

    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'ordering', 'a.ordering',
                'state', 'a.state',
                			'price', 'a.price',
								'item_name', 'a.item_name',
								'item_desc', 'a.item_desc',
								'item_id', 'a.item_id',
								'callback_file', 'a.callback_file',
								'callback_function', 'a.callback_function',
								'callback_id', 'a.callback_id',
								'redirect_url', 'a.redirect_url',
								'redirect_text', 'a.redirect_text',
								'recurring', 'a.recurring',
					

            );
        }

        parent::__construct($config);
    }

	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');
		
		$this->order_id=JRequest::getVar('order_id',0,'get');
		
		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $app->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_wbty_payments');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.price', 'asc');
	}

	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id.= ':' . $this->getState('filter.search');
		$id.= ':' . $this->getState('filter.state');

		return parent::getStoreId($id);
	}

	protected function getListQuery()
	{
		// Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.*'
			)
		);
		$query->from('`#__wbty_payments_order_items` AS a');


        // Join over the users for the checked out user.
        $query->select('uc.name AS editor');
        $query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');
        
		
        

        // Filter by published state
        $published = $this->getState('filter.state');
        if (is_numeric($published)) {
            $query->where('a.state = '.(int) $published);
        } else if ($published === '') {
            $query->where('(a.state IN (0, 1))');
        }
        
        

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = '.(int) substr($search, 3));
			} else {
				$search = $db->Quote('%'.$db->escape($search, true).'%');
                $query->where('( a.price LIKE '.$search.' )');
			}
		}
		
		if ($this->order_id) {
			$query->where('a.order_id = '.(int)$this->order_id);
		}

		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering');
		$orderDirn	= $this->state->get('list.direction');
        if ($orderCol && $orderDirn) {
		    $query->order($db->escape($orderCol.' '.$orderDirn));
        }

		return $query;
	}
}
