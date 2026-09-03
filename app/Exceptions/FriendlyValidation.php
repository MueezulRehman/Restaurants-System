<?php

namespace App\Exceptions;

/**
 * Map common validation messages to business-friendly text.
 * Call from App\Exceptions\Handler or bootstrap exception handling.
 *
 * @author Mueez Ul Rehman
 */
class FriendlyValidation
{
    public static function messages(): array
    {
        return [
            'required' => 'Please fill in :attribute.',
            'email' => 'Enter a valid email for :attribute.',
            'numeric' => ':attribute must be a number.',
            'min.numeric' => ':attribute is too small.',
            'max.numeric' => ':attribute is too large.',
            'unique' => 'This :attribute is already in use.',
            'exists' => 'Selected :attribute is invalid.',
            'in' => 'Selected :attribute is not allowed.',
            'cart.required' => 'Add at least one item before checkout.',
            'cart.*.quantity.required' => 'Each item needs a quantity.',
            'cart.*.quantity.min' => 'Quantity must be greater than zero.',
            'payment_method.required' => 'Choose a payment method.',
            'customer_name.required' => 'Customer name is required.',
        ];
    }
}
