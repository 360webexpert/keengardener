<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Block\Paypal;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\View\Element\Template\Context;

class Processing extends \Magento\Framework\View\Element\Template
{
    // @codingStandardsIgnoreStart
    protected function _toHtml()
    {
        return $this->paypalHtml();
    }
    // @codingStandardsIgnoreEnd

    public function paypalHtml()
    {
        $html = '<html><head><title>PayPal - Processing payment...</title></head><body>';
        $html .= '<style>body {background-color: #F7F6F4;}' .
            ' .container {margin: 150px auto 350px; width: 50%; text-align: center;}' .
            ' .container p {font-family: pp-sans-big-light,Helvetica Neue,Arial,sans-serif; color: #444;
            margin-top: 30px; font-size: 14px;}' .
            ' .container img {width: 150px;}' .
            ' .loader {width: 20px !important; margin: 0px 10px 0 0; position: relative; top: 4px;}</style>';
        $html .= '<div class="container"><img
                 src="' . $this->getViewFileUrl('Ebizmarts_SagePaySuite::images/paypal_checkout.png') . '">';

        $callbackUrl = $this->getUrl('sagepaysuite/paypal/callback', ['_secure' => true]);

        //form POST
        $postData = $this->getData("paypal_post");
        if (!empty($postData) && is_object($postData) && $postData->Status) {
            $html .= '<p><img class="loader"
                src="' . $this->getViewFileUrl('Ebizmarts_SagePaySuite::images/ajax-loader.gif') . '"
                >Processing payment, please wait...</p></div>';
            $html .= '<form id="paypal_post_form" method="POST"
            action="' . $callbackUrl . '">';
            $postData  = get_object_vars($postData);
            $keys      = array_keys($postData);
            $keysCount = count($keys);
            for ($i = 0; $i < $keysCount; $i++) {
                $html .= '<input type="hidden" name="' . $keys[$i] . '" value="' . $postData[$keys[$i]] . '">';
            }

            $html .= '<input type="hidden" name="quoteid" value="' . $this->getRequest()->getParam('quoteid') . '">';

            $html .= '</form>';
            $html .= '<script>document.getElementById("paypal_post_form").submit();</script>';
        } else {
            $html .= '<p>ERROR: Invalid response from PayPal</p></div>';
        }
        $html .= '</body></html>';

        return $html;
    }
}
