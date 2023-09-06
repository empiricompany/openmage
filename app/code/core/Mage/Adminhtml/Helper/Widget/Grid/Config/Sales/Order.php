<?php

/**
 * OpenMage
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available at https://opensource.org/license/osl-3-0-php
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @copyright  Copyright (c) 2006-2020 Magento, Inc. (https://www.magento.com)
 * @copyright  Copyright (c) 2019-2023 The OpenMage Contributors (https://www.openmage.org)
 * @license    https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml Catalog Grid Config Advanced Helper
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Helper_Widget_Grid_Config_Sales_Order extends Mage_Adminhtml_Helper_Widget_Grid_Config_Abstract
{
    /**
     * Collection object
     * @param Mage_Sales_Model_Resource_Order_Grid_Collection $collection
     *
     * return $this
     */
    public function applyAdvancedGridCollection($collection)
    {
        /* if (!$this->isEnabled()) {
            return $this;
        } */
        $collection->getSelect()->joinLeft(
            array('sales_order' => $collection->getTable('sales/order')),
            'sales_order.entity_id = main_table.entity_id',
            array('sales_order.customer_email'),
        );

        return $this;
    }

    
    /**
     * Adminhtml grid widget block
     * @param Mage_Adminhtml_Block_Widget_Grid $gridBlock
     *
     * return $this
     */
    public function applyAdvancedGridColumn($gridBlock)
    {
        
        $gridBlock->addColumn('order.customer_email', array(
            'header' => Mage::helper('sales')->__('Customer Email'),
            'index' => 'customer_email',
            'filter_index' => 'sales_order.customer_email',
            'type' => 'text',
        ));
    }

    /**
     * Get grid enabled for custom columns
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        // skip config check
        return true;
    }

}