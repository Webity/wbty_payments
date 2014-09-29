<?php
/**
 * @version     1.0.0
 * @package     com_wbty_payments
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Webity <david@makethewebwork.com> - http://www.makethewebwork.com/
 */

// no direct access
defined('_JEXEC') or die;

class SignatureUtils
{ 

    /**
     * Computes RFC 2104-compliant HMAC signature for request parameters
     * Implements AWS Signature, as per following spec:
     *
     * In Signature Version 2, string to sign is based on following:
     *
     *    1. The HTTP Request Method followed by an ASCII newline (%0A)
     *    2. The HTTP Host header in the form of lowercase host, followed by an ASCII newline.
     *    3. The URL encoded HTTP absolute path component of the URI
     *       (up to but not including the query string parameters);
     *       if this is empty use a forward '/'. This parameter is followed by an ASCII newline.
     *    4. The concatenation of all query string components (names and values)
     *       as UTF-8 characters which are URL encoded as per RFC 3986
     *       (hex characters MUST be uppercase), sorted using lexicographic byte ordering.
     *       Parameter names are separated from their values by the '=' character
     *       (ASCII character 61), even if the value is empty.
     *       Pairs of parameter and values are separated by the '&' character (ASCII code 38).
     *
     */
    /**
	* This function call appropriate functions for calculating signature
	* @param array $parameters request parameters
	* @param key - Secret key 
	* @param httpMethod - httpMethos used
	* @param host - Host 
	* @requestURi -  Path
		
     */		

    public static function signParameters(array $parameters, $key, $httpMethod, $host, $requestURI,$algorithm) {
        $stringToSign = null;
        $stringToSign = self::_calculateStringToSignV2($parameters, $httpMethod, $host, $requestURI);
        return self::_sign($stringToSign, $key, $algorithm);
    }

    /**
     * Calculate String to Sign for SignatureVersion 2
     * @param array $parameters request parameters
     * @return String to Sign
     */
    private static function _calculateStringToSignV2(array $parameters, $httpMethod, $hostHeader, $requestURI) {
        if ($httpMethod == null) {
        	throw new Exception("HttpMethod cannot be null");
        }
        $data = $httpMethod;
        $data .= "\n";
        
        if ($hostHeader == null) {
        	$hostHeader = "";
        } 
        $data .= $hostHeader;
        $data .= "\n";
        
        if (!isset ($requestURI)) {
        	$requestURI = "/";
        }
		$uriencoded = implode("/", array_map(array("SignatureUtils", "_urlencode"), explode("/", $requestURI)));
        $data .= $uriencoded;
        $data .= "\n";
        
        uksort($parameters, 'strcmp');
        $data .= self::_getParametersAsString($parameters);
        return $data;
    }

    private static function _urlencode($value) {
		return str_replace('%7E', '~', rawurlencode($value));
    }

    /**
     * Convert paremeters to Url encoded query string
     */
    public static function _getParametersAsString(array $parameters) {
        $queryParameters = array();
        foreach ($parameters as $key => $value) {
            $queryParameters[] = $key . '=' . self::_urlencode($value);
        }
        return implode('&', $queryParameters);
    }

    /**
     * Computes RFC 2104-compliant HMAC signature.
     */
    private static function _sign($data, $key, $algorithm) {
        if ($algorithm === 'HmacSHA1') {
            $hash = 'sha1';
        } else if ($algorithm === 'HmacSHA256') {
            $hash = 'sha256';
        } else {
            throw new Exception ("Non-supported signing method specified");
        }
        return base64_encode(
            hash_hmac($hash, $data, $key, true)
        );
    }
}
class SubscriptionButtonGenerator {
	const SIGNATURE_KEYNAME = "signature";
	const SIGNATURE_METHOD_KEYNAME = "signatureMethod";
	const SIGNATURE_VERSION_KEYNAME = "signatureVersion";
	const HMAC_SHA1_ALGORITHM = "HmacSHA1";
	const HMAC_SHA256_ALGORITHM = "HmacSHA256";
	const SIGNATURE_VERSION = "2";
    const COBRANDING_STYLE = "logo";

