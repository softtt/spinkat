<?php
require_once _PS_MODULE_DIR_ . 'ph_simpleblog/ph_simpleblog.php';

class AdminSimpleBlogCategoriesController extends ModuleAdminController
{

    protected $position_identifier = 'id_simpleblog_category';

    public function __construct()
    {

        $this->table = 'simpleblog_category';
        $this->className = 'SimpleBlogCategory';
        $this->lang = true;
        $this->list_no_link = false;
        $this->addRowAction('edit');
        $this->addRowAction('details');
        $this->addRowAction('delete');
        $this->bulk_actions = array('delete' => array('text' => $this->l('Delete selected'), 'confirm' => $this->l('Delete selected items?')));

        $this->bootstrap = true;

        $this->_where = 'AND a.`id_parent` = 0';
        $this->_orderBy = 'position';

        $this->fields_list = array(
            'id_simpleblog_category' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
                'width' => 30
            ),
            'name' => array(
                'title' => $this->l('Name'),
                'width' => 'auto'
            ),
            'description' => array(
                'title' => $this->l('Description'),
                'width' => 500,
                'orderby' => false,
                'callback' => 'getDescriptionClean'
            ),
            'active' => array(
                'title' => $this->l('Displayed'),
                'width' => 25,
                'active' => 'status',
                'align' => 'center',
                'type' => 'bool',
                'orderby' => false
            ),
        );

