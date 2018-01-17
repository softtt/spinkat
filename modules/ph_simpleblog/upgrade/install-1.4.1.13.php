<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_4_1_13($object)
{
    $object->registerHook('displayTopFooter1');

    return true;
}
