<?php

/**
 * OpenMage
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available at https://opensource.org/license/osl-3-0-php
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @copyright  Copyright (c) 2006-2020 Magento, Inc. (https://www.magento.com)
 * @copyright  Copyright (c) 2019-2024 The OpenMage Contributors (https://www.openmage.org)
 * @license    https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog product tier price backend attribute model
 *
 * @category   Mage
 * @package    Mage_Catalog
 */
class Mage_Catalog_Model_Resource_Product_Attribute_Backend_Tierprice extends Mage_Catalog_Model_Resource_Product_Attribute_Backend_Groupprice_Abstract
{
    protected function _construct()
    {
        $this->_init('catalog/product_attribute_tier_price', 'value_id');
    }

    /**
     * Add qty column
     *
     * @param array $columns
     * @return array
     */
    protected function _loadPriceDataColumns($columns)
    {
        $columns = parent::_loadPriceDataColumns($columns);
        $columns['price_qty'] = 'qty';
        return $columns;
    }

    /**
     * Order by qty
     *
     * @param Varien_Db_Select $select
     * @return Varien_Db_Select
     */
    protected function _loadPriceDataSelect($select)
    {
        $select->order('qty');
        return $select;
    }

    /**
     * Load product tier prices
     *
     * @deprecated since 1.3.2.3
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     * @return array
     */
    public function loadProductPrices($product, $attribute)
    {
        $websiteId = null;
        if ($attribute->isScopeGlobal()) {
            $websiteId = 0;
        } elseif ($product->getStoreId()) {
            $websiteId = Mage::app()->getStore($product->getStoreId())->getWebsiteId();
        }

        return $this->loadPriceData($product->getId(), $websiteId);
    }

    /**
     * Delete product tier price data from storage
     *
     * @deprecated since 1.3.2.3
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     * @return $this
     */
    public function deleteProductPrices($product, $attribute)
    {
        $websiteId = null;
        if (!$attribute->isScopeGlobal()) {
            $storeId = $product->getProductId();
            if ($storeId) {
                $websiteId = Mage::app()->getStore($storeId)->getWebsiteId();
            }
        }

        $this->deletePriceData($product->getId(), $websiteId);

        return $this;
    }

    /**
     * Insert product Tier Price to storage
     *
     * @deprecated since 1.3.2.3
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $data
     * @return $this
     */
    public function insertProductPrice($product, $data)
    {
        $priceObject = new Varien_Object($data);
        $priceObject->setEntityId($product->getId());

        return $this->savePriceData($priceObject);
    }
}