        parent::__construct();
    }

    public function renderList()
    {
        $this->initToolbar();
        return parent::renderList();
    }

    public function initPageHeaderToolbar()
    {
        if (($id = Tools::getValue('id_simpleblog_category')))
        {
            $category = $this->loadObject($id);
            if ($this->display == 'details')
            {
                $this->page_header_toolbar_title = $this->l('Posts of category: ').' '.$category->name[$this->context->employee->id_lang];
                $contextLink = $this->context->link->getAdminLink('AdminSimpleBlogPosts', false);
                $token = Tools::getAdminTokenLite('AdminSimpleBlogPosts');

                $this->page_header_toolbar_btn['new_post'] = array(
                    'href' => $contextLink.'&addsimpleblog_post&id_simpleblog_category='.$id.'&token='.$token,
                    'desc' => $this->l('Add new post', null, null, false),
                    'icon' => 'process-icon-new'
                );
            }
            elseif ($this->display == 'edit')
            {
                $this->page_header_toolbar_btn['view_details'] = array(
                    'href' => self::$currentIndex.'&detailssimpleblog_category&id_simpleblog_category='.$id.'&token='.$this->token,
                    'desc' => $this->l('Category posts list', null, null, false),
                    'icon' => 'process-icon-folder-open-alt'
                );
            }
        }

        parent::initPageHeaderToolbar();
    }

    public function getDescriptionClean($description, $row)
    {
        return strip_tags(stripslashes($description));
    }

    public function init()
    {
        parent::init();

        Shop::addTableAssociation($this->table, array('type' => 'shop'));

        if (Shop::getContext() == Shop::CONTEXT_SHOP)
            $this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'simpleblog_category_shop` sa ON (a.`id_simpleblog_category` = sa.`id_simpleblog_category` AND sa.id_shop = '.(int)$this->context->shop->id.') ';
        // else
        //     $this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'simpleblog_category_shop` sa ON (a.`simpleblog_category` = sa.`simpleblog_category` AND sa.id_shop = a.id_shop_default) ';

        if (Shop::getContext() == Shop::CONTEXT_SHOP && Shop::isFeatureActive())
            $this->_where = ' AND sa.`id_shop` = '.(int)Context::getContext()->shop->id;

        if (Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_SHOP)
            unset($this->fields_list['position']);
    }

    public function initFormToolBar()
    {
        unset($this->toolbar_btn['back']);
        $this->toolbar_btn['save-and-stay'] = array(
                        'short' => 'SaveAndStay',
                        'href' => '#',
                        'desc' => $this->l('Save and stay'),
                    );
        $this->toolbar_btn['back'] = array(
                        'href' => self::$currentIndex.'&token='.Tools::getValue('token'),
                        'desc' => $this->l('Back to list'),
                    );


    }

    public function renderForm()
    {
        $this->initFormToolBar();
        if (!$this->loadObject(true))
            return;

        $cover = false;

        $obj = $this->loadObject(true);

        if (isset($obj->id))
        {
            $this->display = 'edit';

            $cover = ImageManager::thumbnail(_PS_MODULE_DIR_ . 'ph_simpleblog/covers_cat/'.$obj->id.'.'.$obj->cover, 'ph_simpleblog_cat_'.$obj->id.'.'.$obj->cover, 350, $obj->cover, false);
        }
        else
        {
            $this->display = 'add';
        }

        $this->fields_value = array(
            'cover' => $cover ? $cover : false,
            'cover_size' => $cover ? filesize(_PS_MODULE_DIR_ . 'ph_simpleblog/covers_cat/'.$obj->id.'.'.$obj->cover) / 1000 : false,
        );



        $categories = SimpleBlogCategory::getCategories($this->context->language->id, true, true);

        array_unshift($categories, array('id' => 0, 'name' => $this->l('No parent')));

        foreach ($categories as $key => $category)
        {
            if (isset($obj->id) && $obj->id)
            {
                if ($category['id'] == $obj->id_simpleblog_category)
                    unset($category[$key]);
            }
        }

        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Category'),
                'image' => '../img/admin/tab-categories.gif'
            ),
            'input' => array(

                array(
                    'type' => 'text',
                    'label' => $this->l('Name:'),
                    'name' => 'name',
                    'required' => true,
                    'lang' => true,
                    'class' => 'copy2friendlyUrl',
                ),

                array(
                    'type' => 'textarea',
                    'label' => $this->l('Description:'),
                    'name' => 'description',
                    'lang' => true,
                    'rows' => 5,
                    'cols' => 40,
                    'autoload_rte' => true,
                ),

                array(
                    'type' => 'text',
                    'label' => $this->l('Meta title:'),
                    'name' => 'meta_title',
                    'lang' => true,
                ),

                array(
                    'type' => 'text',
                    'label' => $this->l('Meta description:'),
                    'name' => 'meta_description',
                    'lang' => true,
                ),

                array(
                    'type' => 'text',
                    'label' => $this->l('Meta keywords:'),
                    'name' => 'meta_keywords',
                    'lang' => true,
                ),

                array(
                    'type' => 'text',
                    'label' => $this->l('Friendly URL:'),
                    'name' => 'link_rewrite',
                    'required' => true,
                    'lang' => true,
                ),

                array(
                    'type' => 'switch',
                    'label' => $this->l('Displayed:'),
                    'name' => 'active',
                    'required' => false,
                    'class' => 't',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),

                array(
                    'type' => 'radio',
                    'label' => $this->l('Display post author?'),
                    'name' => 'display_author',
                    'required' => false,
                    'class' => 't',
                    'values' => array(
                        array(
                            'id' => 'display_author_1',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'display_author_2',
                            'value' => 2,
                            'label' => $this->l('No')
                        ),
                        array(
                            'id' => 'display_author_3',
                            'value' => 3,
                            'label' => $this->l('Use global setting')
                        )
                    ),
                ),

                array(
                    'type' => 'radio',
                    'label' => $this->l('Display post tags?'),
                    'name' => 'display_tags',
                    'required' => false,
                    'class' => 't',
                    'values' => array(
                        array(
                            'id' => 'display_tags_1',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'display_tags_2',
                            'value' => 2,
                            'label' => $this->l('No')
                        ),
                        array(
                            'id' => 'display_tags_3',
                            'value' => 3,
                            'label' => $this->l('Use global setting')
                        )
                    ),
                ),

                array(
                    'type' => 'radio',
                    'label' => $this->l('Allow comments?'),
                    'name' => 'allow_comments',
                    'required' => false,
                    'class' => 't',
                    'values' => array(
                        array(
                            'id' => 'allow_comments_1',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'allow_comments_2',
                            'value' => 2,
                            'label' => $this->l('No')
                        ),
                        array(
                            'id' => 'allow_comments_3',
                            'value' => 3,
                            'label' => $this->l('Use global setting')
                        )
                    ),
                ),

                array(
                    'type' => 'switch',
                    'label' => $this->l('Is gallery?'),
                    'name' => 'is_gallery',
                    'required' => false,
                    'class' => 't',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),

                array(
                    'type' => 'switch',
                    'label' => $this->l('Show section on homepage:'),
                    'name' => 'display_on_homepage',
                    'required' => false,
                    'class' => 't',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'display_on_homepage_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'display_on_homepage_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                ),

                array(
                    'type' => 'text',
                    'label' => $this->l('Homepage section title:'),
                    'name' => 'homepage_title',
                    'required' => false,
                    'lang' => true,
                ),

                array(
                    'type' => 'file',
                    'label' => $this->l('Category image:'),
                    'display_image' => true,
                    'name' => 'cover',
                    'desc' => $this->l('Upload a image from your computer.')
                ),

            ),
            'submit' => array(
                'title' => $this->l('Save and stay'),
                'class' => 'btn btn-default',
                'stay' => true,
            )
        );

        if (Shop::isFeatureActive())
                $this->fields_form['input'][] = array(
                    'type' => 'shop',
                    'label' => $this->l('Shop association:'),
                    'name' => 'checkBoxShopAsso',
                );

        $this->tpl_form_vars['PS_ALLOW_ACCENTED_CHARS_URL'] = (int)Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL');
        $this->tpl_form_vars['PS_FORCE_FRIENDLY_PRODUCT'] = (int)Configuration::get('PS_FORCE_FRIENDLY_PRODUCT');
        $this->show_form_cancel_button = false;

        return parent::renderForm();
    }

    /**

    "Details" view for PrestaShop 1.6

    **/

    public function renderDetails()
    {
        return $this->initListCategoryPosts();
    }

    public function processAdd()
    {
        $object = parent::processAdd();

        // Cover
        if (isset($_FILES['cover']) && $_FILES['cover']['size'] > 0)
        {
            $object->cover = pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION);
            $object->update();
        }

        if (!empty($object->cover))
        {
            $this->createCover($_FILES['cover']['tmp_name'], $object);
        }

        $this->updateAssoShop($object->id);

        return true;
    }

    public function processUpdate()
    {
        $object = parent::processUpdate();

        // Cover
        if (isset($_FILES['cover']) && $_FILES['cover']['size'] > 0)
        {
            $object->cover = pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION);
            $object->update();
        }

        if (!empty($object->cover) && isset($_FILES['cover']) && $_FILES['cover']['size'] > 0)
        {
            $this->createCover($_FILES['cover']['tmp_name'], $object);
        }

        $this->updateAssoShop($object->id);

        return true;
    }

    public function postProcess()
    {
        if (Tools::isSubmit('viewsimpleblog_category') && ($id_simpleblog_category = (int)Tools::getValue('id_simpleblog_category')) && ($SimpleBlogCategory = new SimpleBlogCategory($id_simpleblog_category, $this->context->language->id)) && Validate::isLoadedObject($SimpleBlogCategory))
        {
            $redir = $SimpleBlogCategory->getObjectLink($id_lang);
            Tools::redirectAdmin($redir);
        }

        if (Tools::isSubmit('deleteCover'))
        {
            $this->deleteCover((int)Tools::getValue('id_simpleblog_category'));
        }

        if (($id_simpleblog_category = (int)Tools::getValue('id_simpleblog_category')) && ($direction = Tools::getValue('move')) && Validate::isLoadedObject($SimpleBlogCategory = new SimpleBlogCategory($id_simpleblog_category)))
        {
            if ($SimpleBlogCategory->move($direction))
                Tools::redirectAdmin(self::$currentIndex.'&token='.$this->token);
        }
        elseif (Tools::getValue('position') && !Tools::isSubmit('submitAdd'.$this->table))
        {
            if ($this->tabAccess['edit'] !== '1')
                $this->errors[] = Tools::displayError('You do not have permission to edit this.');
            elseif (!Validate::isLoadedObject($object = new SimpleBlogCategory((int)Tools::getValue($this->identifier))))
                $this->errors[] = Tools::displayError('An error occurred while updating the status for an object.').
                    ' <b>'.$this->table.'</b> '.Tools::displayError('(cannot load object)');
            if (!$object->updatePosition((int)Tools::getValue('way'), (int)Tools::getValue('position')))
                $this->errors[] = Tools::displayError('Failed to update the position.');
            else
                Tools::redirectAdmin(self::$currentIndex.'&conf=5&token='.Tools::getAdminTokenLite('AdminSimpleBlogCategories'));
        }
        else
        {
            // Temporary add the position depend of the selection of the parent category
            if (!Tools::isSubmit('id_simpleblog_category')) // @todo Review
                $_POST['position'] = SimpleBlogCategory::getNbCats(Tools::getValue('id_parent'));
        }

        return parent::postProcess();
    }

    public function ajaxProcessUpdatePositions()
    {
        $way = (int)(Tools::getValue('way'));
        $id_simpleblog_category = (int)(Tools::getValue('id'));
        $positions = Tools::getValue('simpleblog_category');

        foreach ($positions as $position => $value)
        {
            $pos = explode('_', $value);

            $id_simpleblog_category = (int)$pos[2];

            if ((int)$id_simpleblog_category > 0)
            {
                if ($SimpleBlogCategory = new SimpleBlogCategory($id_simpleblog_category))
                {
                    $SimpleBlogCategory->position = $position+1;
                    if ($SimpleBlogCategory->update())
                        echo 'ok position '.(int)$position.' for category '.(int)$SimpleBlogCategory->id.'\r\n';
                }
                else
                {
                    echo '{"hasError" : true, "errors" : "This category ('.(int)$id_simpleblog_category.') cant be loaded"}';
                }

            }
        }
    }

    public function createCover($img = null, $object = null)
    {
        if (!isset($img))
            die('AdminSimpleBlogCategoryController@createCover: No image to process');

        $categoryImgX = Configuration::get('PH_CATEGORY_IMAGE_X');
        $categoryImgY = Configuration::get('PH_CATEGORY_IMAGE_Y');

        if (isset($object) && Validate::isLoadedObject($object))
        {
            $fileTmpLoc = $img;
            $origPath = _PS_MODULE_DIR_ . 'ph_simpleblog/covers_cat/'.$object->id.'.'.$object->cover;

            $tmp_location = _PS_TMP_IMG_DIR_.'ph_simpleblog_cat_'.$object->id.'.'.$object->cover;
            if (file_exists($tmp_location))
                @unlink($tmp_location);

            try
            {
                $orig = PhpThumbFactory::create($fileTmpLoc);
            }
            catch (Exception $e)
            {
                echo $e;
            }

            $orig->adaptiveResize($categoryImgX,$categoryImgY);

            return $orig->save($origPath) && ImageManager::thumbnail(_PS_MODULE_DIR_ . 'ph_simpleblog/covers_cat/'.$object->id.'.'.$object->cover, 'ph_simpleblog_cat_'.$object->id.'.'.$object->cover, 350, $object->cover);
        }
    }

    public function deleteCover($id)
    {
        $object = new SimpleBlogCategory($id, Context::getContext()->language->id);

        $tmp_location = _PS_TMP_IMG_DIR_.'ph_simpleblog_cat_'.$object->id.'.'.$object->cover;
        if (file_exists($tmp_location))
            @unlink($tmp_location);

        $orig_location = _PS_MODULE_DIR_ . 'ph_simpleblog/covers_cat/'.$object->id.'.'.$object->cover;

        if (file_exists($orig_location))
            @unlink($orig_location);

        $object->cover = NULL;
        $object->update();

        Tools::redirectAdmin(self::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminSimpleBlogCategories').'&conf=7');
    }


    public function initListCategoryPosts()
    {
        if (($id = Tools::getValue('id_simpleblog_category')))
        {
            $category = $this->loadObject($id);

            $posts = SimpleBlogPost::getPosts($this->context->language->id, 10, $id, null, false);

            $fields_list = array(
                'id_simpleblog_post' => array(
                    'title' => $this->l('ID'),
                    'align' => 'center',
                    'width' => 30
                ),
                'post_type' => array(
                    'title' => $this->l('Type'),
                    'width' => 'auto',
                    'filter_key' => 'sbpt!name',
                    'width' => 70,
                    'align' => 'center',
                ),
                'cover' => array(
                    'title' => $this->l('Post thumbnail'),
                    'width' => 100,
                    'orderby' => false,
                    'search' => false,
                    'callback' => 'getPostThumbnail'
                ),
                'category' => array(
                    'title' => $this->l('Category'),
                    'width' => 'auto',
                    'search' => false,
                    'align' => 'center',
                ),
                'title' => array(
                    'title' => $this->l('Title'),
                    'width' => 'auto',
                    'filter_key' => 'b!title',
                ),
                'short_content' => array(
                    'title' => $this->l('Description'),
                    'width' => 500,
                    'orderby' => false,
                    'callback' => 'getDescriptionClean',
                    'maxlength' => 100,
                ),
                'views' => array(
                    'title' => $this->l('Views'),
                    'width' => 30,
                    'align' => 'center',
                    'search' => false,
                ),
                // 'likes' => array(
                //     'title' => $this->l('Likes'),
                //     'width' => 30,
                //     'align' => 'center',
                //     'search' => false,
                // ),
                'date_add' => array(
                    'title' => $this->l('Publication date'),
                    'type' => 'date',
                    'filter_key' => 'a!date_add',
                ),
                'active' => array(
                    'title' => $this->l('Displayed'), 'width' => 25, 'active' => 'status',
                    'align' => 'center','type' => 'bool', 'orderby' => false
            ));

            $helper = new HelperList();
            $helper->no_link = false;
            $helper->simple_header = true;
            $helper->module = $this;
            $helper->table = 'simpleblog_post';
            $helper->identifier = 'id_simpleblog_post';
            $helper->title = $this->l('Posts of category: ').' '.$category->name[$this->context->employee->id_lang];
            $helper->token = Tools::getAdminTokenLite('AdminSimpleBlogPosts');
            $helper->currentIndex = $this->context->link->getAdminLink('AdminSimpleBlogPosts', false);
            $helper->show_cancel_button = false;

            $helper->actions = array('edit', 'delete');
            $helper->bulk_actions = array(
                'delete' => array('text' => $this->l('Delete selected'), 'confirm' => $this->l('Delete selected items?')),
                'enableSelection' => array('text' => $this->l('Enable selection')),
                'disableSelection' => array('text' => $this->l('Disable selection')),
            );

            return $helper->generateList($posts, $fields_list);
        }
    }

    public static function getPostThumbnail($cover, $row)
    {
        return ImageManager::thumbnail(_PS_MODULE_DIR_ . 'ph_simpleblog/covers/'.$row['id_simpleblog_post'].'.'.$cover, 'ph_simpleblog_'.$row['id_simpleblog_post'].'-list.'.$cover, 75, $cover, true);
    }
}
