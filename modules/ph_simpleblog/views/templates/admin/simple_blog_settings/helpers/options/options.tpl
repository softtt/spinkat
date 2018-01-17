{*
* 2007-2013 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{extends file="helpers/options/options.tpl"}
{block name="footer"}
    {if isset($categoryData['submit']) || isset($categoryData['buttons'])}
        <div class="panel-footer">
            {if isset($categoryData['submit']) && !empty($categoryData['submit'])}
            <button type="{if isset($categoryData['submit']['type'])}{$categoryData['submit']['type']}{else}submit{/if}" {if isset($categoryData['submit']['id'])}id="{$categoryData['submit']['id']}"{/if} class="btn btn-default pull-left" name="{if isset($categoryData['submit']['name'])}{$categoryData['submit']['name']}{else}submitOptions{$table}{/if}"><i class="process-icon-{if isset($categoryData['submit']['imgclass'])}{$categoryData['submit']['imgclass']}{else}save{/if}"></i> {$categoryData['submit']['title']}</button>
            {/if}
            {if isset($categoryData['buttons'])}
            {foreach from=$categoryData['buttons'] item=btn key=k}
            {if isset($btn.href) && trim($btn.href) != ''}
                <a href="{$btn.href|escape:'html':'UTF-8'}" {if isset($btn['id'])}id="{$btn['id']}"{/if} class="btn btn-default{if isset($btn['class'])} {$btn['class']}{/if}" {if isset($btn.js) && $btn.js} onclick="{$btn.js}"{/if}>{if isset($btn['icon'])}<i class="{$btn['icon']}" ></i> {/if}{$btn.title}</a>
            {else}
                <button type="{if isset($btn['type'])}{$btn['type']}{else}button{/if}" {if isset($btn['id'])}id="{$btn['id']}"{/if} class="{if isset($btn['class'])}{$btn['class']}{else}btn btn-default{/if}" name="{if isset($btn['name'])}{$btn['name']}{else}submitOptions{$table}{/if}"{if isset($btn.js) && $btn.js} onclick="{$btn.js}"{/if}>{if isset($btn['icon'])}<i class="{$btn['icon']}" ></i> {/if}{$btn.title}</button>
            {/if}
            {/foreach}
            {/if}
        </div>
    {/if}
{/block}
