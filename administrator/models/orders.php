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
class Wbty_paymentsModelorders extends WbtyModelList
{

    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'ordering', 'a.ordering',
                'state', 'a.state',
    			'user_id', 'a.user_id',
				'paid', 'a.paid',
				'gateway', 'a.gateway',
				'total_amount', 'a.total_amount',
				'order_date', 'a.order_date',
				'coupon', 'coupons_name',
				'coupon_amount', 'a.coupon_amount',


            );
        }

        parent::__construct($config);
    }

	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');



		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $app->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_wbty_payments');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.order_date', 'desc');
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
		$query->from('`#__wbty_payments_orders` AS a');


        // Join over the users for the checked out user.
        $query->select('uc.name AS editor');
        $query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');


		$query->select('gateways1432.name as gateways_name');
		$query->join('LEFT', '#__wbty_payments_gateways as gateways1432 ON a.gateway=gateways1432.id');
		$query->select('coupons1445.name as coupons_name');
		$query->join('LEFT', '#__wbty_payments_coupons as coupons1445 ON a.coupon=coupons1445.id');
		$query->select('( SELECT COUNT(id) as order_items FROM #__wbty_payments_order_items WHERE order_id = a.id AND state=1) as order_items');


        $query->join('LEFT', '#__wbty_payments_addresses as addy ON a.shipping_address = addy.id');
        $query->select('addy.address_state');


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
                $query->where('( a.user_id LIKE '.$search.' )');
			}
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
