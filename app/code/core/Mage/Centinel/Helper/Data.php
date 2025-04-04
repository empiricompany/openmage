<?php

/**
 * OpenMage
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available at https://opensource.org/license/osl-3-0-php
 *
 * @category   Mage
 * @package    Mage_Centinel
 * @copyright  Copyright (c) 2006-2020 Magento, Inc. (https://www.magento.com)
 * @copyright  Copyright (c) 2022-2024 The OpenMage Contributors (https://www.openmage.org)
 * @license    https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Centinel module base helper
 *
 * @category   Mage
 * @package    Mage_Centinel
 */
class Mage_Centinel_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_moduleName = 'Mage_Centinel';

    /**
     * Return label for cmpi field
     *
     * @param string $fieldName
     * @return string
     */
    public function getCmpiLabel($fieldName)
    {
        switch ($fieldName) {
            case Mage_Centinel_Model_Service::CMPI_PARES:
                return $this->__('3D Secure Verification Result');
            case Mage_Centinel_Model_Service::CMPI_ENROLLED:
                return $this->__('3D Secure Cardholder Validation');
            case Mage_Centinel_Model_Service::CMPI_ECI:
                return $this->__('3D Secure Electronic Commerce Indicator');
            case Mage_Centinel_Model_Service::CMPI_CAVV:
                return $this->__('3D Secure CAVV');
            case Mage_Centinel_Model_Service::CMPI_XID:
                return $this->__('3D Secure XID');
        }
        return '';
    }

    /**
     * Return value for cmpi field
     *
     * @param string $fieldName
     * @param string $value
     * @return string
     */
    public function getCmpiValue($fieldName, $value)
    {
        switch ($fieldName) {
            case Mage_Centinel_Model_Service::CMPI_PARES:
                return $this->_getCmpiParesValue($value);
            case Mage_Centinel_Model_Service::CMPI_ENROLLED:
                return $this->_getCmpiEnrolledValue($value);
            case Mage_Centinel_Model_Service::CMPI_ECI:
                return $this->_getCmpiEciValue($value);
            case Mage_Centinel_Model_Service::CMPI_CAVV: // break intentionally omitted
            case Mage_Centinel_Model_Service::CMPI_XID:
                return $value;
        }
        return '';
    }

    /**
     * Return text value for cmpi eci flag field
     *
     * @param string $value
     * @return string
     */
    private function _getCmpiEciValue($value)
    {
        switch ($value) {
            case '01':
            case '07':
                return $this->__('Merchant Liability');
            case '02':
            case '05':
            case '06':
                return $this->__('Card Issuer Liability');
            default:
                return $value;
        }
    }

    /**
     * Return text value for cmpi enrolled field
     *
     * @param string $value
     * @return string
     */
    private function _getCmpiEnrolledValue($value)
    {
        switch ($value) {
            case 'Y':
                return $this->__('Enrolled');
            case 'U':
                return $this->__('Enrolled but Authentication Unavailable');
            case 'N': // break intentionally omitted
            default:
                return $this->__('Not Enrolled');
        }
    }

    /**
     * Return text value for cmpi pares field
     *
     * @param string $value
     * @return string
     */
    private function _getCmpiParesValue($value)
    {
        switch ($value) {
            case 'Y':
                return $this->__('Successful');
            case 'N':
                return $this->__('Failed');
            case 'U':
                return $this->__('Unable to complete');
            case 'A':
                return $this->__('Successful attempt');
            default:
                return $value;
        }
    }

    /**
     * Return centinel block for payment form with logos
     *
     * @param Mage_Payment_Model_Method_Abstract $method
     * @return Mage_Centinel_Block_Logo|Mage_Core_Block_Abstract|false
     */
    public function getMethodFormBlock($method)
    {
        $blockType = 'centinel/logo';
        if ($this->getLayout()) {
            $block = $this->getLayout()->createBlock($blockType);
        } else {
            $className = Mage::getConfig()->getBlockClassName($blockType);
            $block = new $className();
        }
        $block->setMethod($method);
        return $block;
    }

    /**
     * Return url of page about visa verification
     *
     * @return string
     */
    public function getVisaLearnMorePageUrl()
    {
        return 'https://usa.visa.com/personal/security/vbv/index.html?ep=v_sym_verifiedbyvisa';
    }

    /**
     * Return url of page about mastercard verification
     *
     * @return string
     */
    public function getMastercardLearnMorePageUrl()
    {
        return 'http://www.mastercardbusiness.com/mcbiz/index.jsp?template=/orphans&amp;content=securecodepopup';
    }
}
