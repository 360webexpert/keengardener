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

namespace Mageplaza\FreeGifts\Controller\Adminhtml\Rule;

use Exception;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class MassDelete
 * @package Mageplaza\FreeGifts\Controller\Adminhtml\Rule
 */
class MassDelete extends AbstractMassAction
{
    /**
     * @return ResponseInterface|ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        $rules = $this->getRuleCollection();
        $collectionSize = $rules->getSize();
        foreach ($rules as $rule) {
            try {
                $rule->delete();
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $collectionSize));

        return $this->getResultRedirect('*/*/');
    }
}
