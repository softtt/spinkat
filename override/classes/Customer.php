<?php

class Customer extends CustomerCore
{
    public function __construct($id = null)
    {
        self::$definition['fields']['lastname']['required'] = false;
        self::$definition['fields']['lastname']['validate'] = null;
        self::$definition['fields']['firstname']['size'] = 254;
        self::$definition['fields']['email']['required'] = false;

        parent::__construct($id);
    }
}
