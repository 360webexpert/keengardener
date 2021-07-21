<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Setup;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Ebizmarts\SagePaySuite\Setup\SplitDatabaseConnectionProvider;

class UpgradeSchemaTest extends \PHPUnit\Framework\TestCase
{
    public function testUpgradeRunOnModuleInstall()
    {
        $connectionMock = $this->makeConnectionMock();

        $schemaSetupMock = $this->createSchemaSetupMock($connectionMock);

        $objectManagerHelper = new ObjectManager($this);

        /** @var \Ebizmarts\SagePaySuite\Setup\UpgradeSchema $upgradeSchema */
        $upgradeSchema = $objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Setup\UpgradeSchema',
            [
                "connectionProvider" => $this->makeConnectionProviderMock($connectionMock, $schemaSetupMock)
            ]
        );
        $upgradeSchema->upgrade($schemaSetupMock, $this->createContext());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createContext()
    {
        $moduleContextMock = $this->getMockBuilder('Magento\Framework\Setup\ModuleContextInterface')->disableOriginalConstructor()->getMock();

        $moduleContextMock
            ->expects($this->exactly(2))
            ->method('getVersion')
            ->willReturn('0.0.1'); //TODO Revisar cual fue la primer release.

        return $moduleContextMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeTokenTableMock()
    {
        $tableMock = $this->getMockBuilder('Magento\Framework\DB\Ddl\Table')->disableOriginalConstructor()->getMock();

        $tableMock
            ->expects($this->exactly(10))
            ->method('addColumn')
            ->withConsecutive(
                [
                    'id', 'integer', null, ['nullable' => false, 'primary' => true, 'auto_increment' => true], 'Token Id'
                ],
                [
                    'customer_id', 'integer', null, ['unsigned' => true, 'nullable' => false], 'Customer Id'
                ],
                [
                    'token', 'text', 150, ['nullable' => false], 'Token'
                ],
                [
                    'cc_last_4', 'text', 100, [], 'Cc Last 4'
                ],
                [
                    'cc_exp_month', 'text', 12, [], 'Cc Exp Month'
                ],
                [
                    'cc_type', 'text', 32, [], 'Cc Type'
                ],
                [
                    'cc_exp_year', 'text', 4, [], 'Cc Exp Year'
                ],
                [
                    'vendorname', 'text', 100, [], 'Vendorname'
                ],
                [
                    'created_at', 'timestamp', null, ['nullable' => false, 'default' => 'TIMESTAMP_INIT'], 'Created At'
                ],
                [
                    'store_id', 'smallint', null, ['unsigned' => true], 'Store Id'
                ]
            )
            ->willReturnSelf();
        $tableMock
            ->expects($this->once())
            ->method('addIndex')
            ->with('SAGEPAYSUITE_TOKEN_CUSTOMER_ID', ['customer_id'])
            ->willReturnSelf();

        return $tableMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createSchemaSetupMock($connectionMock)
    {
        $schemaSetupMock = $this->getMockBuilder('Magento\Framework\Setup\SchemaSetupInterface')->disableOriginalConstructor()->getMock();

        $schemaSetupMock->expects($this->exactly(2))
            ->method('getTable')
            ->withConsecutive(
                ['sagepaysuite_token'],
                ['sales_payment_transaction']
            )
            ->willReturnOnConsecutiveCalls(
                'sagepaysuite_token',
                'sales_payment_transaction'
            );
        $schemaSetupMock->expects($this->once())
            ->method('startSetup');
        $schemaSetupMock->expects($this->once())
            ->method('endSetup');
        $schemaSetupMock->expects($this->exactly(2))
            ->method('getConnection')
            ->willReturn($connectionMock);
        $schemaSetupMock->expects($this->once())
            ->method('getIdxName')
            ->willReturn('SAGEPAYSUITE_TOKEN_CUSTOMER_ID');

        return $schemaSetupMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeConnectionMock()
    {
        $tokenTableMock = $this->makeTokenTableMock();

        $connectionMock = $this->getMockBuilder('Magento\Framework\DB\Adapter\AdapterInterface')->disableOriginalConstructor()->getMock();

        $connectionMock->expects($this->once())
            ->method('addColumn')
            ->with(
                'sales_payment_transaction',
                'sagepaysuite_fraud_check',
                [
                    'type' => 'boolean',
                    'nullable' => false,
                    'comment' => 'Opayo Fraud Check Flag'
                ]
            )
            ->willReturnSelf();
        $connectionMock->expects($this->once())
            ->method('newTable')
            ->willReturn($tokenTableMock);
        $connectionMock->expects($this->once())
            ->method('createTable')
            ->with($tokenTableMock);

        return $connectionMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeConnectionProviderMock($connectionMock, $schemaSetupMock)
    {
        $connectionProviderMock = $this->getMockBuilder(SplitDatabaseConnectionProvider::class)->disableOriginalConstructor()->getMock();

        $connectionProviderMock
            ->expects($this->once())
            ->method("getSalesConnection")
            ->with($schemaSetupMock)
            ->willReturn($connectionMock);

        return $connectionProviderMock;
    }
}
