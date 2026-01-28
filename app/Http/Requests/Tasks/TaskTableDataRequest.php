<?php

namespace App\Http\Requests\Tasks;

use Illuminate\Foundation\Http\FormRequest;

class TaskTableDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (!$user) return false;

        // baseline: only Staff or Admin can access tasks list/data
        if (!($user->hasRole('Administrator') || $user->hasRole('Staff'))) {
            return false;
        }

        // if requesting "all", require explicit permission (or admin)
        $scope = (string) $this->query('scope', 'mine');
        if ($scope === 'all') {
            return $user->hasRole('Administrator');
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:255'],
            'scope' => ['nullable', 'in:mine,available,all'],
            'status' => ['nullable', 'in:pending,in_progress,done,cancelled'],
            'page' => ['nullable', 'integer', 'min:1'],
            'size' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function filters(): array
    {
        return [
            'q' => trim((string) $this->query('q', '')),
            'scope' => trim((string) $this->query('scope', 'mine')),
            'status' => trim((string) $this->query('status', '')),
        ];
    }

    public function page(): int
    {
        return max(1, (int) $this->query('page', 1));
    }

    public function size(): int
    {
        $size = (int) $this->query('size', 15);
        return min(100, max(1, $size));
    }
}
