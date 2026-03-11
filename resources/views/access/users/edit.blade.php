@extends('layouts.master')

@section('styles')
  <link rel="stylesheet" href="{{ asset('build/assets/libs/tom-select/css/tom-select.default.min.css') }}">
@endsection

@section('content')
@php
  $roleDefaults = $roles->mapWithKeys(function ($role) {
      $nested = [];

      foreach ($role->permissions as $permission) {
          $page = $permission->page ?: 'Others';
          $words = explode(' ', $permission->name, 2);
          $action = strtolower($words[0] ?? '');
          $resource = $words[1] ?? '';

          if ($resource === '') {
              continue;
          }

          $action = $action === 'edit' ? 'modify' : $action;

          if (! isset($nested[$page][$resource])) {
              $nested[$page][$resource] = [];
          }

          if (! in_array($action, $nested[$page][$resource], true)) {
              $nested[$page][$resource][] = $action;
          }
      }

      return [$role->name => $nested];
  });
@endphp

<div id="access-user-edit-page" class="container">
  <script type="application/json" id="roleDefaultsJson">@json($roleDefaults)</script>
  <!-- Header -->
  <div class="block justify-between page-header md:flex">
    <div>
      <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold">
        Mail Settings
      </h3>
    </div>
    <ol class="flex items-center whitespace-nowrap min-w-0">
      <li class="text-[0.813rem] ps-[0.5rem]">
        <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="javascript:void(0);">
          Mail
          <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
        </a>
      </li>
      <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50" aria-current="page">
        Mail Settings
      </li>
    </ol>
  </div>

  <div class="grid grid-cols-12 gap-6 mb-[3rem]">
    <div class="xl:col-span-12 col-span-12">
      <div class="box">
        <div class="box-header sm:flex block !justify-start">
          <nav aria-label="Tabs" class="md:flex block !justify-start whitespace-nowrap" role="tablist">
            <a class="m-1 block w-full hs-tab-active:bg-primary/10 hs-tab-active:text-primary cursor-pointer text-defaulttextcolor dark:text-defaulttextcolor/70 py-2 px-3 flex-grow text-[0.75rem] font-medium rounded-md hover:text-primary active"
               id="Roles-permission" data-hs-tab="#roles-permission" aria-controls="roles-permission">
              Roles and Permissions
            </a>
            <a class="m-1 block w-full hs-tab-active:bg-primary/10 hs-tab-active:text-primary cursor-pointer text-defaulttextcolor dark:text-defaulttextcolor/70 py-2 px-3 text-[0.75rem] flex-grow font-medium rounded-md hover:text-primary"
               id="account-item" data-hs-tab="#account-settings" aria-controls="account-settings">
              Account Settings
            </a>
          </nav>
        </div>

        <div class="box-body">
          <div class="tab-content">
            <div class="tab-pane show active dark:border-defaultborder/10" id="roles-permission" aria-labelledby="Roles-permission">
                     <div class="sm:p-4 p-0">

                <!-- Assign Role -->
                <h6 class="font-semibold mb-4 text-[1rem]">Assign Role</h6>

                <div class="sm:grid grid-cols-12 gap-6 mb-6">
                  <div class="xl:col-span-6 col-span-12">
                    <label for="user-name" class="form-label">User Name</label>
                    <input type="text" class="form-control w-full !rounded-md" id="user-name" value="{{ $user->username }}" disabled>
                  </div>
                  <div class="xl:col-span-6 col-span-12">
                    <label for="role" class="form-label">Select Role</label>
                    <select
                      name="role"
                      id="role"
                      class="form-control"
                      data-endpoint="{{ route('access.users.update', $user) }}"
                    >
                      @foreach ($roles as $role)
                        <option value="{{ $role->name }}" {{ $userRole && $userRole->name == $role->name ? 'selected' : '' }}>
                          {{ $role->name }}
                        </option>
                      @endforeach
                    </select>
                  </div>
                </div>

                <!-- Permissions grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
                  @foreach ($permissions as $page => $actions)
                    <div class="mb-6 flex flex-col h-full">
                      <h6 class="font-semibold mb-3 text-[1.1rem] flex justify-between items-center">
                        {{ $page }}
                        <span id="feedback-{{ Str::slug($page) }}" class="text-green-500 text-sm hidden">✔ Saved</span>
                      </h6>

                      <div class="table-responsive h-full">
                        <table class="table whitespace-nowrap border border-primary/10 w-full">
                          <thead class="bg-primary/10">
                            <tr>
                              <th class="p-3 text-start">Permission</th>
                              <th class="p-3 text-center">View</th>
                              <th class="p-3 text-center">Modify</th>
                              <th class="p-3 text-center">Delete</th>
                            </tr>
                          </thead>
                          <tbody>
                            @php $groupedPermissions = []; @endphp
                            @foreach ($actions as $permission)
                              @php
                                $words = explode(' ', $permission->name, 2);
                                $action = strtolower($words[0] ?? '');
                                $clean  = $words[1] ?? '';
                                if (!isset($groupedPermissions[$clean])) {
                                    $groupedPermissions[$clean] = ['view' => false, 'modify' => false, 'delete' => false];
                                }
                                $groupedPermissions[$clean][$action] = $permission->id;
                              @endphp
                            @endforeach

                            @foreach ($groupedPermissions as $permissionName => $acts)
                              <tr>
                                <td class="p-3 text-start">{{ $permissionName }}</td>
                                <td class="text-center">
                                  @if ($acts['view'])
                                    <input type="checkbox"
                                      class="permission-checkbox ti-switch shrink-0 !w-[35px] !h-[21px] before:size-4"
                                      data-page="{{ $page }}"
                                      data-action="view"
                                      data-permission="{{ $permissionName }}"
                                      {{ isset($userPermissions[$page][$permissionName]) && in_array('view', $userPermissions[$page][$permissionName]) ? 'checked' : '' }}>
                                  @endif
                                </td>
                                <td class="text-center">
                                  @if ($acts['modify'])
                                    <input type="checkbox"
                                      class="permission-checkbox ti-switch shrink-0 !w-[35px] !h-[21px] before:size-4"
                                      data-page="{{ $page }}"
                                      data-action="modify"
                                      data-permission="{{ $permissionName }}"
                                      {{ isset($userPermissions[$page][$permissionName]) && in_array('modify', $userPermissions[$page][$permissionName]) ? 'checked' : '' }}>
                                  @endif
                                </td>
                                <td class="text-center">
                                  @if ($acts['delete'])
                                    <input type="checkbox"
                                      class="permission-checkbox ti-switch shrink-0 !w-[35px] !h-[21px] before:size-4"
                                      data-page="{{ $page }}"
                                      data-action="delete"
                                      data-permission="{{ $permissionName }}"
                                      {{ isset($userPermissions[$page][$permissionName]) && in_array('delete', $userPermissions[$page][$permissionName]) ? 'checked' : '' }}>
                                  @endif
                                </td>
                              </tr>
                            @endforeach
                          </tbody>
                        </table>
                      </div>
                    </div>
                  @endforeach
                </div>
                <!-- /Permissions grid -->



              </div>
            </div>

            <!-- Account Settings (NEW) -->
