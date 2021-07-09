<?php

namespace WeSupply\Toolbox\Setup;

use Magento\Catalog\Model\Product;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use WeSupply\Toolbox\Logger\Logger as Logger;

use Magento\Catalog\Setup\CategorySetupFactory;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * WeSupply tracking page url
     */
    const WESUPPLY_TRACKING_ID = 'wesupply-tracking-info';

    /**
     * WeSupply store locator page url
     */
    const WESUPPLY_STORE_LOCATOR_ID = 'wesupply-store-locator';

    /**
     * WeSupply store-details page url
     */
    const WESUPPLY_STORE_DETAILS_ID = 'wesupply-store-details';

    /**
     * @var State
     */
    private $state;

    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * @var CategorySetupFactory
     */
    private $catalogSetupFactory;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var WriterInterface
     */
    protected $configWriter;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * UpgradeData constructor.
     * @param WriterInterface $configWriter
     * @param ScopeConfigInterface $scopeConfig
     * @param PageFactory $pageFactory
     * @param PageRepositoryInterface $pageRepository
     * @param StoreManagerInterface $storeManager
     * @param Logger $logger
     * @param State $state
     * @param CategorySetupFactory $categorySetupFactory
     * @throws LocalizedException
     */
    public function __construct(
        WriterInterface $configWriter,
        ScopeConfigInterface $scopeConfig,
        PageFactory $pageFactory,
        PageRepositoryInterface $pageRepository,
        StoreManagerInterface $storeManager,
        Logger $logger,
        State $state,
        CategorySetupFactory $categorySetupFactory
    ) {
        $this->configWriter = $configWriter;
        $this->scopeConfig = $scopeConfig;
        $this->state = $state;
        $areaCode = null;
        try {
            $areaCode = $this->state->getAreaCode();
        } catch (\Exception $ex) {
        }
        if (!$areaCode) {
            $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
        }

        $this->pageFactory = $pageFactory;
        $this->pageRepository = $pageRepository;
        $this->storeManager = $storeManager;
        $this->logger = $logger;

        $this->catalogSetupFactory = $categorySetupFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Exception
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.3') < 0) {
            $cmsPages = [
                [
                    'title' => 'Tracking Info',
                    'identifier' => $this->getTrackingPageIdentifier()
                ],
                [
                    'title' => 'Store Locator',
                    'identifier' => $this->getStoreLocatorPageIdentifier()
                ],
                [
                    'title' => 'Store Details',
                    'identifier' => $this->getStoreDetailsPageIdentifier()
                ]
            ];

            foreach ($cmsPages as $pageData) {
                $page = $this->pageFactory->create()
                    ->setTitle($pageData['title'])
                    ->setIdentifier($pageData['identifier'])
                    ->setIsActive(true)
                    ->setPageLayout('1column')
                    ->setStores([0])
                    ->setContent($this->createIframeContainer());

                try {
                    $page->save();
                } catch (\Exception $e) {
                    $message = __('WeSupply_Toolbox is trying to create a cms page with URL key "%1" but this identifier already exists!', $pageData['identifier']);
                    $this->logger->addNotice($message . ' ' . $e->getMessage());
                }
            }

            /**
             * since 1.0.3 wesupply_subdomaine was moved from step_1 to step_2
             * so we have to copy the old saved value into the new config path if exists
             */
            if ($wesupplySubdomain = $this->scopeConfig->getValue('wesupply_api/step_1/wesupply_subdomain', ScopeInterface::SCOPE_STORE)) {
                $this->configWriter->save('wesupply_api/step_2/wesupply_subdomain', $wesupplySubdomain, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0);
            }
        }

        if (version_compare($context->getVersion(), '1.0.4') < 0) {
            /**
             * delete 'wesupply-tracking-info' cms page as we do not use it anymore
             */
            $existingPage = $this->pageFactory->create()->load($this->getTrackingPageIdentifier());
            if ($existingPage->getId()) {
                try {
                    $this->pageRepository->deleteById($existingPage->getId());
                } catch (NoSuchEntityException $e) {
                    $message = __('WeSupply_Toolbox is trying to delete an existing cms page with URL key "%1" but an unknown error occurred! Please delete it manually if exists.', $this->getTrackingPageIdentifier());
                    $this->logger->addNotice($message . ' ' . $e->getMessage());
                }
            }
        }

        if (version_compare($context->getVersion(), '1.0.5') < 0) {

            $attributeName = 'wesupply_estimation_display';
            /** @var \Magento\Catalog\Setup\CategorySetup $categorySetup */
            $catalogSetup = $this->catalogSetupFactory->create(['setup' => $setup]);

            $catalogSetup->addAttribute(Product::ENTITY, $attributeName, [
                'type' => 'int',
                'label' => 'Display WeSupply Delivery Estimation',
                'input' => 'select',
                'required' => false,
                'sort_order' => 10,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'wysiwyg_enabled' => false,
                'is_html_allowed_on_front' => false,
                'group' => 'WeSupply Options',
                'default' => 1,
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'note' => 'WeSupply Delivery Estimation will not be displayed if the WeSupply Toolbox module is disabled.'
            ]);
        }

        if (version_compare($context->getVersion(), '1.0.6') < 0) {

            $connected = 0;
            $allStores = $this->getAllStores();

            foreach ($allStores as $storeId) {
                $isEnabled = $this->scopeConfig->getValue('wesupply_api/integration/wesupply_enabled', 'stores', $storeId);
                if ($isEnabled) {
                    $connected++;
                    $this->configWriter->save('wesupply_api/step_1/wesupply_connection_status', 1, 'stores', $storeId);
                }

                if ($connected == count($allStores)) {
                    $this->configWriter->save('wesupply_api/step_1/wesupply_connection_status', 1, 'default', 0);
                }
            }
        }

        if (version_compare($context->getVersion(), '1.0.8') < 0) {
            /**
             * since 1.0.8 step_1 and step_2 groups were removed
             * so we have to copy the old saved value into the new config path
             */
            $allStores = $this->getAllStores();
            array_push($allStores, 0); // id for default scope

            $preserveSettings = [
                'step_1' => [
                    'wesupply_client_id',
                    'wesupply_client_secret',
                    'wesupply_connection_status'
                ],
                'step_2' => [
                    'wesupply_subdomain',
                    'access_key'
                ]
            ];

            $copiedClientName = false;
            foreach ($allStores as $storeId) {
                foreach ($preserveSettings as $group => $fields) {
                    foreach ($fields as $field) {
                        $scopeType = $storeId ? ScopeInterface::SCOPE_STORE : ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
                        if ($existing = $this->scopeConfig->getValue('wesupply_api/' . $group . '/' . $field, $scopeType, $storeId)) {
                            $this->configWriter->save('wesupply_api/integration/' . $field, $existing, $scopeType, $storeId);
                            if ($field == 'wesupply_subdomain') {
                                $copiedClientName = true;
                            }
                        }
                    }
                }
            }

            /**
             * Auto generate and save a default Access Key if this is a first install
             */
            if (!$copiedClientName) {
                $this->configWriter->save('wesupply_api/integration/' . 'access_key', $this->random_str(40), 'default', '0');
            }
        }

        $setup->endSetup();
    }

    /**
     * @return array
     */
    private function getAllStores()
    {
        return array_values(array_map(function ($store) {
            return $store->getStoreId();
        }, $this->storeManager->getStores()));
    }

    /**
     * @return string
     */
    private function getTrackingPageIdentifier()
    {
        return self::WESUPPLY_TRACKING_ID;
    }

    /**
     * @return string
     */
    private function getStoreLocatorPageIdentifier()
    {
        return self::WESUPPLY_STORE_LOCATOR_ID;
    }

    /**
     * @return string
     */
    private function getStoreDetailsPageIdentifier()
    {
        return self::WESUPPLY_STORE_DETAILS_ID;
    }

    /**
     * @return string
     */
    private function createIframeContainer()
    {
        $container  = '<!-- Do not delete or edit this container -->' . "\n";
        $container .= '<div class="embedded-iframe-container"></div>';

        return $container;
    }

    /**
     * @param $length
     * @param string $keyspace
     * @return string
     * @throws \Exception
     */
    private function random_str($length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-')
    {
        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces []= $keyspace[random_int(0, $max)];
        }
        return implode('', $pieces);
    }
}
