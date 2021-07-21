<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Model\ResourceModel;

class TokenTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Ebizmarts\SagePaySuite\Model\ResourceModel\Token
     */
    private $resourceTokenModel;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionMock;

    // @codingStandardsIgnoreStart
    protected function setUp()
    {
        $selectMock = $this
            ->getMockBuilder('Magento\Framework\DB\Select')
            ->disableOriginalConstructor()
            ->getMock();
        $selectMock->expects($this->any())
            ->method('from')
            ->willReturnSelf();
        $selectMock->expects($this->any())
            ->method('where')
            ->willReturnSelf();
        $selectMock->expects($this->any())
            ->method('limit')
            ->willReturnSelf();

        $this->connectionMock = $this
            ->getMockBuilder('Magento\Framework\DB\Adapter\AdapterInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->connectionMock->expects($this->any())
            ->method('select')
            ->willReturn($selectMock);

        $resourceMock = $this
            ->getMockBuilder('Magento\Framework\App\ResourceConnection')
            ->disableOriginalConstructor()
            ->getMock();
        $resourceMock->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($this->connectionMock));

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->resourceTokenModel = $objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Model\ResourceModel\Token',
            [
                "resource" => $resourceMock,
            ]
        );
    }
    // @codingStandardsIgnoreEnd

    public function testGetCustomerTokens()
    {
        $zendDbMock = $this->getMockBuilder('Zend_Db_Statement_Interface')->getMock();
        $zendDbMock
            ->expects($this->any())
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(["token_id" => 389], ["token_id" => 2]);

        $selectMock = $this->getMockBuilder('Magento\Framework\DB\Select')
            ->disableOriginalConstructor()
            ->setMethods(['from', 'where'])
            ->getMock();
        $selectMock->expects($this->any())->method('from')->willReturnSelf();
        $selectMock->expects($this->any())->method('where')->willReturnSelf();

        $connectionMock = $this->getMockBuilder('Magento\Framework\DB\Adapter\AdapterInterface')->getMock();
        $connectionMock->expects($this->any())->method('select')->willReturn($selectMock);
        $connectionMock->expects($this->any())->method('query')->willReturn($zendDbMock);

        $resourceMock = $this
            ->getMockBuilder('Magento\Framework\App\ResourceConnection')
            ->disableOriginalConstructor()
            ->getMock();
        $resourceMock->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($connectionMock));

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        /** @var \Ebizmarts\SagePaySuite\Model\ResourceModel\Token $resourceTokenModel */
        $resourceTokenModel = $objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Model\ResourceModel\Token',
            [
                "resource" => $resourceMock,
            ]
        );

        $tokenModelMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Model\Token')
            ->disableOriginalConstructor()
            ->getMock();

        $result = $resourceTokenModel->getCustomerTokens($tokenModelMock, 1, 'testebizmarts');

        $this->assertCount(2, $result);
        $this->assertEquals($result[0]['token_id'], 389);
    }

    public function testGetTokenById()
    {
        $this->connectionMock->expects($this->any())
            ->method('fetchRow')
            ->willReturn((object)[
                "token_id" => 1
            ]);

        $this->assertEquals(
            (object)[
                "token_id" => 1
            ],
            $this->resourceTokenModel->getTokenById(1)
        );
    }

    public function testIsTokenOwnedByCustomer()
    {
        $this->connectionMock->expects($this->any())
            ->method('fetchOne')
            ->willReturn("1");

        $this->assertEquals(
            true,
            $this->resourceTokenModel->isTokenOwnedByCustomer(1, 1)
        );
    }

    public function testTokenIsNotOwnedByCustomer()
    {
        $this->connectionMock->expects($this->any())
            ->method('fetchOne')
            ->willReturn(false);

        $this->assertEquals(
            false,
            $this->resourceTokenModel->isTokenOwnedByCustomer(1, 1)
        );
    }
}
