<?php

use Magento\TestFramework\Helper\Bootstrap;

require BP . '/dev/tests/integration/testsuite/Magento/Catalog/_files/products_rollback.php';
require BP . '/dev/tests/integration/testsuite/Magento/Customer/_files/customer_address_rollback.php';
require BP . '/dev/tests/integration/testsuite/Magento/Customer/_files/customer_rollback.php';
require BP . '/dev/tests/integration/testsuite/Magento/Checkout/_files/active_quote_rollback.php';

/** @var \Magento\Framework\Registry $registry */
$registry = Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$order = Bootstrap::getObjectManager()->create(\Magento\Sales\Model\Order::class);
$order->getResource()->load($order, 'test_order_1', 'increment_id');
$order->delete();

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
