<?php
namespace WeSupply\Toolbox\Model;

use WeSupply\Toolbox\Api\Data\OrderInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Wesupply Order
 * @package WeSupply\Toolbox\Model
 */
class Order extends AbstractModel implements OrderInterface, IdentityInterface
{
    /**
     * CMS block cache tag
     */
    const CACHE_TAG = 'wesupply_order';

    /**#@-*/
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'wesupply_order';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\WeSupply\Toolbox\Model\ResourceModel\Order::class);
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId(), self::CACHE_TAG . '_' . $this->getIdentifier()];
    }

    /**
     * Retrieve wesupply order id
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Retrieve block id
     *
     * @return string
     */
    public function getOrderId()
    {
        return (string)$this->getData(self::ORDER_ID);
    }

    /**
     * Retrieve block id
     *
     * @return string
     */
    public function getOrderNumber()
    {
        return (string)$this->getData(self::ORDER_NUMBER);
    }

    /**
     * Retrieve order info
     *
     * @return string
     */
    public function getInfo()
    {
        return $this->getData(self::INFO);
    }

    /**
     * Retrieve order update time
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * Get store id
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * Get Awaiting Update flag
     *
     * @return bool
     */
    public function getAwaitingUpdate()
    {
        return $this->getData(self::AWAITING_UPDATE);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return OrderInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Set order id
     *
     * @param string $id
     * @return OrderInterface
     */
    public function setOrderId($id)
    {
        return $this->setData(self::ORDER_ID, $id);
    }
    /**
     * Set order increment id
     *
     * @param string $id
     * @return OrderInterface
     */
    public function setOrderNumber($id)
    {
        return $this->setData(self::ORDER_NUMBER, $id);
    }

    /**
     * Set info
     *
     * @param string $info
     * @return OrderInterface
     */
    public function setInfo($info)
    {
        return $this->setData(self::INFO, $info);
    }

    /**
     * Set update time
     *
     * @param string $updateTime
     * @return OrderInterface
     */
    public function setUpdatedAt($updateTime)
    {
        return $this->setData(self::UPDATED_AT, $updateTime);
    }

    /**
     * Set Store ID
     *
     * @param int $id
     * @return OrderInterface
     */
    public function setStoreId($id) {
        return $this->setData(self::STORE_ID, $id);
    }

    /**
     * Set Awaiting Update flag
     *
     * @param bool $awaiting
     *
     * @return OrderInterface|Order
     */
    public function setAwaitingUpdate($awaiting)
    {
        return $this->setData(self::AWAITING_UPDATE, $awaiting);
    }

    /**
     * Check if order is excluded from export
     *
     * @return bool
     */
    public function isExcluded()
    {
        return $this->getData(self::IS_EXCLUDED);
    }

    /**
     * Set is excluded flag
     *
     * @param bool $flag
     * @return OrderInterface|Order
     */
    public function setIsExcluded($flag)
    {
        return $this->setData(self::IS_EXCLUDED, $flag);
    }
}
