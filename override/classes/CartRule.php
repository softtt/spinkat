<?php

class CartRule extends CartRuleCore
{
    public static function array_uintersect($array1, $array2)
    {
        $intersection = array();
        foreach ($array1 as $value1) {
            foreach ($array2 as $value2) {
                if (CartRule::array_uintersect_compare($value1, $value2) == 0) {
                    $intersection[] = $value1;
                    break 1;
                }
            }
        }
        return $intersection;
    }

    public function getBannerLink()
    {
        $image = _PS_CART_RULE_IMG_DIR_.$this->id.'.jpg';
        if (file_exists($image)) {
            return __PS_BASE_URI__.'img/cr/'.$this->id.'.jpg';
        }
    }
}
