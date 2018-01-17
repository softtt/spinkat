<?php
require_once _PS_MODULE_DIR_ . 'ph_simpleblog/ph_simpleblog.php';

class PH_SimpleBlogAuthorsListModuleFrontController extends ModuleFrontController
{
//     public $posts_per_page;
//     public $n;
//     public $p;

    public function init()
    {
        parent::init();
    }

    public function initContent()
    {
        $id_lang = Context::getContext()->language->id;

        parent::initContent();

        $this->context->smarty->assign('is_16', (bool)(version_compare(_PS_VERSION_, '1.6.0', '>=') === true));

        $gridType = Configuration::get('PH_BLOG_COLUMNS');
        $gridColumns = Configuration::get('PH_BLOG_GRID_COLUMNS');
        $blogLayout = Configuration::get('PH_BLOG_LIST_LAYOUT');

        $gridHtmlCols = '';

        if ($blogLayout == 'full')
        {
            $gridHtmlCols = 'ph_col';
        }

        $this->context->smarty->assign(array(
            'authorsSlug' => Configuration::get('PH_BLOG_AUTHORS_SLUG'),
            'authorsMainTitle' => Configuration::get('PH_BLOG_AUTHORS_MAIN_TITLE', $id_lang),
            'authorsDescription' => Configuration::get('PH_BLOG_AUTHORS_MAIN_META_DESCRIPTION', $id_lang),
            'grid' => Configuration::get('PH_BLOG_COLUMNS'),
            'columns' => $gridColumns,
            'gridHtmlCols' => $gridHtmlCols,
            'blogLayout' => $blogLayout,
            'module_dir' => _MODULE_DIR_.'ph_simpleblog/',
            'tpl_path' => _PS_MODULE_DIR_.'ph_simpleblog/views/templates/front/',
            'gallery_dir' => _MODULE_DIR_.'ph_simpleblog/galleries/',
        ));

        $page = Tools::getValue('p', 0);

        // How many posts?
        // $this->posts_per_page = Configuration::get('PH_BLOG_POSTS_PER_PAGE');

        // Authors page things
        $authors = SimpleBlogAuthor::getAuthors($id_lang);

        // $this->assignPagination($this->posts_per_page, sizeof(SimpleBlogPost::getPosts($id_lang, null)));

        // @todo: More flexible
        $meta_title = Configuration::get('PH_BLOG_AUTHORS_MAIN_TITLE', Context::getContext()->language->id) ? Configuration::get('PH_BLOG_AUTHORS_MAIN_TITLE', Context::getContext()->language->id) : null;
        $meta_description = Configuration::get('PH_BLOG_AUTHORS_MAIN_META_DESCRIPTION', Context::getContext()->language->id) ? Configuration::get('PH_BLOG_AUTHORS_MAIN_META_DESCRIPTION', Context::getContext()->language->id) : null;

        if ($meta_title) {
            $this->context->smarty->assign('meta_title', $meta_title);
        }

        if ($meta_description) {
            $this->context->smarty->assign('meta_description', $meta_description);
        }

        $this->context->smarty->assign('authors', $authors);

        $this->setTemplate('authors-list.tpl');
    }

    // public function assignPagination($limit, $nbPosts)
    // {
    //     $this->n = $limit;
    //     $this->p = abs((int)Tools::getValue('p', 1));

    //     $current_url = tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']);
    //     //delete parameter page
    //     $current_url = preg_replace('/(\?)?(&amp;)?p=\d+/', '$1', $current_url);

    //     $range = 2; /* how many pages around page selected */

    //     if ($this->p < 1)
    //         $this->p = 1;

    //     $pages_nb = ceil($nbPosts / (int)$this->n);

    //     $start = (int)($this->p - $range);

    //     if ($start < 1)
    //         $start = 1;
    //     $stop = (int)($this->p + $range);

    //     if ($stop > $pages_nb)
    //         $stop = (int)$pages_nb;
    //     $this->context->smarty->assign('nb_posts', $nbPosts);
    //     $pagination_infos = array(
    //         'products_per_page' => $limit,
    //         'pages_nb' => $pages_nb,
    //         'p' => $this->p,
    //         'n' => $this->n,
    //         'range' => $range,
    //         'start' => $start,
    //         'stop' => $stop,
    //         'current_url' => $current_url
    //     );
    //     $this->context->smarty->assign($pagination_infos);
    // }
}
