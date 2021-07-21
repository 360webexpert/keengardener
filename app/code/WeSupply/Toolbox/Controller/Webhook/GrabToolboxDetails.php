<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WeSupply\Toolbox\Controller\Webhook;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use WeSupply\Toolbox\Helper\Data as Helper;

/**
 * Class VersionsCheck
 *
 * @package WeSupply\Toolbox\Controller\Webhook
 */
class GrabToolboxDetails extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var JsonSerializer
     */
    protected $jsonSerializer;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var ComponentRegistrarInterface
     */
    protected $componentRegistrar;

    /**
     * @var ReadFactory
     */
    protected $readFactory;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * Module name
     */
    const MODULE_NAME = 'WeSupply_Toolbox';

    /**
     * Module current versions
     */
    const MODULE_VERSIONS = 'https://www.weltpixel.com/weltpixel_extensions.json';

    /**
     * VersionsCheck constructor.
     *
     * @param Context                     $context
     * @param JsonFactory                 $jsonFactory
     * @param JsonSerializer              $jsonSerializer
     * @param ProductMetadataInterface    $productMetadata
     * @param ComponentRegistrarInterface $componentRegistrar
     * @param ReadFactory                 $readFactory
     * @param Helper                      $helper
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        JsonSerializer $jsonSerializer,
        ProductMetadataInterface $productMetadata,
        ComponentRegistrarInterface $componentRegistrar,
        ReadFactory $readFactory,
        Helper $helper
    ) {
        $this->resultJsonFactory = $jsonFactory;
        $this->jsonSerializer = $jsonSerializer;
        $this->productMetadata = $productMetadata;
        $this->componentRegistrar = $componentRegistrar;
        $this->readFactory = $readFactory;
        $this->helper = $helper;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     * @throws ValidatorException
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();

        $toolboxDetails = $this->getToolboxDetails();
        $toolboxLatestVersion = $this->getModuleLatestVersion();
        $toolboxStatus = $this->getToolboxStatus();
        $connectionStatus = $this->getConnectionStatus();
        $magentoVersion = $this->getMagentoVersion();

        return $resultJson->setData(
            array_merge(
                $toolboxDetails,
                $toolboxLatestVersion,
                $toolboxStatus,
                $connectionStatus,
                $magentoVersion
            )
        );
    }

    /**
     * @return array
     */
    private function getMagentoVersion()
    {
        return [
            'magento_version' => $this->productMetadata->getVersion()
        ];
    }

    /**
     * @return array
     * @throws ValidatorException
     */
    private function getToolboxDetails()
    {
        $res = [
            'toolbox_version' => 'Unknown',
            'magento_compatibility' => 'Unknown',
        ];
        try {
            $path = $this->componentRegistrar->getPath(
                ComponentRegistrar::MODULE,
                self::MODULE_NAME
            );

            $dirReader = $this->readFactory->create($path);
            $composerJsonData = $dirReader->readFile('composer.json');

            $moduleData = $this->jsonSerializer->unserialize($composerJsonData);
            if (is_array($moduleData)) {
                if (!empty($moduleData['version'])) {
                    $res['toolbox_version'] = $moduleData['version'];
                }
                if (!empty($moduleData['description'])) {
                    $descriptionArr = explode('-', $moduleData['description']);
                    $res['magento_compatibility'] = end($descriptionArr);
                }
            }

            return $res;

        } catch (FileSystemException $e) {
            return [
                'toolbox_version' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * @return false|mixed
     */
    public function getModuleLatestVersion()
    {
        $curl = curl_init(self::MODULE_VERSIONS);

        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $version = false;
        $response = curl_exec($curl);
        if ($response !== false) {
            $latestVersions = json_decode($response, true);
            if (!$this->isSetModuleVersion($latestVersions)) {
                // log error and exit
                $this->logger->error('Cannot get modules latest versions.');
                return false;
            }

            $version = $latestVersions['modules'][self::MODULE_NAME]['version'];
        }

        curl_close($curl);

        return ['latest_toolbox_version' => $version];
    }

    /**
     * @param $latestVersions
     *
     * @return bool
     */
    private function isSetModuleVersion($latestVersions)
    {
        return isset($latestVersions['modules'][self::MODULE_NAME]['version']);
    }

    /**
     * @return array
     */
    private function getToolboxStatus()
    {
        return [
            'toolbox_status' => $this->helper
                ->getConfigDataByPath('wesupply_api/integration/wesupply_enabled')
        ];
    }

    /**
     * @return array
     */
    private function getConnectionStatus()
    {
        return [
            'connection_status' => $this->helper->getConnectionStatusByScope()
        ];
    }
}
