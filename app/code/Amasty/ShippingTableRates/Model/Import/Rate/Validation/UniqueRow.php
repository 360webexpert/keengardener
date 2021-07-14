<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */

declare(strict_types=1);

namespace Amasty\ShippingTableRates\Model\Import\Rate\Validation;

use Amasty\Base\Model\Import\Validation\Validator;
use Amasty\Base\Model\Import\Validation\ValidatorInterface;
use Amasty\ShippingTableRates\Api\Data\ShippingTableRateInterface;
use Amasty\ShippingTableRates\Model\Import\Rate\Renderer;
use Amasty\ShippingTableRates\Model\ResourceModel\Rate\ValidateUnique;
use Magento\Framework\DataObject;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;
use Magento\Framework\App\Request\Http as Request;

class UniqueRow extends Validator implements ValidatorInterface
{
    const NOT_UNIQUE = 'notUnique';

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::NOT_UNIQUE => 'The same Rate already exist:'
    ];

    /**
     * @var ValidateUnique
     */
    private $validateUnique;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Renderer
     */
    private $rateDataRenderer;

    public function __construct(
        DataObject $validationData,
        ValidateUnique $validateUnique,
        Request $request,
        Renderer $rateDataRenderer
    ) {
        parent::__construct($validationData);
        $this->validateUnique = $validateUnique;
        $this->request = $request;
        $this->rateDataRenderer = $rateDataRenderer;
    }

    /**
     * Checks that row isn't already in DB by full match (all columns as a key)
     *
     * @param array $rowData
     * @param string $behavior
     *
     * @return array|bool
     */
    public function validateRow(array $rowData, $behavior)
    {
        if (!in_array($behavior, [Import::BEHAVIOR_ADD_UPDATE])) {
            return true;
        }

        $rowData = $this->rateDataRenderer->renderRateData($rowData);
        $rowData[ShippingTableRateInterface::METHOD_ID] = (int)$this->request->getPost('amastrate_method');

        if ($this->validateUnique->isRowExist($rowData)) {
            return [self::NOT_UNIQUE => ProcessingError::ERROR_LEVEL_CRITICAL];
        }

        return true;
    }
}
