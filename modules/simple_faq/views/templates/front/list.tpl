{capture name=path}{l s='FAQ' mod='simple_faq'}{/capture}

<div>
    <h1>{l s='Questions' mod='simple_faq'}</h1>

    {if isset($questions) && count($questions)}
        <div class="questions">
            {foreach from=$questions item=question}
                {if $question.question}
                    <div class="question row">
                        <div class="question_details col-sm-12">
                            <div class="question_author_infos">
                                <strong>{$question.customer_name|escape:'html':'UTF-8'}</strong>
                            </div>
                            <label>{l s='Question' mod='simple_faq'}</label>
                            <p>{$question.question|escape:'html':'UTF-8'|nl2br}</p>

                            <label>{l s='Answer' mod='simple_faq'}</label>
                            <p>{{$question.answer}}</p>
                        </div><!-- .question_details -->
                    </div><!-- .question -->
                {/if}
            {/foreach}
        </div><!-- .questions -->
    {else}
        <p class="warning alert alert-warning">{l s='There are no questions yet' mod='simple_faq'}</p>
    {/if}

    {* Add question form *}
    <div id="add-question" class="add-question">

        {if isset($logged) AND $logged}
        <form class="std clearfix" action="#add-question" method="post">
            <fieldset>
                <div class="box">
                    <h3 class="page-heading bottom-indent">{l s='New question' mod='simple_faq'}</h3>

                    {if $confirmation}
                        <p class="alert alert-success">{l s='Your question has been successfully sent to our team.' mod='simple_faq'}</p>
                    {/if}

                    {if isset($errors) && count($errors)}
                        <div class="alert alert-danger">
                            <p>{if $errors|@count > 1}{l s='There are %d errors' sprintf=$errors|@count mod='simple_faq'}{else}{l s='There is %d error' sprintf=$errors|@count mod='simple_faq'}{/if}</p>
                            <ol>
                            {foreach from=$errors key=k item=error}
                                <li>{$error}</li>
                            {/foreach}
                            </ol>
                        </div>
                    {/if}

                    <div class="form-group">
                        <label for="customer_name">{l s='Your name' mod='simple_faq'}</label>
                        <input type="text" class="form-control" name="customer_name" id="customer_name"
                            value="{if isset($post_name)}{$post_name|escape:'html':'UTF-8'}{else}{if isset($logged) AND $logged}{$customerName|escape:'html':'UTF-8'}{/if}{/if}" />
                    </div>
                    <div class="form-group">
                        <label for="email">{l s='Your email' mod='simple_faq'}</label>
                        <input type="text" class="form-control" name="email" id="email"
                            value="{if isset($post_email)}{$post_email|escape:'html':'UTF-8'}{else}{if isset($logged) AND $logged AND isset($email) AND $email}{$email|escape:'html':'UTF-8'}{/if}{/if}" />
                    </div>
                    <div class="form-group">
                        <label for="question">{l s='Question' mod='simple_faq'}</label>
                        <textarea class="form-control" id="question" name="question" cols="26" rows="5">{if isset($post_question)}{$post_question|escape:'html':'UTF-8'}{/if}</textarea>
                    </div>
                </div>
                <p class="cart_navigation required submit clearfix">
                    <button type="submit" class="button btn btn-default button-medium" name="submitNewQuestion" id="submitNewQuestion">
                        <span>
                            {l s='Add question' mod='simple_faq'}
                        </span>
                    </button>
                </p>
            </fieldset>
        </form>
        {else}
            <div class="warning alert alert-warning">
                <a href="{$link->getPageLink('authentication', true, null, ['back' => $post->url])|escape:'html':'UTF-8'}">{l s='Only registered and logged customers can add questions' mod='simple_faq'}</a>
            </div><!-- .warning -->
        {/if}

    </div><!-- #add-question -->
</div>
