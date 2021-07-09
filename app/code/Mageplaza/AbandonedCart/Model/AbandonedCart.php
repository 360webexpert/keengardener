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
 * @package     Mageplaza_AbandonedCart
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AbandonedCart\Model;

use Exception;
use Magento\Customer\Model\CustomerFactory;
use Magento\Email\Model\Template;
use Magento\Framework\App\Area;
use Magento\Framework\App\AreaList;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Mail\Template\FactoryInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\TemplateInterface;
use Magento\Framework\Math\Random;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;
use Magento\SalesRule\Api\Data\CouponGenerationSpecInterfaceFactory;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\SalesRule\Model\CouponFactory;
use Magento\SalesRule\Model\Service\CouponManagementService;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\AbandonedCart\Helper\Data;
use Psr\Log\LoggerInterface;
use Zend_Serializer_Exception;

/**
 * Class AbandonedCart
 * @package Mageplaza\AbandonedCart\Model
 */
class AbandonedCart
{
    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Date model
     *
     * @var DateTime
     */
    protected $date;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var CouponGenerationSpecInterfaceFactory
     */
    protected $generationSpecFactory;

    /**
     * @var CouponManagementService
     */
    protected $couponManagementService;

    /**
     * @var Random
     */
    protected $mathRandom;

    /**
     * @var Token
     */
    protected $abandonedCartToken;

    /**
     * @var LogsFactory
     */
    protected $abandonedCartLogs;

    /**
     * Object manager
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var CouponFactory
     */
    protected $couponFactory;

    /**
     * @var Timezone
     */
    protected $templateFactory;

    /**
     * @var RuleRepositoryInterface
     */
    protected $ruleRepositoryInterface;

    /**
     * @var array Coupon config for stores
     */
    protected $couponConfigs = [];

    /**
     * @var AreaList
     */
    protected $areaList;

    /**
     * @var Template
     */
    protected $emailTemplate;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var ResolverInterface
     */
    protected $localeResolver;

    /**
     * AbandonedCart constructor.
     *
     * @param QuoteFactory $quoteFactory
     * @param Data $helper
     * @param LoggerInterface $logger
     * @param DateTime $date
     * @param StoreManagerInterface $storeManager
     * @param TransportBuilder $transportBuilder
     * @param Random $mathRandom
     * @param CustomerFactory $customerFactory
     * @param Token $abandonedCartToken
     * @param LogsFactory $abandonedCartLogs
     * @param ObjectManagerInterface $objectManager
     * @param CouponFactory $couponFactory
     * @param FactoryInterface $templateFactory
     * @param RuleRepositoryInterface $ruleRepositoryInterface
     * @param CouponManagementService $couponManagementService
     * @param CouponGenerationSpecInterfaceFactory $generationSpecFactory
     * @param AreaList $areaList
     * @param Template $emailTemplate
     * @param Escaper $escaper
     * @param TimezoneInterface $timezone
     * @param ResolverInterface $localeResolver
     */
    public function __construct(
        QuoteFactory $quoteFactory,
        Data $helper,
        LoggerInterface $logger,
        DateTime $date,
        StoreManagerInterface $storeManager,
        TransportBuilder $transportBuilder,
        Random $mathRandom,
        CustomerFactory $customerFactory,
        Token $abandonedCartToken,
        LogsFactory $abandonedCartLogs,
        ObjectManagerInterface $objectManager,
        CouponFactory $couponFactory,
        FactoryInterface $templateFactory,
        RuleRepositoryInterface $ruleRepositoryInterface,
        CouponManagementService $couponManagementService,
        CouponGenerationSpecInterfaceFactory $generationSpecFactory,
        AreaList $areaList,
        Template $emailTemplate,
        Escaper $escaper,
        TimezoneInterface $timezone,
        ResolverInterface $localeResolver
    ) {
        $this->objectManager           = $objectManager;
        $this->quoteFactory            = $quoteFactory;
        $this->helper                  = $helper;
        $this->date                    = $date;
        $this->logger                  = $logger;
        $this->storeManager            = $storeManager;
        $this->transportBuilder        = $transportBuilder;
        $this->customerFactory         = $customerFactory;
        $this->mathRandom              = $mathRandom;
        $this->abandonedCartToken      = $abandonedCartToken;
        $this->abandonedCartLogs       = $abandonedCartLogs;
        $this->generationSpecFactory   = $generationSpecFactory;
        $this->couponManagementService = $couponManagementService;
        $this->couponFactory           = $couponFactory;
        $this->templateFactory         = $templateFactory;
        $this->ruleRepositoryInterface = $ruleRepositoryInterface;
        $this->areaList                = $areaList;
        $this->emailTemplate           = $emailTemplate;
        $this->escaper                 = $escaper;
        $this->timezone                = $timezone;
        $this->localeResolver          = $localeResolver;
    }

