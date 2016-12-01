<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_5_0($object)
{
    if (!Configuration::deleteByName('PL_API_FIRSTNAME')) {
        return false;
    }
    if (!Configuration::deleteByName('PL_API_LASTNAME')) {
        return false;
    }
    if (!Configuration::deleteByName('PL_API_EMAIL')) {
        return false;
    }
    if (!Configuration::deleteByName('PL_API_BOUTIQUE')) {
        return false;
    }
    if (!Configuration::deleteByName('PL_API_ADD1')) {
        return false;
    }
    if (!Configuration::deleteByName('PL_API_ADD2')) {
        return false;
    }
    if (!Configuration::deleteByName('PL_API_PHONE')) {
        return false;
    }
    if (!Configuration::deleteByName('PL_API_CITY_NAME')) {
        return false;
    }
    if (!Configuration::deleteByName('PL_API_COUNTRY_NAME')) {
        return false;
    }
    if (!Configuration::deleteByName('PL_API_STATE')) {
        return false;
    }
    if (!Configuration::deleteByName('PL_API_CITY')) {
        return false;
    }
    if (!Configuration::deleteByName('PL_API_CITY_ID')) {
        return false;
    }
    if (!Configuration::deleteByName('PL_API_COUNTRY')) {
        return false;
    }

    if (!$object->createTab()) {
        return false;
    }

    if (!Configuration::updateValue('PL_ST_AWAITING', 0)) {
        return false;
    }

    $object->registerHook('actionObjectOrderUpdateAfter');
    $object->registerHook('actionOrderHistoryAddAfter');
    $object->registerHook('actionOrderStatusPostUpdate');

    if (!Db::getInstance()->Execute('
                    CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'packlink_wait_draft` (
                    `id_order` int(11) NOT NULL AUTO_INCREMENT,
                    `date_add` DATE,
                    PRIMARY KEY (`id_order`) )')) {
        return false;
    }
    if (!Db::getInstance()->Execute('
                    CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'packlink_done_draft` (
                    `id_order` int(11) NOT NULL AUTO_INCREMENT,
                    `date_add` DATE,
                    PRIMARY KEY (`id_order`) )')) {
        return false;
    }
    
    return true;
}
