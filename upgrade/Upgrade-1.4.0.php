<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_4_0($object)
{
    if (!Configuration::updateValue('PL_ST_AWAITING', 0)) {
        return false;
    }
    if (!Configuration::updateValue('PL_ST_PENDING', 3)) {
        return false;
    }
    if (!Configuration::updateValue('PL_ST_READY', 3)) {
        return false;
    }
    if (!Configuration::updateValue('PL_ST_TRANSIT', 4)) {
        return false;
    }
    if (!Configuration::updateValue('PL_ST_DELIVERED', 5)) {
        return false;
    }

    return true;
}
