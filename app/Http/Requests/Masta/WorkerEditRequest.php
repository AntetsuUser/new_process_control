<?php

namespace App\Http\Requests\Masta;

use Illuminate\Foundation\Http\FormRequest;

class WorkerEditRequest extends FormRequest
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
            // ここにルールを書いていく
            'factory_id' => ['required'],
            'department_id' => ['required'],
            'family_name' => ['required'],
            'personal_name' => ['required'],
        ];
    }

    // バリデーションに引っかかったときに出す文字
    public function messages()
    {
        return [
            'factory_id.required' => '工場を選択してください',  
            'department_id.required' => '部署を選択してください',  
            'family_name.required' => '苗字を入力してください',  
            'personal_name.required' => '名前を入力してください',  
        ];
    }
}
