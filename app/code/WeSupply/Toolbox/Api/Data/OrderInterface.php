<?php
namespace WeSupply\Toolbox\Api\Data;

/**
 * Interface OrderInterface
 * @package WeSupply\Toolbox\Api\Data
 */
interface OrderInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID            = 'id';
    const ORDER_ID      = 'order_id';
    const ORDER_NUMBER  = 'order_number';
    const UPDATED_AT    = 'updated_at';
    const INFO          = 'info';
    const STORE_ID      = 'store_id';
    const IS_EXCLUDED   = 'is_excluded';
    /**#@-*/

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get identifier
     *
     * @return int
     */
    public function getOrderId();

    /**
     * Get Order Increment ID
     *
     * @return string
     */
    public function getOrderNumber();

    /**
     * Get title
     *
     * @return string|null
     */
    public function getInfo();

    /**
     * Get updated at time
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Get store id
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Set ID
     *
     * @param int $id
     * @return OrderInterface
     */
    public function setId($id);

    /**
     * Set Order ID
     *
     * @param int $id
     * @return OrderInterface
     */
    public function setOrderId($id);

    /**
     * Set Order Increment ID
     *
     * @param $id
     * @return OrderInterface
     */
    public function setOrderNumber($id);

    /**
     * Set order information
     *
     * @param string $info
     * @return OrderInterface
     */
    public function setInfo($info);

    /**
     * Set update time
     *
     * @param string $updateTime
     * @return OrderInterface
     */
    public function setUpdatedAt($updateTime);

    /**
     * Set Store ID
     *
     * @param int $id
     * @return OrderInterface
     */
    public function setStoreId($id);

    /**
     * @return mixed
     */
    public function isExcluded();

    /**
     * Set is_excluded flag
     *
     * @param bool $flag
     * @return OrderInterface
     */
    public function setIsExcluded($flag);
}