	private static $httpMethod = "POST";
        public static  $SANDBOX_END_POINT = "https://authorize.payments-sandbox.amazon.com/pba/paypipeline";
        public static  $SANDBOX_IMAGE_LOCATION="https://authorize.payments-sandbox.amazon.com/pba/images/SMSubscribeWithOutLogo.png";
        public static  $PROD_END_POINT = "https://authorize.payments.amazon.com/pba/paypipeline";
        public static  $PROD_IMAGE_LOCATION="https://authorize.payments.amazon.com/pba/images/SMSubscribeWithOutLogo.png";
	
	/**
         * Function creates a Map of key-value pairs for all valid values passed to the function 
         * @param accessKey - Put your Access Key here  
         * @param amount - Enter the amount you want to collect for the item
         * @param description - description - Enter a description of the item
         * @param referenceId - Optionally enter an ID that uniquely identifies this transaction for your records
         * @param abandonUrl - Optionally, enter the URL where senders should be redirected if they cancel their transaction
         * @param returnUrl - Optionally enter the URL where buyers should be redirected after they complete the transaction
         * @param immediateReturn - Optionally, enter "1" if you want to skip the final status page in Amazon Payments, 
         * @param processImmediate - Optionally, enter "1" if you want to settle the transaction immediately else "0". Default value is "1"
         * @param ipnUrl - Optionally, type the URL of your host page to which Amazon Payments should send the IPN transaction information.
         * @param collectShippingAddress - Optionally, enter "1" if you want Amazon Payments to return the buyer's shipping address as part of the transaction information.
         * @param signatureMethod - Valid values are  HmacSHA256 and HmacSHA1
	 * @param recurringFrequency - Enter the billing frequency
	 * @param subscriptionPeriod - Optionally, enter the subscription duration. By default subscription will be for an unlimited period.
	 * @param recurringStartDate - Optionally, enter the start date for the subscription. By default susbcription will be processed as soon as subscription id created
	 * @param promotionAmount - Optionally, enter the introductory subscription price.
	 * @param noOfPromotionTransactions - Required if for promotional period referenceId
         * @return - A map of key of key-value pair for all non null parameters
         * @throws Exception
         */

	public static function getSubscriptionParams($accessKey,$amount, $description, $referenceId, $immediateReturn,
			$returnUrl, $abandonUrl, $processImmediate, $ipnUrl, $collectShippingAddress,  
			$signatureMethod,$recurringFrequency,$subscriptionPeriod,$recurringStartDate,$promotionAmount,$noOfPromotionTransactions ) {
		$cobrandingStyle= self::COBRANDING_STYLE;
		$formHiddenInputs = array();
		if($accessKey!=null) $formHiddenInputs["accessKey"] = $accessKey;
		else throw new Exception("Accesskey is Required");
		if($amount!=null) $formHiddenInputs["amount"] = $amount;
		else throw new Exception("Amount is required");
		if($description!=null) $formHiddenInputs["description"] = $description;
		else throw new Exception("Description is required");
		if($recurringFrequency!=null) $formHiddenInputs["recurringFrequency"] = $recurringFrequency;
		else throw new Exception("RecurringFrequency is required");
		if($signatureMethod!=null) $formHiddenInputs[self::SIGNATURE_METHOD_KEYNAME] = $signatureMethod;
                else throw new Exception("Signature Method is required");		
		if ($referenceId != null) $formHiddenInputs["referenceId"] = $referenceId;
		if ($immediateReturn != null) $formHiddenInputs["immediateReturn"] = $immediateReturn;
		if ($returnUrl != null) $formHiddenInputs["returnUrl"] = $returnUrl;
		if ($abandonUrl != null) $formHiddenInputs["abandonUrl"] = $abandonUrl;
		if ($processImmediate != null) $formHiddenInputs["processImmediate"] = $processImmediate;
		if ($ipnUrl != null) $formHiddenInputs["ipnUrl"] = $ipnUrl;
		if ($cobrandingStyle != null) $formHiddenInputs["cobrandingStyle"] = $cobrandingStyle;
		if ($collectShippingAddress != null) $formHiddenInputs["collectShippingAddress"] = $collectShippingAddress;

		if ($subscriptionPeriod != null) $formHiddenInputs["subscriptionPeriod"]= $subscriptionPeriod;
		if ($recurringStartDate != null) $formHiddenInputs["recurringStartDate"]=$recurringStartDate;
		if ($promotionAmount != null) $formHiddenInputs["promotionAmount"]= $promotionAmount;
		if ($noOfPromotionTransactions != null) $formHiddenInputs["noOfPromotionTransactions"]= $noOfPromotionTransactions;
		
	
		$formHiddenInputs[self::SIGNATURE_VERSION_KEYNAME] = self::SIGNATURE_VERSION;
		return $formHiddenInputs;
	}
	 /**
         * Creates a form from the provided key-value pairs 
         * @param formHiddenInputs - A map of key of key-value pair for all non null parameters
         * @param serviceEndPoint - The Endpoint to be used based on environment selected
         * @param imageLocation - The imagelocation based on environment
         * @return - An html form created using the key-value pairs
         */
	public static function getSubscriptionForm(array $formHiddenInputs,$endPoint,$imageLocation) {

		$form = "";
		$form .=  "<form action=\""; 
		$form .= $endPoint;
		$form .= "\" id=\"amazonForm\" method=\"";
		$form .= self::$httpMethod . "\">\n";
		$form .= "<input type=\"image\" src=\"".$imageLocation."\" border=\"0\">\n";
		
		foreach ($formHiddenInputs  as $name => $value) {
			$form .= "<input type=\"hidden\" name=\"$name";  
			$form .= "\" value=\"$value";
			$form .= "\" >\n";
		}
		$form .= "</form>\n";
		return $form;
	}
	
