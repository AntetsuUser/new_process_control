<?php

namespace App\Http\Requests\Masta;

use Illuminate\Foundation\Http\FormRequest;

class MaterialUploadRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'material_file' => ['required', 'mimes:xls,xlsx'],
            'arrival_date' => ['required', 'date'],
        ];
    }
    // バリデーションに引っかかったときに出す文字
    public function messages()
    {
        return [
            'material_file.required' => 'ファイルが選択されていないか、ファイル形式が違います。確認してください。',
            'arrival_date.required' => '入荷日を入力してください。',
            'arrival_date.date' => '入荷日は有効な日付を入力してください。',
        ];
    }
}
