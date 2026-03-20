<?php

namespace App\Support;

use Illuminate\Support\Facades\Request;

class AuditRequestContextResolver
{
    /**
     * @return array<string, mixed>
     */
    public function resolve(): array
    {
        $request = request();
        $actor = $request->user();

        return [
            'actor_id' => optional($actor)->id,
            'actor_type' => $actor ? get_class($actor) : null,
            'request_method' => strtoupper($request->method()),
            'request_url' => Request::fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => (string) $request->header('User-Agent'),
        ];
    }
}
