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

/**
 * Class Low
 * @package Mageplaza\SeoDashboard\Model\ResourceModel
 */
class Low extends AbstractDb
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('mageplaza_seodashboard_low_count_report_issue', 'issue_id');
    }

    /**
     * @param $data
     *
     * @throws LocalizedException
     */
    public function insertMultipleData($data)
    {
        $connection = $this->getConnection();
        $data       = $this->validateData($data);
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
}
