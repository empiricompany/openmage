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
 * @copyright  Copyright (c) 2022-2024 The OpenMage Contributors (https://www.openmage.org)
 * @license    https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Synchronize button renderer
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_System_Config_System_Storage_Media_Synchronize extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Set template
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('system/config/system/storage/media/synchronize.phtml');
    }

    /**
     * Remove scope label
     *
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Return element html
     *
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_toHtml();
    }

    /**
     * Return ajax url for synchronize button
     *
     * @return string
     */
    public function getAjaxSyncUrl()
    {
        return Mage::getSingleton('adminhtml/url')->getUrl('*/system_config_system_storage/synchronize');
    }

    /**
     * Return ajax url for synchronize button
     *
     * @return string
     */
    public function getAjaxStatusUpdateUrl()
    {
        return Mage::getSingleton('adminhtml/url')->getUrl('*/system_config_system_storage/status');
    }

    /**
     * Generate synchronize button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData([
                'id'        => 'synchronize_button',
                'label'     => $this->helper('adminhtml')->__('Synchronize'),
                'onclick'   => 'javascript:synchronize(); return false;',
            ]);

        return $button->toHtml();
    }

    /**
     * Retrieve last sync params settings
     *
     * Return array format:
     * array (
     *  => storage_type     int,
     *  => connection_name  string
     * )
     *
     * @return array
     */
    public function getSyncStorageParams()
    {
        $flag = Mage::getSingleton('core/file_storage')->getSyncFlag();
        $flagData = $flag->getFlagData();

        if ($flag->getState() == Mage_Core_Model_File_Storage_Flag::STATE_NOTIFIED
                && is_array($flagData)
            && isset($flagData['destination_storage_type']) && $flagData['destination_storage_type'] != ''
            && isset($flagData['destination_connection_name'])
        ) {
            $storageType    = $flagData['destination_storage_type'];
            $connectionName = $flagData['destination_connection_name'];
        } else {
            $storageType    = Mage_Core_Model_File_Storage::STORAGE_MEDIA_FILE_SYSTEM;
            $connectionName = '';
        }

        return [
            'storage_type'      => $storageType,
            'connection_name'   => $connectionName,
        ];
    }
}
