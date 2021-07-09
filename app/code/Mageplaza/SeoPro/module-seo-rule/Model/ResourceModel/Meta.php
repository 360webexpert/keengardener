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
 * @package     Mageplaza_SeoRule
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoRule\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Meta
 * @package Mageplaza\SeoRule\Model\ResourceModel
 */
class Meta extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mageplaza_seorule_meta', 'meta_id');
    }

    /**
     * @param $data
     *
     * @return $this
     * @throws LocalizedException
     */
    public function applyRule($data)
    {
        if (empty($data)) {
            return $this;
        }

        $data = $this->validateData($data);
        $this->getConnection()->insertMultiple($this->getMainTable(), $data);

        return $this;
    }

    /**
     * @throws LocalizedException
     */
    public function truncateData()
    {
        $this->getConnection()->truncateTable($this->getMainTable());
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
