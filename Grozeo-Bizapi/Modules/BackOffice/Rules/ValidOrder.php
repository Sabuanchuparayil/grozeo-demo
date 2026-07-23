<?php

namespace BackOffice\Rules;


use BackOffice\Models\TransferOrder;
use Illuminate\Contracts\Validation\Rule;


class ValidOrder implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $model = new TransferOrder;
        $orderField="fsto_id";
      
        return $model->where($orderField, $value)->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The Transfer order id is invalid.';
    }
}
