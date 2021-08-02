<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_FreeGifts
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\FreeGifts\Plugin\Totals;

use Magento\Checkout\Model\TotalsInformationManagement;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\TotalsExtension;
use Magento\Quote\Api\Data\TotalsInterface;
use Mageplaza\FreeGifts\Model\Source\Apply;
use Mageplaza\FreeGifts\Plugin\AbstractPlugin;

/**
 * Class AfterTotalsInformation
 * @package Mageplaza\FreeGifts\Plugin
 */
class AfterTotalsInformation extends AbstractPlugin
{
    /**
     * @param TotalsInformationManagement $subject
     * @param TotalsInterface $result
     *
     * @return TotalsInterface
     * @throws NoSuchEntityException
     * @throws LocalizedException
     * @SuppressWarnings("Unused")
     */
    public function afterCalculate(TotalsInformationManagement $subject, $result)
    {
        if (!$this->isEnabled()) {
            return $result;
        }

        if ($result->getExtensionAttributes() !== null) {
            /** @var TotalsExtension $extension */
            $extension = $result->getExtensionAttributes();
            $validatedCartRules = $this->_helperRule->setExtraData(true)->setApply(Apply::CART)->getValidatedRules();
            $extension->setMpFreeGifts(array_values($validatedCartRules));
        }

        return $result;
    }
}
