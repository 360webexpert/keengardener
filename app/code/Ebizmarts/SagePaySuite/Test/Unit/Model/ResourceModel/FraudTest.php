<?php

namespace Ebizmarts\SagePaySuite\Test\Unit\Model\ResourceModel;

use Ebizmarts\SagePaySuite\Model\ResourceModel\Fraud;
use Magento\Framework\DB\Statement\Pdo\Mysql;

class FraudTest extends \PHPUnit\Framework\TestCase
{
    public function __construct()
    {
        parent::__construct();
    }

    public function testGetOrdersToCancel()
    {
        $selectMock = $this
            ->getMockBuilder('Magento\Framework\DB\Select')
            ->setMethods(['from', 'where', 'limit', 'joinInner'])
            ->disableOriginalConstructor()
            ->getMock();
        $selectMock
            ->expects($this->once())
            ->method('from')
            ->with('sales_order', 'sales_order.entity_id')
            ->willReturnSelf();
        $selectMock
            ->expects($this->exactly(4))
            ->method('where')
            ->withConsecutive(
                ['sales_order.state=?', 'pending_payment'],
                ['sales_order.created_at <= now() - INTERVAL 30 MINUTE'],
                ['sales_order.created_at >= now() - INTERVAL 2 DAY'],
                ["payment.method LIKE '%sagepaysuite%'"]
            )
            ->willReturnSelf();
        $selectMock
            ->expects($this->once())
            ->method("joinInner")
            ->with(
                ["payment" => "sales_order_payment"],
                "sales_order.entity_id = payment.parent_id",
                []
            )
            ->willReturnSelf();
        $selectMock
            ->expects($this->once())
            ->method('limit')
            ->with(10)
            ->willReturnSelf();

        $queryMock = $this
            ->getMockBuilder(Mysql::class)
            ->setMethods(['fetchColumn'])
            ->disableOriginalConstructor()
            ->getMock();
        $queryMock
            ->expects($this->exactly(2))
            ->method('fetchColumn')
            ->willReturnOnConsecutiveCalls(198, false);

        $connectionMock = $this
            ->getMockBuilder('Magento\Framework\DB\Adapter\AdapterInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $connectionMock
            ->expects($this->once())
            ->method('select')
            ->willReturn($selectMock);
        $connectionMock
            ->expects($this->once())
            ->method('query')
            ->with($selectMock)
            ->willReturn($queryMock);

        $resourceMock = $this
            ->getMockBuilder('Magento\Framework\App\ResourceConnection')
            ->disableOriginalConstructor()
            ->getMock();
        $resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($connectionMock);

        /** @var Fraud $fraudModelMock */
        $fraudModelMock = $this
            ->getMockBuilder(Fraud::class)
            ->setMethods(['getTable', 'getConnection'])
            ->disableOriginalConstructor()
            ->getMock();
        $fraudModelMock
            ->expects($this->once())
            ->method('getConnection')
            ->willReturn($connectionMock);
        $fraudModelMock
            ->expects($this->exactly(2))
            ->method('getTable')
            ->withConsecutive(
                ["sales_order"],
                ["sales_order_payment"]
            )
            ->willReturnOnConsecutiveCalls("sales_order", "sales_order_payment");

        $this->assertEquals([198], $fraudModelMock->getOrderIdsToCancel());
    }

    public function testGetShadowPaidPaymentTransactions()
    {
        $selectMock = $this
            ->getMockBuilder('Magento\Framework\DB\Select')
            ->setMethods(['from', 'where', 'limit', 'joinLeft'])
            ->disableOriginalConstructor()
            ->getMock();
        $selectMock
            ->expects($this->once())
            ->method('joinLeft')
            ->with(
                ["payment" => "sales_order_payment"],
                'sales_payment_transaction.payment_id = payment.entity_id',
                []
            )
            ->willReturnSelf();
        $selectMock
            ->expects($this->once())
            ->method('from')
            ->with('sales_payment_transaction', 'transaction_id')
            ->willReturnSelf();
        $selectMock
            ->expects($this->exactly(6))
            ->method('where')
            ->withConsecutive(
                ["sagepaysuite_fraud_check = 0"],
                ["txn_type='capture' OR txn_type='authorization'"],
                ["sales_payment_transaction.parent_id IS NULL"],
                ["created_at >= now() - INTERVAL 2 DAY"],
                ["created_at < now() - INTERVAL 15 MINUTE"],
                ["method LIKE '%sagepaysuite%'"]
            )
            ->willReturnSelf();
        $selectMock
            ->expects($this->once())
            ->method('limit')
            ->with(20)
            ->willReturnSelf();

        $queryMock = $this
            ->getMockBuilder(Mysql::class)
            ->setMethods(['fetch'])
            ->disableOriginalConstructor()
            ->getMock();
        $queryMock
            ->expects($this->exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(['transaction_id' => 198], false);

        $connectionMock = $this
            ->getMockBuilder('Magento\Framework\DB\Adapter\AdapterInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $connectionMock
            ->expects($this->once())
            ->method('select')
            ->willReturn($selectMock);
        $connectionMock
            ->expects($this->once())
            ->method('query')
            ->with($selectMock)
            ->willReturn($queryMock);

        $resourceMock = $this
            ->getMockBuilder('Magento\Framework\App\ResourceConnection')
            ->disableOriginalConstructor()
            ->getMock();
        $resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($connectionMock);

        $fraudModelMock = $this
            ->getMockBuilder(Fraud::class)
            ->setMethods(['getTable', 'getConnection'])
            ->disableOriginalConstructor()
            ->getMock();
        $fraudModelMock
            ->expects($this->once())
            ->method('getConnection')
            ->willReturn($connectionMock);
        $fraudModelMock
            ->expects($this->exactly(2))
            ->method('getTable')
            ->withConsecutive(["sales_payment_transaction"], ["sales_order_payment"])
        ->willReturnOnConsecutiveCalls("sales_payment_transaction", "sales_order_payment");

        $this->assertEquals([['transaction_id' => 198]], $fraudModelMock->getShadowPaidPaymentTransactions());
    }

    public function testConstructIsCallingResetter()
    {
        $fraudModelMock = $this
            ->getMockBuilder(Fraud::class)
            ->disableOriginalConstructor()
            ->getMock();

        $fraudModelMock->expects($this->once())->method('resetUniqueField');

        $fraudModelMock->__construct(
            $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\Db\Context::class)
            ->disableOriginalConstructor()
            ->getMock()
        );
    }
}
