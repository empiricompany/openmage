<?php

/**
 * OpenMage
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available at https://opensource.org/license/osl-3-0-php
 *
 * @category   Mage
 * @package    Mage_Archive
 * @copyright  Copyright (c) 2006-2020 Magento, Inc. (https://www.magento.com)
 * @copyright  Copyright (c) 2022-2025 The OpenMage Contributors (https://www.openmage.org)
 * @license    https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class to work with archives
 *
 * @category   Mage
 * @package    Mage_Archive
 */
class Mage_Archive_Abstract
{
    /**
     * Write data to file. If file can't be opened - throw exception
     *
     * @param string $destination
     * @param string $data
     * @return boolean
     * @throws Mage_Exception
     */
    protected function _writeFile($destination, $data)
    {
        $destination = trim($destination);
        if (false === file_put_contents($destination, $data)) {
            throw new Mage_Exception("Can't write to file: " . $destination);
        }
        return true;
    }

    /**
     * Read data from file. If file can't be opened, throw to exception.
     *
     * @param string $source
     * @return string
     * @throws Mage_Exception
     *
     * @SuppressWarnings("PHPMD.ErrorControlOperator")
     */
    protected function _readFile($source)
    {
        $data = '';
        if (is_file($source) && is_readable($source)) {
            $data = @file_get_contents($source);
            if ($data === false) {
                throw new Mage_Exception("Can't get contents from: " . $source);
            }
        }
        return $data;
    }

    /**
     * Get file name from source (URI) without last extension.
     *
     * @param string $source
     * @param bool $withExtension
     * @return mixed|string
     */
    public function getFilename($source, $withExtension = false)
    {
        $file = str_replace(dirname($source) . DS, '', $source);
        if (!$withExtension) {
            $file = substr($file, 0, strrpos($file, '.'));
        }
        return $file;
    }
}