	 /**
         * Function Generates the html form 
         * @param accessKey - Put your Access Key here  
         * @param secretKey - Put your secret Key here
         * @param amount - Enter the amount you want to collect for the ite
         * @param description - description - Enter a description of the item
         * @param referenceId - Optionally enter an ID that uniquely identifies this transaction for your records
         * @param abandonUrl - Optionally, enter the URL where senders should be redirected if they cancel their transaction
         * @param returnUrl - Optionally enter the URL where buyers should be redirected after they complete the transaction
         * @param immediateReturn - Optionally, enter "1" if you want to skip the final status page in Amazon Payments, 
         * @param processImmediate - Optionally, enter "1" if you want to settle the transaction immediately else "0". Default value is "1"
         * @param ipnUrl - Optionally, type the URL of your host page to which Amazon Payments should send the IPN transaction information.
         * @param collectShippingAddress - Optionally, enter "1" if you want Amazon Payments to return the buyer's shipping address as part of the transaction information
	 * @param signatureMethod - Valid values are  HmacSHA256 and HmacSHA1
	 * @param recurringFrequency - Enter the billing frequency
	 * @param subscriptionPeriod - Optionally, enter the subscription duration. By default subscription will be for an unlimited period.
	 * @param recurringStartDate - Optionally, enter the start date for the subscription. By default susbcription will be processed as soon as subscription id created
	 * @param promotionAmount - Optionally, enter the introductory subscription price.
	 * @param noOfPromotionTransactions - Required if for promotional period referenceId
         * @param environment - Sets the environment where your form will point to can be "sandbox" or "prod" 
         * @return - A map of key of key-value pair for all non null parameters
         * @throws Exception
         */

	 public static function GenerateForm($accessKey,$secretKey,$amount, $description, $referenceId, $immediateReturn,
                        $returnUrl, $abandonUrl, $processImmediate, $ipnUrl,$collectShippingAddress,
                        $signatureMethod,$recurringFrequency,$subscriptionPeriod,$recurringStartDate,$promotionAmount,$noOfPromotionTransactions ,
			$environment) {
		 if($environment=="prod"){
                                $endPoint = self::$PROD_END_POINT;
                                $imageLocation = self::$PROD_IMAGE_LOCATION;
                        }
                        else
                        {
                                $endPoint= self::$SANDBOX_END_POINT;
                                $imageLocation = self::$SANDBOX_IMAGE_LOCATION;
                        }

                $params = self::getSubscriptionParams($accessKey,$amount, $description, $referenceId, $immediateReturn,
                        $returnUrl, $abandonUrl, $processImmediate, $ipnUrl,$collectShippingAddress, $signatureMethod,$recurringFrequency,$subscriptionPeriod,$recurringStartDate,$promotionAmount,$noOfPromotionTransactions );

                $serviceEndPoint = parse_url($endPoint);
                $signature = SignatureUtils::signParameters($params, $secretKey,
                                self::$httpMethod, $serviceEndPoint['host'], $serviceEndPoint['path'],$signatureMethod);
                $params[self::SIGNATURE_KEYNAME] = $signature;
                $simplePayForm = self::getSubscriptionForm($params,$endPoint,$imageLocation);
                print $simplePayForm . "\n";
        }

	
}

