<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require BP . '/dev/tests/integration/testsuite/Magento/Checkout/_files/quote_with_address.php';

/** @var \Magento\Quote\Model\Quote $quote */

/** @var $rate \Magento\Quote\Model\Quote\Address\Rate */
$rate = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Quote\Model\Quote\Address\Rate');
$rate->setCode('freeshipping_freeshipping');
$rate->getPrice();

$quote->getShippingAddress()->setShippingMethod('freeshipping_freeshipping');
$quote->getShippingAddress()->addShippingRate($rate);
$quote->getPayment()->setMethod('sagepaysuiteserver');

$quote->collectTotals();
$quote->save();

//$quote->getPayment()->setMethod('sagepaysuiteserver');
//
/** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
$quoteIdMask = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create('Magento\Quote\Model\QuoteIdMaskFactory')
    ->create();
$quoteIdMask->setQuoteId($quote->getId());
$quoteIdMask->setDataChanges(true);
$quoteIdMask->save();
