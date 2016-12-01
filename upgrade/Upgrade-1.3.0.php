<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_3_0($object)
{
    if (!Configuration::updateValue('PL_IMPORT', 1)) {
        return false;
    }

    if (!$object->registerHook('displayBackOfficeHeader')) {
        return false;
    }
    if (!$object->registerHook('displayOrderDetail')) {
        return false;
    }

    if (version_compare(_PS_VERSION_, '1.6.1', '>=')) {

        if (!$object->registerHook('displayAdminOrderContentShip')) {
            return false;
        }
        if (!$object->registerHook('displayAdminOrderTabShip')) {
            return false;
        }
    }
    if (version_compare(_PS_VERSION_, '1.6.1', '<')) {
        if (!$object->registerHook('displayAdminOrder')) {
            return false;
        }
    }

    $sql = 'SELECT column_name
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE table_name = "'._DB_PREFIX_.'packlink_orders"
                AND table_schema = "'._DB_NAME_.'"
                AND column_name = "details"';
    $column = Db::getInstance()->getRow($sql);

    if (!$column) {
        $sql = 'ALTER TABLE '._DB_PREFIX_.'packlink_orders ADD details VARCHAR(1500)';
    }

    if (!Db::getInstance()->execute($sql)) {
        return false;
    }

    return true;
}