class ButtonGenerator {
	const SIGNATURE_KEYNAME = "signature";
        const SIGNATURE_METHOD_KEYNAME = "signatureMethod";
        const SIGNATURE_VERSION_KEYNAME = "signatureVersion";
        const HMAC_SHA1_ALGORITHM = "HmacSHA1";
        const HMAC_SHA256_ALGORITHM = "HmacSHA256";
	const SIGNATURE_VERSION = "2";
        const COBRANDING_STYLE = "logo";
	private static $httpMethod = "POST";
        public static  $SANDBOX_END_POINT = "https://authorize.payments-sandbox.amazon.com/pba/paypipeline";
        public static  $SANDBOX_IMAGE_LOCATION="https://authorize.payments-sandbox.amazon.com/pba/images/SLDonationWithLogo.png";
        public static  $PROD_END_POINT = "https://authorize.payments.amazon.com/pba/paypipeline";
        public static  $PROD_IMAGE_LOCATION="https://authorize.payments.amazon.com/pba/images/SLDonationWithLogo.png";
	
	/**
         * Function creates a Map of key-value pairs for all valid values passed to the function 
         * @param accessKey - Put your Access Key here  
         * @param amount - Enter the amount you want to collect for the item
         * @param description - description - Enter a description of the item
         * @param referenceId - Optionally enter an ID that uniquely identifies this transaction for your records
         * @param abandonUrl - Optionally, enter the URL where senders should be redirected if they cancel their transaction
         * @param returnUrl - Optionally enter the URL where buyers should be redirected after they complete the transaction
         * @param immediateReturn - Optionally, enter "1" if you want to skip the final status page in Amazon Payments
         * @param processImmediate - Optionally, enter "1" if you want to settle the transaction immediately else "0". Default value is "1"
         * @param ipnUrl - Optionally, type the URL of your host page to which Amazon Payments should send the IPN transaction information.
         * @param collectShippingAddress - Optionally, enter "1" if you want Amazon Payments to return the buyer's shipping address as part of the transaction information.
         * @param signatureMethod -Valid values are  HmacSHA256 and HmacSHA1
	 * @param donationType - Optionally, enter the type of donation. Valid values are fixedAmount, minimumAmount and anyAmount. Default value is anyAmount
         * @return - A map of key of key-value pair for all non null parameters
         * @throws Exception
         */

