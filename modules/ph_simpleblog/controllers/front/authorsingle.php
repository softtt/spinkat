<?php
/*
* @author    Krystian Podemski <podemski.krystian@gmail.com>
* @site
* @copyright  Copyright (c) 2013-2014 Krystian Podemski - www.PrestaHome.com
* @license    You only can use module, nothing more!
*/
require_once _PS_MODULE_DIR_ . 'ph_simpleblog/ph_simpleblog.php';

class PH_SimpleBlogAuthorSingleModuleFrontController extends ModuleFrontController
{
    public $simpleblog_author_rewrite;
    public $SimpleBlogAuthor;

    public function init()
    {
        parent::init();

        $simpleblog_author_rewrite = Tools::getValue('rewrite', 0);

        if ($simpleblog_author_rewrite)
            $this->simpleblog_author_rewrite = $simpleblog_author_rewrite;

        $id_lang = Context::getContext()->language->id;

        $SimpleBlogAuthor = SimpleBlogAuthor::getByRewrite($this->simpleblog_author_rewrite, $id_lang);

        if (!Validate::isLoadedObject($SimpleBlogAuthor) || Validate::isLoadedObject($SimpleBlogAuthor) && !$SimpleBlogAuthor->active) {
            Tools::redirect('index.php?controller=404');
        }

        if (Validate::isLoadedObject($SimpleBlogAuthor) && $this->simpleblog_author_rewrite != $SimpleBlogAuthor->link_rewrite) {
            Tools::redirect(SimpleBlogAuthor::getLink($SimpleBlogAuthor->link_rewrite, $SimpleBlogAuthor->category_rewrite));
        }

        if (!empty($SimpleBlogAuthor->meta_title)) {
            $this->context->smarty->assign('meta_title', $SimpleBlogAuthor->meta_title);
        }

        if (!empty($SimpleBlogAuthor->meta_description)) {
            $this->context->smarty->assign('meta_description', $SimpleBlogAuthor->meta_description);
        }

        if (!empty($SimpleBlogAuthor->meta_keywords)) {
            $this->context->smarty->assign('meta_keywords', $SimpleBlogAuthor->meta_keywords);
        }

        $this->SimpleBlogAuthor = $SimpleBlogAuthor;
    }


    public function initContent()
    {
        $this->context->controller->addJqueryPlugin('cooki-plugin');
        $this->context->controller->addJqueryPlugin('cookie-plugin');
        $this->context->controller->addjqueryPlugin('fancybox');

        $this->context->controller->addCSS(array(
            _THEME_CSS_DIR_ . 'category.css' => 'all',
            _THEME_CSS_DIR_ . 'product_list.css' => 'all',
        ));

        parent::initContent();

        $this->context->smarty->assign('author', $this->SimpleBlogAuthor);
        $this->context->smarty->assign('is_16', (version_compare(_PS_VERSION_, '1.6.0', '>=') === true) ? true : false);

        $categories = SimpleBlogAuthor::getCategories($this->SimpleBlogAuthor->id, Context::getContext()->language->id);
        $this->context->smarty->assign('categories', $categories);

        $this->setTemplate('author-single.tpl');
    }
}
