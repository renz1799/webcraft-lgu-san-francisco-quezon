@extends('layouts.master')

@section('styles')
    <!-- Add custom styles here if needed -->
@endsection

@section('content')

<!-- Page Header -->
<div class="block justify-between page-header md:flex">
    <div>
        <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold">
            Manage Permissions
        </h3>
    </div>
    <ol class="flex items-center whitespace-nowrap min-w-0">
        <li class="text-[0.813rem] ps-[0.5rem]">
            <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="javascript:void(0);">
                Pages
                <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
            </a>
        </li>
        <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50" aria-current="page">
            Manage Permissions
        </li>
    </ol>
</div>
<!-- Page Header Close -->

<!-- Permissions Management -->
<div class="container mt-5">
    <!-- Add Permission Form -->
    <div class="card mb-5">
        <div class="card-header">
            <h6 class="text-[1rem] font-semibold">Add New Permission</h6>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            <form action="{{ route('permissions.store') }}" method="POST">
                @csrf
                <div class="form-group mb-3">
                    <label for="permission_name" class="form-label">Permission Name</label>
                    <input type="text" id="permission_name" name="name" class="form-control" placeholder="e.g., manage users" required>
                </div>
                <div class="form-group mb-3">
                    <label for="page" class="form-label">Page</label>
                    <input type="text" id="page" name="page" class="form-control" placeholder="e.g., Manage Users" required>
                </div>
                <div class="form-group mb-3">
                    <label for="guard_name" class="form-label">Guard Name</label>
                    <select id="guard_name" name="guard_name" class="form-control">
                        <option value="web" selected>Web</option>
                        <option value="api">API</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Add Permission</button>
            </form>
        </div>
    </div>

    <!-- Permissions Table -->
    <div class="card">
        <div class="card-header">
            <h6 class="text-[1rem] font-semibold">Existing Permissions</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table whitespace-nowrap min-w-full">
                    <thead class="bg-primary/10">
                        <tr class="border-b border-primary/10">
                            <th scope="col" class="text-start">Permission Name</th>
                            <th scope="col" class="text-start">Page</th> <!-- Added Page Column -->
                            <th scope="col" class="text-start">Guard</th>
                            <th scope="col" class="text-start">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($permissions as $permission)
                            <tr class="border-b border-primary/10">
                                <td class="text-start">{{ $permission->name }}</td>
                                <td class="text-start">{{ $permission->page }}</td> <!-- Show Page -->
                                <td class="text-start">{{ $permission->guard_name }}</td>
                                <td class="text-start">
                                    <form action="{{ route('permissions.destroy', $permission) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this permission?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No permissions found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
    <!-- Add custom JavaScript here if needed -->
@endsection