	public static function getDonationParams($accessKey,$amount, $description, $referenceId, $immediateReturn,
			$returnUrl, $abandonUrl, $processImmediate, $ipnUrl, $collectShippingAddress,  
			$signatureMethod,$donationType) {
		$cobrandingStyle= self::COBRANDING_STYLE;
		
		$formHiddenInputs = array();
		if($accessKey!=null) $formHiddenInputs["accessKey"] = $accessKey;
		else throw new Exception("Accesskey is Required");
		if($description!=null) $formHiddenInputs["description"] = $description;
		else throw new Exception("Description is required");
   	        if($signatureMethod!=null) $formHiddenInputs[self::SIGNATURE_METHOD_KEYNAME] = $signatureMethod;
                else throw new Exception("Signature Method is required");
	
		if ($donationType == null) $donationType="anyAmount"; 
		if ($donationType == "minimumAmount"){
			$formHiddenInputs["minimumDonationAmount"]= $amount;
		}else if ($donationType == "fixedAmount"){
			$formHiddenInputs["amount"]= $amount;
		}
		$formHiddenInputs["donationType"]=$donationType;
		$formHiddenInputs["isdonationWidget"]="1";

		if ($referenceId != null) $formHiddenInputs["referenceId"] = $referenceId;
		if ($immediateReturn != null) $formHiddenInputs["immediateReturn"] = $immediateReturn;
		if ($returnUrl != null) $formHiddenInputs["returnUrl"] = $returnUrl;
		if ($abandonUrl != null) $formHiddenInputs["abandonUrl"] = $abandonUrl;
		if ($processImmediate != null) $formHiddenInputs["processImmediate"] = $processImmediate;
		if ($ipnUrl != null) $formHiddenInputs["ipnUrl"] = $ipnUrl;
		if ($cobrandingStyle != null) $formHiddenInputs["cobrandingStyle"] = $cobrandingStyle;
		if ($collectShippingAddress != null) $formHiddenInputs["collectShippingAddress"] = $collectShippingAddress;
	
		$formHiddenInputs[self::SIGNATURE_VERSION_KEYNAME] = self::SIGNATURE_VERSION;
		return $formHiddenInputs;
	}
	 /**
         * Creates a form from the provided key-value pairs 
         * @param formHiddenInputs - A map of key of key-value pair for all non null parameters
         * @param serviceEndPoint - The Endpoint to be used based on environment selected
         * @param imageLocation - The imagelocation based on environment
	 * @param donationType - Optionally, enter the type of donation. Valid values are fixedAmount, minimumAmount and anyAmount. Default value is anyAmount
         * @return - An html form created using the key-value pairs
         */
	public static function getDonationForm(array $formHiddenInputs,$endPoint,$imageLocation,$donationType) {

		$form = "";
		if($donationType!="fixedAmount"){

			$form.="<div style=\"width:20em;padding-left:10px;padding-top:10px;padding-right:10px;padding-bottom:10px;\">";
			$form.="<table class=\"table\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\"> <tr>";
		}
		$form .=  "<form action=\""; 
		$form .= $endPoint;
		$form .= "\" id=\"amazonForm\" method=\"";
		$form .= self::$httpMethod . "\">\n";
		
		foreach ($formHiddenInputs  as $name => $value) {
			$form .= "<input type=\"hidden\" name=\"$name";  
			$form .= "\" value=\"$value";
			$form .= "\" >\n";
		}
		if($donationType != "fixedAmount"){
			$form .= "<td>$&nbsp;<input type=\"text\" name=\"amount\" size=\"8\" value=\"" ;
			if($donationType == "minimumAmount")
				$form .= str_replace('USD ','',$formHiddenInputs["minimumDonationAmount"]);
			else 
				$form.="";	
			$form .=  "\"</td><td>&nbsp;</td><td><input type=\"image\" src=\"";
			$form .= $imageLocation;
			$form .= "\" border=\"0\"></td></form></tr></table></div>";
			}
			else{
			$form .= "<input type=\"image\" src=\"".$imageLocation."\" border=\"0\">\n";
			$form .= "</form>\n";
			}

		return $form;
	}
	 /**
         * Function Generates the html form 
         * @param accessKey - Put your Access Key here  
         * @param secretKey - Put your secret Key here
         * @param amount - Enter the amount you want to collect for the ite
         * @param description - description - Enter a description of the item
         * @param referenceId - Optionally enter an ID that uniquely identifies this transaction for your records
         * @param abandonUrl - Optionally, enter the URL where senders should be redirected if they cancel their transaction
         * @param returnUrl - Optionally enter the URL where buyers should be redirected after they complete the transaction
         * @param immediateReturn - Optionally, enter "1" if you want to skip the final status page in Amazon Payments
         * @param processImmediate - Optionally, enter "1" if you want to settle the transaction immediately else "0". Default value is "1"
         * @param ipnUrl - Optionally, type the URL of your host page to which Amazon Payments should send the IPN transaction information.
         * @param collectShippingAddress - Optionally, enter "1" if you want Amazon Payments to return the buyer's shipping address as part of the transaction information
         * @param signatureMethod - Valid values are  HmacSHA256 and HmacSHA1
	 * @param donationType - Optionally, enter the type of donation. Valid values are fixedAmount, minimumAmount and anyAmount. Default value is anyAmount
         * @param environment - Sets the environment where your form will point to can be "sandbox" or "prod" 
         * @return - A map of key of key-value pair for all non null parameters
         * @throws Exception
         */

