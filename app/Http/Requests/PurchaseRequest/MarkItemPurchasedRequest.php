<?php

namespace App\Http\Requests\PurchaseRequest;

use Illuminate\Foundation\Http\FormRequest;

class MarkItemPurchasedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'harga_aktual' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'harga_aktual.required' => 'Harga aktual wajib diisi.',
            'harga_aktual.numeric'  => 'Harga aktual harus berupa angka.',
            'harga_aktual.min'      => 'Harga aktual tidak boleh negatif.',
        ];
    }
}
