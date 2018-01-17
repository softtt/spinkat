{if isset($links) && count($links)}
<div class="additional-top-links-mobile-nav menu-section" id="additional_top_links_mobile_nav">
  <div class="cat-title">
    {if isset($default) && $default != ''}
      {$default|escape:'html':'UTF-8'}
    {/if}
  </div>

  <div class="block_content">
    <ul>
      {foreach from=$links item=link}
        <li>
          <a href="{$link['link']|escape:'html':'UTF-8'}">{$link['label']|escape:'html':'UTF-8'}</a>
        </li>
      {/foreach}
    </ul>
  </div>
</div>
{/if}