    /**
     * Prepare data for abandoned cart
     *
     * @throws Zend_Serializer_Exception
     * @throws NoSuchEntityException
     */
    public function prepareForAbandonedCart()
    {
        foreach ($this->storeManager->getStores() as $store) {
            if ($this->helper->isEnabled($store->getId())) {
                $this->prepareForStore($store->getId());
            }
        }
    }

    /**
     * @param $storeId
     *
     * @return $this
     * @throws Zend_Serializer_Exception
     * @throws NoSuchEntityException
     */
    public function prepareForStore($storeId)
    {
        $configs = $this->helper->getEmailConfig($storeId);
        if (empty($configs)) {
            return $this;
        }

        $day      = 86400;
        $current  = strtotime($this->date->date());
        $lastSend = $current - max(array_column($configs, 'send')) - $day;

        $quoteCollection = $this->quoteFactory->create()
            ->getCollection()
            ->addFieldToFilter('items_count', ['neq' => '0'])
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('store_id', $storeId)
            ->addFieldToFilter('customer_email', ['neq' => null])
            ->addFieldToFilter(
                [
                    'created_at',
                    'updated_at'
                ],
                [
                    ['gteq' => $this->date->date('Y-m-d H:i:s', $lastSend)],
                    ['gteq' => $this->date->date('Y-m-d H:i:s', $lastSend)]
                ]
            )->setOrder('updated_at');

        /** @var Quote $quote */
        foreach ($quoteCollection as $quote) {
            $quoteUpdatedTime = strtotime($quote->getUpdatedAt());
            if ($quoteUpdatedTime < 0) {
                $quoteUpdatedTime = strtotime($quote->getCreatedAt());
            }
            $quoteId = $quote->getId();
            foreach ($configs as $configId => $config) {
                $validateEmail = $this->abandonedCartToken->validateEmail($quote, $configId);
                $time          = $quoteUpdatedTime + $config['send'];
                if ($validateEmail && $time <= $current) {
                    $coupon = [];
                    if ((bool) $config['coupon']) {
                        try {
                            $coupon = $this->createCoupon($quote->getStoreId());
                        } catch (Exception $e) {
                            $this->logger->critical($e);
                        }
                    }
                    $newCartToken = $this->mathRandom->getUniqueHash();
                    $this->sendMail($quote, $config, $newCartToken, $coupon);
                    $this->abandonedCartToken->saveToken($quoteId, $configId, $newCartToken);
                }
            }
        }

        return $this;
    }

    /**
     * Send abandoned cart email
     *
     * @param Quote $quote
     * @param $config
     * @param $newCartToken
     * @param array $coupon
     *
     * @throws NoSuchEntityException
     */
    public function sendMail($quote, $config, $newCartToken, $coupon = [])
    {
        $customerEmail = $quote->getCustomerEmail();
        $customerName  = trim($quote->getFirstname() . ' ' . $quote->getLastname());

        if (!$customerName) {
            $customer = $quote->getCustomerId() ? $quote->getCustomer() : null;
            if ($customer && $customer->getId()) {
                $customerName = trim($customer->getFirstname() . ' ' . $customer->getLastname());
            } else {
                $customerName = explode('@', $customerEmail)[0];
            }
        }

        $couponCode = isset($coupon['coupon_code']) ? $coupon['coupon_code'] : '';

        /** @var Store $store */
        $store = $this->storeManager->getStore($quote->getStoreId());

        /** @var TemplateInterface $template */
        $template = $this->templateFactory->get($config['template'])
            ->setOptions(['area' => Area::AREA_FRONTEND, 'store' => $store->getId()]);

        $vars = [
            'quoteId'       => $quote->getId(),
            'customer_name' => ucfirst($customerName),
            'coupon_code'   => $couponCode,
            'to_date'       => isset($coupon['to_date']) ? $this->getCreatedAtFormatted(
                $coupon['to_date'],
                2,
                $store
            ) : '',
            'sender'        => $config['sender'],
            'checkout_url'  => $template->getUrl($store, 'abandonedcart/checkout/cart', [
                'id'      => $quote->getId(),
                'token'   => $newCartToken,
                '_nosid'  => true,
                '_query'  => $this->helper->getUrlSuffix($store),
                '_secure' => $store->isUrlSecure()
            ])
        ];

        $areaObject = $this->areaList->getArea($this->emailTemplate->getDesignConfig()->getArea());
        $areaObject->load(Area::PART_TRANSLATE);

        $transport = $this->transportBuilder->setTemplateIdentifier($config['template'])
            ->setTemplateOptions(['area' => Area::AREA_FRONTEND, 'store' => $store->getId()])
            ->setFrom($config['sender'])
            ->addTo($customerEmail, $customerName)
            ->setTemplateVars($vars)
            ->getTransport();

        try {
            $transport->sendMessage();
            $success = true;
        } catch (Exception $e) {
            $success = false;
            $this->logger->error($e->getMessage());
        }

        if (!isset($config['ignore_log'])) {
            $emailBody = $template->setVars($vars)->processTemplate();

            $subject = $this->escaper->escapeHtml($template->getSubject());
            $this->abandonedCartLogs->create()->saveLogs(
                $quote,
                $customerEmail,
                $customerName,
                $config['sender'],
                $subject,
                $emailBody,
                $success,
                $couponCode
            );
        }
    }

