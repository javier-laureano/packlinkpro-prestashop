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
class HelperList extends HelperListCore
{

    public function createTemplate($tpl_name)
    {
        if ($this->context->controller instanceof AdminOrdersController && $tpl_name == 'list_header.tpl') {
            $module = Module::getInstanceByName('packlink');
            if (version_compare(_PS_VERSION_, '1.6', '>=')) {
                $this->override_folder = preg_replace('#'.preg_quote(_PS_ROOT_DIR_).'#', '../../../..', $module->getLocalPath()).'override/controllers/admin/templates/16/'.$this->override_folder;
            } else {        
                $this->override_folder = preg_replace('#'.preg_quote(_PS_ROOT_DIR_).'#', '../../../..', $module->getLocalPath()).'override/controllers/admin/templates/15/'.$this->override_folder;
            }    
        }
        
        return parent::createTemplate($tpl_name);
    }
}
