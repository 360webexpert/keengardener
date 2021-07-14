<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Model\Import\Rate\Behaviors;

use Amasty\Base\Model\Import\Behavior\BehaviorInterface;
use Amasty\ShippingTableRates\Model\Import\Rate\Renderer;
use Amasty\ShippingTableRates\Model\ResourceModel\Rate as RateResource;
use Magento\Framework\DataObject;
use Magento\Framework\App\Request\Http as Request;

class Add implements BehaviorInterface
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Renderer
     */
    private $renderer;

    /**
     * @var RateResource
     */
    private $rateResource;

    public function __construct(
        Request $request,
        Renderer $renderer,
        RateResource $rateResource
    ) {
        $this->request = $request;
        $this->renderer = $renderer;
        $this->rateResource = $rateResource;
    }

    /**
     * @param array $importData
     * @return DataObject
     */
    public function execute(array $importData)
    {
        $resultImportObject = new DataObject();
        $shippingMethodId = $this->request->getPost('amastrate_method');

        foreach ($importData as &$rateData) {
            $rateData = $this->renderer->renderRateData($rateData);
            $rateData['method_id'] = $shippingMethodId;
        }
        $this->rateResource->insertBunch($importData);

        $resultImportObject->setCountItemsCreated(count($importData));

        return $resultImportObject;
    }
}
