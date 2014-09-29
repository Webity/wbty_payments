<?php
/**
 * @package    WBTY Payments
 *
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

require_once(dirname(__FILE__) .DS. "WBTYPaymentsGatewayModel.php");

class WBTYPaymentsAuthorize extends WBTYPaymentsGatewayModel {
	
	function __construct($properties = null) {
		
		// Call the function to get the merchant and post data
		$this->info = parent::getGateway();
		
		parent::__construct($properties);
	}
	
	
	function process_cc() {
		
		// By default, this sample code is designed to post to our test server for
		// developer accounts: https://test.authorize.net/gateway/transact.dll
		// for real accounts (even in test mode), please make sure that you are
		// posting to: https://secure.authorize.net/gateway/transact.dll
		if ($this->info['merchant']['type'] == 0) {
			$post_url = "https://test.authorize.net/gateway/transact.dll";
		} else {
			$post_url = "https://secure.authorize.net/gateway/transact.dll";
		}
		
		$post_values = array(
			
			// the API Login ID and Transaction Key must be replaced with valid values
			"x_login"			=> $this->info['merchant']['api_login_id'],
			"x_tran_key"		=> $this->info['merchant']['transaction_key'],
		
			"x_version"			=> "3.1",
			"x_delim_data"		=> "TRUE",
			"x_delim_char"		=> "|",
			"x_relay_response"	=> "FALSE",
			
			"x_test_request" 	=> ($this->info['merchant']['type'] == 0 ? "TRUE" : ''),
		
			"x_type"			=> "AUTH_CAPTURE",
			"x_method"			=> "CC",
			"x_card_num"		=> $this->info['post']['creditCardNumber'],
			"x_exp_date"		=> (strlen($this->info['post']['expDateMonth']) == 1 ? '0' : '').$this->info['post']['expDateMonth'].$this->info['post']['expDateYear'],
		
			"x_amount"			=> $this->info['post']['amount'],
			//"x_description"		=> "Sample Transaction",
		
			"x_first_name"		=> $this->info['post']['firstName'],
			"x_last_name"		=> $this->info['post']['lastName'],
			"x_address"			=> $this->info['post']['address1'],
			"x_city"			=> $this->info['post']['city'],
			"x_state"			=> $this->info['post']['state'],
			"x_zip"				=> $this->info['post']['zip'],
			"x_country"			=> $this->info['post']['country']
			// Additional fields can be added here as outlined in the AIM integration
			// guide at: http://developer.authorize.net
			// PDF at http://www.authorize.net/support/AIM_guide.pdf
		);
		
		// If the user has entered a different shipping address make sure to include
		// that information in the post values
		if ($this->info['post']['shipping_different'] == 1) {
			$shipping_values = array(
				'x_ship_to_first_name' => $this->info['post']['firstName'],
				'x_ship_to_last_name' => $this->info['post']['lastName'],
				'x_ship_to_address' => $this->info['post']['shipping_address1'],
				'x_ship_to_city' => $this->info['post']['shipping_city'],
				'x_ship_to_state' => $this->info['post']['shipping_state'],
				'x_ship_to_zip' => $this->info['post']['shipping_zip'],
				'x_ship_to_country' => $this->info['post']['shipping_country']
			);
			
			$post_values = array_merge($post_values, $shipping_values);
		}
	
		// This section takes the input fields and converts them to the proper format
		// for an http post.  For example: "x_login=username&x_tran_key=a1B2c3D4"
		$post_string = "";
		foreach( $post_values as $key => $value )
			{ $post_string .= "$key=" . urlencode( $value ) . "&"; }
		$post_string = rtrim( $post_string, "& " );
		
		// submit the post, and record the response.
		// If you receive an error, you may want to ensure that you have the curl
		// library enabled in your php configuration
		$request = curl_init($post_url); // initiate curl object
			curl_setopt($request, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response
			curl_setopt($request, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
			curl_setopt($request, CURLOPT_POSTFIELDS, $post_string); // use HTTP POST to send form data
			curl_setopt($request, CURLOPT_SSL_VERIFYPEER, FALSE); // uncomment this line if you get no gateway response.
			$post_response = curl_exec($request); // execute curl post and store results in $post_response
			// additional options may be required depending upon your server configuration
			// you can find documentation on curl options at http://www.php.net/curl_setopt
		curl_close ($request); // close curl object
		
		// This line takes the response and breaks it into an array using the specified delimiting character
		$response_array = explode($post_values["x_delim_char"],$post_response);
		
		if ($response_array[0] == 2 || $response_array[0] == 3) {
			$this->APIerror($response_array);
			return false;
		}
	
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