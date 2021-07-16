<?php

namespace App;

class Validator
{
    public function validate(array $school)
    {
        $errors = [];

        if (empty($school['name'])) {
            $errors['name'] = "No name";
        }

        return $errors;
    }
}