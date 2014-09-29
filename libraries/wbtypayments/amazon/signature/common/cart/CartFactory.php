<?php

/**
 * Factory which generates signature input (to pass to SignatureCalculator) and 
 * the final cart html.
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
abstract class CartFactory {
  // Strings to construct the final cart form, with regular expression replacements
  // indicated via [YOUR REPLACEMENT VALUE]
  protected static $CART_JAVASCRIPT_START = "<script type=\"text/javascript\" src=\"https://images-na.ssl-images-amazon.com/images/G/01/cba/js/jquery.js\"></script>\n<script type=\"text/javascript\" src=\"https://images-na.ssl-images-amazon.com/images/G/01/cba/js/widget/widget.js\"></script>\n";
  
  protected static $CART_FORM_START = "<form method=\"POST\" action=\"http://payments.amazon.com/checkout/[MERCHANT_ID]\">\n";

  protected static $CART_FORM_SIGNATURE_INPUT_FIELD = "<input type=\"hidden\" name=\"merchant_signature\" value=\"[SIGNATURE]\" />\n";

  protected static $CART_FORM_BUTTON_INPUT_FIELD = "<input type=\"image\" src=\"https://payments.amazon.com/gp/cba/button?ie=UTF8&color=orange&background=white&size=medium\" alt=\"Checkout with Amazon Payments\" />\n";

  protected static $CART_FORM_END = "</form>\n";


  /**
   * Gets cart html fragment used to generate entire cart html
   * 
   * @param merchantID
   * @param awsAccessKeyID
   */
  public abstract function getCart($merchantID, $awsAccessKeyID);
  
  
  /**
   * Returns the concatenated cart used for signature generation.
   * 
   * @param merchantID
   * @param awsAccessKeyID
   */
  public abstract function getSignatureInput($merchantID, $awsAccessKeyID);

  /**
   * Returns a finalized full cart html including the base 64 encoded cart,
   * signature, and buy button image link.
   * 
   * @param merchantID
   * @param awsAccessKeyID
   * @param signature
   */
  public abstract function getCartHTML($merchantID, $awsAccessKeyID, $signature);
}
?>
