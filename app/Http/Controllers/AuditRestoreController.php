<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Permission;
use App\Http\Requests\Logs\RestoreSubjectRequest;

class AuditRestoreController extends Controller
{
public function restore(RestoreSubjectRequest $request)
{
    $model = $request->model();   // already validated & authorized
    $model->restore();

    return response()->json(['ok' => true]);
}
}
