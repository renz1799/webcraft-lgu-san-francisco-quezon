<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LoginDetail;
use Illuminate\Support\Facades\Log;


class LoginLogController extends Controller
{
    /**
     * Display the Login Logs page.
     */
    public function index()
    {
        return view('logs.index'); // Render the Blade view
    }

    /**
     * Fetch Login Logs for Datatables with Chunking.
     */
    public function getLogs(Request $request)
    {
        // Fetch pagination parameters from DataTables
        $start = $request->input('start', 0); // Start offset
        $length = $request->input('length', 20); // Number of records per page
        $searchValue = $request->input('search.value'); // Search term
    
        // Fetch sorting parameters
        $orderColumnIndex = $request->input('order.0.column'); // Column index for ordering
        $orderDirection = $request->input('order.0.dir', 'asc'); // Order direction (asc or desc)
        $columns = $request->input('columns'); // All column data from DataTables
    
        // Determine the column to sort by
        $orderColumnName = isset($columns[$orderColumnIndex]['name']) ? $columns[$orderColumnIndex]['name'] : 'created_at';
    
        // Base query with eager loading
        $query = LoginDetail::with('user')
            ->select(['login_details.*']);
    
        // Handle sorting for related model columns
        if ($orderColumnName === 'user') {
            $query->join('users', 'users.id', '=', 'login_details.user_id')
                  ->orderBy('users.username', $orderDirection);
        } else {
            $query->orderBy("login_details.$orderColumnName", $orderDirection);
        }
    
        // Apply search filter if there's a search value
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->whereHas('user', function ($userQuery) use ($searchValue) {
                    $userQuery->where('username', 'like', '%' . $searchValue . '%');
                })
                ->orWhere('ip_address', 'like', '%' . $searchValue . '%')
                ->orWhere('device', 'like', '%' . $searchValue . '%')
                ->orWhere('address', 'like', '%' . $searchValue . '%');
            });
        }
    
        // Get filtered records count for DataTables
        $totalFiltered = $query->count();
    
        // Apply pagination and fetch records
        $logs = $query
            ->offset($start) // Offset for pagination
            ->limit($length) // Limit to the requested number of records
            ->get();
    
        // Total records in the database (unfiltered)
        $totalRecords = LoginDetail::count();
    
        // Format data for DataTables response
        $data = $logs->map(function ($log) {
            return [
                'user' => $log->user->username ?? 'N/A',
                'ip_address' => $log->ip_address,
                'device' => $log->getDeviceDetailsAttribute(),
                'location' => $log->location
                    ? '<a href="' . $log->location . '" target="_blank" class="text-primary hover:underline">' .
                        ($log->address ?? 'View on Maps') . '</a>'
                    : 'Unknown Location',
                'created_at' => $log->created_at->format('M d, Y h:i A'),
            ];
        });
    
        // Prepare the response for DataTables
        return response()->json([
            'draw' => $request->input('draw'), // For DataTables' internal tracking
            'recordsTotal' => $totalRecords, // Total records in the database
            'recordsFiltered' => $totalFiltered, // Total records after filtering
            'data' => $data, // The actual data for the current page
        ]);
    }
    
    
    
}