<div class="tab-pane hidden dark:border-defaultborder/10" id="account-settings" aria-labelledby="account-item">
  <div class="sm:p-4 p-0">
    <h6 class="font-semibold mb-4 text-[1rem]">Account Settings</h6>

    <div class="box p-4">
      <div class="flex items-center justify-between">
        <div>
          <h6 class="font-semibold mb-1">Reset Account Password</h6>
          <p class="text-sm text-muted">
            Generates a one-time 6-digit password for this user. Share securely and have them change it after login.
          </p>
        </div>
        <button
          type="button"
          class="ti-btn btn-wave bg-danger text-white"
          id="resetPasswordButton"
          data-endpoint="{{ route('access.users.password.reset', $user) }}"
        >
          Generate Temporary Password
        </button>
      </div>
    </div>
  </div>
</div>
          </div>
        </div>

        <!-- Footer: single Save button -->
        <div class="box-footer">
          <div class="ltr:float-right rtl:float-left">
            <button type="button" class="ti-btn btn-wave ti-btn-light m-1" id="restoreDefaultsButton">
              Restore Defaults
            </button>

            <button
              type="button"
              class="ti-btn btn-wave bg-primary text-white m-1"
              id="savePermissionsButton"
              data-endpoint="{{ route('access.users.update', $user) }}"
            >
              Save Changes
            </button>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

@endsection
