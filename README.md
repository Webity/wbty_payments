Wbty Payments
===========

Component to handle payment processing for other components. It can also be used a stand-alone donation system.

Design Goals
-----------

Wbty Payments was designed to function as a stand-alone cart for other Joomla components. Instead of every component having to integrate its own payment processor, all of the payment functionality as well as the cart functionality is in one place.

Current Payment Processors Supported
-----------

* Authorize.net
* Paypal Standard
* Paypal Pro
* Amazon Simple Payments
* (Don't use Google Checkout)

Integrating into a component
----------

createOrder expects an array. At a minimum, 'amount', 'item_name', 'item_desc', 'item_id' are essential in setting up the order information. Optionally, callback and redirect information can be passed.

Callback information is used when the order is successfully completed. This can be used to update the component's expiration dates and activate items.

Redirect information is used to direct back to the component on successful completion of payment.

    jimport('wbtypayments.wbtypayments');

	$order_info = array(
					'amount' => $package->price,
					'item_name' => $package->name,
					'item_desc' => $package->description,
					'item_id' => $package->id,
					'callback_file' => JPATH_COMPONENT.'/helpers/helper.php',
					'callback_function' => 'ComponentHelper::confirmOrder',
					'callback_id' => $package->id,
					'redirect_url' => 'index.php?option=com_comname&task=task.edit&package_id='.$package->id,
					'redirect_text' => 'Complete Action for '.$package->name
				);

	if (WbtyPayments::createOrder($order_info)) {
		$url = WbtyPayments::getCheckoutUrl();
	} else {
		$app->enqueueMessage('Error Purchasing Package.');
		$url = 'index.php?option=com_comname&view=yourview';
	}

If using the included module, parameters are set in the module on the right side. Callback and redirect information can be added in the advanced section.
