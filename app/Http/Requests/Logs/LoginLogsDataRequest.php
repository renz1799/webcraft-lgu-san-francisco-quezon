<?php
// app/Http/Requests/Logs/LoginLogsDataRequest.php

namespace App\Http\Requests\Logs;

use Illuminate\Foundation\Http\FormRequest;

class LoginLogsDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        $u = $this->user();
        return $u && ($u->hasRole('admin') || $u->can('view Login Logs'));
    }

    public function rules(): array
    {
        return [
            'start'                 => ['sometimes','integer','min:0'],
            'length'                => ['sometimes','integer','min:1','max:100'],
            'search.value'          => ['nullable','string','max:255'],
            'order.0.column'        => ['nullable','integer'],
            'order.0.dir'           => ['nullable','in:asc,desc'],
            'columns'               => ['nullable','array'],
            'columns.*.name'        => ['nullable','string','max:64'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $v = parent::validated();

        $v['start']  = (int)($v['start']  ?? 0);
        $v['length'] = (int)($v['length'] ?? 20);
        $v['search'] = $v['search']['value'] ?? null;

        // Resolve order column name from DataTables format
        $orderIdx  = $this->input('order.0.column');
        $orderName = 'created_at';
        if ($orderIdx !== null) {
            $cols = $this->input('columns', []);
            if (!empty($cols[$orderIdx]['name'])) {
                $orderName = $cols[$orderIdx]['name'];
            }
        }
        $v['order_by']  = $orderName;
        $v['order_dir'] = $this->input('order.0.dir', 'desc');

        return $v;
    }
}
