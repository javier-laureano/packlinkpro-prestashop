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

if (!defined('_PS_VERSION_')) {
    exit;
}

class Packlink extends Module
{

    /**
     * Module link in BO
     * @var String
     */
    private $module_link;

    /**
     * Logs
     * @var array
     */
    protected $logs = array();

    /**
     * If debug mod
     * @var boolean
     */
    protected $debug = true;

    /**
     * Constructor of module
     */
    public function __construct()
    {
        $this->name = 'packlink';
        $this->tab = 'shipping_logistics';
        $this->version = '1.0.1';
        $this->author = '202-ecommerce';
        $this->module_key = 'a7a3a395043ca3a09d703f7d1c74a107';
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => '2.0');
        $this->bootstrap = true;

        parent::__construct();

        $this->includeFiles();

        $this->displayName = $this->l('Use Packlink service');
        $this->description = $this->l('Use Packlink service');
    }

    private function includeFiles()
    {
        $path = $this->getLocalPath().'classes'.DIRECTORY_SEPARATOR;
        foreach (scandir($path) as $class) {
            if ($class != "index.php" && is_file($path.$class)) {
                $class_name = Tools::substr($class, 0, -4);
                if ($class_name != 'index' && !preg_match('#\.old#isD', $class) && !class_exists($class_name)) {
                    require_once $path.$class_name.'.php';
                }
            }
        }

        $path .= 'helper'.DIRECTORY_SEPARATOR;

        foreach (scandir($path) as $class) {
            if ($class != "index.php" && is_file($path.$class)) {
                $class_name = Tools::substr($class, 0, -4);
                if ($class_name != 'index' && !preg_match('#\.old#isD', $class) && !class_exists($class_name)) {
                    require_once $path.$class_name.'.php';
                }
            }
        }

        $path .= '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'api'.DIRECTORY_SEPARATOR;

        foreach (scandir($path) as $class) {
            if ($class != "index.php" && is_file($path.$class)) {
                $class_name = Tools::substr($class, 0, -4);
                if ($class_name != 'index' && !preg_match('#\.old#isD', $class) && !class_exists($class_name)) {
                    require_once $path.$class_name.'.php';
                }
            }
        }
    }


    ############################################################################################################
    # Install / Upgrade / Uninstall
    ############################################################################################################

    /**
     * Module install
     * @return boolean if install was successfull
     */
    public function install()
    {
        // Install default
        if (!parent::install()) {
            return false;
        }

        // install DataBase
        if (!$this->installSQL()) {
            return false;
        }
        if (!Configuration::updateValue('PL_API_KEY', '')) {
            return false;
        }
        if (!Configuration::updateValue('PL_API_KG', '1')) {
            return false;
        }
        if (!Configuration::updateValue('PL_API_CM', '1')) {
            return false;
        }
        if (!Configuration::updateValue('PL_API_FIRSTNAME', '')) {
            return false;
        }
        if (!Configuration::updateValue('PL_API_LASTNAME', '')) {
            return false;
        }
        if (!Configuration::updateValue('PL_API_EMAIL', Configuration::get('PS_SHOP_EMAIL'))) {
            return false;
        }
        if (!Configuration::updateValue('PL_API_BOUTIQUE', Configuration::get('PS_SHOP_NAME'))) {
            return false;
        }
        if (!Configuration::updateValue('PL_API_ADD1', Configuration::get('PS_SHOP_ADDR1'))) {
            return false;
        }
        if (!Configuration::updateValue('PL_API_ADD2', Configuration::get('PS_SHOP_ADDR2'))) {
            return false;
        }
        if (!Configuration::updateValue('PL_API_PHONE', Configuration::get('PS_SHOP_PHONE'))) {
            return false;
        }
        if (!Configuration::updateValue('PL_API_COUNTRY', '')) {
            return false;
        }
        if (!Configuration::updateValue('PL_API_STATE', '')) {
            return false;
        }
        if (!Configuration::updateValue('PL_API_CITY', '')) {
            return false;
        }
        if (!Configuration::updateValue('PL_API_COUNTRY_NAME', '')) {
            return false;
        }
        if (!Configuration::updateValue('PL_API_ZIPCODE', '')) {
            return false;
        }
        if (!Configuration::updateValue('PL_API_CITY_NAME', '')) {
            return false;
        }
        if (!Configuration::updateValue('PL_API_CITY_ID', '')) {
            return false;
        }

        // Install tabs
        if (!$this->installTabs()) {
            return false;
        }

        // Install carrier
        if (!$this->installCarrier()) {
            return false;
        }

        // Registration hook
        if (!$this->registrationHook()) {
            return false;
        }

        return true;
    }

    /**
     * Module uninstall
     * @return boolean if uninstall was successfull
     */
    public function uninstall()
    {

        // Uninstall default
        if (!parent::uninstall()) {
            return false;
        }

        //Uninstall DataBase
        if (!$this->uninstallSQL()) {
            return false;
        }

        // Delete tabs
        if (!$this->uninstallTabs()) {
            return false;
        }

        // Install carrier
        if (!$this->uninstallCarrier()) {
            return false;
        }
        if (!Configuration::deleteByName('PL_API_KEY')) {
            return false;
        }
        if (!Configuration::deleteByName('PL_API_CM')) {
            return false;
        }
        if (!Configuration::deleteByName('PL_API_KG')) {
            return false;
        }
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
        if (!Configuration::deleteByName('PL_API_COUNTRY')) {
            return false;
        }
        if (!Configuration::deleteByName('PL_API_STATE')) {
            return false;
        }
        if (!Configuration::deleteByName('PL_API_CITY')) {
            return false;
        }
        if (!Configuration::deleteByName('PL_API_COUNTRY_NAME')) {
            return false;
        }
        if (!Configuration::deleteByName('PL_API_ZIPCODE')) {
            return false;
        }
        if (!Configuration::deleteByName('PL_API_CITY_NAME')) {
            return false;
        }
        if (!Configuration::deleteByName('PL_API_CITY_ID')) {
            return false;
        }

        return true;
    }

    ############################################################################################################
    # Tabs
    ############################################################################################################

    /**
     * Initialisation to install / uninstall
     */
    private function installTabs()
    {
        
        $menu_id = 14;

        // Install All Tabs directly via controller's install function
        $path = $this->getLocalPath().'controllers'.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR;
        $controllers = scandir($path);
        foreach ($controllers as $controller) {
            if ($controller != 'index.php' && !preg_match('#\.old#isD', $controller) && is_file($path.$controller)) {
                require_once $path.$controller;
                $controller_name = Tools::substr($controller, 0, -4);
                //Check if class_name is an existing Class or not
                if (class_exists($controller_name)) {
                    if (method_exists($controller_name, 'install')) {
                        if (!call_user_func(array($controller_name, 'install'), $menu_id, $this->name)) {
                            return false;
                        }
                    }
                }
            }
        }
        return true;
    }
    

    /**
     * Delete tab
     * @return  boolean if successfull
     */
    public function uninstallTabs()
    {
        return PlTotAdminTabHelper::deleteAdminTabs($this->name);
    }

    ############################################################################################################
    # SQL
    ############################################################################################################
    
    /**
     * Install DataBase table
     * @return boolean if install was successfull
     */
    private function installSQL()
    {
        // Install All Object Model SQL via install function
        $path = $this->getLocalPath().'classes'.DIRECTORY_SEPARATOR;
        $classes = scandir($path);
        foreach ($classes as $class) {
            if ($class != 'index.php' && !preg_match('#\.old#isD', $class) && is_file($path.$class)) {
                $class_name = Tools::substr($class, 0, -4);
                // Check if class_name is an existing Class or not
                if (class_exists($class_name)) {
                    if (method_exists($class_name, 'install')) {
                        if (!call_user_func(array($class_name, 'install'))) {
                            return false;
                        }
                    }
                }
            }
        }
        
        $sql = array();
        $sql[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."packlink_orders` (
              `id_order` INT(11) NOT NULL PRIMARY KEY,
              `draft_reference` VARCHAR(255) NOT NULL,
              `postcode` VARCHAR(21),
              `postalzone` INT(11)
        ) ENGINE = "._MYSQL_ENGINE_." ";

        foreach ($sql as $q) {
            if (!DB::getInstance()->execute($q)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Uninstall DataBase table
     * @return boolean if install was successfull
     */
    private function uninstallSQL()
    {
        // Uninstall All Object Model SQL via install function
        $path = $this->getLocalPath().'classes'.DIRECTORY_SEPARATOR;
        $classes = scandir($path);
        foreach ($classes as $class) {
            if ($class != 'index.php' && !preg_match('#\.old#isD', $class) && is_file($path.$class)) {
                $class_name = Tools::substr($class, 0, -4);
                // Check if class_name is an existing Class or not
                if (class_exists($class_name)) {
                    if (method_exists($class_name, 'uninstall')) {
                        if (!call_user_func(array($class_name, 'uninstall'))) {
                            return false;
                        }
                    }
                }
            }
        }
        
        $sql = "DROP TABLE IF EXISTS `"._DB_PREFIX_."packlink_orders`";
        if (!DB::getInstance()->execute($sql)) {
            return false;
        }
    
        return true;
    }


    ############################################################################################################
    # Carrier
    ############################################################################################################
    private function installCarrier()
    {
        return PackLinkCarrier::installCarrierPL();
    }

    private function uninstallCarrier()
    {
        // We delete the carriers we created earlier
        return PackLinkCarrier::uninstallCarrierPL();
    }



    ############################################################################################################
    # Hook
    ############################################################################################################

    /**
     * [registrationHook description]
     * @return [type] [description]
     */
    private function registrationHook()
    {
        // Example :
        if (!$this->registerHook('actionOrderStatusPostUpdate')) {
            return false;
        }

        if (!$this->registerHook('ActionCarrierUpdate')) {
            return false;
        }

        if (!$this->registerHook('ActionOrderStatusUpdate')) {
            return false;
        }
        
        return true;
    }

    /*
    ** Hook update carrier
    **
    */

    ############################################################################################################
    # Administration
    ############################################################################################################

    /**
     * Admin display
     * @return String Display admin content
     */
    public function getContent()
    {

        // Suffix to link
        $suffixLink = '&configure='.$this->name.'&token='.Tools::getValue('token');
        $suffixLink .= '&tab_module='.$this->tab.'&module_name='.$this->name;
        $output = '';
        $link = new Link;
        $packlink_enabled = $this->enablePacklink();
        $packlink_deleted = $this->deletedPacklink();

        $sdk = new PacklinkSDK(Configuration::get('PL_API_KEY'), $this);
        
        $default_language = new Language(Configuration::get('PS_LANG_DEFAULT'));
        $language = $default_language->iso_code;
        $language_code = $default_language->language_code;


        // Base
        if (version_compare(_PS_VERSION_, '1.5', '>')) {
            $this->module_link = 'index.php?controller='.Tools::getValue('controller').$suffixLink;
        } else {
            $this->module_link = 'index.php?tab='.Tools::getValue('tab').$suffixLink;
        }

        if (Tools::getValue('createCarrier') == 1) {
            $this->installCarrier();
            Tools::redirectAdmin($this->module_link);
        }

        if (Tools::getValue('submit-query')) {
            $PL_API_KEY = Tools::getValue('PL_API_KEY');
            Configuration::updateValue('PL_API_KEY', $PL_API_KEY);
            $output .=$this->displayConfirmation($this->l('Settings updated successfully'));
        } else {
            $PL_API_KEY = Configuration::get('PL_API_KEY');
        }

        if (Tools::getValue('submit-address')) {
 
            //Refaire call api pour récupérer les valeurs d'identifiants de zip code et postalzone

            $packlink_name = Tools::getValue('shop_name');
            Configuration::updateValue('PL_API_BOUTIQUE', $packlink_name);
            $packlink_firstname = Tools::getValue('shop_firstname');
            Configuration::updateValue('PL_API_FIRSTNAME', $packlink_firstname);
            $packlink_lastname = Tools::getValue('shop_lastname');
            Configuration::updateValue('PL_API_LASTNAME', $packlink_lastname);
            $packlink_email = Tools::getValue('shop_email');
            Configuration::updateValue('PL_API_EMAIL', $packlink_email);
            $packlink_address1 = Tools::getValue('address1');
            Configuration::updateValue('PL_API_ADD1', $packlink_address1);
            $packlink_address2 = Tools::getValue('address2');
            Configuration::updateValue('PL_API_ADD2', $packlink_address2);
            $packlink_country = Tools::getValue('select_country');
            Configuration::updateValue('PL_API_COUNTRY', $packlink_country);
            $packlink_phone = Tools::getValue('phone');
            Configuration::updateValue('PL_API_PHONE', $packlink_phone);

            $packlink_country_name = $sdk->getPostalZones(
                array(
                    'language' => $language.'_'.Tools::strtoupper($language),
                    'platform' => 'PRO',
                    'platform_country' => $language
                )
            );

            foreach ($packlink_country_name as $key => $value) {
                if ($value->id == $packlink_country) {
                    Configuration::updateValue('PL_API_COUNTRY_NAME', $value->isoCode);
                }
            }

            $sdk = new PacklinkSDK(Configuration::get('PL_API_KEY'), $this);

            $packlink_city = Tools::getValue('select_city');

            $datas_city = $sdk->getPostalCodes(
                array(
                    'platform' => 'PRO',
                    'platform_country' => $language,
                    'postalzone' => $packlink_country,
                    'q' => urlencode($packlink_city)
                )
            );

            $error_city = false;
            
            if (empty($datas_city)) {
                $error_city = true;
            }

            foreach ($datas_city as $key => $value) {
                if ($value->text == $packlink_city) {
                    Configuration::updateValue('PL_API_CITY', $packlink_city);
                    Configuration::updateValue('PL_API_CITY_NAME', $datas_city[$key]->city);
                    Configuration::updateValue('PL_API_STATE', $datas_city[$key]->state);
                    Configuration::updateValue('PL_API_ZIPCODE', $datas_city[$key]->zipcode);
                    Configuration::updateValue('PL_API_CITY_ID', $datas_city[$key]->id);
                } else {
                    $error_city = true;
                }
            }

            if ($error_city) {
                $output .=$this->displayError($this->l('Error : your city or zipcode is invalid. Please choose one from the auto complete list.'));
                $packlink_city = Configuration::get('PL_API_CITY');
            } else {
                $output .=$this->displayConfirmation($this->l('Settings updated successfully'));
            }
          
        } else {
            $packlink_name = Configuration::get('PL_API_BOUTIQUE');
            $packlink_firstname = Configuration::get('PL_API_FIRSTNAME');
            $packlink_lastname = Configuration::get('PL_API_LASTNAME');
            $packlink_email = Configuration::get('PL_API_EMAIL');
            $packlink_address1 = Configuration::get('PL_API_ADD1');
            $packlink_address2 = Configuration::get('PL_API_ADD2');
            $packlink_city = Configuration::get('PL_API_CITY');
            $packlink_country = Configuration::get('PL_API_COUNTRY');
            $packlink_phone = Configuration::get('PL_API_PHONE');
        }

        $last_carrier_id = $this->getLinkId();
        if (version_compare(_PS_VERSION_, '1.6', '>')) {
            $carrier_link = $link->getAdminLink('AdminCarrierWizard', true).'&id_carrier='.$last_carrier_id;
            $carrier_link_general = $link->getAdminLink('AdminCarrierWizard', true);
        } else {
            $carrier_link = $link->getAdminLink('AdminCarriers', true).'&id_carrier='.$last_carrier_id.'&updatecarrier';
            $carrier_link_general = $link->getAdminLink('AdminCarriers', true);
        }

        $datas = $sdk->getPostalZones(
            array(
                'language' => $language.'_'.Tools::strtoupper($language),
                'platform' => 'PRO',
                'platform_country' => $language
            )
        );
  

        if (Tools::getValue('submit-conversion')) {
            $length = Tools::getValue('length');
            Configuration::updateValue('PL_API_CM', $length);
            $weight = Tools::getValue('weight');
            Configuration::updateValue('PL_API_KG', $weight);
            $output .=$this->displayConfirmation($this->l('Settings updated successfully'));
        } else {
            $length = Configuration::get('PL_API_CM');
            $weight = Configuration::get('PL_API_KG');
        }

        $unit_weight = Configuration::get('PS_WEIGHT_UNIT');
        $unit_length = Configuration::get('PS_DIMENSION_UNIT');

        $this->context->smarty->assign(array(
            'PL_API_KEY' => $PL_API_KEY,
            'packlink_enabled' => $packlink_enabled,
            'packlink_deleted' => $packlink_deleted,
            'carrier_link' => $carrier_link,
            'carrier_link_general' => $carrier_link_general,
            'module_link' => $this->module_link,
            'shop_name' => $packlink_name,
            'address1' => $packlink_address1,
            'address2' => $packlink_address2,
            'select_city' => $packlink_city,
            'select_country' => $packlink_country,
            'phone' => $packlink_phone,
            'shop_firstname' => $packlink_firstname,
            'shop_lastname' => $packlink_lastname,
            'shop_email' => $packlink_email,
            'select_country_pl' => $datas,
            'language' => $language,
            'link' => $link->getAdminLink('AdminPackLink', true).'&ajax=true&action=GetPostCode',
            'weight' => $weight,
            'length' => $length,
            'unit_weight' => $unit_weight,
            'unit_length' => $unit_length
        ));
        $this->postProcess();

        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $this->context->controller->addCSS($this->_path.'views/css/bootstrap.min.css', 'all');
            $this->context->controller->addJS(_PS_JS_DIR_.'/jquery/plugins/autocomplete/jquery.autocomplete.js', 'all');
        }
        
        return $output.$this->display(__FILE__, 'back.tpl');
        
    }


    public function enablePacklink()
    {
        $pl_carrier_id =  Configuration::get('PL_API_CARRIER_REF');

        $sql = 'SELECT active FROM '._DB_PREFIX_.'carrier WHERE id_reference = '.(int)$pl_carrier_id.' ORDER BY id_carrier DESC';
        $is_active = Db::getInstance()->getValue($sql);

        return $is_active;

    }

    public function getLinkId()
    {
        $pl_carrier_id =  Configuration::get('PL_API_CARRIER_REF');

        $sql = 'SELECT id_carrier FROM '._DB_PREFIX_.'carrier WHERE id_reference = '.(int)$pl_carrier_id.' ORDER BY id_carrier DESC';
        $last_carrier_id = Db::getInstance()->getValue($sql);

        return $last_carrier_id;

    }

    public function deletedPacklink()
    {
        $pl_carrier_id =  Configuration::get('PL_API_CARRIER_REF');

        $sql = 'SELECT deleted FROM '._DB_PREFIX_.'carrier WHERE id_reference = '.(int)$pl_carrier_id.' ORDER BY id_carrier DESC';
        $is_deleted = Db::getInstance()->getValue($sql);

        return $is_deleted;

    }
    
    public function getCartAddressDelivery($id_address_delivery)
    {

        $sql = 'SELECT * FROM '._DB_PREFIX_.'address WHERE id_address = '.(int)$id_address_delivery;
        $id_address_delivery = Db::getInstance()->executeS($sql);

        return $id_address_delivery;

    }

    public function getCartCountryDelivery($country)
    {

        $sql = 'SELECT iso_code FROM '._DB_PREFIX_.'country WHERE id_country = '.(int)$country;
        $country = Db::getInstance()->getValue($sql);

        return $country;

    }

    public function getCartStateDelivery($state)
    {

        $sql = 'SELECT name FROM '._DB_PREFIX_.'state WHERE id_state = '.(int)$state;
        $state = Db::getInstance()->getValue($sql);

        return $state;

    }

    public function getEmailDelivery($customer_id)
    {

        $sql = 'SELECT email FROM '._DB_PREFIX_.'customer WHERE id_customer = '.(int)$customer_id;
        $customer_email = Db::getInstance()->getValue($sql);

        return $customer_email;

    }

    public function getCartProductCat($id_category)
    {

        $sql = 'SELECT name FROM '._DB_PREFIX_.'category_lang WHERE id_category = '.(int)$id_category;
        $id_category_default = Db::getInstance()->getValue($sql);

        return $id_category_default;

    }

    public function convertToDistance($distance)
    {
        $distance = $distance * Configuration::get('PL_API_KG');
        return $distance;
    }

    public function convertToWeight($weight)
    {
        $weight = $weight * Configuration::get('PL_API_CM');
        return $weight;
    }

    public function hookActionOrderStatusPostUpdate($params)
    {
        if ($params['newOrderStatus']->id != 12 && $params['newOrderStatus']->id != 2) {
            return false;
        }

        if (!is_array($params)) {
            $id_order_params = $params;
        } else {
            $id_order_params = $params['id_order'];
        }

        $order = new Order((int) $id_order_params);

        if ($order->id_carrier == (int)(Configuration::get('PL_API_CARRIER'))) {
        
            $address_delivery = $this->getCartAddressDelivery($order->id_address_delivery);
            $country_delivery = $this->getCartCountryDelivery($address_delivery[0]['id_country']);
            $state_delivery = $this->getCartStateDelivery($address_delivery[0]['id_state']);
            if (!$state_delivery) {
                $state_delivery = '';
            }
            $email_delivery = $this->getEmailDelivery($address_delivery[0]['id_customer']);

            $cart_products = array();
            $cart_products = $order->getProducts();


            $sdk = new PacklinkSDK(Configuration::get('PL_API_KEY'), $this);
            $datas_client = $sdk->getCitiesByPostCode($address_delivery[0]['postcode'], $country_delivery);

            $postal_zone_id_to = $datas_client[0]->postalZone->id;
            $zip_code_id_to = $datas_client[0]->id;

            if (count($datas_client) > 1) {
                foreach ($datas_client as $key => $value) {
                    $city = Tools::strtolower($address_delivery[0]['city']);
                    $arr = array("-", "/", ",", "_");
                    $city_formated = str_replace($arr, " ", $city);
                    $city_formated_pl = Tools::strtolower(str_replace($arr, " ", $value->city->name));

                    if ($city_formated_pl == $city_formated) {
                        $postal_zone_id_to = $value->postalZone->id;
                        $zip_code_id_to = $value->id;
                    }
                }
            }

            

            $shipments_datas =
                 array(
                    'from' => array(
                         'name' => Configuration::get('PL_API_FIRSTNAME'),
                         'surname' => Configuration::get('PL_API_LASTNAME'),
                         'company' => Configuration::get('PL_API_BOUTIQUE'),
                         'street1' => Configuration::get('PL_API_ADD1'),
                         'street2' => Configuration::get('PL_API_ADD2'),
                         'zip_code' => Configuration::get('PL_API_ZIPCODE'),
                         'city' => Configuration::get('PL_API_CITY_NAME'),
                         'country' => Configuration::get('PL_API_COUNTRY_NAME'),
                         'state' => Configuration::get('PL_API_STATE'),
                         'phone' => Configuration::get('PL_API_PHONE'),
                         'email' => Configuration::get('PL_API_EMAIL')
                    ),
                    'to' => array(
                         'name' => $address_delivery[0]['firstname'],
                         'surname' => $address_delivery[0]['lastname'],
                         'company' => $address_delivery[0]['company'],
                         'street1' => $address_delivery[0]['address1'],
                         'street2' => $address_delivery[0]['address2'],
                         'zip_code' => $address_delivery[0]['postcode'],
                         'city' => $address_delivery[0]['city'],
                         'country' => $country_delivery,
                         'state' => $state_delivery,
                         'phone' => $address_delivery[0]['phone_mobile'],
                         'email' => $email_delivery
                    ),
                    'additional_data' => array(
                         'postal_zone_id_from' => Configuration::get('PL_API_COUNTRY'),
                         'postal_zone_id_to' => $postal_zone_id_to,
                         'zip_code_id_from' => Configuration::get('PL_API_CITY_ID'),
                         'zip_code_id_to' => $zip_code_id_to,
                    ),
                    'contentvalue' => $order->total_products_wt,
                    'source' => 'module_prestashop',
            );

            if (count($cart_products) > 1) {
                $packages = array(
                     'weight' => 0,
                     'length' => 0,
                     'width' => 0,
                     'height' => 0
                );
                $shipments_datas['packages'][] = $packages;
                $cmpt = 0;
                foreach ($cart_products as $key => $value) {

                    $category = $this->getCartProductCat($value['id_category_default']);

                    $product_link = $this->context->link->getProductLink($value['product_id']);
                    $image = Image::getCover($value['product_id']);
                    $product = new Product($value['product_id'], false, Context::getContext()->language->id);

                    $product_img_link = $this->context->link->getImageLink($product->link_rewrite, $image['id_image']);

                    $weight = $this->convertToWeight($value['weight']);
                    $width = $this->convertToDistance($value['width']);
                    $height = $this->convertToDistance($value['height']);
                    $depth = $this->convertToDistance($value['depth']);
                    
                    $value['cart_quantity'] = $value['product_quantity'];

                    $items =
                        array(
                            'quantity' => $value['cart_quantity'],
                            'category_name'  => $category,
                            'picture_url' => $product_img_link,
                            'item_id' => $value['product_id'],
                            'price' => $value['product_price'],
                            'item_url' => $product_link,
                            'title' => $value['product_name']
                    );

                    $shipments_datas['additional_data']['items'][] = $items;

                    for ($i = 1; $i <= $value['cart_quantity']; $i++) {
                        $packages = array(
                             'weight' => $weight,
                             'length' => $depth,
                             'width' => $width,
                             'height' => $height
                        );
                        $shipments_datas['additional_data']['items'][$cmpt]['package'][] = $packages;
                        
                    }
                    $cmpt++;
                }
            } else {
                

                foreach ($cart_products as $key => $value) {

                    $weight = $this->convertToWeight($cart_products[$key]['weight']);
                    $width = $this->convertToDistance($cart_products[$key]['width']);
                    $height = $this->convertToDistance($cart_products[$key]['height']);
                    $depth = $this->convertToDistance($cart_products[$key]['depth']);

                    for ($i = 1; $i <= $cart_products[$key]['product_quantity']; $i++) {
                        $packages = array(
                             'weight' => $weight,
                             'length' => $depth,
                             'width' => $width,
                             'height' => $height
                        );
                        $shipments_datas['packages'][] = $packages;
                    }
                }
                
            }


            $this->logs[] = '======================================================================================================';

            $this->logs[] = '['.strftime('%Y-%m-%d %H:%M:%S').'] : Id order : '.$id_order_params;
            $this->logs[] = '['.strftime('%Y-%m-%d %H:%M:%S').'] : Id cart : '.$order->id_cart;
            $this->logs[] = '['.strftime('%Y-%m-%d %H:%M:%S').'] : Id carrier : '.$order->id_carrier;

            foreach ($shipments_datas as $key => $data) {
                $this->logs[] = '['.strftime('%Y-%m-%d %H:%M:%S').'] : Shippement datas : '.$key;
                if (is_array($data)) {
                    foreach ($data as $key => $value1) {
                        if (is_array($value1)) {
                            foreach ($value1 as $key => $value2) {
                                if (is_array($value2)) {
                                    foreach ($value2 as $key => $value3) {
                                        if (is_array($value3)) {
                                            foreach ($value3 as $key => $value4) {
                                                if (!is_array($value4)) {
                                                    $this->logs[] = '['.strftime('%Y-%m-%d %H:%M:%S').'] : Items datas : '.$key.' => '.$value4;
                                                }
                                            }
                                        } else {
                                            $this->logs[] = '['.strftime('%Y-%m-%d %H:%M:%S').'] : Items datas : '.$key.' => '.$value3;
                                        }
                                    }
                                } else {
                                    $this->logs[] = '['.strftime('%Y-%m-%d %H:%M:%S').'] : Shippement datas : '.$key.' => '.$value2;
                                }
                            }
                        } else {
                            $this->logs[] = '['.strftime('%Y-%m-%d %H:%M:%S').'] : Shippement datas : '.$key.' => '.$value1;
                        }
                    }
                }
            }
            
            $this->logs[] = '======================================================================================================';

            if ($this->debug) {
                $this->writeLog();
            }

 

            $_packlink_orders_old = new PLOrder($order->id);

            if ($_packlink_orders_old->id == '') {

                $pl_reference = $this->callSDK($shipments_datas);

                if ($pl_reference->reference != '') {
                    $_packlink_orders = new PLOrder();
                    $_packlink_orders->id_order = $order->id;
                    $_packlink_orders->draft_reference = $pl_reference->reference;
                    $_packlink_orders->postcode = $zip_code_id_to;
                    $_packlink_orders->postalzone = $postal_zone_id_to;
                    $_packlink_orders->save();
                }
                
            }
        }
    }

    public function callSDK($shipments_datas)
    {
        # TODO : call to packlink
        $sdk = new PacklinkSDK(Configuration::get('PL_API_KEY'), $this);
        $datas = $sdk->createDraft($shipments_datas);

        return $datas;

    }

    public function hookActionCarrierUpdate($params)
    {
        if ($params['carrier']->id_reference == '') {
            $sql = 'SELECT id_reference FROM '._DB_PREFIX_.'carrier WHERE id_carrier = '.(int) $params['id_carrier'];
            $id = Db::getInstance()->getValue($sql);
        }
        if (Configuration::get('PL_API_CARRIER_REF') == $params['carrier']->id_reference || Configuration::get('PL_API_CARRIER_REF') == $id) {
            Configuration::updateValue('PL_API_CARRIER', $params['carrier']->id);
        }
    }

    public function getOrderShippingCost($params, $shipping_cost)
    {
        return $shipping_cost;
    }


    public function getOrderShippingCostExternal($params)
    {
        return $this->getOrderShippingCost($params);
    }

    final public function getLogs()
    {
        return $this->logs;
    }

    final protected function writeLog()
    {
        if (!$this->debug) {
            return false;
        }

        $handle = fopen(dirname(__FILE__).'/log_order.txt', 'a+');

        foreach ($this->getLogs() as $value) {
            fwrite($handle, $value."\r");
        }

        $this->logs = array();

        fclose($handle);
    }

    /**
     * Processing post in BO
     */
    public function postProcess()
    {
    }
}
