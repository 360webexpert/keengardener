<?php

namespace Ebizmarts\SagePaySuite\Test\Unit\Setup;

use Ebizmarts\SagePaySuite\Setup\Uninstall;
use Ebizmarts\SagePaySuite\Setup\SplitDatabaseConnectionProvider;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

class UninstallTest extends \PHPUnit\Framework\TestCase
{
    /** @var SplitDatabaseConnectionProvider|\PHPUnit_Framework_MockObject_MockObject */
    private $connectionProvider;

    /** @var SchemaSetupInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $setup;

    /** @var ModuleContextInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $context;

    /** @var Uninstall */
    private $uninstall;

    /** @var AdapterInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $connection;

    public function setUp()
    {
        $this->connectionProvider = $this->getMockBuilder(SplitDatabaseConnectionProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->setup = $this->getMockBuilder(SchemaSetupInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->context = $this->getMockBuilder(ModuleContextInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->connection = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->uninstall = new Uninstall($this->connectionProvider);
    }

    public function testUninstall()
    {
        $this->connectionProvider->expects($this->exactly(2))->method('getSalesConnection')->willReturn($this->connection);
        $this->setup->expects($this->once())->method('getConnection')->willReturn($this->connection);

        $this->setupExpectsGetTable();
        $this->connectionExpectsModifyColumn();
        $this->connectionExpectsDropColumn();
        $this->connectionExpectsDropTable();

        $this->uninstall->uninstall($this->setup, $this->context);
    }

    private function connectionExpectsModifyColumn()
    {
        $this->connection->expects($this->once())
            ->method('modifyColumn')
            ->with('sales_order_payment', 'last_trans_id', ['nullable' => true]);
    }

    private function connectionExpectsDropColumn()
    {
        $this->connection->expects($this->once())
            ->method('dropColumn')
            ->with('sales_payment_transaction', 'sagepaysuite_fraud_check');
    }

    private function connectionExpectsDropTable()
    {
        $this->connection->expects($this->once())->method('dropTable')->with('sagepaysuite_token');
    }

    private function setupExpectsGetTable()
    {
        $this->setup->expects($this->exactly(3))->method('getTable')->willReturnCallback(function ($table) {
            return $table;
        });
    }
}
