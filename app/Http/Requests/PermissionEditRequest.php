<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PermissionEditRequest extends FormRequest
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
        $permission = $this->route('permission');
        return [
            'name' => ['required', 'unique:permissions,name,' . $permission->id]
        ];
    }
    public function attributes()
    {
        return [
            'name' => 'nombre del permiso',
        ];
    }
}
