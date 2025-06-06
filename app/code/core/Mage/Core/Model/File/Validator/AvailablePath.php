<?php

/**
 * OpenMage
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available at https://opensource.org/license/osl-3-0-php
 *
 * @category   Mage
 * @package    Mage_Core
 * @copyright  Copyright (c) 2006-2020 Magento, Inc. (https://www.magento.com)
 * @copyright  Copyright (c) 2019-2025 The OpenMage Contributors (https://www.openmage.org)
 * @license    https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Validator for check not protected/available path
 *
 * Mask symbols from path:
 * "?" - something directory with any name
 * "*" - something directory structure, which can not exist
 * Note: For set directory structure which must be exist, need to set mask "/?/{@*}"
 * Mask symbols from filename:
 * "*" - something symbols in file name
 * Example:
 * <code>
 * //set available path
 * $validator->setAvailablePath(array('/path/to/?/*fileMask.xml'));
 * $validator->isValid('/path/to/MyDir/Some-fileMask.xml'); //return true
 * $validator->setAvailablePath(array('/path/to/{@*}*.xml'));
 * $validator->isValid('/path/to/my.xml'); //return true, because directory structure can't exist
 * </code>
 *
 * @category   Mage
 * @package    Mage_Core
 */
class Mage_Core_Model_File_Validator_AvailablePath extends Zend_Validate_Abstract
{
    public const PROTECTED_PATH     = 'protectedPath';
    public const NOT_AVAILABLE_PATH = 'notAvailablePath';
    public const PROTECTED_LFI      = 'protectedLfi';

    /**
     * The path
     *
     * @var string
     */
    protected $_value;

    /**
     * Protected paths
     *
     * @var array
     */
    protected $_protectedPaths = [];

    /**
     * Available paths
     *
     * @var array
     */
    protected $_availablePaths = [];

    /**
     * Cache of made regular expressions from path masks
     *
     * @var array
     */
    protected $_pathsData;

    public function __construct()
    {
        $this->_initMessageTemplates();
    }

    /**
     * Initialize message templates with translating
     *
     * @return $this
     */
    protected function _initMessageTemplates()
    {
        if (!$this->_messageTemplates) {
            $this->_messageTemplates = [
                self::PROTECTED_PATH =>
                    Mage::helper('core')->__('Path "%value%" is protected and cannot be used.'),
                self::NOT_AVAILABLE_PATH =>
                    Mage::helper('core')->__('Path "%value%" is not available and cannot be used.'),
                self::PROTECTED_LFI =>
                    Mage::helper('core')->__('Path "%value%" may not include parent directory traversal ("../", "..\\").'),
            ];
        }
        return $this;
    }

    /**
     * Set paths masks
     *
     * @param array $paths  All paths masks types.
     *                      E.g.: array('available' => array(...), 'protected' => array(...))
     * @return $this
     */
    public function setPaths(array $paths)
    {
        if (isset($paths['available']) && is_array($paths['available'])) {
            $this->_availablePaths = $paths['available'];
        }
        if (isset($paths['protected']) && is_array($paths['protected'])) {
            $this->_protectedPaths = $paths['protected'];
        }
        return $this;
    }

    /**
     * Set protected paths masks
     *
     * @return $this
     */
    public function setProtectedPaths(array $paths)
    {
        $this->_protectedPaths = $paths;
        return $this;
    }

    /**
     * Add protected paths masks
     *
     * @param string|array $path
     * @return $this
     */
    public function addProtectedPath($path)
    {
        if (is_array($path)) {
            $this->_protectedPaths = array_merge($this->_protectedPaths, $path);
        } else {
            $this->_protectedPaths[] = $path;
        }
        return $this;
    }

    /**
     * Get protected paths masks
     *
     * @return array
     */
    public function getProtectedPaths()
    {
        return $this->_protectedPaths;
    }

    /**
     * Set available paths masks
     *
     * @return $this
     */
    public function setAvailablePaths(array $paths)
    {
        $this->_availablePaths = $paths;
        return $this;
    }

    /**
     * Add available paths mask
     *
     * @param string|array $path
     * @return $this
     */
    public function addAvailablePath($path)
    {
        if (is_array($path)) {
            $this->_availablePaths = array_merge($this->_availablePaths, $path);
        } else {
            $this->_availablePaths[] = $path;
        }
        return $this;
    }

