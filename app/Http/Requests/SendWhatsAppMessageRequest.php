<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendWhatsAppMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'device_id' => 'nullable|integer|exists:whatsapp_devices,id',
            'phone' => 'required|string|min:7|max:20',
            'message' => 'required|string|min:1|max:4096',
            'template_id' => 'nullable|integer|exists:blast_templates,id',
            'template_variables' => 'nullable|array',
            'pasien_id' => 'nullable|integer|exists:pasien,no_rkm_medis',
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'phone.required' => 'Nomor telepon harus diisi',
            'phone.string' => 'Nomor telepon harus berupa teks',
            'phone.min' => 'Nomor telepon minimal 10 karakter',
            'phone.max' => 'Nomor telepon maksimal 20 karakter',
            'message.required' => 'Pesan harus diisi',
            'message.string' => 'Pesan harus berupa teks',
            'message.min' => 'Pesan minimal 1 karakter',
            'message.max' => 'Pesan maksimal 4096 karakter',
            'template_id.integer' => 'Template ID harus berupa angka',
            'template_id.exists' => 'Template yang dipilih tidak ditemukan',
            'template_variables.array' => 'Variabel template harus berupa array',
            'pasien_id.integer' => 'Pasien ID harus berupa angka',
            'pasien_id.exists' => 'Pasien yang dipilih tidak ditemukan',
        ];
    }
}
