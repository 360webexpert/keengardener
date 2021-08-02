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

namespace Mageplaza\FreeGifts\Controller\Gift;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable as TypeConfigurable;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\FreeGifts\Block\Gift\Option as GiftOption;

/**
 * Class Option
 * @package Mageplaza\FreeGifts\Controller\Gift
 */
class Option extends AbstractGift
{
    /**
     * @return ResponseInterface|Json|ResultInterface
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $html = '';
        $data = $this->getRequestParams();
        $productGift = $this->getHelperGift()->getProductById($data['gift_id']);

        if ((int)$productGift->getRequiredOptions() || $productGift->getTypeId() === TypeConfigurable::TYPE_CODE) {
            $this->_view->loadLayout();

            $this->_registry->register('product', $productGift);
            $this->_registry->register('current_product', $productGift);

            /** @var GiftOption $optionBlock */
            $optionBlock = $this->_view->getLayout()->getBlock('mpfreegifts_option');
            $html = $optionBlock->setRuleId($data['rule_id'])->toHtml();
        }

        if ($html !== '') {
            return $this->_json->setData([
                'option' => true,
                'html' => $html,
            ]);
        }

        return $this->addGift();
    }
}
