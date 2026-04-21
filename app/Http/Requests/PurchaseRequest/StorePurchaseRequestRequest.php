<?php

namespace App\Http\Requests\PurchaseRequest;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'judul_pengajuan'        => ['required', 'string', 'max:255'],
            'keperluan'              => ['nullable', 'string'],
            'tanggal_dibutuhkan'     => ['required', 'date'],
            'items'                  => ['required', 'array', 'min:1'],
            'items.*.nama_barang'    => ['required', 'string', 'max:255'],
            'items.*.satuan'         => ['required', 'string', 'max:50'],
            'items.*.jumlah'         => ['required', 'integer', 'min:1'],
            'items.*.biaya_estimasi' => ['required', 'numeric', 'min:0'],
            'items.*.catatan_item'   => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'judul_pengajuan.required'        => 'Judul pengajuan wajib diisi.',
            'judul_pengajuan.string'          => 'Judul pengajuan harus berupa teks.',
            'judul_pengajuan.max'             => 'Judul pengajuan maksimal 255 karakter.',
            'tanggal_dibutuhkan.required'     => 'Tanggal dibutuhkan wajib diisi.',
            'tanggal_dibutuhkan.date'         => 'Tanggal dibutuhkan harus berupa tanggal yang valid.',
            'items.required'                  => 'Daftar item wajib diisi.',
            'items.array'                     => 'Daftar item harus berupa array.',
            'items.min'                       => 'Minimal harus ada 1 item.',
            'items.*.nama_barang.required'    => 'Nama barang wajib diisi.',
            'items.*.nama_barang.string'      => 'Nama barang harus berupa teks.',
            'items.*.nama_barang.max'         => 'Nama barang maksimal 255 karakter.',
            'items.*.satuan.required'         => 'Satuan wajib diisi.',
            'items.*.satuan.string'           => 'Satuan harus berupa teks.',
            'items.*.satuan.max'              => 'Satuan maksimal 50 karakter.',
            'items.*.jumlah.required'         => 'Jumlah wajib diisi.',
            'items.*.jumlah.integer'          => 'Jumlah harus berupa bilangan bulat.',
            'items.*.jumlah.min'              => 'Jumlah minimal 1.',
            'items.*.biaya_estimasi.required' => 'Biaya estimasi wajib diisi.',
            'items.*.biaya_estimasi.numeric'  => 'Biaya estimasi harus berupa angka.',
            'items.*.biaya_estimasi.min'      => 'Biaya estimasi tidak boleh negatif.',
        ];
    }
}
