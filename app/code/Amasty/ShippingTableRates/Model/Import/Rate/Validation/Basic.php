<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Model\Import\Rate\Validation;

use Amasty\Base\Model\Import\Validation\Validator;
use Amasty\ShippingTableRates\Api\Data\ShippingTableRateInterface;
use Amasty\Base\Model\Import\Validation\ValidatorInterface;
use Amasty\ShippingTableRates\Model\Import\Rate\Validation as Validation;
use Amasty\ShippingTableRates\Model\Import\Rate\Mapping;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;
use Magento\Framework\DataObject;

class Basic extends Validator implements ValidatorInterface
{
    const INVALID_COUNTRY = 'invalidCountryCode';
    const INVALID_STATE = 'invalidStateCode';
    const INVALID_SHIPPING_TYPE = 'invalidShippingType';
    const INVALID_NUMERIC_VALUE = 'invalidNumericValue';
    const RATE_IS_EMPTY = 'emptyRate';

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::INVALID_COUNTRY => '<b>Error!</b>Invalid country',
        self::INVALID_STATE => '<b>Error!</b>Invalid state',
        self::INVALID_SHIPPING_TYPE => '<b>Error!</b>Invalid shipping type',
        self::INVALID_NUMERIC_VALUE => '<b>Error!</b>Invalid numeric value:',
        self::RATE_IS_EMPTY => '<b>Error!</b>Rate is not specified:'
    ];

    /**
     * @var Validation
     */
    private $validation;

    public function __construct(
        DataObject $validationData,
        Validation $validation
    ) {
        $this->validation = $validation;
        parent::__construct($validationData);
    }

    /**
     * @param array $rowData
     * @param string $behavior
     * @return array|bool
     */
    public function validateRow(array $rowData, $behavior)
    {
        $this->errors = [];

        if (!isset($rowData[ShippingTableRateInterface::COST_BASE])) {
            $this->addErrorByCode(self::RATE_IS_EMPTY, ProcessingError::ERROR_LEVEL_CRITICAL);
        }

        if (!$this->validation->validateCountry($rowData[ShippingTableRateInterface::COUNTRY])) {
            $this->addErrorByCode(self::INVALID_COUNTRY, ProcessingError::ERROR_LEVEL_NOT_CRITICAL);
        }

        if (!$this->validation->validateState(
            $rowData[ShippingTableRateInterface::STATE],
            $rowData[ShippingTableRateInterface::COUNTRY]
        )) {
            $this->addErrorByCode(self::INVALID_STATE, ProcessingError::ERROR_LEVEL_NOT_CRITICAL);
        }

        if (!$this->validation->validateShippingType($rowData[ShippingTableRateInterface::SHIPPING_TYPE])) {
            $this->addErrorByCode(self::INVALID_SHIPPING_TYPE, ProcessingError::ERROR_LEVEL_CRITICAL);
        }

        foreach (Mapping::NUMERIC_DATA as $value) {
            if (!$this->validation->validateNumericValue($rowData[$value])) {
                if (!empty(Mapping::DESCRIPTION_DATA[$value])) {
                    $value = Mapping::DESCRIPTION_DATA[$value];
                }

                $this->addErrorMessage(
                    $this->getMessageByCode(self::INVALID_NUMERIC_VALUE) . ' ' . $value,
                    ProcessingError::ERROR_LEVEL_NOT_CRITICAL
                );
            }
        }

        return parent::validateResult();
    }

    /**
     * @param string $message
     * @param string $level
     */
    private function addErrorMessage($message, $level)
    {
        $this->errors[$message] = $level;
    }

    /**
     * @param string $code
     * @param string $level
     */
    private function addErrorByCode($code, $level)
    {
        $message = $this->getMessageByCode($code);

        $this->addErrorMessage($message, $level);
    }

    /**
     * @param string $code
     * @return string
     */
    private function getMessageByCode($code)
    {
        return $this->messageTemplates[$code];
    }
}
