<?php

class FrontController extends FrontControllerCore
{
    /**
     * Initializes page header variables
     */
    public function initHeader()
    {
        $active_tags = Tag::getActiveTags();
        
        $links = Tag::getNamesAndLinks($active_tags,$this->context);

        $this->context->smarty->assign(array(
            'links' => $links
        ));

        parent::initHeader();
    }
    protected function canonicalRedirection($canonical_url = '')
    {
        if (!$canonical_url || !Configuration::get('PS_CANONICAL_REDIRECT') || strtoupper($_SERVER['REQUEST_METHOD']) != 'GET' || Tools::getValue('live_edit')) {
            return;
        }

        $match_url = rawurldecode(Tools::getCurrentUrlProtocolPrefix().$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
        if (!preg_match('/^'.Tools::pRegexp(rawurldecode($canonical_url), '/').'([&?].*)?$/', $match_url)) {
            $params = array();
            $url_details = parse_url($canonical_url);

            if (!empty($url_details['query'])) {
                parse_str($url_details['query'], $query);
                foreach ($query as $key => $value) {
                    $params[Tools::safeOutput($key)] = Tools::safeOutput($value);
                }
            }
            $excluded_key = array('isolang', 'id_lang', 'controller', 'fc', 'id_product', 'id_category', 'id_manufacturer', 'id_supplier', 'id_cms', 'id_series');
            foreach ($_GET as $key => $value) {
                if (!in_array($key, $excluded_key) && Validate::isUrl($key) && Validate::isUrl($value)) {
                    $params[Tools::safeOutput($key)] = Tools::safeOutput($value);
                }
            }

            $str_params = http_build_query($params, '', '&');
            if (!empty($str_params)) {
                $final_url = preg_replace('/^([^?]*)?.*$/', '$1', $canonical_url).'?'.$str_params;
            } else {
                $final_url = preg_replace('/^([^?]*)?.*$/', '$1', $canonical_url);
            }

            // Don't send any cookie
            Context::getContext()->cookie->disallowWriting();

            if (defined('_PS_MODE_DEV_') && _PS_MODE_DEV_ && $_SERVER['REQUEST_URI'] != __PS_BASE_URI__) {
                die('[Debug] This page has moved<br />Please use the following URL instead: <a href="'.$final_url.'">'.$final_url.'</a>');
            }

            $redirect_type = Configuration::get('PS_CANONICAL_REDIRECT') == 2 ? '301' : '302';
            header('HTTP/1.0 '.$redirect_type.' Moved');
            header('Cache-Control: no-cache');
            Tools::redirectLink($final_url);
        }
    }
}
