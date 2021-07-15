<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Model\ResourceModel;

use Amasty\ShippingTableRates\Api\Data\ShippingTableRateInterface;
use Amasty\ShippingTableRates\Helper\Data;
use Amasty\ShippingTableRates\Model\ConfigProvider;
use Amasty\ShippingTableRates\Model\Import\Rate\Import;
use Magento\Framework\DB\Select;
use Magento\Framework\FlagManager;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Quote\Model\Quote\Address\RateRequest;

/**
 * Rate Resource
 */
class Rate extends AbstractDb
{
    const MAIN_TABLE = 'amasty_table_rate';
    const FIND_PATTERN = '~^[\p{L}\p{Z}-]+$~u';

    /**
     * @var FlagManager
     */
    private $flagManager;

    /**
     * @var TableMaintainer
     */
    private $tableMaintainer;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Data
     */
    private $helper;

    public function __construct(
        FlagManager $flagManager,
        TableMaintainer $tableMaintainer,
        ConfigProvider $configProvider,
        Data $helper,
        Context $context,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->flagManager = $flagManager;
        $this->tableMaintainer = $tableMaintainer;
        $this->configProvider = $configProvider;
        $this->helper = $helper;
    }

    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, ShippingTableRateInterface::ID);
    }

    /**
     * @param int $methodId
     */
    public function deleteBy($methodId)
    {
        $this->getConnection()->delete($this->getMainTable(), 'method_id=' . (int)$methodId);
    }

    /**
     * @param array $data
     */
    public function insertBunch(array $data): void
    {
        $this->getConnection()->insertMultiple($this->tableMaintainer->getTable(self::MAIN_TABLE), $data);
    }

    /**
     * @return string
     */
    public function getMainTable()
    {
        if ($this->flagManager->getFlagData(Import::IMPORT_STATE_KEY) == Import::STATE_ACTIVE) {
            return $this->tableMaintainer->getReplicaTable(self::MAIN_TABLE);
        }

        return parent::getMainTable();
    }

    /**
     * @param array $methodIds
     * @param array $shippingTypes
     * @return array where key = method_id, value = shipping types array
     */
    public function getUniqueRateTypes(array $methodIds, array $shippingTypes): array
    {
        $ratesTypes = [];
        $select = $this->getConnection()->select()
            ->from(
                $this->getMainTable(),
                [
                    ShippingTableRateInterface::METHOD_ID,
                    ShippingTableRateInterface::SHIPPING_TYPE
                ]
            )->where(
                ShippingTableRateInterface::METHOD_ID . ' IN(?)',
                $methodIds
            )->where(
                ShippingTableRateInterface::SHIPPING_TYPE . ' IN(?)',
                $shippingTypes
            )->order(
                ShippingTableRateInterface::SHIPPING_TYPE . ' ' . Select::SQL_DESC
            )->group(
                [
                    ShippingTableRateInterface::SHIPPING_TYPE,
                    ShippingTableRateInterface::METHOD_ID
                ]
            );

        foreach ((array)$this->getConnection()->fetchAll($select) as $item) {
            $ratesTypes[(int)$item[ShippingTableRateInterface::METHOD_ID]][]
                = (int)$item[ShippingTableRateInterface::SHIPPING_TYPE];
        }

        return $ratesTypes;
    }

    /**
     * @param RateRequest $request
     * @param int $methodId
     * @param array $totals
     * @param int $shippingType
     * @param bool $allowFreePromo
     * @return array
     */
    public function getMethodRates(
        RateRequest $request,
        int $methodId,
        array $totals,
        int $shippingType,
        bool $allowFreePromo
    ): array {
        $select = $this->getConnection()->select()
            ->from($this->getMainTable())
            ->where(ShippingTableRateInterface::METHOD_ID . ' = ?', $methodId);

        $this->addAddressFilters($select, $request);
        $this->addTotalsFilters($select, $totals, $shippingType, $request, $allowFreePromo);

        return (array)$this->getConnection()->fetchAssoc($select);
    }

    /**
     * @param Select $select
     * @param RateRequest $request
     * @return $this
     */
    private function addAddressFilters(Select $select, RateRequest $request): self
    {
        $connection = $this->getConnection();
        $inputZip = $request->getDestPostcode();

        $select
            ->where(
                $connection->prepareSqlCondition(
                    ShippingTableRateInterface::COUNTRY,
                    [
                        [
                            'like' => $request->getDestCountryId(),
                        ],
                        [
                            'eq' => '0',
                        ],
                        [
                            'eq' => '',
                        ],
                    ]
                ),
                null,
                Select::TYPE_CONDITION
            )->where(
                $connection->prepareSqlCondition(
                    ShippingTableRateInterface::STATE,
                    [
                        [
                            'like' => $request->getDestRegionId(),
                        ],
                        [
                            'eq' => '0',
                        ],
                        [
                            'eq' => '',
                        ],
                    ]
                ),
                null,
                Select::TYPE_CONDITION
            )->where(
                $connection->prepareSqlCondition(
                    ShippingTableRateInterface::CITY,
                    [
                        [
                            'like' => $request->getDestCity(),
                        ],
                        [
                            'eq' => '0',
                        ],
                        [
                            'eq' => '',
                        ],
                    ]
                ),
                null,
                Select::TYPE_CONDITION
            );

        if ($this->configProvider->getNumericZip()) {
            $this->addZipFilters($select, $request);
        } else {
            $select->where("? LIKE zip_from OR zip_from = ''", $inputZip);
        }

        return $this;
    }

    /**
     * @param Select $select
     * @param RateRequest $request
     * @return $this
     */
    private function addZipFilters(Select $select, RateRequest $request): self
    {
        $connection = $this->getConnection();
        $inputZip = $request->getDestPostcode();

        if ($inputZip == '*') {
            $inputZip = '';
        }
        $zipData = $this->helper->getDataFromZip($inputZip);
        $zipData['district'] = $zipData['district'] !== '' ? (int)$zipData['district'] : -1;

        $select
            ->where('`num_zip_from` <= ? OR `zip_from` = ""', $zipData['district'])
            ->where('`num_zip_to` >= ? OR `zip_to` = ""', $zipData['district']);

        if (!empty($zipData['area']) && preg_match(self::FIND_PATTERN, $zipData['area'])) {
            $select->where(
                $connection->prepareSqlCondition(
                    ShippingTableRateInterface::ZIP_FROM,
                    [
                        [
                            ['regexp' => '^' . $zipData['area'] . '[0-9]+'],
                            ['eq' => '']
                        ],
                    ]
                ),
                null,
                Select::TYPE_CONDITION
            );
        }

        //to prefer rate with zip
        $select->order(
            [
                ShippingTableRateInterface::NUM_ZIP_FROM . ' ' . Select::SQL_DESC,
                ShippingTableRateInterface::NUM_ZIP_TO . ' ' . Select::SQL_DESC,
            ]
        );

        return $this;
    }

    /**
     * @param Select $select
     * @param array $totals
     * @param int $shippingType
     * @param RateRequest $request
     * @param bool $allowFreePromo
     * @return $this
     */
    private function addTotalsFilters(
        Select $select,
        array $totals,
        int $shippingType,
        RateRequest $request,
        bool $allowFreePromo
    ): self {
        if (!($request->getFreeShipping() && $allowFreePromo)) {
            $select
                ->where(ShippingTableRateInterface::PRICE_FROM . ' <= ?', $totals['not_free_price'])
                ->where(ShippingTableRateInterface::PRICE_TO . ' >= ?', $totals['not_free_price']);
        }

        $select
            ->where(ShippingTableRateInterface::WEIGHT_FROM . ' <= ?', $totals['not_free_weight'])
            ->where(ShippingTableRateInterface::WEIGHT_TO . ' >= ?', $totals['not_free_weight'])
            ->where(ShippingTableRateInterface::QTY_FROM . ' <= ?', $totals['not_free_qty'])
            ->where(ShippingTableRateInterface::QTY_TO . ' >= ?', $totals['not_free_qty'])
            ->where(
                $this->getConnection()->prepareSqlCondition(
                    ShippingTableRateInterface::SHIPPING_TYPE,
                    [
                        [
                            'eq' => $shippingType,
                        ],
                        [
                            'eq' => '',
                        ],
                        [
                            'eq' => '0',
                        ],
                    ]
                ),
                null,
                Select::TYPE_CONDITION
            );

        return $this;
    }
}
