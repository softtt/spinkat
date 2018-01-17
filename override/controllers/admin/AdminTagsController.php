<?php


class AdminTagsController extends AdminTagsControllerCore
{
    public $bootstrap = true;

    public function __construct()
    {
        $this->table = 'tag';
        $this->className = 'Tag';

        $this->fields_list = array(
            'id_tag' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs'
            ),
            'name' => array(
                'title' => $this->l('Name'),
                'filter_key' => 'a!name'
            ),
            'products' => array(
                'title' => $this->l('Products'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'havingFilter' => true
            ),
            'active' => array(
                'title' => $this->l('Выводить в списке тегов'),
                'active' => 'status',
                'type' => 'bool',
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'orderby' => false
            )
        );

        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Delete selected items?')
            )
        );

        AdminController::__construct();
    }

    public function processAdd()
    {
        $this->checkUniqueName();

        parent::processAdd();
    }

    public function checkUniqueName()
    {
        $name = Tools::getValue('name');

        $sql = 'SELECT id_tag FROM `'._DB_PREFIX_.'tag` WHERE name="'.$name.'" ';
        $res = Db::getInstance()->getValue($sql);

        if ($res) {
            $this->errors[] = sprintf('Тег %s уже существует.', $name);
        }
    }

    public function postProcess()
    {
        return AdminController::postProcess();
    }
    
    public function renderForm()
    {
        /** @var Tag $obj */
        if (!($obj = $this->loadObject(true))) {
            return;
        }

        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Tag'),
                'icon' => 'icon-tag'
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => 'Имя',
                    'name' => 'name',
                    'required' => true
                ),
                array(
                    'type' => 'text',
                    'label' => 'Заголовок H1',
                    'name' => 'title_for_seo_h1',
                    'lang' => false,
                    'required' => false,
                ),
                array(
                    'type' => 'textarea',
                    'label' => "Текст описания",
                    'name' => 'description',
                    'lang' => false,
                    'cols' => 60,
                    'rows' => 10,
                    'col' => 6,
                    'autoload_rte' => 'rte', //Enable TinyMCE editor for description
                ),
                array(
                    'type' => 'text',
                    'label' => "Ссылка",
                    'name' => 'link_rewrite',
                    'lang' => false,
                    'col' => 4,
                    'hint' => 'Запрещенные символы: &lt;&gt;;=#{}'
                ),
                array(
                    'type' => 'text',
                    'label' => 'SEO Meta Title',
                    'name' => 'meta_title',
                    'lang' => false,
                    'col' => 4,
                    'hint' => 'Запрещенные символы: &lt;&gt;;=#{}'
                ),
                array(
                    'type' => 'text',
                    'label' => 'SEO Meta Description',
                    'name' => 'meta_description',
                    'lang' => false,
                    'col' => 6,
                    'hint' => 'Запрещенные символы: &lt;&gt;;=#{}'
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            )
        );

        return AdminController::renderForm();
    }

    public function initPageHeaderToolbar()
    {
        if ($this->display == 'edit') {
            if ($tag = $this->loadObject(true)) {

                // adding button for preview this product
                if ($url_preview = Context::getContext()->link->getTagLink($tag)) {
                    $this->page_header_toolbar_btn['preview'] = array(
                        'short' => $this->l('Просмотр', null, null, false),
                        'href' => $url_preview,
                        'desc' => $this->l('Просмотр', null, null, false),
                        'target' => true,
                        'class' => 'previewUrl'
                    );
                }
            }
        }

        parent::initPageHeaderToolbar();
    }
}
