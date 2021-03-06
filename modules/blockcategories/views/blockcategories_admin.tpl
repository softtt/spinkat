{*
* 2007-2015 PrestaShop
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
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<div class="form-group">
	<label class="control-label col-lg-3">
		<span class="label-tooltip" data-toggle="tooltip" data-html="true" title="" data-original-title="{l s='In the default theme, these images will be displayed in the top horizontal menu; but only if the category is one of the first level (see Top horizontal menu module for more info).' mod='blockcategories'}">
			{l s='Menu thumbnails' mod='blockcategories'}
		</span>
	</label>
	<div class="col-lg-4">
		{$helper}
	</div>
	<div class="col-lg-6 col-lg-offset-3">
		<div class="help-block">{l s='Рекомендуемое разрешение (для текущего шаблона): %1spx x %2spx' sprintf=[$format.width, $format.height]}</div>
		<!-- <div class="help-block">{l s='Recommended dimensions (for the default theme): %1spx x %2spx' sprintf=[$format.width, $format.height]}</div> -->
	</div>
</div>
