<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Amazon\Connector\Inventory\Get;

/**
 * Class \Ess\M2ePro\Model\Amazon\Connector\Inventory\Get\ItemsResponser
 */
abstract class ItemsResponser extends \Ess\M2ePro\Model\Connector\Command\Pending\Responser
{
    //########################################

    protected function validateResponse()
    {
        $responseData = $this->getResponse()->getResponseData();
        return isset($responseData['data']);
    }

    protected function prepareResponseData()
    {
        $preparedData = [];

        $responseData = $this->getResponse()->getResponseData();

        foreach ($responseData['data'] as $receivedItem) {
            if (empty($receivedItem['identifiers']['sku'])) {
                continue;
            }

            $preparedData[$receivedItem['identifiers']['sku']] = $receivedItem;
        }

        $this->preparedResponseData = $preparedData;
    }

    //########################################
}
