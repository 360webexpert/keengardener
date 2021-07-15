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
 * @package     Mageplaza_SeoCrosslinks
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoCrosslinks\Plugin;

use Exception;
use Magento\Catalog\Helper\Output;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Message\ManagerInterface;
use Mageplaza\SeoCrosslinks\Helper\Data;
use Mageplaza\SeoCrosslinks\Model\Term\Source\ApplyFor;

/**
 * Class SeoCrosslinksCatalog
 * @package Mageplaza\SeoCrosslinks\Plugin
 */
class SeoCrosslinksCatalog
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Http
     */
    protected $httpRequest;

    /**
     * SeoCrosslinksCatalog constructor.
     *
     * @param Data $helperData
     * @param ManagerInterface $messageManager
     * @param Http $httpRequest
     */
    function __construct(
        Data $helperData,
        ManagerInterface $messageManager,
        Http $httpRequest
    ) {
        $this->helperData     = $helperData;
        $this->messageManager = $messageManager;
        $this->httpRequest    = $httpRequest;
    }

    /**
     * @param Output $subject
     * @param callable $proceed
     * @param $method
     * @param $result
     * @param $params
     *
     * @return mixed
     */
    public function aroundProcess(Output $subject, callable $proceed, $method, $result, $params)
    {
        $result = $proceed($method, $result, $params);

        $fullActionName = $this->httpRequest->getFullActionName();
        if ($this->helperData->isEnableSeoCrossLinks()
            && ($params['attribute'] == 'description')
            && in_array($fullActionName, ['catalog_category_view', 'catalog_product_view'])) {
            try {
                $applyFor       = ($fullActionName == 'catalog_category_view') ? ApplyFor::CATEGORY : ApplyFor::PRODUCT;
                $termCollection = $this->helperData->getTermCollection($applyFor);
                foreach ($termCollection as $term) {
                    $this->helperData->replaceKeyword($term, $result);
                }
            } catch (Exception $e) {
                $this->messageManager->addError(__('Can\'t apply seo cross links'));
            }
        }

        return $result;
    }
}