    /**
     * Get available paths masks
     *
     * @return array
     */
    public function getAvailablePaths()
    {
        return $this->_availablePaths;
    }

    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @throws Exception        Throw exception on empty both paths masks types
     * @param string $value     File/dir path
     * @return bool
     */
    public function isValid($value)
    {
        $value = trim($value);
        $this->_setValue($value);

        if (!$this->_availablePaths && !$this->_protectedPaths) {
            throw new Exception(Mage::helper('core')->__('Please set available and/or protected paths list(s) before validation.'));
        }

        if (preg_match('#\.\.[\\\/]#', $this->_value)) {
            $this->_error(self::PROTECTED_LFI, $this->_value);
            return false;
        }

        //validation
        $protectedExtensions = Mage::helper('core/data')->getProtectedFileExtensions();
        $value = str_replace(['/', '\\'], DS, $this->_value);
        $valuePathInfo = pathinfo(ltrim($value, '\\/'));
        $fileNameExtension = pathinfo($valuePathInfo['filename'], PATHINFO_EXTENSION);

        if (in_array($fileNameExtension, $protectedExtensions)) {
            $this->_error(self::NOT_AVAILABLE_PATH, $this->_value);
            return false;
        }

        if ($valuePathInfo['dirname'] == '.' || $valuePathInfo['dirname'] == DS) {
            $valuePathInfo['dirname'] = '';
        }

        if ($this->_protectedPaths && !$this->_isValidByPaths($valuePathInfo, $this->_protectedPaths, true)) {
            $this->_error(self::PROTECTED_PATH, $this->_value);
            return false;
        }
        if ($this->_availablePaths && !$this->_isValidByPaths($valuePathInfo, $this->_availablePaths, false)) {
            $this->_error(self::NOT_AVAILABLE_PATH, $this->_value);
            return false;
        }

        return true;
    }

    /**
     * Validate value by path masks
     *
     * @param array $valuePathInfo  Path info from value path
     * @param array $paths          Protected/available paths masks
     * @param bool $protected       Paths masks is protected?
     * @return bool
     */
    protected function _isValidByPaths($valuePathInfo, $paths, $protected)
    {
        foreach ($paths as $path) {
            $path = ltrim($path, '\\/');
            if (!isset($this->_pathsData[$path]['regFilename'])) {
                $pathInfo = pathinfo($path);
                $options['file_mask'] = $pathInfo['basename'];
                if ($pathInfo['dirname'] == '.' || $pathInfo['dirname'] == DS) {
                    $pathInfo['dirname'] = '';
                } else {
                    $pathInfo['dirname'] = str_replace(['/', '\\'], DS, $pathInfo['dirname']);
                }
                $options['dir_mask'] = $pathInfo['dirname'];
                $this->_pathsData[$path]['options'] = $options;
            } else {
                $options = $this->_pathsData[$path]['options'];
            }

            //file mask
            if (str_contains($options['file_mask'], '*')) {
                if (!isset($this->_pathsData[$path]['regFilename'])) {
                    //make regular
                    $reg = $options['file_mask'];
                    $reg = str_replace('.', '\.', $reg);
                    $reg = str_replace('*', '.*?', $reg);
                    $reg = "/^($reg)$/";
                } else {
                    $reg = $this->_pathsData[$path]['regFilename'];
                }
                $resultFile = preg_match($reg, $valuePathInfo['basename']);
            } else {
                $resultFile = ($options['file_mask'] == $valuePathInfo['basename']);
            }

            //directory mask
            $reg = $options['dir_mask'] . DS;
            if (!isset($this->_pathsData[$path]['regDir'])) {
                //make regular
                $reg = str_replace('.', '\.', $reg);
                $reg = str_replace('*\\', '||', $reg);
                $reg = str_replace('*/', '||', $reg);
                $reg = str_replace(DS, '[\\' . DS . ']', $reg);
                $reg = str_replace('?', '([^\\' . DS . ']+)', $reg);
                $reg = str_replace('||', '(.*[\\' . DS . '])?', $reg);
                $reg = "/^$reg$/";
            } else {
                $reg = $this->_pathsData[$path]['regDir'];
            }
            $resultDir = preg_match($reg, $valuePathInfo['dirname'] . DS);

            if ($protected && ($resultDir && $resultFile)) {
                return false;
            } elseif (!$protected && ($resultDir && $resultFile)) {
                //return true because one match with available path mask
                return true;
            }
        }
        if ($protected) {
            return true;
        } else {
            //return false because no one match with available path mask
            return false;
        }
    }
}
