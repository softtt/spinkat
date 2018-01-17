<!-- Block manufacturers module -->
{if $manufacturers}
  <div id="block_manufacturers_mobile_nav" class="menu-section block-manufacturers-mobile-nav">
    <div class="cat-title">
      Бренды
    </div>
    <div class="block_content">
      {if $text_list}
      <ul>
        {foreach from=$manufacturers item=manufacturer name=manufacturer_list}
          {if $smarty.foreach.manufacturer_list.iteration <= $text_list_nb && $manufacturer.nb_products > 0}
            <li class="{if $smarty.foreach.manufacturer_list.last}last_item{elseif $smarty.foreach.manufacturer_list.first}first_item{else}item{/if}">
              <a class="{if $manufacturer.id_manufacturer == $current_manufacturer_id}selected{/if}"
              href="{$link->getmanufacturerLink($manufacturer.id_manufacturer, $manufacturer.link_rewrite)|escape:'html':'UTF-8'}" title="{l s='More about %s' mod='blockmanufacturer' sprintf=[$manufacturer.name]}">
                {$manufacturer.name|escape:'html':'UTF-8'}
              </a>
            </li>
          {/if}
        {/foreach}
      </ul>
      {/if}
    </div>
  </div>
{/if}
<!-- /Block manufacturers module -->
