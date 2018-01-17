<?php

class ObjectModel extends ObjectModelCore
{

   /**
     * Validates submitted values and returns an array of errors, if any.
     *
     * @param bool $htmlentities If true, uses htmlentities() for field name translations in errors.
     *
     * @return array
     */
    public function validateController($htmlentities = true)
    {
        $this->cacheFieldsRequiredDatabase();
        $errors = array();
        $required_fields_database = (isset(self::$fieldsRequiredDatabase[get_class($this)])) ? self::$fieldsRequiredDatabase[get_class($this)] : array();
        foreach ($this->def['fields'] as $field => $data) {
            $value = Tools::getValue($field, $this->{$field});
            // Check if field is required by user
            if (in_array($field, $required_fields_database)) {
                $data['required'] = true;
            }

            // Checking for required fields
            if (isset($data['required']) && $data['required'] && empty($value) && $value !== '0') {
                if (!$this->id || $field != 'passwd') {
                    // $errors[$field] = '<b>'.self::displayFieldName($field, get_class($this), $htmlentities).'</b> '.Tools::displayError("is invalid.");
                    $errors[$field] = sprintf(Tools::displayError('The %s field is required.'), self::displayFieldName($field, get_class($this), $htmlentities));
                }
            }

            // Checking for maximum fields sizes
            if (isset($data['size']) && !empty($value) && Tools::strlen($value) > $data['size']) {
                $errors[$field] = sprintf(
                    Tools::displayError('%1$s is too long. Maximum length: %2$d'),
                    self::displayFieldName($field, get_class($this), $htmlentities),
                    $data['size']
                );
            }

            // Checking for fields validity
            // Hack for postcode required for country which does not have postcodes
            if (!empty($value) || $value === '0' || ($field == 'postcode' && $value == '0')) {
                if (isset($data['validate']) && !Validate::$data['validate']($value) && (!empty($value) || $data['required'])) {
                    // $errors[$field] = '<b>'.self::displayFieldName($field, get_class($this), $htmlentities).'</b> '.Tools::displayError('is invalid.');
                    $errors[$field] = sprintf(Tools::displayError('Неверный формат поля %s.'), self::displayFieldName($field, get_class($this), $htmlentities));

                } else {
                    if (isset($data['copy_post']) && !$data['copy_post']) {
                        continue;
                    }
                    if ($field == 'passwd') {
                        if ($value = Tools::getValue($field)) {
                            $this->{$field} = Tools::encrypt($value);
                        }
                    } else {
                        $this->{$field} = $value;
                    }
                }
            }
        }

        return $errors;
    }
}