	 public static function GenerateForm($accessKey,$secretKey,$amount, $description, $referenceId, $immediateReturn,
                        $returnUrl, $abandonUrl, $processImmediate, $ipnUrl,$collectShippingAddress,
                        $signatureMethod,$donationType,$environment) {
			 if($environment=="prod"){
                                $endPoint = self::$PROD_END_POINT;
                                $imageLocation = self::$PROD_IMAGE_LOCATION;
                        }
                        else
                        {
                                $endPoint= self::$SANDBOX_END_POINT;
                                $imageLocation = self::$SANDBOX_IMAGE_LOCATION;
                        }


                $params = self::getDonationParams($accessKey,$amount, $description, $referenceId, $immediateReturn,
                        $returnUrl, $abandonUrl, $processImmediate, $ipnUrl,$collectShippingAddress, $signatureMethod,$donationType);

                $serviceEndPoint = parse_url($endPoint);
                $signature = SignatureUtils::signParameters($params, $secretKey,
                                self::$httpMethod, $serviceEndPoint['host'], $serviceEndPoint['path'],$signatureMethod);
                $params[self::SIGNATURE_KEYNAME] = $signature;
                $simplePayForm = self::getDonationForm($params,$endPoint,$imageLocation,$donationType);
                print $simplePayForm . "\n";
        }

	
}

class SubscriptionButtonSample {

	private static $accessKey = "";				//Put your Access Key here
	private static $secretKey = "";			//Put  your Secret Key here
	private static $amount="USD 1"; 						//Enter the amount you want to collect for the item
	private static $recurringFrequency = "1 month";						//Enter the billing frequency
	private static $signatureMethod="HmacSHA256"; 					//Valid values are  HmacSHA256 and HmacSHA1.
	private static $description="Test Widget";					 //Enter a description of the item
	private static $referenceId="test-reference123"; 				 //Optionally, enter an ID that uniquely identifies this transaction for your records
	private static $abandonUrl="http://wbty.co/cancel.html";		 //Optionally, enter the URL where senders should be redirected if they cancel their transaction
	private static $returnUrl="http://wbty.co/return.html";			 //Optionally enter the URL where buyers should be redirected after they complete the transaction
	private static $immediateReturn="1"; 						 //Optionally, enter "1" if you want to skip the final status page in Amazon Payments
	private static $processImmediate="1"; 						 //Optionally, enter "1" if you want to settle the transaction immediately else "0". Default value is "1" 
	private static $ipnUrl="http://wbty.co/ipn";				 //Optionally, type the URL of your host page to which Amazon Payments should send the IPN transaction information.
	private static $collectShippingAddress=null;					 //Optionally, enter "1" if you want Amazon Payments to return the buyer's shipping address as part of the transaction information
	private static $subscriptionPeriod = "12 months";							//Optionally, enter the subscription duration. By default subscription will be for an unlimited period.
	private static $RecurringStartDate = null;							//Optionally, enter the start date for the subscription. By default susbcription will be processed as soon as subscription id created 
	private static $promotionAmount = null; 							//Optionally, enter the introductory subscription price.
	private static $NoOfPromotionTransactions = null; 					//Required if for promotional period referenceId
	
	 private static $environment="sandbox"; 					//Valid values are "sandbox" or "prod"

	public static function Sampleform($accessKey, $amount) {
		self::$accessKey = $accessKey;
		self::$amount = $amount;
		try{
			ButtonGenerator::GenerateForm(self::$accessKey,self::$secretKey,self::$amount, self::$description, self::$referenceId, self::$immediateReturn,self::$returnUrl, self::$abandonUrl, self::$processImmediate, self::$ipnUrl, self::$collectShippingAddress,self::$signatureMethod,self::$recurringFrequency,self::$subscriptionPeriod,self::$RecurringStartDate,self::$promotionAmount, self::$NoOfPromotionTransactions, self::$environment);

		}
		catch(Exception $e){
			echo 'Exception : ', $e->getMessage(),"\n";
		}
	}
	
	public static function set($valueArray) {
		foreach ($valueArray as $key=>$value) {
			if (isset(self::$$key)) {
				self::$$key = $value;
			}
		}
	}
}

class DonationButtonSample {

