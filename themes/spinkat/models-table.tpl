{if isset($models) && count($models) && isset($attr_groups)}
<div class="models_table block_shadow">
    <table class="table table-bordered models">
        <thead>
            <tr>
                <th>Модель</th>
                {foreach from=$attr_groups item=group}
                    {if $group['hide'] == 0}
                        <th>{$group['group_name']|escape:'html':'UTF-8'}</th>
                    {/if}
                {/foreach}
                <th>Стоимость</th>
                <th class="add_to_cart_cell"></th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$models item=model}
                <tr class="product_attribute" data-combination-id="{$model['id_product_attribute']}">
                    <td class="title">
                        <a href="#">{$model['name']|escape:'html':'UTF-8'}</a>
                        {*
                        {if isset($model['is_new']) && $model['is_new']}
                            <span class="label_new">{l s='New'}</span>
                        {/if}
                        *}
                        {if isset($model['gift']) && $model['gift']}
                            <span class="label_gift">{l s='Gift'}</span>
                        {/if}
                    </td>
                    {foreach from=$attr_groups item=group}
                        {if $group['hide'] == 0}
                            <td class="product_attribute_cell" data-attr-title="{$group['group_name']|escape:'html':'UTF-8'}:">
                                {foreach from=$group['models_attributes'] item=model_attrs}
                                    {if $model_attrs['id_product_attribute'] == $model['id_product_attribute']}
                                        {$model_attrs['attribute_name']|escape:'html':'UTF-8'}
                                    {/if}
                                {/foreach}
                            </td>
                        {/if}
                    {/foreach}

                    <td class="price_cell" data-attr-title="Цена:">
                        <span class="price product-price">{convertPrice price=$model['price']|floatval}</span>
                    </td>

                    <td class="add_to_cart_cell">
                        <form action="{$link->getPageLink('cart')|escape:'html':'UTF-8'}" method="post" class="submit_model_order">
                            <input type="hidden" name="token" value="{$static_token}" />
                            <input type="hidden" name="id_product" value="{$model['id_product']|intval}" />
                            <input type="hidden" name="add" value="1" />
                            <input type="hidden" name="id_product_attribute" value="{$model['id_product_attribute']|intval}" />
                            <input type="hidden" name="qty" value="1" />

                            {foreach from=$attr_groups item=group}
                                {foreach from=$group['models_attributes'] item=model_attrs}
                                    {if $model_attrs['id_product_attribute'] == $model['id_product_attribute']}
                                        <input type="hidden" class="hidden_model_group_id" name="model_group_{$model_attrs['id_attribute_group']|intval}" value="{$model_attrs['id_attribute']|intval}">
                                    {/if}
                                {/foreach}
                            {/foreach}

                            {if ($model['allow_oosp'] || $model['quantity'] > 0)}
                                <button type="submit" name="Submit" class="btn button product_page_add_to_cart_button">
                                    <span>{l s='Add to cart'}</span>
                                </button>
                            {else}
                                <button type="submit" name="Submit" class="btn button product_page_add_to_cart_button disabled">
                                    <span>{l s='Add to cart'}</span>
                                </button>

                            {/if}

                            <span class="availability-block">
                                {include file="$tpl_dir./_availability_block.tpl" allow_oosp=$model['allow_oosp'] quantity=$model['quantity']}
                            </span>
                        </form>
                    </td>
                </tr>
            {/foreach}
        </tbody>
    </table>
</div>

<script>
    $(document).ready(function() {
        $('.models_table .product_attribute .title a').click(function (e) {
            e.preventDefault()

            var request = '',
                tab_attributes = [],
                attribute_groups = $(this).parents('.product_attribute').find('input.hidden_model_group_id'),
                id_product_attribute = $(this).parents('.product_attribute').find('input[name=id_product_attribute]').val()

            if (attribute_groups.length > 0) {
                $(attribute_groups).each(function() {
                    tab_attributes.push($(this).val())
                })
            }

            // build new request
            for (var i in attributesCombinations)
                for (var a in tab_attributes)
                    if (attributesCombinations[i]['id_attribute'] === tab_attributes[a])
                        request += '/' + attributesCombinations[i]['id_attribute'] + '-' + attributesCombinations[i]['group'] + attribute_anchor_separator + attributesCombinations[i]['attribute']
            request = request.replace(request.substring(0, 1), '#/') + '/' + id_product_attribute + '-model_id'
            var url = window.location + ''

            // redirection
            if (url.indexOf('#') != -1)
                url = url.substring(0, url.indexOf('#'))

            window.location.replace(url + request)

            // Update product availability block
            var availability_block = $(this).parents('.product_attribute').find('span.availability-block').html()
            $('.product-availability-block').html(availability_block)

            $('#idCombination').val(id_product_attribute)

            $('#product_feedback_form input[name=id_product_attribute]').val(id_product_attribute)

            var model = models[id_product_attribute]
            var tab_information = $('#tab_information')

            if (model) {
                if (tab_information.length) {
                    var tabs = tab_information.find('.nav-tabs')
                    var tabs_content = tab_information.find('.tab-content')

                    $('#short_description_content').html(model['top_description'])

                    if (model['long_description']) {
                        tabs_content.find('#description section .product_description').hide()
                        tabs_content.find('#description section .model_description').html(model['long_description']).show()
                    } else {
                        tabs_content.find('#description section .product_description').show()
                        tabs_content.find('#description section .model_description').html('').hide()
                    }

                    if (model['attribute_video']) {
                        tabs.find('.video').show()
                        tabs_content.find('#video').html(model['attribute_video'])
                    } else {
                        tabs.find('.video').hide().removeClass('active')
                        tabs_content.find('#video').removeClass('active')
                    }

                    if (!tabs.find('.active').length)
                        tabs.find('li:visible:first a').tab('show')

                    if (model['name']) {
                        $('.product_model_title').html(model['name'])
                    } else {
                        $('.product_model_title').html($product_name)
                    }

                }

                if (model['meta_title']) {
                    $('title').html(model['meta_title'])
                }

                if (model['meta_description']) {
                    $('meta[name="description"]').attr('content', model['meta_description'])
                    $('meta[property="og:description"]').attr('content', model['meta_description'])
                }

                // Update gift block
                var gift_block = $('.product-gift')

                if (model['gift'] && model['gift'][0] && model['gift'][0]['link']) {
                    gift_block.find('a')
                        .attr('href', model['gift'][0]['link'])
                        .attr('rel', model['gift'][0]['link'])

                    gift_block.find('.quick-view img')
                        .attr('src', model['gift'][0]['banner'])
                    gift_block.show()
                } else {
                    gift_block.find('a')
                        .attr('href', '')
                        .attr('rel', '')
                    gift_block.hide()
                }

                var free_delivery_block = $('.product-free-delivery')

                if (model['free_delivery'] && model['free_delivery'] == true) {
                    free_delivery_block.show()
                } else {
                    free_delivery_block.hide()
                }

                var new_box = $('.new-box')

                if (model['is_new'] && model['is_new'] == 1)
                    new_box.show()
                else
                    new_box.hide()
            }

            $.scrollTo('.primary_block', 400);
        })

        if ($('#idCombination').val())
            $('.product_attribute[data-combination-id=' + $('#idCombination').val() + '] .title a').click()
    })
</script>
{/if}
