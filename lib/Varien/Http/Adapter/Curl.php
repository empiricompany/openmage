<?php

/**
 * OpenMage
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available at https://opensource.org/license/osl-3-0-php
 *
 * @category   Varien
 * @package    Varien_Http
 * @copyright  Copyright (c) 2006-2020 Magento, Inc. (https://www.magento.com)
 * @copyright  Copyright (c) 2020-2025 The OpenMage Contributors (https://www.openmage.org)
 * @license    https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * HTTP CURL Adapter
 *
 * @category   Varien
 * @package    Varien_Http
 */
class Varien_Http_Adapter_Curl implements Zend_Http_Client_Adapter_Interface
{
    /**
     * Parameters array
     *
     * @var array
     */
    protected $_config = [];

    /**
     * Curl handle
     *
     * @var resource|CurlHandle|null
     */
    protected $_resource;

    /**
     * Allow parameters
     *
     * @var array
     */
    protected $_allowedParams = [
        'timeout'       => CURLOPT_TIMEOUT,
        'maxredirects'  => CURLOPT_MAXREDIRS,
        'proxy'         => CURLOPT_PROXY,
        'ssl_cert'      => CURLOPT_SSLCERT,
        'userpwd'       => CURLOPT_USERPWD,
        'ssl_version'   => CURLOPT_SSLVERSION,
    ];

    /**
     * Array of CURL options
     *
     * @var array
     */
    protected $_options = [];

    /**
     * Apply current configuration array to transport resource
     *
     * @return Varien_Http_Adapter_Curl
     */
    protected function _applyConfig()
    {
        curl_setopt_array($this->_getResource(), $this->_options);

        if (empty($this->_config)) {
            return $this;
        }

        $verifyPeer = $this->_config['verifypeer'] ?? 0;
        curl_setopt($this->_getResource(), CURLOPT_SSL_VERIFYPEER, $verifyPeer);

        $verifyHost = $this->_config['verifyhost'] ?? 0;
        curl_setopt($this->_getResource(), CURLOPT_SSL_VERIFYHOST, $verifyHost);

        foreach (array_keys($this->_config) as $param) {
            if (array_key_exists($param, $this->_allowedParams)) {
                curl_setopt($this->_getResource(), $this->_allowedParams[$param], $this->_config[$param]);
            }
        }
        return $this;
    }

    /**
     * Set array of additional cURL options
     *
     * @return Varien_Http_Adapter_Curl
     */
    public function setOptions(array $options = [])
    {
        $this->_options = $options;
        return $this;
    }

    /**
     * Add additional option to cURL
     *
     * @param  int $option      the CURLOPT_* constants
     * @param  mixed $value
     * @return Varien_Http_Adapter_Curl
     */
    public function addOption($option, $value)
    {
        $this->_options[$option] = $value;
        return $this;
    }

    /**
     * Add additional options list to curl
     *
     *
     * @return Varien_Http_Adapter_Curl
     */
    public function addOptions(array $options)
    {
        $this->_options = $options + $this->_options;
        return $this;
    }

    /**
     * Set the configuration array for the adapter
     *
     * @param array $config
     * @return Varien_Http_Adapter_Curl
     */
    public function setConfig($config = [])
    {
        $this->_config = $config;
        return $this;
    }

    /**
     * Connect to the remote server
     *
     * @deprecated since 1.4.0.0-rc1
     * @param string  $host
     * @param int     $port
     * @param boolean $secure
     * @return Varien_Http_Adapter_Curl
     */
    public function connect($host, $port = 80, $secure = false)
    {
        return $this->_applyConfig();
    }

    /**
     * Send request to the remote server
     *
     * @param string        $method
     * @param string|Zend_Uri_Http $url
     * @param string        $http_ver
     * @param array         $headers
     * @param string        $body
     * @return string Request as text
     */
    public function write($method, $url, $http_ver = '1.1', $headers = [], $body = '')
    {
        if ($url instanceof Zend_Uri_Http) {
            $url = $url->getUri();
        }
        $this->_applyConfig();

        $header = $this->_config['header'] ?? true;
        $options = [
            CURLOPT_URL                     => $url,
            CURLOPT_RETURNTRANSFER          => true,
            CURLOPT_HEADER                  => $header,
            CURLOPT_HTTP_VERSION            => CURL_HTTP_VERSION_1_1,
        ];
        if ($method == Zend_Http_Client::POST) {
            $options[CURLOPT_POST]          = true;
            $options[CURLOPT_POSTFIELDS]    = $body;
        } elseif ($method == Zend_Http_Client::GET) {
            $options[CURLOPT_HTTPGET]       = true;
        }
        if (is_array($headers)) {
            $options[CURLOPT_HTTPHEADER]    = $headers;
        }

        curl_setopt_array($this->_getResource(), $options);

        return $body;
    }

    /**
     * Read response from server
     *
     * @return string|bool
     */
    public function read()
    {
        $response = curl_exec($this->_getResource());

        // Remove 100 and 101 responses headers
        while (Zend_Http_Response::extractCode($response) == 100 || Zend_Http_Response::extractCode($response) == 101) {
            $response = preg_split('/^\r?$/m', $response, 2);
            $response = trim($response[1]);
        }

        if (stripos($response, "Transfer-Encoding: chunked\r\n")) {
            $response = str_ireplace("Transfer-Encoding: chunked\r\n", '', $response);
        }

        return $response;
    }

    /**
     * Close the connection to the server
     *
     * @return Varien_Http_Adapter_Curl
     */
    public function close()
    {
        curl_close($this->_getResource());
        $this->_resource = null;
        return $this;
    }

    /**
     * Returns a cURL handle on success
     *
     * @return resource|CurlHandle
     */
    protected function _getResource()
    {
        if (is_null($this->_resource)) {
            $this->_resource = curl_init();
        }
        return $this->_resource;
    }

    /**
     * Get last error number
     *
     * @return int
     */
    public function getErrno()
    {
        return curl_errno($this->_getResource());
    }

    /**
     * Get string with last error for the current session
     *
     * @return string
     */
    public function getError()
    {
        return curl_error($this->_getResource());
    }

    /**
     * Get information regarding a specific transfer
     *
     * @param int $opt CURLINFO option
     * @return mixed
     */
    public function getInfo($opt = 0)
    {
        if (!$opt) {
            return curl_getinfo($this->_getResource());
        }

        return curl_getinfo($this->_getResource(), $opt);
    }

    /**
     * curl_multi_* requests support
     *
     * @param array $urls
     * @param array $options
     * @return array
     */
    public function multiRequest($urls, $options = [])
    {
        $handles = [];
        $result  = [];

        $multihandle = curl_multi_init();

        foreach ($urls as $key => $url) {
            $handles[$key] = curl_init();
            curl_setopt($handles[$key], CURLOPT_URL, $url);
            curl_setopt($handles[$key], CURLOPT_HEADER, 0);
            curl_setopt($handles[$key], CURLOPT_RETURNTRANSFER, 1);
            if (!empty($options)) {
                curl_setopt_array($handles[$key], $options);
            }
            curl_multi_add_handle($multihandle, $handles[$key]);
        }
        $process = null;
        do {
            curl_multi_exec($multihandle, $process);
            usleep(100);
        } while ($process > 0);

        foreach ($handles as $key => $handle) {
            $result[$key] = curl_multi_getcontent($handle);
            curl_multi_remove_handle($multihandle, $handle);
        }
        curl_multi_close($multihandle);
        return $result;
    }
}
