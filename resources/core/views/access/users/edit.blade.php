@extends('layouts.master')

@section('styles')
  <link rel="stylesheet" href="{{ asset('build/assets/libs/tom-select/css/tom-select.default.min.css') }}">
  <style>
    .user-access-profile-cover {
      background: linear-gradient(135deg, rgba(79, 70, 229, 0.94), rgba(14, 165, 233, 0.9));
    }

    .dark .user-access-profile-cover {
      background: linear-gradient(135deg, rgba(49, 46, 129, 0.96), rgba(8, 145, 178, 0.9));
    }

    .user-access-metric {
      border: 1px solid rgba(255, 255, 255, 0.18);
      background: rgba(255, 255, 255, 0.1);
      border-radius: 1rem;
      padding: 0.85rem 1rem;
      backdrop-filter: blur(8px);
    }

    .user-access-mini-card {
      border: 1px solid rgba(15, 23, 42, 0.08);
      border-radius: 0.9rem;
      padding: 1rem;
      background: rgba(248, 250, 252, 0.72);
    }

    .dark .user-access-mini-card {
      border-color: rgba(255, 255, 255, 0.06);
      background: rgba(17, 24, 39, 0.55);
    }

    .user-access-help {
      border: 1px solid rgba(79, 70, 229, 0.1);
      background: rgba(79, 70, 229, 0.04);
      border-radius: 1rem;
      padding: 1rem 1.1rem;
    }

    .dark .user-access-help {
      border-color: rgba(129, 140, 248, 0.14);
      background: rgba(79, 70, 229, 0.08);
    }

    .permission-grid-table th,
    .permission-grid-table td {
      vertical-align: middle;
    }

    .permission-grid-table tbody tr:last-child td {
      border-bottom-width: 0;
    }

    .user-access-avatar-fallback {
      width: 5.5rem;
      height: 5.5rem;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 1.35rem;
      font-weight: 700;
      letter-spacing: 0.08em;
      color: #fff;
      background: rgba(255, 255, 255, 0.14);
      border: 1px solid rgba(255, 255, 255, 0.22);
    }

    .user-access-pill {
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
      border-radius: 999px;
      padding: 0.35rem 0.75rem;
      font-size: 0.75rem;
      font-weight: 600;
      line-height: 1;
    }

    .user-access-shell {
      width: 100%;
      max-width: none;
      padding-inline: 0.5rem;
    }

    @media (min-width: 1280px) {
      .user-access-shell {
        padding-inline: 1rem;
      }
    }
  </style>
@endsection

@section('content')
@php
  $adminRoutes = $adminRoutes ?? app(\App\Core\Support\AdminRouteResolver::class);
  $moduleScopedAccess = $moduleScopedAccess ?? $adminRoutes->isModuleScoped();
  $moduleContextName = $moduleContextName
      ?? (trim((string) ($currentModule->name ?? $adminRoutes->scopedModuleCode() ?? 'Module')) ?: 'Module');
  $displayName = trim((string) ($user->profile?->full_name ?? '')) ?: $user->username;
  $nameParts = preg_split('/\s+/', trim($displayName)) ?: [];
  $initials = collect($nameParts)
      ->filter()
      ->map(fn ($part) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))
      ->take(2)
      ->implode('');
  $initials = $initials !== '' ? $initials : \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->username, 0, 2));
  $profilePhoto = $user->profile?->profile_photo_path
      ? asset('storage/' . ltrim($user->profile->profile_photo_path, '/'))
      : null;
  $currentRoleName = $userRole?->name ?? 'No role assigned';
  $directPermissionCount = collect($userPermissions)->sum(
      fn ($perPage) => collect($perPage)->sum(fn ($actions) => count($actions))
  );
  $managedPagesCount = $permissions->count();
  $statusLabel = $user->is_active ? 'Active' : 'Inactive';
  $statusTone = $user->is_active ? 'bg-success/15 text-success' : 'bg-danger/15 text-danger';
  $verificationLabel = $user->email_verified_at ? 'Verified' : 'Unverified';
  $joinedDate = $user->created_at?->format('M d, Y') ?? 'N/A';
  $updatedDate = $user->updated_at?->format('M d, Y') ?? 'N/A';
