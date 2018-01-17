{if $blockCategTree && $blockCategTree.children|@count}
<!-- Block categories module -->
<div id="blockcategories_mobile_nav" class="menu-section blockcategories-mobile-nav">
  <div class="cat-title">
    Категории
  </div>
  <div class="block_content">
    <ul class="">
      {foreach from=$blockCategTree.children item=child name=blockCategTree}
        {if $smarty.foreach.blockCategTree.last}
          {include file="$branche_tpl_path" node=$child last='true'}
        {else}
          {include file="$branche_tpl_path" node=$child}
        {/if}
      {/foreach}
    </ul>
  </div>
</div>
<!-- /Block categories module -->
{/if}
