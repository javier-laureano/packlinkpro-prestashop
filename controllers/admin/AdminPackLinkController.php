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
class AdminPackLinkController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    public static function install($menu_id, $module_name)
    {
        return PlTotAdminTabHelper::addAdminTab(array(
            'id_parent' => $menu_id,
            'className' => 'AdminPacklink',
            'default_name' => 'Packlink',
            'name' => 'Packlink',
            'position' => 14,
            'active' => false,
            'module' => $module_name,
        ));
    }


    public function ajaxProcessGetPostCode()
    {

        $default_language = new Language(Configuration::get('PS_LANG_DEFAULT'));
        $language = $default_language->iso_code;
        $sdk = new PacklinkSDK(Configuration::get('PL_API_KEY'), $this);

        $datas = $sdk->getPostalCodes(
            array(
                'platform' => 'PRO',
                'platform_country' => $language,
                'postalzone' => Tools::getValue('postalzone'),
                'q' => Tools::getValue('q')
            )
        );
        $cities = '';

        foreach ($datas as $key => $value) {
            $cities .= $value->text."\n";
        }

        echo $cities;
    }
}
