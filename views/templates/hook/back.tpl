{*
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
*}
 <script>


$(document).ready(function() {
    $( "#select_country" ).change(function() {
       $("#select_city").val('');
       $('#select_city').unautocomplete();
        $("#select_city").autocomplete('{$link}&postalzone='+$('#select_country').val(), {
            minChars: 1,
            autoFill: true,
            max:20,
            matchContains: true,
            mustMatch:false,
            scroll:false,
            cacheLength:0,
            });
        $("#select_city").attr('autocomplete', 'off');

    });
  

    $("#select_city").autocomplete('{$link}&postalzone='+$('#select_country').val(), {
        minChars: 1,
        autoFill: true,
        max:20,
        matchContains: true,
        mustMatch:false,
        scroll:false,
        cacheLength:0,
        });
    $("#select_city").attr('autocomplete', 'off');


}); 


  </script>
<style>
label {
    text-align:left;
}
</style>
{if $packlink_deleted == 0}
<div class="panel panel-default">
   <div class="panel-body">
   <p>{l s='In order to use Packlink services, you have to configure the carrier that has been created when installing the module: ' mod='packlink'}
       <a href="{$carrier_link|escape:'htmlall':'UTF-8'}" target="_blank">{l s='Delivery > Carriers > Packlink' mod='packlink'}</a></p>  
   </div>
</div>
{/if}
<div class="panel panel-default">
  <div class="panel-heading">{l s='Packlink configuration' mod='packlink'}</div>
  <div class="panel-body">
    <form role="form" action = "" method="post">
      <div class="form-group">
            <label class="pull-left" for="PL_API_KEY">{l s='API Key' mod='packlink'}</label>
            <input id="PL_API_KEY" name="PL_API_KEY" value="{$PL_API_KEY|escape:'htmlall':'UTF-8'}" size="100" maxlength="100" class="form-control" required="required"/><br>
      </div>
      <div class="form-group"><label>{l s='Carrier to use with packlink' mod='packlink'}</label></div>
      <div style="clear: both"></div>
      <div class="form-group">      
      {if $packlink_deleted == 0}
          {if $packlink_enabled == 0}
            <div class="alert alert-warning">
              {l s='The module is configured to be used with Prestashop carrier "Packlink". It\'s not enabled for the moment. Please configure it and enable it by clicking' mod='packlink'}
              <a href="{$carrier_link|escape:'htmlall':'UTF-8'}" target="_blank">{l s='here' mod='packlink'}</a>
              {l s='to use Packlink service.' mod='packlink'}
            </div>
          {else}
            <div class="alert alert-success">
              {l s='The module is configured to be used with Prestashop carrier "Packlink". The carrier is enabled : every order made with this carrier will be transfered to Packlink.' mod='packlink'}
            </div>
          {/if}
      {else}
            <div class="alert alert-danger">
              {l s='The carrier linked to Packlink cannot be found anymore. Please recreate one to use the service.' mod='packlink'}
            </div>
            <a href="{$module_link|escape:'htmlall':'UTF-8'}&createCarrier=1" class="btn btn-default" role="button">{l s='Create a carrier for Packlink' mod='packlink'}</a><br>
      {/if}

      </div>
      <button type="submit" name="submit-query" value="submit" class="btn btn-default pull-right"><i class="process-icon-save"></i>{l s='Save' mod='packlink'}</button>
    </form>
  </div>
</div>
<div class="panel panel-default">
  <div class="panel-heading">{l s='Unit datas' mod='packlink'}</div>
  <div class="panel-body">
    <form role="form" action = "" method="post">
      <div class="form-group">
            <label for="weight">{l s='Conversion ' mod='packlink'}{$unit_weight|escape:'htmlall':'UTF-8'}{l s=' to 1 kg' mod='packlink'}</label>
            <input id="weight" name="weight" value="{$weight|escape:'htmlall':'UTF-8'}" class="form-control" /><br>
            <label for="length">{l s='Conversion ' mod='packlink'}{$unit_length|escape:'htmlall':'UTF-8'}{l s=' to 1 cm' mod='packlink'}</label>
            <input id="length" name="length" value="{$length|escape:'htmlall':'UTF-8'}" class="form-control" /><br>

      </div>
      <button type="submit" name="submit-conversion" value="submit" class="btn btn-default pull-right"><i class="process-icon-save"></i>{l s='Save' mod='packlink'}</button>
    </form>
  </div>
</div>
<div class="panel panel-default">
  <div class="panel-heading">{l s='Packlink address configuration' mod='packlink'}</div>
  <div class="panel-body">
    <form role="form" action = "" method="post">
      <div class="form-group">
            <label for="shop_firstname">{l s='Firstname' mod='packlink'}</label>
            <input id="shop_firstname" name="shop_firstname" value="{$shop_firstname|escape:'htmlall':'UTF-8'}" class="form-control" /><br>
            <label for="shop_lastname">{l s='Lastname' mod='packlink'}</label>
            <input id="shop_lastname" name="shop_lastname" value="{$shop_lastname|escape:'htmlall':'UTF-8'}" class="form-control" /><br>
            <label for="shop_email">{l s='Email' mod='packlink'}</label>
            <input id="shop_email" name="shop_email" value="{$shop_email|escape:'htmlall':'UTF-8'}" class="form-control" /><br>
            <label for="shop_name">{l s='Boutique name' mod='packlink'}</label>
            <input id="shop_name" name="shop_name" value="{$shop_name|escape:'htmlall':'UTF-8'}" class="form-control" /><br>
            <label for="address1">{l s='Address 1' mod='packlink'}</label>
            <input id="address1" name="address1" value="{$address1|escape:'htmlall':'UTF-8'}" class="form-control" /><br>
            <label for="address2">{l s='Address 2' mod='packlink'}</label>
            <input id="address2" name="address2" value="{$address2|escape:'htmlall':'UTF-8'}" class="form-control" /><br>
            <label for="phone">{l s='Telephone' mod='packlink'}</label>
            <input id="phone" name="phone" value="{$phone|escape:'htmlall':'UTF-8'}" class="form-control" /><br>

            <label for="select_country">{l s='Country' mod='packlink'}</label>
            <select class="form-control" id="select_country" name="select_country">
                {foreach from=$select_country_pl item=select_countries}
                    {html_options values={$select_countries->id|escape:'htmlall':'UTF-8'} output={$select_countries->name|escape:'htmlall':'UTF-8'} selected={$select_country|escape:'htmlall':'UTF-8'}}
                {/foreach}
            </select><br>

            <label for="select_city">{l s='City or zipcode' mod='packlink'}</label>
            <input id="select_city" name="select_city" value="{$select_city|escape:'htmlall':'UTF-8'}" class="form-control" required="required" /><br>

      </div>
      <button type="submit" name="submit-address" value="submit" class="btn btn-default pull-right"><i class="process-icon-save"></i>{l s='Save' mod='packlink'}</button>
    </form>
  </div>
</div>




