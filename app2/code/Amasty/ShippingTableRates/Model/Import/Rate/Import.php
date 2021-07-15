<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Model\Import\Rate;

use Amasty\Base\Model\Import\AbstractImport;
use Amasty\Base\Model\Import\Behavior\BehaviorProviderInterface;
use Amasty\Base\Model\Import\ImportCounter;
use Amasty\Base\Model\Import\Mapping\MappingInterface;
use Amasty\Base\Model\Import\Validation\EncodingValidator;
use Amasty\Base\Model\Import\Validation\ValidatorPoolInterface;
use Amasty\Base\Model\MagentoVersion;
use Amasty\Base\Model\Serializer;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\StringUtils;
use Magento\ImportExport\Model\Import\AbstractEntity;
use Magento\ImportExport\Model\ImportFactory;
use Magento\ImportExport\Model\ResourceModel\Helper;

class Import extends AbstractImport
{
    const IMPORT_STATE_KEY = 'amasty_table_rates_import_state';
    const STATE_INACTIVE = '0';
    const STATE_ACTIVE = '1';

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(
        $entityTypeCode,
        ValidatorPoolInterface $validatorPool,
        BehaviorProviderInterface $behaviorProvider,
        MappingInterface $mapping,
        EncodingValidator $encodingValidator,
        StringUtils $string,
        ScopeConfigInterface $scopeConfig,
        ImportFactory $importFactory,
        Helper $resourceHelper,
        ProcessingErrorAggregator $errorAggregator,
        ResourceConnection $resource,
        Serializer $serializer,
        array $data = [],
        MagentoVersion $magentoVersion = null,
        ImportCounter $importCounter = null
    ) {
        $this->serializer = $serializer;
        parent::__construct(
            $entityTypeCode,
            $validatorPool,
            $behaviorProvider,
            $mapping,
            $encodingValidator,
            $string,
            $scopeConfig,
            $importFactory,
            $resourceHelper,
            $errorAggregator,
            $resource,
            $data,
            $magentoVersion,
            $importCounter
        );
    }

    /**
     * Validate data rows and save bunches to DB
     *
     * @return $this|AbstractImport
     */
    protected function _saveValidatedBunches()
    {
        $source = $this->getSource();
        $bunchRows = [];
        $startNewBunch = false;

        $source->rewind();
        $this->_dataSourceModel->cleanBunches();
        $masterAttributeCode = $this->getMasterAttributeCode();

        while ($source->valid() || count($bunchRows) || isset($entityGroup)) {
            if ($startNewBunch || !$source->valid()) {
                /* If the end approached add last validated entity group to the bunch */
                if (!$source->valid() && isset($entityGroup)) {
                    foreach ($entityGroup as $key => $value) {
                        $bunchRows[$key] = $value;
                    }
                    unset($entityGroup);
                }
                $this->_dataSourceModel->saveBunch($this->getEntityTypeCode(), $this->getBehavior(), $bunchRows);

                $bunchRows = [];
                $startNewBunch = false;
            }
            if ($source->valid()) {
                $valid = true;
                try {
                    $rowData = $source->current();
                    foreach ($rowData as $attrName => $element) {
                        if (!mb_check_encoding($element, 'UTF-8')) {
                            $valid = false;
                            $this->addRowError(
                                AbstractEntity::ERROR_CODE_ILLEGAL_CHARACTERS,
                                $this->_processedRowsCount,
                                $attrName
                            );
                        }
                    }
                } catch (\InvalidArgumentException $e) {
                    $valid = false;
                    $this->addRowError($e->getMessage(), $this->_processedRowsCount);
                }
                if (!$valid) {
                    $this->_processedRowsCount++;
                    $source->next();
                    continue;
                }

                /**
                 * fix master attribute code issue. Master attribute code can be zero
                 */
                if (isset($rowData[$masterAttributeCode])) {
                    /* Add entity group that passed validation to bunch */
                    if (isset($entityGroup)) {
                        foreach ($entityGroup as $key => $value) {
                            $bunchRows[$key] = $value;
                        }
                        $productDataSize = strlen($this->serializer->serialize($bunchRows));

                        /* Check if the new bunch should be started */
                        $isBunchSizeExceeded = ($this->_bunchSize > 0 && count($bunchRows) >= $this->_bunchSize);
                        $startNewBunch = $productDataSize >= $this->_maxDataSize || $isBunchSizeExceeded;
                    }

                    /* And start a new one */
                    $entityGroup = [];
                }

                if (isset($entityGroup) && $this->validateRow($rowData, $source->key())) {
                    /* Add row to entity group */
                    $entityGroup[$source->key()] = $this->_prepareRowForDb($rowData);
                } elseif (isset($entityGroup)) {
                    /* In case validation of one line of the group fails kill the entire group */
                    unset($entityGroup);
                }

                $this->_processedRowsCount++;
                $source->next();
            }
        }
        return $this;
    }
}
