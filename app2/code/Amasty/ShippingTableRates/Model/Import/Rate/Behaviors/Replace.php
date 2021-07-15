<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


declare(strict_types=1);

namespace Amasty\ShippingTableRates\Model\Import\Rate\Behaviors;

use Amasty\Base\Model\Import\Behavior\BehaviorInterface;
use Amasty\ShippingTableRates\Model\Import\Rate\Import;
use Amasty\ShippingTableRates\Model\ResourceModel\Rate;
use Amasty\ShippingTableRates\Model\ResourceModel\TableMaintainer;
use Magento\Framework\App\Request\Http as Request;
use Magento\Framework\FlagManager;

/**
 * Our replacement behavior means that all old records will be removed and new records will be added
 */
class Replace implements BehaviorInterface
{
    /**
     * @var Add
     */
    private $addBehavior;

    /**
     * @var FlagManager
     */
    private $flagManager;

    /**
     * @var TableMaintainer
     */
    private $tableMaintainer;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var bool
     */
    private $isDeleted = false;

    public function __construct(
        Request $request,
        Add $addBehavior,
        FlagManager $flagManager,
        TableMaintainer $tableMaintainer
    ) {
        $this->request = $request;
        $this->addBehavior = $addBehavior;
        $this->flagManager = $flagManager;
        $this->tableMaintainer = $tableMaintainer;
    }

    /**
     * @param array $importData
     * @return \Magento\Framework\DataObject
     */
    public function execute(array $importData)
    {
        $shippingMethodId = (int)$this->request->getPost('amastrate_method');
        $replicaTableName = $this->tableMaintainer->getReplicaTable(Rate::MAIN_TABLE);
        $count = 0;

        $this->tableMaintainer->clearTable($replicaTableName);
        $this->tableMaintainer->copyDataToReplicaTable(Rate::MAIN_TABLE);

        $this->flagManager->saveFlag(Import::IMPORT_STATE_KEY, Import::STATE_ACTIVE);

        if (!$this->isDeleted) {
            $count = $this->tableMaintainer->getRateCountByMethodId(Rate::MAIN_TABLE, $shippingMethodId);
            $this->tableMaintainer->clearTableByMethodId(Rate::MAIN_TABLE, $shippingMethodId);
            $this->isDeleted = true;
        }
        $resultImport = $this->addBehavior->execute($importData);

        $this->flagManager->saveFlag(Import::IMPORT_STATE_KEY, Import::STATE_INACTIVE);

        $resultImport->setCountItemsDeleted($resultImport->getCountItemsDeleted() + $count);
        $this->tableMaintainer->clearTable($replicaTableName);

        return $resultImport;
    }
}
