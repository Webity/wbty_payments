<?php
/**
 * @package    WBTY Payments
 *
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

require_once(dirname(__FILE__) ."/WBTYPaymentsGatewayModel.php");

class WBTYPaymentsStripe extends WBTYPaymentsGatewayModel {
	
	function __construct($properties = null) {
		
		// Call the function to get the merchant and post data
		$this->info = parent::getGateway();
		
		parent::__construct($properties);
	}
	
	
	function process_cc() {

		$db = JFactory::getDbo();
		$session = JFactory::getSession();
		// Get the order information based on the session _order_id
		$db->setQuery('SELECT * FROM #__wbty_payments_orders WHERE id='.$session->get('wbtypayments.order_id'));
		$order = $db->loadObject();
		
		// Import the Stripe library and set the API key to test or live
		jimport('stripe.Stripe');

		if ($this->info['merchant']['type'] == 0) {
			Stripe::setApiKey($this->info['merchant']['test_secret_key']); // Test secret key
		} else {
			Stripe::setApiKey($this->info['merchant']['live_secret_key']); // Live secret key
		}
		
		// Get the credit card token
		$token = $this->info['post']['stripeToken'];
		$config =& JFactory::getConfig();

		try {
			// Store the customer details first
			$customer = Stripe_Customer::create(array(
				"email" => 			$this->info['post']['email'],
				"description" => 	"Customer for " . $config->get('sitename'),
			  	"card" => $token, // obtained with Stripe.js
				'metadata' => 		array(
										"firstName" => $this->info['post']['firstName'],
										"lastName" 	=> $this->info['post']['lastName'],
										"address" 	=> $this->info['post']['shipping_address1'],
										"city" 		=> $this->info['post']['shipping_city'],
										"state" 	=> $this->info['post']['shipping_state'],
										"zip" 		=> $this->info['post']['shipping_zip'],
										"country" 	=> $this->info['post']['shipping_country']
									)
			));
			
			// Then charge them
			Stripe_Charge::create(array(
			  "amount" => $this->info['post']['amount']*100,
			  "currency" => "usd",
			  "customer" => $customer->id,
			  "description" => "Charge on site: " . $config->get('sitename')
			));
		} catch(Stripe_CardError $e) {
			// The card has been declined
			if ($message = $e->getMessage()) {
				JFactory::getApplication()->enqueueMessage($message, 'error');
			}
			return false;
		}
		
		
		/* TODO: Check for errors
		
		try {
		  // Use Stripe's bindings...
		} catch(Stripe_CardError $e) {
		  // Since it's a decline, Stripe_CardError will be caught
		  $body = $e->getJsonBody();
		  $err  = $body['error'];
		
		  print('Status is:' . $e->getHttpStatus() . "\n");
		  print('Type is:' . $err['type'] . "\n");
		  print('Code is:' . $err['code'] . "\n");
		  // param is '' in this case
		  print('Param is:' . $err['param'] . "\n");
		  print('Message is:' . $err['message'] . "\n");
		} catch (Stripe_InvalidRequestError $e) {
		  // Invalid parameters were supplied to Stripe's API
		} catch (Stripe_AuthenticationError $e) {
		  // Authentication with Stripe's API failed
		  // (maybe you changed API keys recently)
		} catch (Stripe_ApiConnectionError $e) {
		  // Network communication with Stripe failed
		} catch (Stripe_Error $e) {
		  // Display a very generic error to the user, and maybe send
		  // yourself an email
		} catch (Exception $e) {
		  // Something else happened, completely unrelated to Stripe
		}
		
		$this->APIerror($response_array);
		return false;
		}*/

		return true;
	}
	
	
	function APIerror($data) {
		
		// Initialize database
		$db = JFactory::getDBO();
		
		// Get order_id from session data	
		$session = JFactory::getSession();
		$order_id = $session->get('wbtypayments.order_id');
		
		// Create a new row in the errors table with this order ID
		$query = "INSERT INTO #__wbty_payments_errors (`number`,`message`,`order_id`) VALUES ('".$data[0]."','".$data[3]."','$order_id')";
		$db->setQuery($query);
		$db->query();
		
		$error_id = $db->insertid();
		
		$extra_errors = array('Response reason code' => $data[2]);
	
		foreach($extra_errors as $key => $value) {
			$query = "INSERT INTO #__wbty_payments_error_extra_items (`error_id`,`name`,`value`) VALUES ('".$error_id."','".$key."','".$value."')";
			$db->setQuery($query);
			$db->query();
		}
	}
	
	function setPaid() {
		parent::setPaid();
	}
	
}