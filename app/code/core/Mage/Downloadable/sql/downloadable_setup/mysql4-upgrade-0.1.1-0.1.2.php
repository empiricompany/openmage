<?php

/**
 * OpenMage
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available at https://opensource.org/license/osl-3-0-php
 *
 * @category   Mage
 * @package    Mage_Downloadable
 * @copyright  Copyright (c) 2006-2020 Magento, Inc. (https://www.magento.com)
 * @copyright  Copyright (c) 2020-2024 The OpenMage Contributors (https://www.openmage.org)
 * @license    https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/** @var Mage_Catalog_Model_Resource_Setup  $installer */
$installer = $this;
$installer->startSetup();

/** @var Varien_Db_Adapter_Pdo_Mysql $conn */
$conn = $installer->getConnection();

$installer->run("
CREATE TABLE `{$installer->getTable('downloadable/sample')}` (
  `sample_id` int(10) unsigned NOT NULL auto_increment,
  `product_id` int(10) unsigned NOT NULL default '0',
  `sample_file` varchar(255) NOT NULL default '',
  `sort_order` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`sample_id`),
  KEY `DOWNLODABLE_SAMPLE_PRODUCT` (`product_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
");

$conn->addConstraint(
    'FK_DOWNLODABLE_SAMPLE_PRODUCT',
    $installer->getTable('downloadable/sample'),
    'product_id',
    $installer->getTable('catalog/product'),
    'entity_id',
);

$installer->run("
CREATE TABLE `{$installer->getTable('downloadable/sample_title')}` (
  `title_id` int(10) unsigned NOT NULL auto_increment,
  `sample_id` int(10) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `title` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`title_id`),
  KEY `DOWNLOADABLE_SAMPLE_TITLE_SAMPLE` (`sample_id`),
  KEY `DOWNLOADABLE_SAMPLE_TITLE_STORE` (`store_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
");

$conn->addConstraint(
    'FK_DOWNLOADABLE_SAMPLE_TITLE_SAMPLE',
    $installer->getTable('downloadable/sample_title'),
    'sample_id',
    $installer->getTable('downloadable/sample'),
    'sample_id',
);
$conn->addConstraint(
    'FK_DOWNLOADABLE_SAMPLE_TITLE_STORE',
    $installer->getTable('downloadable/sample_title'),
    'store_id',
    $installer->getTable('core/store'),
    'store_id',
);

$installer->run("
CREATE TABLE `{$installer->getTable('downloadable/link')}` (
  `link_id` int(10) unsigned NOT NULL auto_increment,
  `product_id` int(10) unsigned NOT NULL default '0',
  `sort_order` int(10) unsigned NOT NULL default '0',
  `number_of_downloads` int(10) unsigned,
  `is_shareable` smallint(1) unsigned NOT NULL default '0',
  `link_file` varchar(255) NOT NULL default '',
  `sample_file` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`link_id`),
  KEY `DOWNLODABLE_LINK_PRODUCT` (`product_id`),
  KEY `DOWNLODABLE_LINK_PRODUCT_SORT_ORDER` (`product_id` , `sort_order`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
");

$conn->addConstraint(
    'FK_DOWNLODABLE_LINK_PRODUCT',
    $installer->getTable('downloadable/link'),
    'product_id',
    $installer->getTable('catalog/product'),
    'entity_id',
);

$installer->run("
CREATE TABLE `{$installer->getTable('downloadable/link_title')}` (
  `title_id` int(10) unsigned NOT NULL auto_increment,
  `link_id` int(10) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `title` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`title_id`),
  KEY `DOWNLOADABLE_LINK_TITLE_LINK` (`link_id`),
  KEY `DOWNLOADABLE_LINK_TITLE_STORE` (`store_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
");

$conn->addConstraint(
    'FK_DOWNLOADABLE_LINK_TITLE_LINK',
    $installer->getTable('downloadable/link_title'),
    'link_id',
    $installer->getTable('downloadable/link'),
    'link_id',
);
$conn->addConstraint(
    'FK_DOWNLOADABLE_LINK_TITLE_STORE',
    $installer->getTable('downloadable/link_title'),
    'store_id',
    $installer->getTable('core/store'),
    'store_id',
);

$installer->run("
CREATE TABLE `{$installer->getTable('downloadable/link_price')}` (
  `price_id` int(10) unsigned NOT NULL auto_increment,
  `link_id` int(10) unsigned NOT NULL default '0',
  `website_id` smallint(5) unsigned NOT NULL default '0',
  `price` decimal(12,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`price_id`),
  KEY `DOWNLOADABLE_LINK_PRICE_LINK` (`link_id`),
  KEY `DOWNLOADABLE_LINK_PRICE_WEBSITE` (`website_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
");

$conn->addConstraint(
    'FK_DOWNLOADABLE_LINK_PRICE_LINK',
    $installer->getTable('downloadable/link_price'),
    'link_id',
    $installer->getTable('downloadable/link'),
    'link_id',
);
$conn->addConstraint(
    'FK_DOWNLOADABLE_LINK_PRICE_WEBSITE',
    $installer->getTable('downloadable/link_price'),
    'website_id',
    $installer->getTable('core/website'),
    'website_id',
);

$installer->endSetup();
