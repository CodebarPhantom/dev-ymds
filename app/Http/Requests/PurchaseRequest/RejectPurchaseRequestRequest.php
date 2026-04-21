<?php

namespace App\Http\Requests\PurchaseRequest;

use Illuminate\Foundation\Http\FormRequest;

class RejectPurchaseRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'catatan' => ['required', 'string', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'catatan.required' => 'Catatan penolakan wajib diisi.',
            'catatan.string'   => 'Catatan penolakan harus berupa teks.',
            'catatan.min'      => 'Catatan penolakan tidak boleh kosong.',
        ];
    }
}
