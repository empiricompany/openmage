<?php

/**
 * OpenMage
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available at https://opensource.org/license/osl-3-0-php
 *
 * @category   Mage
 * @package    Mage_Dataflow
 * @copyright  Copyright (c) 2006-2020 Magento, Inc. (https://www.magento.com)
 * @copyright  Copyright (c) 2022-2024 The OpenMage Contributors (https://www.openmage.org)
 * @license    https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Dataflow Batch import model
 *
 * @category   Mage
 * @package    Mage_Dataflow
 *
 * @method Mage_Dataflow_Model_Resource_Batch_Import _getResource()
 * @method Mage_Dataflow_Model_Resource_Batch_Import getResource()
 * @method int getBatchId()
 * @method $this setBatchId(int $value)
 * @method int getStatus()
 * @method $this setStatus(int $value)
 */
class Mage_Dataflow_Model_Batch_Import extends Mage_Dataflow_Model_Batch_Abstract
{
    protected function _construct()
    {
        $this->_init('dataflow/batch_import');
    }
}
