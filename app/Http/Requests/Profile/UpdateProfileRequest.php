<?php

namespace App\Http\Requests\Profile;

use App\Http\Requests\BaseFormRequest;

class UpdateProfileRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $userId = (string) $this->user()->id;

        return [
            'first_name'       => ['bail','required','string','max:255'],
            'middle_name'      => ['nullable','string','max:255'],
            'last_name'        => ['bail','required','string','max:255'],
            'name_extension'   => ['nullable','string','max:50'],

            'email'            => ['bail','required','email:rfc','max:255',"unique:users,email,{$userId},id"],
            'username'         => ['bail','required','string','max:255',"unique:users,username,{$userId},id"],

            'address'          => ['nullable','string','max:255'],
            'contact_details'  => ['nullable','string','max:255'],

            'profile_photo'    => ['nullable','image','mimes:jpg,jpeg,png','max:2048'],
        ];
    }
}
