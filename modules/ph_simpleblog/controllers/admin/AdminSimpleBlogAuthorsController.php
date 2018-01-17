<?php
require_once _PS_MODULE_DIR_ . 'ph_simpleblog/ph_simpleblog.php';

class AdminSimpleBlogAuthorsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'simpleblog_author';
        $this->className = 'SimpleBlogAuthor';
        $this->lang = true;

        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->bulk_actions = array('delete' => array('text' => $this->l('Delete selected'), 'confirm' => $this->l('Delete selected items?')));

        $this->is_16 = (version_compare(_PS_VERSION_, '1.6.0', '>=') === true) ? true : false;

        $this->bootstrap = true;

        # Authors Section global options
        $this->fields_options = array(
            array(
                'title' => $this->l('Authors - Settings'),
                'image' => '../img/t/AdminOrderPreferences.gif',
                'info' => '<div class="alert alert-info">'.$this->l('Setting for Authors section as public page.').'</div>',
                'fields' => array(

                    'PH_BLOG_AUTHORS_SLUG' => array(
                        'title' => $this->l('Authors page main URL (by default: authors)'),
                        'validation' => 'isGenericName',
                        'required' => true,
                        'type' => 'text',
                        'size' => 40
                    ), // PH_BLOG_AUTHORS_SLUG

                    'PH_BLOG_AUTHORS_MAIN_TITLE' => array(
                        'title' => $this->l('Authors page title:'),
                        'validation' => 'isGenericName',
                        'type' => 'textLang',
                        'size' => 40,
                        'desc' => $this->l('Meta Title for Authors section homepage'),
                    ), // PH_BLOG_AUTHORS_MAIN_TITLE

                    'PH_BLOG_AUTHORS_MAIN_META_DESCRIPTION' => array(
                        'title' => $this->l('Authors page description:'),
                        'validation' => 'isGenericName',
                        'type' => 'textLang',
                        'size' => 75,
                        'desc' => $this->l('Meta Description for Authors section homepage'),
                    ), // PH_BLOG_AUTHORS_MAIN_META_DESCRIPTION

                ),
                'submit' => array('title' => $this->l('Update'), 'class' => 'btn btn-default'),
            ),
        );

        # Authors objects list
        $this->fields_list = array(
            'id_simpleblog_author' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
                'width' => 30
            ),
            'cover' => array(
                'title' => $this->l('Author thumbnail'),
                'width' => 100,
                'orderby' => false,
                'search' => false,
                'callback' => 'getAuthorThumbnail'
            ),
            'name' => array(
                'title' => $this->l('Name'),
                'width' => 'auto'
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

    public static function getAuthorThumbnail($cover, $row)
    {
        return ImageManager::thumbnail(_PS_MODULE_DIR_ . 'ph_simpleblog/covers_authors/'.$row['id_simpleblog_author'].'.'.$cover, 'ph_simpleblog_authors_'.$row['id_simpleblog_author'].'-list.'.$cover, 75, $cover, true);
    }

    public function renderForm()
    {
        if (!$this->loadObject(true))
            return;

        $cover = false;

        $obj = $this->loadObject(true);

        if (isset($obj->id))
        {
            $this->display = 'edit';

            $cover = ImageManager::thumbnail(_PS_MODULE_DIR_ . 'ph_simpleblog/covers_authors/'.$obj->id.'.'.$obj->cover, 'ph_simpleblog_authors_'.$obj->id.'.'.$obj->cover, 350, $obj->cover, false);
        }
        else
        {
            $this->display = 'add';
        }

        $this->fields_value = array(
            'cover' => $cover ? $cover : false,
            'cover_size' => $cover ? filesize(_PS_MODULE_DIR_ . 'ph_simpleblog/covers_authors/'.$obj->id.'.'.$obj->cover) / 1000 : false,
        );


        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Author')
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
                    'label' => $this->l('Short description:'),
                    'name' => 'description_short',
                    'lang' => true,
                    'rows' => 5,
                    'cols' => 40,
                    'autoload_rte' => true,
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
                    'type' => 'file',
                    'label' => $this->l('Author image:'),
                    'display_image' => true,
                    'name' => 'cover',
                    'desc' => $this->l('Upload a image from your computer.')
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
            ),
            'submit' => array(
                'title' => $this->l('Save and stay'),
                'class' => 'btn btn-default',
                'stay' => true,
            )
        );

        $this->tpl_form_vars['PS_ALLOW_ACCENTED_CHARS_URL'] = (int)Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL');
        $this->tpl_form_vars['PS_FORCE_FRIENDLY_PRODUCT'] = (int)Configuration::get('PS_FORCE_FRIENDLY_PRODUCT');
        $this->show_form_cancel_button = false;

        return parent::renderForm();
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
            $this->createCover($_FILES['cover']['tmp_name'], $object);

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
            $this->createCover($_FILES['cover']['tmp_name'], $object);

        return true;
    }

    public function postProcess()
    {
        if (Tools::isSubmit('deleteCover'))
            $this->deleteCover((int)Tools::getValue('id_simpleblog_author'));

        return parent::postProcess();
    }

    public function createCover($img = null, $object = null)
    {
        if (!isset($img))
            die('AdminSimpleBlogAuthorsController@createCover: No image to process');

        // $thumbX = Configuration::get('PH_BLOG_THUMB_X');
        // $thumbY = Configuration::get('PH_BLOG_THUMB_Y');
        $thumbX = 65;
        $thumbY = 65;

        $thumb_wide_X = Configuration::get('PH_BLOG_THUMB_X_WIDE');
        $thumb_wide_Y = Configuration::get('PH_BLOG_THUMB_Y_WIDE');

        $thumbMethod = Configuration::get('PH_BLOG_THUMB_METHOD');

        if (isset($object) && Validate::isLoadedObject($object))
        {
            $fileTmpLoc = $img;
            $origPath = _PS_MODULE_DIR_ . 'ph_simpleblog/covers_authors/'.$object->id.'.'.$object->cover;
            $pathAndName = _PS_MODULE_DIR_ . 'ph_simpleblog/covers_authors/'.$object->id.'-thumb.'.$object->cover;
            $pathAndNameWide = _PS_MODULE_DIR_ . 'ph_simpleblog/covers_authors/'.$object->id.'-wide.'.$object->cover;

            $tmp_location = _PS_TMP_IMG_DIR_.'ph_simpleblog_authors_'.$object->id.'.'.$object->cover;
            if (file_exists($tmp_location))
                @unlink($tmp_location);

            $tmp_location_list = _PS_TMP_IMG_DIR_.'ph_simpleblog_authors_'.$object->id.'-list.'.$object->cover;
            if (file_exists($tmp_location_list))
                @unlink($tmp_location_list);

            try
            {
                $orig = PhpThumbFactory::create($fileTmpLoc);
                $thumb = PhpThumbFactory::create($fileTmpLoc);
                $thumbWide = PhpThumbFactory::create($fileTmpLoc);
            }
            catch (Exception $e)
            {
                echo $e;
            }

            if ($thumbMethod == '1')
            {
                $thumb->adaptiveResize($thumbX,$thumbY);
                $thumbWide->adaptiveResize($thumb_wide_X,$thumb_wide_Y);
            }
            elseif ($thumbMethod == '2')
            {
                $thumb->cropFromCenter($thumbX,$thumbY);
                $thumbWide->cropFromCenter($thumb_wide_X,$thumb_wide_Y);
            }

            return $orig->save($origPath) && $thumb->save($pathAndName) && $thumbWide->save($pathAndNameWide) && ImageManager::thumbnail(_PS_MODULE_DIR_ . 'ph_simpleblog/covers_authors/'.$object->id.'.'.$object->cover, 'ph_simpleblog_authors_'.$object->id.'.'.$object->cover, 350, $object->cover);
        }

    }

    public function deleteCover($id)
    {
        $object = new SimpleBlogAuthor($id, Context::getContext()->language->id);

        SimpleBlogAuthor::deleteCover($object);

        Tools::redirectAdmin(self::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminSimpleBlogAuthors').'&conf=7');
    }
}
