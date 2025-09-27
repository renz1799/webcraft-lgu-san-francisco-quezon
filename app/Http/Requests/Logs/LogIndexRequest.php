<?php
// app/Http/Requests/Logs/LogIndexRequest.php
namespace App\Http\Requests\Logs;

use App\Http\Requests\BaseFormRequest;

class LogIndexRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'action'    => ['sometimes','string','max:100'],
            'actor_id'  => ['sometimes','uuid'],
            'date_from' => ['sometimes','date'],
            'date_to'   => ['sometimes','date','after_or_equal:date_from'],
            'per_page'  => ['sometimes','integer','min:10','max:200'],
        ];
    }
}
