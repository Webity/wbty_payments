<?php
require_once("signature/common/cart/CartFactory.php");
                                                                                                                                                            /**
 * Returns a simple static cart to generate a signature from,
 * and the final complete cart html.
 *
 * Copyright 2008-2008 Amazon.com, Inc., or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the “License”).
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 *    http://aws.amazon.com/apache2.0/
 *
 * or in the “license” file accompanying this file.
 * This file is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,
 * either express or implied. See the License for the specific language governing permissions and limitations under the License.
 */
class XMLCartFactory extends CartFactory {
   protected static $CART_FORM_ORDER_INPUT_FIELD =
      "<input type=\"hidden\" name=\"order-input\" value=\"type:merchant-signed-order/aws-accesskey/1;order:[ORDER];signature:[SIGNATURE];aws-access-key-id:[AWS_ACCESS_KEY_ID]\" />\n";
	
   public function XMLCartFactory() {
   }

   /**
    * Gets cart html fragment used to generate entire cart html
    * Base 64 encode the cart.
    * 
    */
    public function getCart($merchantID, $awsAccessKeyID) {
        $cartXML = $this->getCartXML($merchantID, $awsAccessKeyID);
        return base64_encode($cartXML);
    }
   
   /**
    * Returns the concatenated cart used for signature generation.
    * @see CartFactory
    */
   public function getSignatureInput($merchantID, $awsAccessKeyID) {
        return $this->getCartXML($merchantID, $awsAccessKeyID);
   }

   /**
    * Returns a finalized full cart html including the base 64 encoded cart,
    * signature, and buy button image link.
    */
   public function getCartHTML($merchantID, $awsAccessKeyID, $signature) {
        $cartHTML = '';

	$cartHTML = $cartHTML . CartFactory::$CART_JAVASCRIPT_START;
        $cartHTML = $cartHTML . preg_replace("\\[MERCHANT_ID\\]", $merchantID, CartFactory::$CART_FORM_START);

	// construct the order-input section
	$encodedCart = $this->getCart($merchantID, $awsAccessKeyID);
        $input = preg_replace("\\[ORDER\\]", $encodedCart, XMLCartFactory::$CART_FORM_ORDER_INPUT_FIELD);
        $input = preg_replace("\\[SIGNATURE\\]", $signature, $input);
        $input = preg_replace("\\[AWS_ACCESS_KEY_ID\\]", $awsAccessKeyID, $input);

        $cartHTML = $cartHTML . $input;        
	$cartHTML = $cartHTML . CartFactory::$CART_FORM_BUTTON_INPUT_FIELD;
	$cartHTML = $cartHTML . CartFactory::$CART_FORM_END;

	return $cartHTML;
   }
	
    /**
     * Replace with your own cart here to try out
     * different promotions, tax, shipping, etc. 
     * 
     * @param merchantID
     * @param awsAccessKeyID
     */
    private function getCartXML($merchantID, $awsAccessKeyID) {
        return
	    "<?xml version=\"1.0\" encoding=\"UTF-8\"?>" .
	    "<Order xmlns=\"http://payments.amazon.com/checkout/2008-06-15/\">" .
	    "    <ClientRequestId>123457</ClientRequestId>" .
	    "    <Cart>" .
	    "    <Items>" .
	    "      <Item>" .
	    "         <SKU>CALVIN-HOBBES</SKU>" .
	    "         <MerchantId>" . $merchantID . "</MerchantId>" .
	    "         <Title>The Complete Calvin and Hobbes</Title>" .
	    "         <Description>By Bill Watterson</Description>" .
	    "         <Price>" .
	    "            <Amount>2.50</Amount>" .
	    "            <CurrencyCode>USD</CurrencyCode>" .
	    "         </Price>" .
	    "         <Quantity>1</Quantity>" .
	    "         <Weight>" .
	    "            <Amount>8.5</Amount>" .
	    "            <Unit>lb</Unit>" .
	    "         </Weight>" .
	    "         <Category>Books</Category>" .
	    "      </Item>" .
	    "    </Items>" .
	    "    </Cart>" .
	    "</Order>";
    }
}