	private static $accessKey = "";				//Put your Access Key here
	private static $secretKey = "";		//Put  your Secret Key here
	private static $amount="USD 1"; 						//Enter the amount you want to collect for the item
	private static $signatureMethod="HmacSHA256"; 					//Valid values are  HmacSHA256 and HmacSHA1.
	private static $description="Test Widget";					 //Enter a description of the item
	private static $referenceId="test-reference123"; 				 //Optionally, enter an ID that uniquely identifies this transaction for your records
	private static $abandonUrl="http://yourwebsite.com/cancel.html";		 //Optionally, enter the URL where senders should be redirected if they cancel their transaction
	private static $returnUrl="http://yourwebsite.com/return.html";			 //Optionally enter the URL where buyers should be redirected after they complete the transaction
	private static $immediateReturn="0"; 						 //Optionally, enter "1" if you want to skip the final status page in Amazon Payments
	private static $processImmediate="1"; 						 //Optionally, enter "1" if you want to settle the transaction immediately else "0". Default value is "1" 
	private static $ipnUrl="http://yourwebsite.com/ipn";				 //Optionally, type the URL of your host page to which Amazon Payments should send the IPN transaction information.
	private static $collectShippingAddress=null;					 //Optionally, enter "1" if you want Amazon Payments to return the buyer's shipping address as part of the transaction information
	private static $donationType = "fixedAmount"; 					//Optionally, enter the type of donation. Valid values are fixedAmount, minimumAmount and anyAmount. Default value is anyAmount
	 private static $environment="sandbox"; 					//Valid values are "sandbox" or "prod"

	public static function Sampleform() {
		try{
			ButtonGenerator::GenerateForm(self::$accessKey,self::$secretKey,self::$amount, self::$description, self::$referenceId, self::$immediateReturn,self::$returnUrl, self::$abandonUrl, self::$processImmediate, self::$ipnUrl, self::$collectShippingAddress,self::$signatureMethod, self::$donationType,self::$environment);

		}
		catch(Exception $e){
			echo 'Exception : ', $e->getMessage(),"\n";
		}
	}
	
	public static function set($valueArray) {
		foreach ($valueArray as $key=>$value) {
			if (isset(self::$$key)) {
				self::$$key = $value;
			}
		}
	}
}

$secret = "5Edawf/GLWSWFaOn74o5dehz2z+Wm8fLR1c4YnIm";
?>

<div id="purchaseform">
<h2>Amazon Payments</h2>
<p>You are about to be redirected to Amazon's site to complete the order. If you are not redirected automatically, please click the "Amazon" button below.</p>

<?php
$form = array();
$amount = 0;
$names = array();
foreach ($this->order as $o) {
	$amount += $o['price'];
	$names[] = $o['item_name'];
}
$names = array_unique($names);
$name = implode(',', $names);

$form['SignatureVersion'] = 2;
$form['immediateReturn'] = 1;
$form['amount'] = "USD " . $amount;
$form['SignatureMethod'] = "HmacSHA256";
$form['description'] = $name;
$form['ipnUrl'] = null;
$form['returnUrl'] = JRoute::_(JURI::root() .'index.php?option=com_wbty_payments&task=thankyou');
$form['abandonUrl'] = JRoute::_(JURI::root() .'index.php?option=com_wbty_payments&task=cart');
$form['cobrandingStyle'] = "logo";
$form['processImmediate'] = 1;
$form['referenceId'] = "wbtypayments-".$this->order[0]['order_id'];

if (strpos(JURI::root(), '//localhost')!==FALSE) {
	$app =& JFactory::getApplication();
	$app->enqueueMessage('Amazon Payments will not work properly on localhost. Please move to a web accessible server to continue development.');
	$form['abandonUrl'] = JRoute::_('http://makethewebwork.com/index.php?option=com_wbty_payments&task=ipn');
	$form['returnUrl'] = JRoute::_('http://makethewebwork.com/index.php?option=com_wbty_payments&task=thankyou');
}

$form['secretKey'] = $this->gateway['Secret Key'];
$form['accessKey'] = $this->gateway['Access Key'];
$form['environment'] = $this->method->type ? 'prod' : 'sandbox';

DonationButtonSample::set($form);
DonationButtonSample::SampleForm($form['accessKey'], $form['amount']);
?>

</div>

<script>
	document.getElementById('amazonForm').submit();
</script>

<div class="clear"></div>