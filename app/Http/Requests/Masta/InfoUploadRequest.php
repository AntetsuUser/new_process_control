<?php

namespace App\Http\Requests\Masta;

use Illuminate\Foundation\Http\FormRequest;

class InfoUploadRequest extends FormRequest
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
            'file' =>['required', 'mimes:xls,xlsx'],
        ];
    }
    // バリデーションに引っかかったときに出す文字
    public function messages()
    {
        return [
            'file.required' =>'ファイルが選択されていないか、ファイル形式が違います確認してください',
        ];
    }
}
