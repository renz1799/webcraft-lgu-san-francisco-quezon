<?php

namespace App\Core\Http\Requests\Tasks;

use App\Core\Support\AdminContextAuthorizer;
use Illuminate\Foundation\Http\FormRequest;

class ChangeTaskStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user
            && app(AdminContextAuthorizer::class)->allowsPermission($user, 'tasks.update_status');
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:pending,in_progress,done,cancelled'],
            'note' => ['nullable', 'string'],
        ];
    }
}
