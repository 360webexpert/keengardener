<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Walmart\Listing\Product\Action\Type\Revise;

/**
 * Class \Ess\M2ePro\Model\Walmart\Listing\Product\Action\Type\Revise\Validator
 */
class Validator extends \Ess\M2ePro\Model\Walmart\Listing\Product\Action\Type\Validator
{
    //########################################

    /**
     * @return bool
     */
    public function validate()
    {
        if (!$this->validateMagentoProductType()) {
            return false;
        }

        if (!$this->validateSku()) {
            return false;
        }

        if (!$this->validateCategory()) {
            return false;
        }

        if (!$this->validateMissedOnChannelBlocked()) {
            return false;
        }

        if (!$this->validateOnlinePriceInvalidBlocked()) {
            return false;
        }

        if (!$this->validateGeneralBlocked()) {
            return false;
        }

        if ($this->getVariationManager()->isRelationParentType() && !$this->validateParentListingProduct()) {
            return false;
        }

        if (!$this->validatePhysicalUnitAndSimple()) {
            return false;
        }

        if ($this->getVariationManager()->isPhysicalUnit() && !$this->validatePhysicalUnitMatching()) {
            return false;
        }

        if (!$this->getListingProduct()->isListed() || !$this->getListingProduct()->isRevisable()) {
            $this->addMessage('Item is not Listed or not available');
            return false;
        }

        if (!$this->validateProductIds()) {
            return false;
        }

        if (!$this->validateStartEndDates()) {
            return false;
        }

        if (!$this->validateQty()) {
            return false;
        }

        if (!$this->validatePrice()) {
            return false;
        }

        if (!$this->validatePromotions()) {
            return false;
        }

        if (!$this->validatePriceAndPromotionsFeedBlocked()) {
            return false;
        }

        return true;
    }

    //########################################

    protected function validateParentListingProduct()
    {
        return true;
    }

    //########################################
}
