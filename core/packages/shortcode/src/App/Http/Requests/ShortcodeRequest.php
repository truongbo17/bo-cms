<?php

namespace Bo\Shortcode\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShortcodeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // only allow updates if the user is logged in
        return bo_auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'key' => 'required|min:3|max:255',
            'value' => 'required',
            'type' => 'required',
            'name' => 'required|min:3|max:255',
        ];
    }
}
