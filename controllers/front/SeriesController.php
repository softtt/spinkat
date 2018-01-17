<?php
/**
 * Series is a Product with combinations and flag `is_series` setted to true.
 *
 */

class SeriesController extends FrontController
{
    public $php_self = 'series';

    /** @var Series of products */
    protected $series;

    /** @var Category */
    protected $category;

    /** @var int Number of products in the current page. */
    protected $nbModels;

    /** @var array Products to be displayed in the current page . */
    protected $series_models;

    /**
     * Sets default medias for this controller
     */
    public function setMedia()
    {
        parent::setMedia();

        if (!$this->useMobileTheme()) {
            $this->addCSS(array(
                _THEME_CSS_DIR_.'product_list.css' => 'all',
            ));
        }
    }

    public function init()
    {
        parent::init();

        if ($id_series = (int)Tools::getValue('id_series')) {
            $this->series = new Series($id_series, true, $this->context->language->id, $this->context->shop->id);
        }

        if (!Validate::isLoadedObject($this->series)) {
            // header('HTTP/1.1 404 Not Found');
            // header('Status: 404 Not Found');
            // $this->errors[] = Tools::displayError('Series not found');

            Tools::redirect('index.php?controller=404');
        } else {
            $this->canonicalRedirection();
            /*
             * If the series is associated to the shop
             * and is active or not active but preview mode (need token + file_exists)
             * allow showing the series
             * In all the others cases => 404 "Series is no longer available"
             */
            if (!$this->series->isAssociatedToShop() || !$this->series->active) {
                if (Tools::getValue('adtoken') == Tools::getAdminToken('AdminProducts'.(int)Tab::getIdFromClassName('AdminProducts').(int)Tools::getValue('id_employee')) && $this->series->isAssociatedToShop()) {
                    // If the series is not active, it's the admin preview mode
                    $this->context->smarty->assign('adminActionDisplay', true);
                } else {
                    $this->context->smarty->assign('adminActionDisplay', false);
                    if (!$this->series->id_product_redirected || $this->series->id_product_redirected == $this->series->id) {
                        $this->series->redirect_type = '404';
                    }
                    switch ($this->series->redirect_type) {
                        case '301':
                            header('HTTP/1.1 301 Moved Permanently');
                            header('Location: '.$this->context->link->getProductLink($this->series->id_product_redirected));
                            exit;
                        break;
                        case '302':
                            header('HTTP/1.1 302 Moved Temporarily');
                            header('Cache-Control: no-cache');
                            header('Location: '.$this->context->link->getProductLink($this->series->id_product_redirected));
                            exit;
                        break;
                        case '404':
                        default:
                            header('HTTP/1.1 404 Not Found');
                            header('Status: 404 Not Found');
                            $this->errors[] = Tools::displayError('This series is no longer available.');
                        break;
                    }
                }
            } elseif (!$this->series->checkAccess(isset($this->context->customer->id) && $this->context->customer->id ? (int)$this->context->customer->id : 0)) {
                header('HTTP/1.1 403 Forbidden');
                header('Status: 403 Forbidden');
                $this->errors[] = Tools::displayError('You do not have access to this series.');
            } else {
                // Load category
                $id_category = false;
                if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] == Tools::secureReferrer($_SERVER['HTTP_REFERER']) // Assure us the previous page was one of the shop
                    && preg_match('~^.*(?<!\/content)\/([0-9]+)\-(.*[^\.])|(.*)id_(category|product)=([0-9]+)(.*)$~', $_SERVER['HTTP_REFERER'], $regs)) {
                    // If the previous page was a category and is a parent category of the series use this category as parent category
                    $id_object = false;
                    if (isset($regs[1]) && is_numeric($regs[1])) {
                        $id_object = (int)$regs[1];
                    } elseif (isset($regs[5]) && is_numeric($regs[5])) {
                        $id_object = (int)$regs[5];
                    }
                    if ($id_object) {
                        $referers = array($_SERVER['HTTP_REFERER'],urldecode($_SERVER['HTTP_REFERER']));
                        if (in_array($this->context->link->getCategoryLink($id_object), $referers)) {
                            $id_category = (int)$id_object;
                        } elseif (isset($this->context->cookie->last_visited_category) && (int)$this->context->cookie->last_visited_category && in_array($this->context->link->getProductLink($id_object), $referers)) {
                            $id_category = (int)$this->context->cookie->last_visited_category;
                        }
                    }
                }
                if (!$id_category || !Category::inShopStatic($id_category, $this->context->shop) || !Series::idIsOnCategoryId((int)$this->series->id, array('0' => array('id_category' => $id_category)))) {
                    $id_category = (int)$this->series->id_category_default;
                }
                $this->category = new Category((int)$id_category, (int)$this->context->cookie->id_lang);
                if (isset($this->context->cookie) && isset($this->category->id_category) && !(Module::isInstalled('blockcategories') && Module::isEnabled('blockcategories'))) {
                    $this->context->cookie->last_visited_category = (int)$this->category->id_category;
                }
            }
        }
    }

    /**
     * Logic of this page in common is the same as for Category page
     */
    public function initContent()
    {
        parent::initContent();

        $this->setTemplate(_PS_THEME_DIR_.'series.tpl');

        if (!$this->errors) {
            $this->assignCategory();

            // Product sort must be called before assignModelsList()
            $this->productSort();

            $tag = Tools::getValue('id_tag');
            $this->assignModelsList($tag);

            //allow tags only on spinning pages 13 - spinning id
            if ($this->category->id == 13) {
                $this->context->smarty->assign(array(
                    'allow_tags' => true
                ));
            }

            $this->context->smarty->assign(array(
                'category'             => $this->category,
                // 'description_short'    => Tools::truncateString($this->category->description, 350),
                'series'               => $this->series,
                'models'               => (isset($this->series_models) && $this->series_models) ? $this->series_models : null,
                // 'id_category'          => (int)$this->category->id,
                // 'id_category_parent'   => (int)$this->category->id_parent,
                'return_category_name' => Tools::safeOutput($this->category->name),
                // 'add_prod_display'     => Configuration::get('PS_ATTRIBUTE_CATEGORY_DISPLAY'),
                'categorySize'         => Image::getSize(ImageType::getFormatedName('category')),
                'mediumSize'           => Image::getSize(ImageType::getFormatedName('medium')),
                'thumbSceneSize'       => Image::getSize(ImageType::getFormatedName('m_scene')),
                'homeSize'             => Image::getSize(ImageType::getFormatedName('home')),
                'allow_oosp'           => (int)Configuration::get('PS_ORDER_OUT_OF_STOCK'),
                'comparator_max_item'  => (int)Configuration::get('PS_COMPARATOR_MAX_ITEM'),
                'body_classes' => array(
                    $this->php_self.'-'.$this->series->id,
                    $this->php_self.'-'.$this->series->link_rewrite,
                    'category-'.(isset($this->category) ? $this->category->id : ''),
                    'category-'.(isset($this->category) ? $this->category->getFieldByLang('link_rewrite') : '')
                ),
            ));
        }
    }

    public function canonicalRedirection($canonical_url = '')
    {
        if (Tools::getValue('live_edit')) {
            return;
        }
        if (Validate::isLoadedObject($this->series)) {
            parent::canonicalRedirection($this->context->link->getSeriesLink($this->series));
        }
    }

    /**
     * Assign template vars related to category
     */
    protected function assignCategory()
    {
        // Assign category to the template
        if ($this->category !== false && Validate::isLoadedObject($this->category) && $this->category->inShop() && $this->category->isAssociatedToShop()) {
            $path = Tools::getPath($this->category->id, $this->series->name, true, 'products', null, $this->series->id_manufacturer);
        } elseif (Category::inShopStatic($this->series->id_category_default, $this->context->shop)) {
            $this->category = new Category((int)$this->series->id_category_default, (int)$this->context->language->id);
            if (Validate::isLoadedObject($this->category) && $this->category->active && $this->category->isAssociatedToShop()) {
                $path = Tools::getPath((int)$this->series->id_category_default, $this->series->name, false, 'products', null, $this->series->id_manufacturer);
            }
        }
        if (!isset($path) || !$path) {
            $path = Tools::getPath((int)$this->context->shop->id_category, $this->series->name, false, 'products', null, $this->series->id_manufacturer);
        }

        $this->context->smarty->assign(array(
            'path' => $path,
            'category' => $this->category,
        ));
    }

    public function assignModelsList($tag = null)
    {
        $this->nbModels = $this->series->getModels(null, true, 0, 0, array(), $tag);
        $this->pagination((int)$this->nbModels); // Pagination must be call after "getModels"
        $this->series_models = $this->series->getModels($this->context->language->id, false, $this->p, $this->n, array(), $tag);
    }

    public function getProduct()
    {
        return $this->series;
    }

    public function getCategory()
    {
        return $this->category;
    }
}
