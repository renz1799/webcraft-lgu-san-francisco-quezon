<?php

namespace App\Core\Http\Requests\Tasks;

use App\Core\Support\AdminContextAuthorizer;
use Illuminate\Foundation\Http\FormRequest;

class DeleteTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user
            && app(AdminContextAuthorizer::class)->allowsPermission($user, 'tasks.archive');
    }

    public function rules(): array
    {
        return [];
    }
}
