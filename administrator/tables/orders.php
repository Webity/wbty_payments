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
jimport('wbty_components.tables.wbtytable');

/**
 * order Table class
 */
class Wbty_paymentsTableorders extends WbtyTable
{
	
	public function __construct(&$db)
	{
		parent::__construct('#__wbty_payments_orders', 'id', $db);
	}

}
