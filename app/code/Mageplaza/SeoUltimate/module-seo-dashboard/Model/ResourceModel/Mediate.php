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
 * @package     Mageplaza_SeoDashboard
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoDashboard\Model\ResourceModel;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Zend_Db_Expr;
use Zend_Db_Select_Exception;

/**
 * Class Mediate
 * @package Mageplaza\SeoDashboard\Model\ResourceModel
 */
class Mediate extends AbstractDb
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('mageplaza_seodashboard_mediate_content_data', 'data_id');
    }

    /**
     * @param $data
     *
     * @throws LocalizedException
     */
    public function insertMultipleData($data)
    {
        $connection = $this->getConnection();

        $data = $this->validateData($data);
        try {
            $connection->beginTransaction();
            $connection->insertMultiple($this->getMainTable(), $data);
            $connection->commit();
        } catch (Exception $e) {
            $connection->rollBack();
        }
    }

    /**
     * @param $data
     *
     * @return array
     * @throws LocalizedException
     */
    public function validateData($data)
    {
        $fieldsTable = array_keys($this->getConnection()->describeTable($this->getMainTable()));
        $newData     = [];
        foreach ($data as $key => $item) {
            foreach ($item as $field => $value) {
                if (in_array($field, $fieldsTable)) {
                    $newData[$key][$field] = $value;
                }
            }
        }

        return $newData;
    }

    /**
     * @param $field
     * @param $entity
     *
     * @return array
     * @throws LocalizedException
     * @throws Zend_Db_Select_Exception
     */
    public function getDuplicateCollection($field, $entity)
    {
        $select = $this->getConnection()
            ->select()
            ->from($this->getMainTable(), [$field, 'store_id'])
            ->where('`entity` = \'' . $entity . '\'')
            ->where('`' . $field . '` <> \'\'')
            ->group(['store_id', $field])
            ->having('COUNT(data_id) > 1')
            ->columns(['entity_ids' => new Zend_Db_Expr("GROUP_CONCAT(entity_id SEPARATOR ',')")]);

        return $this->getConnection()->fetchAll($select);
    }
}
