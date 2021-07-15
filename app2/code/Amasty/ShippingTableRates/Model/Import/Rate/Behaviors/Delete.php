<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


declare(strict_types=1);

namespace Amasty\ShippingTableRates\Model\Import\Rate\Behaviors;

use Amasty\Base\Model\Import\Behavior\BehaviorInterface;
use Amasty\ShippingTableRates\Api\Data\ShippingTableRateInterface;
use Amasty\ShippingTableRates\Model\Import\Rate\Renderer;
use Amasty\ShippingTableRates\Model\ResourceModel\Rate\DeleteQueryCollector;
use Magento\Framework\App\Request\Http as Request;
use Magento\Framework\DataObject;

class Delete implements BehaviorInterface
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Renderer
     */
    private $rateDataRenderer;

    /**
     * @var DeleteQueryCollector
     */
    private $deleteQueryCollector;

    public function __construct(
        Request $request,
        Renderer $rateDataRenderer,
        DeleteQueryCollector $deleteQueryCollector
    ) {
        $this->request = $request;
        $this->rateDataRenderer = $rateDataRenderer;
        $this->deleteQueryCollector = $deleteQueryCollector;
    }

    /**
     * import data is already coming in bunches
     *
     * @param array $importData
     * @return \Magento\Framework\DataObject
     */
    public function execute(array $importData)
    {
        $resultImportObject = new DataObject();
        $shippingMethodId = (int)$this->request->getPost('amastrate_method');

        foreach ($importData as $rowData) {
            $rowData = $this->rateDataRenderer->renderRateData($rowData);
            $rowData[ShippingTableRateInterface::METHOD_ID] = $shippingMethodId;
            $this->deleteQueryCollector->collectRowsBunch($rowData);
        }

        $this->deleteQueryCollector->deleteBunch();

        $resultImportObject->setCountItemsDeleted($this->deleteQueryCollector->getDeletedRowsCount());

        return $resultImportObject;
    }
}
