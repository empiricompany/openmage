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
 * @copyright  Copyright (c) 2017-2024 The OpenMage Contributors (https://www.openmage.org)
 * @license    https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Convert profiles grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_System_Convert_Gui_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('convertProfileGrid');
        $this->setDefaultSort('profile_id');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('dataflow/profile_collection')
            ->addFieldToFilter('entity_type', ['notnull' => '']);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('profile_id', [
            'header'    => Mage::helper('adminhtml')->__('ID'),
            'width'     => '50px',
            'index'     => 'profile_id',
        ]);
        $this->addColumn('name', [
            'header'    => Mage::helper('adminhtml')->__('Profile Name'),
            'index'     => 'name',
        ]);
        $this->addColumn('direction', [
            'header'    => Mage::helper('adminhtml')->__('Profile Direction'),
            'index'     => 'direction',
            'type'      => 'options',
            'options'   => ['import' => 'Import', 'export' => 'Export'],
            'width'     => '120px',
        ]);
        $this->addColumn('entity_type', [
            'header'    => Mage::helper('adminhtml')->__('Entity Type'),
            'index'     => 'entity_type',
            'type'      => 'options',
            'options'   => ['product' => 'Products', 'customer' => 'Customers'],
            'width'     => '120px',
        ]);

        $this->addColumn('store_id', [
            'type'      => 'store',
        ]);

        $this->addColumn('created_at', [
            'header'    => Mage::helper('adminhtml')->__('Created At'),
            'type'      => 'datetime',
            'align'     => 'center',
            'index'     => 'created_at',
        ]);
        $this->addColumn('updated_at', [
            'header'    => Mage::helper('adminhtml')->__('Updated At'),
            'type'      => 'datetime',
            'align'     => 'center',
            'index'     => 'updated_at',
        ]);

        $this->addColumn('action', [
            'align'     => 'center',
            'type'      => 'action',
            'actions'   => [
                [
                    'url'       => $this->getUrl('*/*/edit') . 'id/$profile_id',
                    'caption'   => Mage::helper('adminhtml')->__('Edit'),
                ],
            ],
        ]);

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['id' => $row->getId()]);
    }
}
