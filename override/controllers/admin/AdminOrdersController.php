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
class AdminOrdersController extends AdminOrdersControllerCore
{
    public function __construct()
    {
        parent::__construct();

        $this->_select = '
        a.id_currency,
        a.id_order AS id_pdf,
        CONCAT(LEFT(c.`firstname`, 1), \'. \', c.`lastname`) AS `customer`,
        osl.`name` AS `osname`,
        os.`color`,
        IF((SELECT so.id_order FROM `'._DB_PREFIX_.'orders` so WHERE so.id_customer = a.id_customer AND so.id_order < a.id_order LIMIT 1) > 0, 0, 1) as new,
        country_lang.name as cname,
        IF(a.valid, 1, 0) badge_success,
        a.id_order as packlink';
        $this->_join = '
        LEFT OUTER JOIN `'._DB_PREFIX_.'packlink_orders` pl ON (pl.`id_order` = a.`id_order`)
        LEFT JOIN `'._DB_PREFIX_.'customer` c ON (c.`id_customer` = a.`id_customer`)
        LEFT JOIN `'._DB_PREFIX_.'address` address ON address.id_address = a.id_address_delivery
        LEFT JOIN `'._DB_PREFIX_.'country` country ON address.id_country = country.id_country
        LEFT JOIN `'._DB_PREFIX_.'country_lang` country_lang ON (country.`id_country` = country_lang.`id_country` AND country_lang.`id_lang` = '.(int)$this->context->language->id.')
        LEFT JOIN `'._DB_PREFIX_.'order_state` os ON (os.`id_order_state` = a.`current_state`)
        LEFT JOIN `'._DB_PREFIX_.'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = '.(int)$this->context->language->id.')';
        $this->_orderBy = 'id_order';
        $this->_orderWay = 'DESC';
        $this->_use_found_rows = true;
        $statuses = OrderState::getOrderStates((int)$this->context->language->id);
        foreach ($statuses as $status) {
            $this->statuses_array[$status['id_order_state']] = $status['name'];
        }
        $module = Module::getInstanceByName('packlink');

        $this->fields_list['packlink'] = array(
            'title' => $module->setTranslation(13),
            'align' => 'text-center',
            'callback' => 'packlinkActions',
            'remove_onclick' => true,
            'type' => 'select',
            'list' => array(
                'create' => $module->setTranslation(9),
                'print' => $module->setTranslation(10),
                'view' => $module->setTranslation(11),
                'send' => $module->setTranslation(12),
            ),
            'filter_key' => 'pl!details',
            'filter_type' => false,
            'orderby' => false,
        );

        $this->bulk_actions['downloadPdfLabelsPL'] = array(
            'text' => $module->setTranslation(8),
            'icon' => 'icon-print'
        );

        $this->bulk_actions['createPacklinkShipment'] = array(
            'text' =>  $module->setTranslation(7),
            'icon' => 'icon-plus-sign'
        );

    }

    public function processFilter()
    {
        Hook::exec('action'.$this->controller_name.'ListingFieldsModifier', array(
            'fields' => &$this->fields_list,
        ));

        if (!isset($this->list_id)) {
            $this->list_id = $this->table;
        }

        $prefix = $this->getCookieFilterPrefix();

        if (isset($this->list_id)) {
            foreach ($_POST as $key => $value) {
                if ($value === '') {
                    unset($this->context->cookie->{$prefix.$key});
                } elseif (stripos($key, $this->list_id.'Filter_') === 0) {
                    $this->context->cookie->{$prefix.$key} = !is_array($value) ? $value : serialize($value);
                } elseif (stripos($key, 'submitFilter') === 0) {
                    $this->context->cookie->$key = !is_array($value) ? $value : serialize($value);
                }
            }

            foreach ($_GET as $key => $value) {
                if (stripos($key, $this->list_id.'Filter_') === 0) {
                    $this->context->cookie->{$prefix.$key} = !is_array($value) ? $value : serialize($value);
                } elseif (stripos($key, 'submitFilter') === 0) {
                    $this->context->cookie->$key = !is_array($value) ? $value : serialize($value);
                }
                if (stripos($key, $this->list_id.'Orderby') === 0 && Validate::isOrderBy($value)) {
                    if ($value === '' || $value == $this->_defaultOrderBy) {
                        unset($this->context->cookie->{$prefix.$key});
                    } else {
                        $this->context->cookie->{$prefix.$key} = $value;
                    }
                } elseif (stripos($key, $this->list_id.'Orderway') === 0 && Validate::isOrderWay($value)) {
                    if ($value === '' || $value == $this->_defaultOrderWay) {
                        unset($this->context->cookie->{$prefix.$key});
                    } else {
                        $this->context->cookie->{$prefix.$key} = $value;
                    }
                }
            }
        }

        $filters = $this->context->cookie->getFamily($prefix.$this->list_id.'Filter_');
        $definition = false;
        if (isset($this->className) && $this->className) {
            $definition = ObjectModel::getDefinition($this->className);
        }

        foreach ($filters as $key => $value) {
            /* Extracting filters from $_POST on key filter_ */
            if ($value != null && !strncmp($key, $prefix.$this->list_id.'Filter_', 7 + Tools::strlen($prefix.$this->list_id))) {
                $key = Tools::substr($key, 7 + Tools::strlen($prefix.$this->list_id));
                /* Table alias could be specified using a ! eg. alias!field */
                $tmp_tab = explode('!', $key);
                $filter = count($tmp_tab) > 1 ? $tmp_tab[1] : $tmp_tab[0];

                if ($field = $this->filterToField($key, $filter)) {
                    $type = (array_key_exists('filter_type', $field) ? $field['filter_type'] : (array_key_exists('type', $field) ? $field['type'] : false));
                    if (($type == 'date' || $type == 'datetime') && is_string($value)) {
                        $value = Tools::unSerialize($value);
                    }
                    $key = isset($tmp_tab[1]) ? $tmp_tab[0].'.`'.$tmp_tab[1].'`' : '`'.$tmp_tab[0].'`';

                    // Assignment by reference
                    if (array_key_exists('tmpTableFilter', $field)) {
                        $sql_filter = & $this->_tmpTableFilter;
                    } elseif (array_key_exists('havingFilter', $field)) {
                        $sql_filter = & $this->_filterHaving;
                    } else {
                        $sql_filter = & $this->_filter;
                    }

                    /* Only for date filtering (from, to) */
                    if (is_array($value)) {
                        if (isset($value[0]) && !empty($value[0])) {
                            if (!Validate::isDate($value[0])) {
                                $this->errors[] = Tools::displayError('The \'From\' date format is invalid (YYYY-MM-DD)');
                            } else {
                                $sql_filter .= ' AND '.pSQL($key).' >= \''.pSQL(Tools::dateFrom($value[0])).'\'';
                            }
                        }

                        if (isset($value[1]) && !empty($value[1])) {
                            if (!Validate::isDate($value[1])) {
                                $this->errors[] = Tools::displayError('The \'To\' date format is invalid (YYYY-MM-DD)');
                            } else {
                                $sql_filter .= ' AND '.pSQL($key).' <= \''.pSQL(Tools::dateTo($value[1])).'\'';
                            }
                        }
                    } else {
                        $sql_filter .= ' AND ';
                        $check_key = ($key == $this->identifier || $key == '`'.$this->identifier.'`');
                        $alias = ($definition && !empty($definition['fields'][$filter]['shop'])) ? 'sa' : 'a';

                        if ($type == 'int' || $type == 'bool') {
                            $sql_filter .= (($check_key || $key == '`active`') ?  $alias.'.' : '').pSQL($key).' = '.(int)$value.' ';
                        } elseif ($type == 'decimal') {
                            $sql_filter .= ($check_key ?  $alias.'.' : '').pSQL($key).' = '.(float)$value.' ';
                        } elseif ($type == 'select') {
                            $sql_filter .= ($check_key ?  $alias.'.' : '').pSQL($key).' = \''.pSQL($value).'\' ';
                        } elseif ($type == 'price') {
                            $value = (float)str_replace(',', '.', $value);
                            $sql_filter .= ($check_key ?  $alias.'.' : '').pSQL($key).' = '.pSQL(trim($value)).' ';
                        } else {
                            if ($key != 'pl.`details`') {
                                $sql_filter .= ($check_key ?  $alias.'.' : '').pSQL($key).' LIKE \'%'.pSQL(trim($value)).'%\' ';
                            } else {
                                if ($value == 'send') {
                                    $sql_filter .= ($check_key ?  $alias.'.' : '').pSQL($key).' LIKE \'%'.pSQL("AWAITING_COMPLETION").'%\' ';
                                } else if ($value == 'create') {
                                    $sql_filter .= 'pl.id_order IS NULL';
                                } else if ($value == 'print') {
                                    $sql_filter .= ($check_key ?  $alias.'.' : '').pSQL($key).' LIKE \'%'.pSQL("READY_TO_PRINT").'%\' OR '. ($check_key ?  $alias.'.' : '').pSQL($key).' LIKE \'%'.pSQL("READY_FOR_COLLECTION").'%\' ';
                                } else if ($value == 'view') {
                                    $sql_filter .= ($check_key ?  $alias.'.' : '').pSQL($key).' LIKE \'%'.pSQL("IN_TRANSIT").'%\' OR '. ($check_key ?  $alias.'.' : '').pSQL($key).' LIKE \'%'.pSQL("DELIVERED").'%\'  OR '. ($check_key ?  $alias.'.' : '').pSQL($key).' LIKE \'%'.pSQL("PURCHASE_SUCCESS").'%\'  OR '. ($check_key ?  $alias.'.' : '').pSQL($key).' LIKE \'%'.pSQL("CARRIER_PENDING").'%\'  OR '. ($check_key ?  $alias.'.' : '').pSQL($key).' LIKE \'%'.pSQL("CARRIER_OK").'%\'  OR '. ($check_key ?  $alias.'.' : '').pSQL($key).' LIKE \'%'.pSQL("CARRIER_KO").'%\'  OR '. ($check_key ?  $alias.'.' : '').pSQL($key).' LIKE \'%'.pSQL("CANCELED").'%\' ';
                                } else {
                                    $sql_filter .= '1';
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    protected function getCookieFilterPrefix()
    {
        return str_replace(array('admin', 'controller'), '', Tools::strtolower(get_class($this)));
    }

    public function packlinkActions($id_order, $tr)
    {

        $pl_order = new PLOrder($id_order);
        $module = Module::getInstanceByName('packlink');
        $path = _PS_MODULE_DIR_."packlink/";

        if ($pl_order->details && $pl_order->details != '') {

            $details = Tools::jsonDecode($pl_order->details);
            $sdk = new PacklinkSDK(Configuration::get('PL_API_KEY'), $this);
            
            if ($details->state == "AWAITING_COMPLETION") {
                $this->context->smarty->assign(array(
                    'suivi' => $module->setTranslation(12),
                    'iconBtn' => "icon-truck",
                    'link_suivi' => $details->tracking_url,
                    'img' => '../modules/packlink/views/img/delivery.gif',
                    'target' => '_blank'
                ));
            } else if ($details->state == "READY_TO_PRINT" || $details->state == "READY_FOR_COLLECTION") {
                $pdf_url = $pl_order->pdf;
                if (!$pdf_url || $pdf_url == '') {
                    $url = $sdk->getPdfLabels($pl_order->draft_reference);
                    $pdf_url = $url['0'];
                }
                $this->context->smarty->assign(array(
                    'suivi' => $module->setTranslation(10),
                    'iconBtn' => "icon-print",
                    'link_suivi' => $pdf_url,
                    'img' => '../modules/packlink/views/img/printer.gif',
                    'target' => '_self'
                ));
            } else if ($details->state == "IN_TRANSIT" || $details->state == "DELIVERED" || $details->state == 'PURCHASE_SUCCESS' || $details->state == "CARRIER_PENDING" || $details->state == 'CARRIER_OK' || $details->state == 'CARRIER_KO' || $details->state == 'CANCELED') {
                $this->context->smarty->assign(array(
                    'suivi' => $module->setTranslation(11),
                    'iconBtn' => "icon-search",
                    'link_suivi' => $details->tracking_url,
                    'img' => '../modules/packlink/views/img/search.gif',
                    'target' => '_blank'
                ));
            }
        } else {
            $this->context->smarty->assign(array(
                'suivi' => $module->setTranslation(9),
                'iconBtn' => "icon-plus-sign",
                'link_suivi' => Context::getContext()->link->getAdminLink('AdminOrders')."&create_pl_draft=".$id_order,
                'img' => '../modules/packlink/views/img/add.gif',
                'target' => '_self'
            ));
        }
        
        if (version_compare(_PS_VERSION_, '1.6', '>=')) {
            return $module->display($path, 'views/templates/admin/_pl_action.tpl');
        } else {
            return $module->display($path, 'views/templates/admin/_pl_action15.tpl');
        }
    }

    public function processBulkDownloadPdfLabelsPL()
    {
        $path = _PS_MODULE_DIR_."packlink/pdf/";
        
        include _PS_MODULE_DIR_.'packlink/api/PDFMerger/PDFMerger.php';
        $success = 0;
        $errors = 0;
        $not_pl_orders = '';
        $module = Module::getInstanceByName('packlink');
        if (Tools::isSubmit('submitBulkdownloadPdfLabelsPLorder')) {
            $sdk = new PacklinkSDK(Configuration::get('PL_API_KEY'), $this);
            if (Tools::getValue('orderBox')) {
                $orderBoxPl = array_reverse(Tools::getValue('orderBox'));
                $pdf = @new PDFMerger();
                $i = 0;
                $lastKey = array_search(end($orderBoxPl), $orderBoxPl);
                foreach ($orderBoxPl as $key => $id_order) {
                    $pl_pdf_url = '';
                    $pl_order = new PlOrder((int)$id_order);
                    $order = new Order((int)$id_order);
                    if (!Validate::isLoadedObject($order) || !Validate::isLoadedObject($pl_order)) {
                        if ($key != $lastKey) {
                            $not_pl_orders .= $id_order.', ';
                        } else {
                            $not_pl_orders .= $id_order.'. ';
                        }
                        $errors = 1;
                    } else {
                        $pl_details = Tools::jsonDecode($pl_order->details);
                        if ($pl_details->state == "READY_TO_PRINT" || $pl_details->state == "READY_FOR_COLLECTION" || $pl_details->state == "IN_TRANSIT" || $pl_details->state == "DELIVERED") {
                            $pl_pdf_url = $pl_order->pdf;
                            if (!$pl_pdf_url || $pl_pdf_url == '') {
                                $url = $sdk->getPdfLabels($pl_order->draft_reference);
                                $pl_pdf_url = $url['0'];
                            }
                        }
                        if (!$pl_pdf_url) {
                            if ($key != $lastKey) {
                                $not_pl_orders .= $id_order.', ';
                            } else {
                                $not_pl_orders .= $id_order.'. ';
                            }
                            $errors = 1;
                        } else {
                            $result = file_put_contents($path.'plPdf'.$i.'.pdf', Tools::file_get_contents($pl_pdf_url));
                            $pdf->addPDF($path.'plPdf'.$i.'.pdf', 'all');
                            $success = 1;
                        }
                    }
                    $i ++;
                }
                if ($errors && $success) {
                    $pdf->merge('file', $path.'PacklinkExpedition.pdf');
                    $pdf_link = Context::getContext()->link->getAdminLink('AdminGeneratePdfPl').'&submitAction=generatePDF';
                    $module->setTranslation(2, $not_pl_orders, $pdf_link);
                } elseif (!$errors && $success) {
                    $pdf->merge('download', 'PacklinkExpedition.pdf');
                } elseif ($errors && !$success) {
                    $module->setTranslation(3, $not_pl_orders);
                }
            } else {
                $module->setTranslation(1);
                $errors = 1;
            }

            if (!$errors) {
                $pdf->merge('download', 'PacklinkExpedition.pdf');
            }
        }
    }

    public function processBulkCreatePacklinkShipment()
    {
        if (Tools::isSubmit('submitBulkcreatePacklinkShipmentorder')) {
            $module = Module::getInstanceByName('packlink');
            $existed_orders = '';
            $created_oreders = '';
            $no_api_key = '';
            if (Tools::getValue('orderBox')) {
                $orderBoxPl = array_reverse(Tools::getValue('orderBox'));
                $lastKey = array_search(end($orderBoxPl), $orderBoxPl);
                foreach ($orderBoxPl as $key => $id_order) {
                    $pl_order = new PlOrder((int)$id_order);
                    if ($pl_order->id_order) {
                        if ($key != $lastKey) {
                            $existed_orders .= $id_order.', ';
                        } else {
                            $existed_orders .= $id_order.'. ';
                        }
                    } else {
                        if (Configuration::get('PL_API_KEY')) {
                            $module->createPlShippement((int)$id_order);
                            if ($key != $lastKey) {
                                $created_oreders .= $id_order.', ';
                            } else {
                                $created_oreders .= $id_order.'. ';
                            }
                        } else {
                            $no_api_key .= $id_order.', ';
                        }
                    }
                }
                if ($existed_orders) {
                    $module->setTranslation(4, $existed_orders);
                }
                if ($no_api_key) {
                    $module->setTranslation(14);
                }
                if ($created_oreders) {
                    $module->setTranslation(6, $created_oreders);
                }
            } else {
                $module->setTranslation(5);
            }
        }
    }

    public function postProcess()
    {
        if (Tools::getValue('create_pl_draft')) {
            $module = Module::getInstanceByName('packlink');
            if (Configuration::get('PL_API_KEY')) {
                $module->createPlShippement((int)Tools::getValue('create_pl_draft'));
                $module->setTranslation(6, (int)Tools::getValue('create_pl_draft'));
            } else {
                $module->setTranslation(14);
            }
        }

        parent::postProcess();
    }
}
