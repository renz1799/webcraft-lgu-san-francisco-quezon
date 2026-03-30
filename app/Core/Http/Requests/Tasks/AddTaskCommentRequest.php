<?php

namespace App\Core\Http\Requests\Tasks;

use App\Core\Support\AdminContextAuthorizer;
use Illuminate\Foundation\Http\FormRequest;

class AddTaskCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user
            && app(AdminContextAuthorizer::class)->allowsPermission($user, 'tasks.comment');
    }

    public function rules(): array
    {
        return [
            'note' => ['required', 'string'],
        ];
    }
}
