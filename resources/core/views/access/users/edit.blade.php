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

    .permission-concern-nav {
      display: flex;
      flex-wrap: wrap;
      gap: 0.75rem;
    } 

    .permission-concern-button {
      border: 1px solid rgba(79, 70, 229, 0.14);
      background: rgba(248, 250, 252, 0.86);
      border-radius: 999px;
      padding: 0.7rem 1rem;
      min-width: 12rem;
      text-align: left;
      transition: all 0.2s ease;
    }

    .permission-concern-button:hover {
      border-color: rgba(79, 70, 229, 0.28);
      background: rgba(79, 70, 229, 0.06);
    }

    .permission-concern-button.is-active {
      border-color: rgba(79, 70, 229, 0.26);
      background: linear-gradient(135deg, rgba(79, 70, 229, 0.12), rgba(14, 165, 233, 0.08));
      box-shadow: 0 10px 24px rgba(79, 70, 229, 0.1);
    }

    .dark .permission-concern-button {
      border-color: rgba(129, 140, 248, 0.16);
      background: rgba(15, 23, 42, 0.55);
    }

    .dark .permission-concern-button.is-active {
      background: linear-gradient(135deg, rgba(79, 70, 229, 0.18), rgba(14, 165, 233, 0.12));
    }

    .permission-section-card {
      border: 1px solid rgba(15, 23, 42, 0.08);
      border-radius: 1rem;
      background: #fff;
      overflow: hidden;
    }

    .dark .permission-section-card {
      border-color: rgba(255, 255, 255, 0.08);
      background: rgba(15, 23, 42, 0.5);
    }

    .permission-section-summary {
      list-style: none;
      cursor: pointer;
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 1rem;
      padding: 1.1rem 1.15rem;
      background: rgba(248, 250, 252, 0.75);
    }

    .permission-section-summary::-webkit-details-marker {
      display: none;
    }

    .dark .permission-section-summary {
      background: rgba(15, 23, 42, 0.42);
    }

    .permission-section-card[open] .permission-section-summary {
      border-bottom: 1px solid rgba(15, 23, 42, 0.08);
    }

    .dark .permission-section-card[open] .permission-section-summary {
      border-bottom-color: rgba(255, 255, 255, 0.08);
    }

    .permission-section-chevron {
      transition: transform 0.2s ease;
    }

    .permission-section-card[open] .permission-section-chevron {
      transform: rotate(180deg);
    }

    .permission-section-body {
      padding: 1rem 1.15rem 1.15rem;
    }

    .permission-toggle-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
      padding: 0.9rem 1rem;
      border: 1px solid rgba(15, 23, 42, 0.08);
      border-radius: 0.95rem;
      background: rgba(248, 250, 252, 0.55);
    }

    .permission-toggle-row + .permission-toggle-row {
      margin-top: 0.75rem;
    }

    .dark .permission-toggle-row {
      border-color: rgba(255, 255, 255, 0.08);
      background: rgba(2, 6, 23, 0.34);
    }

    .permission-toggle-copy {
      min-width: 0;
    }

    .permission-toggle-title {
      font-weight: 600;
      margin-bottom: 0.2rem;
    }

    .permission-toggle-meta {
      font-size: 0.75rem;
      color: rgb(100 116 139);
      margin-bottom: 0;
    }

    .dark .permission-toggle-meta {
      color: rgba(148, 163, 184, 0.92);
    }

    .permission-toggle-control {
      flex-shrink: 0;
    }

    .permission-save-status {
      display: none;
      align-items: center;
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
  $permissionConcernCatalog = [
      'documents' => [
          'label' => 'Documents',
          'description' => 'AIR, RIS, PAR, ICS, PTR, ITR, and WMR workflow permissions.',
          'order' => 10,
      ],
      'inventory' => [
          'label' => 'Inventory',
          'description' => 'Items, inventory records, stocks, and inspection access.',
          'order' => 20,
      ],
      'reference_data' => [
          'label' => 'Reference Data',
          'description' => 'Asset setup, departments, fund sources, and accountable personnel.',
          'order' => 30,
      ],
      'reports' => [
          'label' => 'Reports',
          'description' => 'Printing workspaces and generated report access.',
          'order' => 40,
      ],
      'tasks' => [
          'label' => 'Tasks',
          'description' => 'Module task queue visibility and task actions.',
          'order' => 50,
      ],
      'access' => [
          'label' => 'Access',
          'description' => 'Users, roles, permissions, and audit-related administration.',
          'order' => 60,
      ],
      'other' => [
          'label' => 'Other',
          'description' => 'Additional permissions that do not fit a main concern yet.',
          'order' => 99,
      ],
  ];
  $resolvePermissionConcern = function (string $page, array $section) use ($permissionConcernCatalog): string {
      $title = \Illuminate\Support\Str::lower((string) ($section['title'] ?? $page));
      $pageKey = \Illuminate\Support\Str::lower($page);
      $haystack = $pageKey . ' ' . $title;

      return match (true) {
          str_contains($haystack, 'air'),
          str_contains($haystack, 'ris'),
          str_contains($haystack, 'par'),
          str_contains($haystack, 'ics'),
          str_contains($haystack, 'ptr'),
          str_contains($haystack, 'itr'),
          str_contains($haystack, 'wmr') => 'documents',
          str_contains($haystack, 'inventory'),
          str_contains($haystack, 'items'),
          str_contains($haystack, 'stock'),
          str_contains($haystack, 'inspection') => 'inventory',
          str_contains($haystack, 'asset type'),
          str_contains($haystack, 'asset categor'),
          str_contains($haystack, 'department'),
          str_contains($haystack, 'fund cluster'),
          str_contains($haystack, 'fund source'),
          str_contains($haystack, 'accountable') => 'reference_data',
          str_contains($haystack, 'report'),
          str_contains($haystack, 'rpci'),
          str_contains($haystack, 'rpcppe'),
          str_contains($haystack, 'rpcsp'),
          str_contains($haystack, 'regspi'),
          str_contains($haystack, 'rspi'),
          str_contains($haystack, 'rrsp'),
          str_contains($haystack, 'ssmi'),
          str_contains($haystack, 'sticker'),
          str_contains($haystack, 'property card'),
          str_contains($haystack, 'stock card') => 'reports',
          str_contains($haystack, 'task') => 'tasks',
          str_contains($haystack, 'access'),
          str_contains($haystack, 'user'),
          str_contains($haystack, 'role'),
          str_contains($haystack, 'permission'),
          str_contains($haystack, 'audit') => 'access',
          default => 'other',
      };
  };
  $permissionConcernGroups = [];

  foreach (($permissionSections ?? []) as $page => $section) {
      $concernKey = $resolvePermissionConcern($page, $section);
      $concernMeta = $permissionConcernCatalog[$concernKey] ?? $permissionConcernCatalog['other'];
      $entries = [];
      $assignedCount = 0;

      foreach (($section['rows'] ?? []) as $row) {
          foreach (($section['actions'] ?? []) as $action) {
              if (! isset($row['actions'][$action['key']])) {
                  continue;
              }

              $isChecked = isset($userPermissions[$page][$row['key']])
                  && in_array($action['key'], $userPermissions[$page][$row['key']], true);

              $entries[] = [
                  'row_key' => $row['key'],
                  'row_label' => $row['label'],
                  'action_key' => $action['key'],
                  'action_label' => $action['label'],
                  'checked' => $isChecked,
              ];

              if ($isChecked) {
                  $assignedCount++;
              }
          }
      }

      $permissionConcernGroups[$concernKey] ??= [
          'key' => $concernKey,
          'label' => $concernMeta['label'],
          'description' => $concernMeta['description'],
          'order' => $concernMeta['order'],
          'entry_count' => 0,
          'assigned_count' => 0,
          'sections' => [],
      ];

      $permissionConcernGroups[$concernKey]['entry_count'] += count($entries);
      $permissionConcernGroups[$concernKey]['assigned_count'] += $assignedCount;
      $permissionConcernGroups[$concernKey]['sections'][] = [
          'page' => $page,
          'slug' => \Illuminate\Support\Str::slug($page),
          'title' => $section['title'],
          'entry_count' => count($entries),
          'assigned_count' => $assignedCount,
          'entries' => $entries,
      ];
  }

  uasort($permissionConcernGroups, fn (array $left, array $right): int => $left['order'] <=> $right['order']);
  $firstPermissionConcernKey = array_key_first($permissionConcernGroups);
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
                  ? 'Separate role assignment from direct permission tuning inside ' . $moduleContextName . '.'
                  : 'Switch between role assignment, permission management, and account recovery without leaving the page.' }}
            </p>
          </div>

          <nav aria-label="Tabs" class="md:flex block !justify-start whitespace-nowrap" role="tablist">
            <a class="m-1 block w-full hs-tab-active:bg-primary/10 hs-tab-active:text-primary cursor-pointer text-defaulttextcolor dark:text-defaulttextcolor/70 py-2 px-3 flex-grow text-[0.75rem] font-medium rounded-md hover:text-primary active"
               id="roles-item" data-hs-tab="#roles-panel" aria-controls="roles-panel">
              Roles
            </a>
            <a class="m-1 block w-full hs-tab-active:bg-primary/10 hs-tab-active:text-primary cursor-pointer text-defaulttextcolor dark:text-defaulttextcolor/70 py-2 px-3 flex-grow text-[0.75rem] font-medium rounded-md hover:text-primary"
               id="permissions-item" data-hs-tab="#permissions-panel" aria-controls="permissions-panel">
              Permissions
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
            <div class="tab-pane show active dark:border-defaultborder/10" id="roles-panel" aria-labelledby="roles-item">
              <div class="sm:p-2 p-0">
                <div class="user-access-help mb-6">
                  <div class="sm:flex items-start justify-between gap-4">
                    <div>
                      <h6 class="font-semibold mb-1 text-[1rem]">Role Assignment</h6>
                      <p class="text-[0.8125rem] text-textmuted dark:text-textmuted/80 mb-0">
                        {{ $moduleScopedAccess
                            ? 'Assign the baseline role for ' . $moduleContextName . '. Direct permission overrides are handled in the separate Permissions tab.'
                            : 'Select the user\'s baseline role here. Direct permission overrides are handled in the separate Permissions tab.' }}
                      </p>
                    </div>

                    <div class="flex flex-wrap gap-2 mt-3 sm:mt-0">
                      <span class="badge bg-primary/10 text-primary">{{ $currentRoleName }}</span>
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
              </div>
            </div>

            <div class="tab-pane hidden dark:border-defaultborder/10" id="permissions-panel" aria-labelledby="permissions-item">
              <div class="sm:p-2 p-0">
                <div class="user-access-help mb-6">
                  <div class="sm:flex items-start justify-between gap-4">
                    <div>
                      <h6 class="font-semibold mb-1 text-[1rem]">Direct Permissions</h6>
                      <p class="text-[0.8125rem] text-textmuted dark:text-textmuted/80 mb-0">
                        {{ $moduleScopedAccess
                            ? 'Fine-tune direct permissions for ' . $moduleContextName . '. These overrides sit on top of the selected role.'
                            : 'Fine-tune direct permissions here. These overrides sit on top of the selected role.' }}
                      </p>
                    </div>

                    <div class="flex flex-wrap gap-2 mt-3 sm:mt-0">
                      <span class="badge bg-primary/10 text-primary">{{ $managedPagesCount }} permission sections</span>
                      <span class="badge bg-secondary/10 text-secondary">{{ $directPermissionCount }} direct permissions</span>
                    </div>
                  </div>
                </div>

                @if (count($permissionConcernGroups) > 1)
                  <div class="mb-6">
                    <div class="permission-concern-nav" role="tablist" aria-label="Permission concern tabs">
                      @foreach ($permissionConcernGroups as $concernKey => $concern)
                        <button
                          type="button"
                          class="permission-concern-button {{ $concernKey === $firstPermissionConcernKey ? 'is-active' : '' }}"
                          data-permission-concern-tab="{{ $concernKey }}"
                          aria-pressed="{{ $concernKey === $firstPermissionConcernKey ? 'true' : 'false' }}"
                        >
                          <div class="flex items-center justify-between gap-3 mb-1">
                            <span class="font-semibold text-[0.875rem]">{{ $concern['label'] }}</span>
                            <span class="badge bg-primary/10 text-primary">{{ count($concern['sections']) }}</span>
                          </div>
                          <p class="text-[0.75rem] text-textmuted dark:text-textmuted/80 mb-0">
                            {{ $concern['description'] }}
                          </p>
                        </button>
                      @endforeach
                    </div>
                  </div>
                @endif

                <div class="space-y-6">
                  @foreach ($permissionConcernGroups as $concernKey => $concern)
                    <section
                      data-permission-concern-panel="{{ $concernKey }}"
                      style="{{ $concernKey === $firstPermissionConcernKey ? '' : 'display: none;' }}"
                    >
                      <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
                        <div>
                          <h6 class="font-semibold text-[1rem] mb-1">{{ $concern['label'] }}</h6>
                          <p class="text-[0.75rem] text-textmuted dark:text-textmuted/80 mb-0">
                            {{ $concern['description'] }}
                          </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                          <span class="badge bg-primary/10 text-primary">{{ count($concern['sections']) }} sections</span>
                          <span class="badge bg-secondary/10 text-secondary">{{ $concern['assigned_count'] }} direct permissions</span>
                        </div>
                      </div>

                      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 items-start">
                        @foreach ($concern['sections'] as $section)
                          <details
                            class="permission-section-card self-start"
                            data-permission-accordion="{{ $concernKey }}"
                            {{ $loop->first ? 'open' : '' }}
                          >
                            <summary class="permission-section-summary">
                              <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                  <h6 class="font-semibold text-[0.975rem] mb-0">{{ $section['title'] }}</h6>
                                  <span class="badge bg-primary/10 text-primary">{{ $section['entry_count'] }} toggles</span>
                                  <span class="badge bg-secondary/10 text-secondary">{{ $section['assigned_count'] }} enabled</span>
                                </div>
                                <p class="text-[0.75rem] text-textmuted dark:text-textmuted/80 mb-0">
                                  One permission per row for easier review and assignment.
                                </p>
                              </div>

                              <i class="ti ti-chevron-down text-textmuted permission-section-chevron"></i>
                            </summary>

                            <div class="permission-section-body">
                              @foreach ($section['entries'] as $entry)
                                <label class="permission-toggle-row">
                                  <div class="permission-toggle-copy">
                                    <div class="flex flex-wrap items-center gap-2">
                                      <p class="permission-toggle-title">{{ $entry['row_label'] }}</p>
                                      <span class="badge bg-primary/10 text-primary">{{ $entry['action_label'] }}</span>
                                    </div>
                                    <p class="permission-toggle-meta mb-0">
                                      {{ $section['title'] }} direct override
                                    </p>
                                  </div>

                                  <input
                                    type="checkbox"
                                    class="permission-checkbox permission-toggle-control ti-switch shrink-0 !w-[35px] !h-[21px] before:size-4"
                                    data-page="{{ $section['page'] }}"
                                    data-action="{{ $entry['action_key'] }}"
                                    data-permission="{{ $entry['row_key'] }}"
                                    aria-label="Toggle {{ $entry['action_label'] }} for {{ $entry['row_label'] }} on {{ $section['title'] }}"
                                    {{ $entry['checked'] ? 'checked' : '' }}
                                  >
                                </label>
                              @endforeach
                            </div>
                          </details>
                        @endforeach
                      </div>
                    </section>
                  @endforeach
                </div>

                <div class="mt-6 pt-6 border-t border-dashed dark:border-defaultborder/10">
                  <div class="sm:flex items-center justify-between gap-3">
                    <p class="text-[0.75rem] text-textmuted dark:text-textmuted/80 mb-3 sm:mb-0">
                      {{ $moduleScopedAccess
                          ? 'Restore Defaults previews the selected role baseline for ' . $moduleContextName . ' before you save direct permission changes.'
                          : 'Restore Defaults previews the selected role baseline before you save direct permission changes.' }}
                    </p>

                    <div class="flex flex-wrap justify-end gap-2">
                      <span
                        id="permissionsSavedBadge"
                        class="badge bg-success/10 text-success permission-save-status"
                        style="display: none;"
                      >
                        Saved just now
                      </span>

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
      </div>
    </div>
  </div>
</div>
@endsection
