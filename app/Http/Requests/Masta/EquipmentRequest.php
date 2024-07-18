<?php

namespace App\Http\Requests\Masta;

use Illuminate\Foundation\Http\FormRequest;

class EquipmentRequest extends FormRequest
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
            //'検証する値'=>['検証ルール1', '検証ルール2']
            'factory_id' => ['required'],
            'department_id' => ['required'],
            'line' => ['required'],
            'equipment_id' => ['required'],
            'category' => ['required'],
            'model' => ['required'],


        ];
    }
    // バリデーションに引っかかったときに出す文字
    public function messages()
    {
        return [
            'factory_id.required' => '工場を選択してください',  
            'department_id.required' => '部署を選択してください',  
            'line.required' => 'ラインを入力してください',  
            'equipment_id.required' => '設備Noを入力してください',  
            'category.required' => '区分を入力してください',  
            'model.required' => '型式を入力してください',  
        ];
    }
}
