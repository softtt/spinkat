<div id="callback-link-block">
  <div class="phone">
    <span class="phone-icon"></span>
    <a id="callback-link" href="#callback_form" class="order-link" title="{l s='Callback me' mod='callback'}">
      {l s='Order a callback' mod='callback'}
    </a>
  </div>

  {if $email}
    <a class="shop-email" href="mailto:{$email}"><b>{l s='e-mail:' mod='callback'}</b> {$email}</a>
  {/if}
</div>


<div style="display:none">
  <div id="callback_form">
    <div id="callback_form_data">
      <div class="header_callback_form">
        <h2 class="title">{l s='Order callback' mod='callback'}</h2>
        <p>{l s='Fill in form below and we will call back you shortly' mod='callback'}</p>
      </div>

      <form id="id_callback_form" action="#">
        <div class="callback_content">
          <label>{l s='Name' mod='callback'}</label><sup> *</sup>
          <input type="text" name="client" class="required">

          <label>{l s='Phone' mod='callback'}</label><sup> *</sup>
          <input type="text" name="phone" class="required" data-validate="isPhoneNumber">

          <label>{l s='E-mail' mod='callback'}</label>
          <input type="text" name="email" data-validate="isEmail">

          <label>{l s='Message' mod='callback'}</label>
          <textarea name="message"></textarea>

          <p class="error validation" style="display: none;">{l s='Input data incorrect' mod='callback'}</p>
          <p class="error callback" style="display: none;">{l s='Some error occurred please contact us!' mod='callback'}</p>
          <p class="success callback" style="display: none;">{l s='Your message is sent!' mod='callback'}</p>
          <p class="submit">
            <button id="submitCallback" type="submit" class="btn button button-medium">
              <span>{l s='Send' mod='callback'}</span>
            </button>
          </p>
        </div>
      </form><!-- /end new_comment_form_content -->
    </div>

    <div id="callback_form_success" style="display: none;">
      <div class="header_callback_form">
        <h2 class="title">{l s='Order callback' mod='callback'}</h2>
      </div>
        <div class="callback_content">
          <p class="success callback">{l s='Your message is sent!' mod='callback'}</p>
        </div>
    </div>
  </div>
</div>

{addJsDefL name='callback_controller_url'}{$callback_controller_url}{/addJsDefL}
