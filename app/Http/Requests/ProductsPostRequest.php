<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductsPostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "sku" => "required",
            "name" => "required",
            "price" => "required",
            "sale_price" => "required",
            "sale_price_start_date" => "required",
            "sale_price_end_date" => "required",
            "quantity_available" => "required",
        ];
    }
}
