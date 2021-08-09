<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Amazon\Listing\Product\Action\Type\Revise;

use \Ess\M2ePro\Model\Amazon\Listing\Product\Action\Type\ListAction\Request as ListActionRequest;

/**
 * Class \Ess\M2ePro\Model\Amazon\Listing\Product\Action\Type\Revise\Request
 */
class Request extends \Ess\M2ePro\Model\Amazon\Listing\Product\Action\Type\Request
{
    //########################################

    protected function getActionData()
    {
        $data = array_merge(
            [
                'sku' => $this->getAmazonListingProduct()->getSku()
            ],
            $this->getQtyData(),
            $this->getRegularPriceData(),
            $this->getBusinessPriceData(),
            $this->getDetailsData(),
            $this->getImagesData()
        );

        if ($this->getVariationManager()->isRelationChildType()) {
            $variationData = [
                'parentage'  => ListActionRequest::PARENTAGE_CHILD,
                'attributes' => $this->getVariationManager()->getTypeModel()->getChannelOptions(),
            ];

            /** @var \Ess\M2ePro\Model\Amazon\Listing\Product $parentAmazonListingProduct */
            $parentAmazonListingProduct = $this->getVariationManager()
                ->getTypeModel()
                ->getParentListingProduct()
                ->getChildObject();

            $parentSku = $parentAmazonListingProduct->getSku();
            if (!empty($parentSku)) {
                $variationData['parent_sku'] = $parentSku;
            }

            $channelTheme = $parentAmazonListingProduct->getVariationManager()->getTypeModel()->getChannelTheme();
            if (!empty($channelTheme)) {
                $variationData['theme'] = $channelTheme;
            }

            $data['variation_data'] = $variationData;
        }

        return $data;
    }

    //########################################
}
