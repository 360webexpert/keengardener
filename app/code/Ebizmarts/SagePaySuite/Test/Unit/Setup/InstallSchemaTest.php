<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Setup;

use Ebizmarts\SagePaySuite\Setup\SplitDatabaseConnectionProvider;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class InstallSchemaTest extends \PHPUnit\Framework\TestCase
{
    public function testInstall()
    {
        $objectManagerHelper = new ObjectManager($this);

        $connectionProviderMock = $this->getMockBuilder(SplitDatabaseConnectionProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $connectionMock = $this
            ->getMockBuilder('Magento\Framework\DB\Adapter\AdapterInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $connectionMock
            ->expects($this->once())
            ->method('modifyColumn')
            ->with(
                'sales_order_payment',
                "last_trans_id",
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 100,
                    'nullable' => false
                ]
            );

        $schemaSetupMock = $this
            ->getMockBuilder(\Magento\Setup\Module\Setup::class)
            ->disableOriginalConstructor()
            ->getMock();
        $schemaSetupMock->expects($this->once())
            ->method('startSetup');
        $schemaSetupMock->expects($this->once())
            ->method('endSetup');
        $schemaSetupMock->expects($this->once())
            ->method('getTable')
            ->with('sales_order_payment')
            ->willReturn('sales_order_payment');

        $connectionProviderMock
            ->expects($this->once())
            ->method("getSalesConnection")
            ->with($schemaSetupMock)
            ->willReturn($connectionMock);

        $moduleContextMock = $this
            ->getMockBuilder('Magento\Framework\Setup\ModuleContextInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $installSchema = $objectManagerHelper->getObject(
            "Ebizmarts\SagePaySuite\Setup\InstallSchema",
            [
                "connectionProvider" => $connectionProviderMock
            ]
        );

        $installSchema->install($schemaSetupMock, $moduleContextMock);
    }
}
