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
<!-- Simple one click checkout module -->
<div id="simple_one_click_checkout">
    <p id="one_click_checkout" class="buttons_bottom_block no-print">
        <button type="one_click_checkout" name="one_click_checkout" class="button ajax_add_to_cart_button" href="#one_click_checkout_form">
            <span>{l s='Checkout in on click' mod='simple_one_click_checkout'}</span>
        </button>
    </p>
</div>

<div style="display:none">
  <div id="one_click_checkout_form">
    <div id="one_click_checkout_form_data">
      <div class="header_one_click_checkout_form">
        <h2 class="title">{l s='Checkout in one click' mod='simple_one_click_checkout'}</h2>
      </div>

      <form id="asdasdasd" action="#">
        <div class="simple_one_click_checkout_content">
          <label>{l s='Name' mod='simple_one_click_checkout'}</label><sum> *</sum>
          <input type="text" name="client" class="required">

          <label>{l s='Phone' mod='simple_one_click_checkout'}</label><sum> *</sum>
          <input type="text" name="phone" class="required" data-validate="isPhoneNumber">

          <label>{l s='E-mail' mod='simple_one_click_checkout'}</label>
          <input type="text" name="email" data-validate="isEmail">

          <label>{l s='Message' mod='simple_one_click_checkout'}</label>
          <textarea name="message"></textarea>

          <p class="error validation" style="display: none;">{l s='Input data incorrect' mod='simple_one_click_checkout'}</p>
          <p class="error simple_one_click_checkout" style="display: none;">{l s='Some error occurred while saving your order. Please contact us!' mod='simple_one_click_checkout'}</p>
          <p class="success simple_one_click_checkout" style="display: none;">{l s='Your order has been saved.' mod='simple_one_click_checkout'}</p>
          <p class="submit">
            <button id="submitsimple_one_click_checkout" type="submit" class="btn button button-medium">
              <span>{l s='Checkout' mod='simple_one_click_checkout'}</span>
            </button>
          </p>
        </div>
      </form><!-- /end new_comment_form_content -->
    </div>

    <div id="simple_one_click_checkout_form_success" style="display: none;">
      <div class="header_one_click_checkout_form">
        <h2 class="title">{l s='Checkout in one click' mod='simple_one_click_checkout'}</h2>
      </div>
        <div class="simple_one_click_checkout_content">
          <p class="success simple_one_click_checkout">{l s='Your order has been saved. Manager will contact you soon.' mod='simple_one_click_checkout'}</p>
        </div>
    </div>
  </div>
</div>
<!-- /Simple one click checkout module -->

{addJsDef oneclickorder_url=$link->getModuleLink('simple_one_click_checkout', 'oneclickorder', array(), true)|escape:'quotes':'UTF-8'}
