<?php

namespace Ebizmarts\SagePaySuite\Test\Unit\Api\SagePayData;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class PiTransactionResultAvsCvcCheckTest extends \PHPUnit\Framework\TestCase
{

    public function testOkAccessors()
    {
        $objectManager = new ObjectManager($this);

        /** @var \Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultAvsCvcCheck $sut */
        $sut = $objectManager->getObject(
            'Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultAvsCvcCheck'
        );

        $sut->setSecurityCode('Matched');
        $sut->setPostalCode('Ok');
        $sut->setAddress('OkAddress');
        $sut->setStatus('Approved');

        $this->assertEquals('Matched', $sut->getSecurityCode());
        $this->assertEquals('Ok', $sut->getPostalCode());
        $this->assertEquals('OkAddress', $sut->getAddress());
        $this->assertEquals('Approved', $sut->getStatus());
    }

}