    /**
     * @param Logs $log
     *
     * @throws MailException
     * @throws NoSuchEntityException
     */
    public function sendAgain($log)
    {
        $store = $this->storeManager->getStore();
        $this->transportBuilder->setTemplateIdentifier('send_again')
            ->setTemplateOptions(['area' => Area::AREA_FRONTEND, 'store' => $store->getId()])
            ->setTemplateVars([
                'body'    => htmlspecialchars_decode($log->getEmailContent()),
                'subject' => $log->getSubject()
            ])
            ->setFrom($log->getSender())
            ->addTo($log->getCustomerEmail(), $log->getCustomerName())
            ->getTransport()
            ->sendMessage();

        $log->setUpdatedAt($this->date->date());
        $log->setStatus(true);
    }

    /**
     * Generate Coupon Code by Configuration
     *
     * @param null $storeId
     *
     * @return $this|array
     * @throws InputException
     * @throws LocalizedException
     * @throws Exception
     */
    public function createCoupon($storeId = null)
    {
        $coupon       = [];
        $couponConfig = $this->getCouponConfig($storeId);
        if (!empty($couponConfig)) {
            $couponSpec  = $this->generationSpecFactory->create(['data' => $couponConfig]);
            $couponCodes = $this->couponManagementService->generate($couponSpec);
            $couponCode  = $couponCodes[0];

            $coupon = $this->couponFactory->create()->loadByCode($couponCode);
            $coupon->setMpGeneratedByAbandonedCart(1);
            if ($couponConfig['valid']) {
                $expirationDate = strtotime($this->date->date()) + $couponConfig['valid'] * 3600;
                if (!$coupon->getMpAceExpiresAt()
                    || ($coupon->getMpAceExpiresAt() && strtotime($coupon->getMpAceExpiresAt()) > $expirationDate)) {
                    try {
                        $coupon->setMpAceExpiresAt($this->date->date('Y-m-d H:i:s', $expirationDate))->save();
                    } catch (Exception $e) {
                        $this->logger->critical($e);
                    }
                }
            }
            if ($couponCode) {
                $coupon = [
                    'coupon_code' => $couponCode,
                    'to_date'     => $coupon->getMpAceExpiresAt() ?: ''
                ];
            }
        }

        return $coupon;
    }

    /**
     * @param $storeId
     *
     * @return mixed
     */
    protected function getCouponConfig($storeId)
    {
        if (!isset($this->couponConfigs[$storeId])) {
            $couponConfig = [];
            if ($ruleId = $this->helper->getCouponConfig('rule', $storeId)) {
                $couponConfig = [
                    'rule_id'  => $ruleId,
                    'quantity' => 1,
                    'length'   => (int) $this->helper->getCouponConfig('length', $storeId) ?: 5,
                    'format'   => $this->helper->getCouponConfig('format', $storeId),
                    'prefix'   => $this->helper->getCouponConfig('prefix', $storeId),
                    'suffix'   => $this->helper->getCouponConfig('suffix', $storeId),
                    'dash'     => (int) $this->helper->getCouponConfig('dash', $storeId),
                    'valid'    => (int) $this->helper->getCouponConfig('valid', $storeId)
                ];
            }
            $this->couponConfigs[$storeId] = $couponConfig;
        }

        return $this->couponConfigs[$storeId];
    }

    /**
     * @param string $date
     * @param int $format
     * @param Store $store
     *
     * @return string
     * @throws Exception
     */
    public function getCreatedAtFormatted($date, $format, $store)
    {
        return $this->timezone->formatDateTime(
            new \DateTime($date),
            $format,
            $format,
            $this->localeResolver->getDefaultLocale(),
            $this->timezone->getConfigTimezone('store', $store)
        );
    }
}
