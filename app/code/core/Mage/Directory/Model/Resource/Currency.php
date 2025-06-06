<?php

/**
 * OpenMage
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available at https://opensource.org/license/osl-3-0-php
 *
 * @category   Mage
 * @package    Mage_Directory
 * @copyright  Copyright (c) 2006-2020 Magento, Inc. (https://www.magento.com)
 * @copyright  Copyright (c) 2020-2024 The OpenMage Contributors (https://www.openmage.org)
 * @license    https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Directory Currency Resource Model
 *
 * @category   Mage
 * @package    Mage_Directory
 */
class Mage_Directory_Model_Resource_Currency extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Currency rate table
     *
     * @var string
     */
    protected $_currencyRateTable;

    /**
     * Currency rate cache array
     *
     * @var array
     */
    protected static $_rateCache;

    /**
     * Define main and currency rate tables
     *
     */
    protected function _construct()
    {
        $this->_init('directory/currency', 'currency_code');
        $this->_currencyRateTable   = $this->getTable('directory/currency_rate');
    }

    /**
     * Retrieve currency rate (only base=>allowed)
     *
     * @param Mage_Directory_Model_Currency|string $currencyFrom
     * @param Mage_Directory_Model_Currency|string $currencyTo
     * @return float|int
     */
    public function getRate($currencyFrom, $currencyTo)
    {
        if ($currencyFrom instanceof Mage_Directory_Model_Currency) {
            $currencyFrom = $currencyFrom->getCode();
        }

        if ($currencyTo instanceof Mage_Directory_Model_Currency) {
            $currencyTo = $currencyTo->getCode();
        }

        if ($currencyFrom == $currencyTo) {
            return 1;
        }

        if (!isset(self::$_rateCache[$currencyFrom][$currencyTo])) {
            $read = $this->_getReadAdapter();
            $bind = [
                ':currency_from' => strtoupper($currencyFrom),
                ':currency_to'   => strtoupper($currencyTo),
            ];
            $select = $read->select()
                ->from($this->_currencyRateTable, 'rate')
                ->where('currency_from = :currency_from')
                ->where('currency_to = :currency_to');

            self::$_rateCache[$currencyFrom][$currencyTo] = $read->fetchOne($select, $bind);
        }

        return self::$_rateCache[$currencyFrom][$currencyTo];
    }

    /**
     * Retrieve currency rate (base=>allowed or allowed=>base)
     *
     * @param Mage_Directory_Model_Currency|string $currencyFrom
     * @param Mage_Directory_Model_Currency|string $currencyTo
     * @return float
     */
    public function getAnyRate($currencyFrom, $currencyTo)
    {
        if ($currencyFrom instanceof Mage_Directory_Model_Currency) {
            $currencyFrom = $currencyFrom->getCode();
        }

        if ($currencyTo instanceof Mage_Directory_Model_Currency) {
            $currencyTo = $currencyTo->getCode();
        }

        if ($currencyFrom == $currencyTo) {
            return 1;
        }

        if (!isset(self::$_rateCache[$currencyFrom][$currencyTo])) {
            $adapter = $this->_getReadAdapter();
            $bind    = [
                ':currency_from' => strtoupper($currencyFrom),
                ':currency_to'   => strtoupper($currencyTo),
            ];
            $select  = $adapter->select()
                ->from($this->_currencyRateTable, 'rate')
                ->where('currency_from = :currency_from')
                ->where('currency_to = :currency_to');

            $rate    = $adapter->fetchOne($select, $bind);
            if ($rate === false) {
                $select = $adapter->select()
                    ->from($this->_currencyRateTable, new Zend_Db_Expr('1/rate'))
                    ->where('currency_to = :currency_from')
                    ->where('currency_from = :currency_to');
                $rate = $adapter->fetchOne($select, $bind);
            }
            self::$_rateCache[$currencyFrom][$currencyTo] = $rate;
        }

        return self::$_rateCache[$currencyFrom][$currencyTo];
    }

    /**
     * Saving currency rates
     *
     * @param array $rates
     */
    public function saveRates($rates)
    {
        if (is_array($rates) && count($rates)) {
            $adapter = $this->_getWriteAdapter();
            $data    = [];
            foreach ($rates as $currencyCode => $rate) {
                foreach ($rate as $currencyTo => $value) {
                    $value = abs($value);
                    if ($value == 0) {
                        continue;
                    }
                    $data[] = [
                        'currency_from' => $currencyCode,
                        'currency_to'   => $currencyTo,
                        'rate'          => $value,
                    ];
                }
            }
            if ($data) {
                $adapter->insertOnDuplicate($this->_currencyRateTable, $data, ['rate']);
            }
        } else {
            Mage::throwException(Mage::helper('directory')->__('Invalid rates received'));
        }
    }

    /**
     * Retrieve config currency data by config path
     *
     * @param Mage_Directory_Model_Currency $model
     * @param string $path
     *
     * @return array
     */
    public function getConfigCurrencies($model, $path)
    {
        $result  = [];
        $config = Mage::app()->getConfig();

        // default
        $result = array_merge($result, explode(',', trim($config->getNode($path, 'default'))));

        // stores
        foreach (Mage::app()->getStores(true) as $store) {
            $result = array_merge($result, explode(',', trim($config->getNode($path, 'stores', $store->getCode()))));
        }

        // websites
        foreach (Mage::app()->getWebsites(true) as $website) {
            $result = array_merge($result, explode(',', trim($config->getNode($path, 'websites', $website->getCode()))));
        }

        sort($result);

        return array_unique($result);
    }

    /**
     * Return currency rates
     *
     * @param string|array $currency
     * @param array $toCurrencies
     *
     * @return array
     */
    public function getCurrencyRates($currency, $toCurrencies = null)
    {
        $rates = [];
        if (is_array($currency)) {
            foreach ($currency as $code) {
                $rates[$code] = $this->_getRatesByCode($code, $toCurrencies);
            }
        } else {
            $rates = $this->_getRatesByCode($currency, $toCurrencies);
        }

        return $rates;
    }

    /**
     * Protected method used by getCurrencyRates() method
     *
     * @param string $code
     * @param array $toCurrencies
     * @return array
     */
    protected function _getRatesByCode($code, $toCurrencies = null)
    {
        $adapter = $this->_getReadAdapter();
        $bind    = [
            ':currency_from' => $code,
        ];
        $select  = $adapter->select()
            ->from($this->getTable('directory/currency_rate'), ['currency_to', 'rate'])
            ->where('currency_from = :currency_from')
            ->where('currency_to IN(?)', $toCurrencies);
        $rowSet  = $adapter->fetchAll($select, $bind);
        $result  = [];

        foreach ($rowSet as $row) {
            $result[$row['currency_to']] = $row['rate'];
        }

        return $result;
    }
}
