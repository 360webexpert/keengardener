<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WeSupply\Toolbox\Plugin\Magento\Checkout\Model;

use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\QuoteRepository;
use WeSupply\Toolbox\Helper\Data as WeSupplyHelper;

/**
 * Class ShippingInformationManagement
 * @package WeSupply\Toolbox\Plugin\Magento\Checkout\Model
 */
class ShippingInformationManagement
{
    protected $quoteRepository;

    protected $dataHelper;

    /**
     * @var WeSupplyHelper
     */
    protected $helper;

    /**
     * ShippingInformationManagement constructor.
     * @param WeSupplyHelper $helper
     * @param QuoteRepository $quoteRepository
     */
    public function __construct(
        WeSupplyHelper $helper,
        QuoteRepository $quoteRepository
    )
    {
        $this->helper = $helper;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param $cartId
     * @param ShippingInformationInterface $addressInformation
     * @throws NoSuchEntityException
     */
    public function beforeSaveAddressInformation (
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        ShippingInformationInterface $addressInformation
    )
    {
        if (
            !$this->helper->getWeSupplyEnabled() ||
            !$this->helper->getDeliveryEstimationsEnabled() ||
            !$extensionAttributes = $addressInformation->getExtensionAttributes()
        ) {
            return;
        }

        if ($quote = $this->quoteRepository->getActive($cartId)) {
//            $quote->setDeliveryRequestId($extensionAttributes->getSelectedDeliveryRequestId());
            $quote->setDeliveryTimestamp($extensionAttributes->getSelectedDeliveryTimestamp());
        }
    }
}