@endphp

<div id="access-user-edit-page" class="container user-access-shell">
  <script type="application/json" id="roleDefaultsJson">@json($roleDefaults ?? [])</script>

  <div class="block justify-between page-header md:flex">
    <div>
      <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold">
        {{ $moduleScopedAccess ? 'Manage Module Access' : 'Edit User Access' }}
      </h3>
      <p class="text-textmuted dark:text-textmuted/80 mb-0">
        {{ $moduleScopedAccess
            ? 'Adjust role assignment and direct permissions for ' . $moduleContextName . ' without changing the user\'s platform identity.'
            : 'Review the user profile, assign a baseline role, and fine-tune direct permissions from one workspace.' }}
      </p>
    </div>
    <ol class="flex items-center whitespace-nowrap min-w-0">
      <li class="text-[0.813rem] ps-[0.5rem]">
        <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="{{ $adminRoutes->route('access.users.index') }}">
          Access
          <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
        </a>
      </li>
      <li class="text-[0.813rem] ps-[0.5rem]">
        <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="{{ $adminRoutes->route('access.users.index') }}">
          {{ $moduleScopedAccess ? 'Assigned Users' : 'Users' }}
          <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
        </a>
      </li>
      <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50" aria-current="page">
        {{ $moduleScopedAccess ? 'Manage Access' : 'Edit Access' }}
      </li>
    </ol>
  </div>

  <div class="grid grid-cols-12 gap-6 mb-[3rem]">
    <div class="xxl:col-span-4 xl:col-span-5 col-span-12">
      <div class="box overflow-hidden">
        <div class="box-body !p-0">
          <div class="user-access-profile-cover p-6 sm:flex items-start gap-4">
            <div class="shrink-0">
              @if ($profilePhoto)
                <span class="avatar avatar-xxl avatar-rounded ring-[3px] ring-white/30 overflow-hidden">
                  <img src="{{ $profilePhoto }}" alt="{{ $displayName }}">
                </span>
              @else
                <span class="avatar avatar-xxl avatar-rounded user-access-avatar-fallback">{{ $initials }}</span>
              @endif
            </div>

            <div class="flex-grow mt-4 sm:mt-0">
              <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
                <div>
                  <h5 class="font-semibold text-white text-[1.125rem] mb-1">{{ $displayName }}</h5>
                  <p class="mb-2 text-white/80">{{ '@' . $user->username }}</p>
                  <div class="flex flex-wrap gap-2">
                    <span class="user-access-pill bg-white/15 text-white">{{ $currentRoleName }}</span>
                    <span class="user-access-pill bg-white/15 text-white">{{ $user->user_type ?: 'User' }}</span>
                    <span class="user-access-pill bg-white/15 text-white">{{ $verificationLabel }}</span>
                  </div>
                </div>

                <div class="text-start sm:text-end text-white/80">
                  <p class="mb-1 text-[0.75rem] uppercase tracking-[0.08em]">Access Control</p>
                  <p class="mb-0 text-[0.875rem]">{{ $directPermissionCount }} direct permissions</p>
                </div>
              </div>

              <p class="text-[0.875rem] text-white/75 mb-4">
                {{ $user->email ?: 'No email recorded for this account.' }}
              </p>

              <div class="grid grid-cols-3 gap-3">
                <div class="user-access-metric">
                  <p class="text-white/70 text-[0.6875rem] uppercase tracking-[0.08em] mb-1">Role</p>
                  <p class="text-white font-semibold mb-0">{{ $currentRoleName }}</p>
                </div>
                <div class="user-access-metric">
                  <p class="text-white/70 text-[0.6875rem] uppercase tracking-[0.08em] mb-1">Pages</p>
                  <p class="text-white font-semibold mb-0">{{ $managedPagesCount }}</p>
                </div>
                <div class="user-access-metric">
                  <p class="text-white/70 text-[0.6875rem] uppercase tracking-[0.08em] mb-1">Status</p>
                  <p class="text-white font-semibold mb-0">{{ $statusLabel }}</p>
                </div>
              </div>
            </div>
          </div>

          <div class="p-6 border-b border-dashed dark:border-defaultborder/10">
            <p class="text-[0.9375rem] mb-3 font-semibold">Account Snapshot</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div class="user-access-mini-card">
                <p class="text-[0.75rem] text-textmuted dark:text-textmuted/80 uppercase tracking-[0.08em] mb-1">Email</p>
                <p class="font-medium mb-0 break-all">{{ $user->email ?: 'Not provided' }}</p>
              </div>
              <div class="user-access-mini-card">
                <p class="text-[0.75rem] text-textmuted dark:text-textmuted/80 uppercase tracking-[0.08em] mb-1">Joined</p>
                <p class="font-medium mb-0">{{ $joinedDate }}</p>
              </div>
              <div class="user-access-mini-card">
                <p class="text-[0.75rem] text-textmuted dark:text-textmuted/80 uppercase tracking-[0.08em] mb-1">Verification</p>
                <p class="font-medium mb-0">{{ $verificationLabel }}</p>
              </div>
              <div class="user-access-mini-card">
                <p class="text-[0.75rem] text-textmuted dark:text-textmuted/80 uppercase tracking-[0.08em] mb-1">Updated</p>
                <p class="font-medium mb-0">{{ $updatedDate }}</p>
              </div>
            </div>
          </div>

          <div class="p-6">
            <p class="text-[0.9375rem] mb-3 font-semibold">Access Notes</p>
            <div class="user-access-help">
              <p class="font-medium mb-2">How changes apply</p>
              <ul class="list-disc ps-5 text-[0.8125rem] text-textmuted dark:text-textmuted/80 space-y-1 mb-0">
                <li>Changing the role resets this user's direct permissions to that role's defaults.</li>
                <li>Use <span class="font-semibold">Restore Defaults</span> to preview the role baseline before saving.</li>
                @if ($moduleScopedAccess)
                  <li>Platform identity, account status, and password recovery remain in Core Platform.</li>
                @else
                  <li>Temporary password generation is available under <span class="font-semibold">Account Settings</span>.</li>
                @endif
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="xxl:col-span-8 xl:col-span-7 col-span-12">
      <div class="box overflow-hidden">
        <div class="box-header sm:flex block !justify-between !items-center gap-4">
          <div>
            <div class="box-title">{{ $moduleScopedAccess ? 'Module Access Workspace' : 'User Access Workspace' }}</div>
            <p class="text-[0.75rem] text-textmuted dark:text-textmuted/80 mb-0">
              {{ $moduleScopedAccess
                  ? 'Manage this user\'s role assignment and direct permissions inside ' . $moduleContextName . '.'
                  : 'Switch between permission management and account recovery tools without leaving the page.' }}
            </p>
          </div>

          <nav aria-label="Tabs" class="md:flex block !justify-start whitespace-nowrap" role="tablist">
            <a class="m-1 block w-full hs-tab-active:bg-primary/10 hs-tab-active:text-primary cursor-pointer text-defaulttextcolor dark:text-defaulttextcolor/70 py-2 px-3 flex-grow text-[0.75rem] font-medium rounded-md hover:text-primary active"
               id="Roles-permission" data-hs-tab="#roles-permission" aria-controls="roles-permission">
              Roles and Permissions
            </a>
            @unless($moduleScopedAccess)
              <a class="m-1 block w-full hs-tab-active:bg-primary/10 hs-tab-active:text-primary cursor-pointer text-defaulttextcolor dark:text-defaulttextcolor/70 py-2 px-3 text-[0.75rem] flex-grow font-medium rounded-md hover:text-primary"
                 id="account-item" data-hs-tab="#account-settings" aria-controls="account-settings">
                Account Settings
              </a>
            @endunless
          </nav>
        </div>

        <div class="box-body">
          <div class="tab-content">
            <div class="tab-pane show active dark:border-defaultborder/10" id="roles-permission" aria-labelledby="Roles-permission">
              <div class="sm:p-2 p-0">
                <div class="user-access-help mb-6">
                  <div class="sm:flex items-start justify-between gap-4">
                    <div>
                      <h6 class="font-semibold mb-1 text-[1rem]">Role Assignment</h6>
                      <p class="text-[0.8125rem] text-textmuted dark:text-textmuted/80 mb-0">
                        {{ $moduleScopedAccess
                            ? 'Assign the baseline role for ' . $moduleContextName . ', then use the grid below to add or remove direct permissions for this module only.'
                            : 'Select the user\'s baseline role first, then use the grid below to add or remove direct permissions.' }}
                      </p>
                    </div>

                    <div class="flex flex-wrap gap-2 mt-3 sm:mt-0">
                      <span class="badge bg-primary/10 text-primary">{{ $managedPagesCount }} permission sections</span>
                      <span class="badge bg-secondary/10 text-secondary">{{ $directPermissionCount }} direct permissions</span>
                    </div>
                  </div>
                </div>

                <div class="grid grid-cols-12 gap-6 mb-6">
                  <div class="xl:col-span-4 col-span-12">
                    <label for="user-name" class="form-label">User Name</label>
                    <input type="text" class="form-control w-full !rounded-md" id="user-name" value="{{ $user->username }}" disabled>
                  </div>

                  <div class="xl:col-span-4 col-span-12">
                    <label for="display-name" class="form-label">Display Name</label>
                    <input type="text" class="form-control w-full !rounded-md" id="display-name" value="{{ $displayName }}" disabled>
                  </div>

                  <div class="xl:col-span-4 col-span-12">
                    <label for="role" class="form-label">Select Role</label>
                    <select
                      name="role"
                      id="role"
                      class="form-control"
                      data-endpoint="{{ $adminRoutes->route('access.users.update', $user) }}"
                    >
                      @foreach ($roles as $role)
                        <option value="{{ $role->name }}" {{ $userRole && $userRole->name == $role->name ? 'selected' : '' }}>
                          {{ $role->name }}
                        </option>
                      @endforeach
                    </select>
                  </div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                  @foreach (($permissionSections ?? []) as $page => $section)
                    <div class="box border border-defaultborder/10 dark:border-defaultborder/10 shadow-none mb-0 h-full">
                      <div class="box-header !justify-between !items-start gap-3">
                        <div>
                          <h6 class="font-semibold text-[1rem] mb-1">{{ $section['title'] }}</h6>
                          <p class="text-[0.75rem] text-textmuted dark:text-textmuted/80 mb-0">
                            Toggle direct access for this module.
                          </p>
                        </div>

                        <span id="feedback-{{ Str::slug($page) }}" class="badge bg-success/10 text-success hidden">Saved</span>
                      </div>

                      <div class="box-body !p-0">
                        <table class="table whitespace-nowrap permission-grid-table mb-0 w-full">
                          <thead class="bg-primary/10">
                            <tr>
                              <th class="p-3 text-start">Permission</th>
                              @foreach ($section['actions'] as $action)
                                <th class="p-3 text-center">{{ $action['label'] }}</th>
                              @endforeach
                            </tr>
                          </thead>
                          <tbody>
                            @foreach ($section['rows'] as $row)
                              <tr>
                                <td class="p-3 text-start">{{ $row['label'] }}</td>
                                @foreach ($section['actions'] as $action)
                                  <td class="text-center">
                                    @if (isset($row['actions'][$action['key']]))
                                      <input type="checkbox"
                                        class="permission-checkbox ti-switch shrink-0 !w-[35px] !h-[21px] before:size-4"
                                        data-page="{{ $page }}"
                                        data-action="{{ $action['key'] }}"
                                        data-permission="{{ $row['key'] }}"
                                        aria-label="Toggle permission access for {{ $row['label'] }} on {{ $section['title'] }}"
                                        {{ isset($userPermissions[$page][$row['key']]) && in_array($action['key'], $userPermissions[$page][$row['key']], true) ? 'checked' : '' }}>
                                    @endif
                                  </td>
                                @endforeach
                              </tr>
                            @endforeach
                          </tbody>
                        </table>
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>
            </div>

            @unless($moduleScopedAccess)
              <div class="tab-pane hidden dark:border-defaultborder/10" id="account-settings" aria-labelledby="account-item">
              <div class="sm:p-2 p-0">
                <div class="grid grid-cols-12 gap-6">
                  <div class="xl:col-span-7 col-span-12">
                    <div class="box border border-defaultborder/10 dark:border-defaultborder/10 shadow-none mb-0">
                      <div class="box-header">
                        <div class="box-title">Password Recovery</div>
                      </div>
                      <div class="box-body">
                        <h6 class="font-semibold mb-2">Generate Temporary Password</h6>
                        <p class="text-sm text-textmuted dark:text-textmuted/80 mb-4">
                          This immediately replaces the user's current password with a one-time temporary code. Share it securely and ask the user to change it right after login.
                        </p>

                        <button
                          type="button"
                          class="ti-btn btn-wave bg-danger text-white"
                          id="resetPasswordButton"
                          data-endpoint="{{ $adminRoutes->route('access.users.password.reset', $user) }}"
                        >
                          Generate Temporary Password
                        </button>
                      </div>
                    </div>
                  </div>

                  <div class="xl:col-span-5 col-span-12">
                    <div class="box border border-defaultborder/10 dark:border-defaultborder/10 shadow-none mb-0">
                      <div class="box-header">
                        <div class="box-title">Account Snapshot</div>
                      </div>
                      <div class="box-body">
                        <ul class="list-group">
                          <li class="list-group-item">
                            <div class="flex items-center justify-between gap-3">
                              <span class="text-textmuted dark:text-textmuted/80">Current Role</span>
                              <span class="font-medium">{{ $currentRoleName }}</span>
                            </div>
                          </li>
                          <li class="list-group-item">
                            <div class="flex items-center justify-between gap-3">
                              <span class="text-textmuted dark:text-textmuted/80">Account Status</span>
                              <span class="badge {{ $statusTone }}">{{ $statusLabel }}</span>
                            </div>
                          </li>
                          <li class="list-group-item">
                            <div class="flex items-center justify-between gap-3">
                              <span class="text-textmuted dark:text-textmuted/80">User Type</span>
                              <span class="font-medium">{{ $user->user_type ?: 'User' }}</span>
                            </div>
                          </li>
                          <li class="list-group-item">
                            <div class="flex items-center justify-between gap-3">
                              <span class="text-textmuted dark:text-textmuted/80">Verification</span>
                              <span class="font-medium">{{ $verificationLabel }}</span>
                            </div>
                          </li>
                          <li class="list-group-item">
                            <div class="flex items-center justify-between gap-3">
                              <span class="text-textmuted dark:text-textmuted/80">Last Update</span>
                              <span class="font-medium">{{ $updatedDate }}</span>
                            </div>
                          </li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              </div>
            @endunless
          </div>
        </div>

        <div class="box-footer">
          <div class="sm:flex items-center justify-between gap-3">
            <p class="text-[0.75rem] text-textmuted dark:text-textmuted/80 mb-3 sm:mb-0">
              {{ $moduleScopedAccess
                  ? 'Restore Defaults previews the selected role baseline for ' . $moduleContextName . ' before you save direct permission changes.'
                  : 'Restore Defaults previews the selected role baseline before you save direct permission changes.' }}
            </p>

            <div class="flex flex-wrap justify-end gap-2">
              <button type="button" class="ti-btn btn-wave ti-btn-light" id="restoreDefaultsButton">
                Restore Defaults
              </button>

              <button
                type="button"
                class="ti-btn btn-wave bg-primary text-white"
                id="savePermissionsButton"
                data-endpoint="{{ $adminRoutes->route('access.users.update', $user) }}"
              >
                Save Changes
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
