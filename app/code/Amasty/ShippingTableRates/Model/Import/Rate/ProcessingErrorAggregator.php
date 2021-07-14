<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */

declare(strict_types=1);

namespace Amasty\ShippingTableRates\Model\Import\Rate;

/**
 * Optimized error processor
 */
class ProcessingErrorAggregator extends \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregator
{
    /**
     * Check if an error has already been added to the aggregator
     *
     * @param int $rowNum
     * @param string $errorCode
     * @param string $columnName
     * @return bool
     */
    protected function isErrorAlreadyAdded($rowNum, $errorCode, $columnName = null)
    {
        $errors = $this->getErrorByRowNumber($rowNum);
        foreach ($errors as $error) {
            if ($errorCode == $error->getErrorCode() && $columnName == $error->getColumnName()) {
                return true;
            }
        }

        return false;
    }
}
