<?php

namespace Ebizmarts\SagePaySuite\Setup;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Config\ConfigOptionsListConstants;

class SplitDatabaseConnectionProvider
{
    /** @var ResourceConnection */
    private $resource;

    /** @var DeploymentConfig  */
    private $deploymentConfig;

    public function __construct(ResourceConnection $resource, DeploymentConfig $deploymentConfig)
    {
        $this->resource = $resource;
        $this->deploymentConfig = $deploymentConfig;
    }

    public function getSalesConnection(SchemaSetupInterface $setup)
    {
        if ($this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTIONS . "/sales")) {
            $connection = $this->resource->getConnectionByName("sales");
        } else {
            $connection = $setup->getConnection();
        }

        return $connection;
    }
}
