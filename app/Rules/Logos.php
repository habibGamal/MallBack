<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Request;

class Logos implements Rule
{
    private $branches_number;
    private $errors = [];
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($branches_number)
    {
        $this->branches_number = $branches_number;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $array_of_logos)
    {
            $logos_to_valid = [];
            for ($i = 0; $i < $this->branches_number; $i++) {
                // => create new array to arrange the positions of logos
                // => [null,2,3,4,null,5] : if there is no file we put null
                $logo = array_filter($array_of_logos, function ($value) use ($i) {
                    return ($i + 1) == intval($value->getClientOriginalName());
                });
                if(array_key_exists(0,$logo)){
                    $logos_to_valid[] = $logo[0];
                }
            }
            $errors = [];
            foreach ($logos_to_valid as $key => $value) {
                // => create errors array for null values
                if ($value == null) {
                    $errors[] = 'The logo of branch number ' . ($key + 1) . ' is not found.';
                }
            }
            if(count($errors) > 0){
                $this->errors = $errors;
                return false;
            }
            return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return  $this->errors;
    }
}
