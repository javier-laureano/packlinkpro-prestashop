<?php
/**
* Copyright 2016 OMI Europa S.L (Packlink)

* Licensed under the Apache License, Version 2.0 (the "License");
* you may not use this file except in compliance with the License.
* You may obtain a copy of the License at

*  http://www.apache.org/licenses/LICENSE-2.0

* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and
* limitations under the License.
*/

class PackLinkCarrier extends Carrier
{

    /**
     * Function to create new carrier
     * @return bool       
     */
    public static function installCarrierPL()
    {
        
     
        //Create new carrier
        $carrier = new Carrier();
        $carrier->name = 'packlink';
        $carrier->active = false;
        $carrier->deleted = 0;
        $carrier->shipping_handling = false;
        $carrier->range_behavior = 0;
        $carrier->delay[Configuration::get('PS_LANG_DEFAULT')] = 'packlink';
        $carrier->shipping_external = true;
        $carrier->is_module = true;
        $carrier->external_module_name = 'packlink';
        $carrier->need_range = true;

        if ($carrier->add()) {

            $groups = Group::getGroups(true);
            foreach ($groups as $group) {
                Db::getInstance()->autoExecute(_DB_PREFIX_ . 'carrier_group', array(
                    'id_carrier' => (int) $carrier->id,
                    'id_group' => (int) $group['id_group']
                ), 'INSERT');
            }
 
            $rangePrice = new RangePrice();
            $rangePrice->id_carrier = $carrier->id;
            $rangePrice->delimiter1 = '0';
            $rangePrice->delimiter2 = '0';
            $rangePrice->add();
 
            $rangeWeight = new RangeWeight();
            $rangeWeight->id_carrier = $carrier->id;
            $rangeWeight->delimiter1 = '0';
            $rangeWeight->delimiter2 = '0';
            $rangeWeight->add();
 
            $zones = Zone::getZones(true);
            foreach ($zones as $z) {
                Db::getInstance()->autoExecute(
                    _DB_PREFIX_ . 'carrier_zone',
                    array('id_carrier' => (int) $carrier->id, 'id_zone' => (int) $z['id_zone']),
                    'INSERT'
                );
                Db::getInstance()->autoExecuteWithNullValues(
                    _DB_PREFIX_ . 'delivery',
                    array('id_carrier' => $carrier->id, 'id_range_price' => (int) $rangePrice->id, 'id_range_weight' => null, 'id_zone' => (int) $z['id_zone'], 'price' => '0'),
                    'INSERT'
                );
                Db::getInstance()->autoExecuteWithNullValues(
                    _DB_PREFIX_ . 'delivery',
                    array('id_carrier' => $carrier->id, 'id_range_price' => null, 'id_range_weight' => (int) $rangeWeight->id, 'id_zone' => (int) $z['id_zone'], 'price' => '0'),
                    'INSERT'
                );
            }
 
            if (file_exists(dirname(_PS_ADMIN_DIR_) . '/module/packlink/views/img/' . 'packlink' . '.jpg')) {
                copy(dirname(_PS_ADMIN_DIR_) . '/module/packlink/views/img/' . 'packlink' . '.jpg', _PS_SHIP_IMG_DIR_ . '/' . (int) $carrier->id . '.jpg');
            }
            //assign carrier logo

            Configuration::updateValue('PL_API_CARRIER', $carrier->id);
            Configuration::updateValue('PL_API_CARRIER' . '_REF', $carrier->id);
        }
    
        return true;
    }
    /**
     * Function to delete carrier
     * @return bool       
     */
    public static function uninstallCarrierPL()
    {
        $tmp_carrier_id = Configuration::get('PL_API_CARRIER');
        $carrier = new Carrier($tmp_carrier_id);
        return $carrier->delete();
    }
}
