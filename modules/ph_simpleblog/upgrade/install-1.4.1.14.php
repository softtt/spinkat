<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_4_1_14($object)
{
    $object->registerHook('productTab');
    $object->registerHook('productTabContent');

    return true;
}
