<?php

/**
 * OpenMage
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available at https://opensource.org/license/osl-3-0-php
 *
 * @category   Magento
 * @package    Magento_Profiler
 * @copyright  Copyright (c) 2006-2020 Magento, Inc. (https://www.magento.com)
 * @copyright  Copyright (c) 2022-2025 The OpenMage Contributors (https://www.openmage.org)
 * @license    https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class that represents profiler output in Html format
 */
class Magento_Profiler_Output_Csvfile extends Magento_Profiler_OutputAbstract
{
    /**
     * @var string
     */
    protected $_filename;

    /**
     * @var string
     */
    protected $_delimiter;

    /**
     * @var string
     */
    protected $_enclosure;

    /**
     * @var string
     */
    protected $_escape = '\\';

    /**
     * Start output buffering
     *
     * @param string      $filename Target file to save CSV data
     * @param string|null $filter Pattern to filter timers by their identifiers (SQL LIKE syntax)
     * @param string      $delimiter Delimiter for CSV format
     * @param string      $enclosure Enclosure for CSV format
     */
    public function __construct($filename, $filter = null, $delimiter = ',', $enclosure = '"')
    {
        parent::__construct($filter);

        $this->_filename = $filename;
        $this->_delimiter = $delimiter;
        $this->_enclosure = $enclosure;
    }

    /**
     * Display profiling results
     */
    public function display()
    {
        $fileHandle = fopen($this->_filename, 'w');
        if (!$fileHandle) {
            throw new Varien_Exception(sprintf('Can not open a file "%s".', $this->_filename));
        }

        $needLock = (!str_starts_with($this->_filename, 'php://'));
        $isLocked = false;
        while ($needLock && !$isLocked) {
            $isLocked = flock($fileHandle, LOCK_EX);
        }

        $this->_writeFileContent($fileHandle);

        if ($isLocked) {
            flock($fileHandle, LOCK_UN);
        }
        fclose($fileHandle);
    }

    /**
     * Write content into an opened file handle
     *
     * @param resource $fileHandle
     */
    protected function _writeFileContent($fileHandle)
    {
        foreach ($this->_getTimers() as $timerId) {
            $row = [];
            foreach ($this->_getColumns() as $columnId) {
                $row[] = $this->_renderColumnValue($timerId, $columnId);
            }
            fputcsv($fileHandle, $row, $this->_delimiter, $this->_enclosure, $this->_escape);
        }
    }
}
