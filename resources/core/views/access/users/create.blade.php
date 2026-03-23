@extends('layouts.master')

@php
  $adminRoutes = $adminRoutes ?? app(\App\Core\Support\AdminRouteResolver::class);
  $currentContext = app(\App\Core\Support\CurrentContext::class);
  $currentModule = $currentModule ?? $currentContext->module();
  $onboardingMode = $onboardingMode ?? ($adminRoutes->isModuleScoped() ? 'module' : 'core');
  $isModuleOnboarding = $onboardingMode === 'module';
  $moduleId = (string) ($moduleId ?? $currentModule?->id ?? '');
  $selectedModuleId = (string) old('module_id', $selectedModuleId ?? $moduleId);
  $departmentId = (string) ($departmentId ?? '');
  $selectedDepartmentId = (string) old('department_id', $selectedDepartmentId ?? $departmentId);
  $rolesByModule = $rolesByModule ?? [];
  $departmentsByModule = $departmentsByModule ?? [];
  $moduleHints = $moduleHints ?? [];
  $viewErrors = $errors ?? new \Illuminate\Support\ViewErrorBag();
  $moduleContextName = trim((string) (
      $isModuleOnboarding
          ? ($currentModule?->name ?? $adminRoutes->scopedModuleCode() ?? 'Module')
          : (data_get($moduleHints, $selectedModuleId . '.module_name') ?? 'Core Platform')
  )) ?: 'Module';
  $pageTitle = $isModuleOnboarding ? 'Onboard Staff to ' . $moduleContextName : 'Platform User Onboarding';
  $pageDescription = $isModuleOnboarding
      ? 'Add staff to ' . $moduleContextName . ' without duplicating platform identities. Department and module are derived from the active module context.'
      : 'Create or attach a platform identity, then assign module membership from Core Platform with guided module and department defaults.';
  $submitLabel = $isModuleOnboarding ? 'Add Staff to ' . $moduleContextName : 'Create Platform User';
  $onboardingConfig = [
      'departmentsByModule' => $departmentsByModule,
      'rolesByModule' => $rolesByModule,
      'moduleHints' => $moduleHints,
      'selectedModuleId' => $selectedModuleId,
      'selectedDepartmentId' => $selectedDepartmentId,
      'selectedRole' => old('role'),
  ];
@endphp

@section('content')
<div
  id="user-onboarding-page"
  data-onboarding-mode="{{ $onboardingMode }}"
  data-success-message="{{ session('success') }}"
  data-info-message="{{ session('info') }}"
  data-error-message="{{ $viewErrors->any() ? $viewErrors->first() : '' }}"
>
  <div class="block justify-between page-header md:flex">
    <div>
      <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white text-[1.125rem] font-semibold">
        {{ $pageTitle }}
      </h3>
      <p class="text-textmuted dark:text-textmuted/80 mb-0">
        {{ $pageDescription }}
      </p>
    </div>
  </div>

  @if ($viewErrors->any())
    <div class="alert alert-danger mb-4">
      <ul class="list-disc ms-6">
        @foreach ($viewErrors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="box">
    <div class="box-header">
      <div>
        <h5 class="box-title">{{ $isModuleOnboarding ? 'Module-Assisted Onboarding' : 'Core Platform Onboarding' }}</h5>
        <p class="text-xs text-[#8c9097] mt-2 mb-0">
          @if ($isModuleOnboarding)
            Core remains the owner of platform identities. This form only assigns staff to {{ $moduleContextName }} using the module's default department and sends an account setup email.
          @else
            Core can create or reuse a platform identity, then assign one module membership with a guided department suggestion based on the selected module.
          @endif
        </p>
      </div>
    </div>

    <div class="box-body">
      <form id="user-onboarding-form" method="POST" action="{{ $adminRoutes->route('access.users.store') }}" class="grid grid-cols-12 gap-4">
        @csrf

        @if ($isModuleOnboarding)
          <input type="hidden" name="module_id" value="{{ $moduleId }}">
          <input type="hidden" name="department_id" value="{{ $selectedDepartmentId }}">

          <div class="xl:col-span-6 col-span-12">
            <label class="form-label">Module</label>
            <div class="rounded-md border border-defaultborder bg-light px-4 py-3 text-sm text-defaulttextcolor dark:text-white">
              {{ $moduleContextName }}
            </div>
          </div>

          <div class="xl:col-span-6 col-span-12">
            <label class="form-label">Department</label>
            <div class="rounded-md border border-defaultborder bg-light px-4 py-3 text-sm text-defaulttextcolor dark:text-white">
              {{ $departmentLabel }} <span class="text-[#8c9097]">(derived from module default)</span>
            </div>
            <div class="text-xs text-[#8c9097] mt-1">
              This user will be assigned to the {{ $departmentLabel }} by default.
            </div>
          </div>
        @else
          <div class="xl:col-span-6 col-span-12">
            <label for="module_id" class="form-label">Module</label>
            <select id="module_id" name="module_id" class="form-control w-full !rounded-md" required>
              <option value="">Select module</option>
              @foreach ($modules as $module)
                <option value="{{ $module->id }}" @selected($selectedModuleId === (string) $module->id)>
                  {{ $module->name }}{{ $module->resolvedType() === 'platform' ? ' (Platform)' : '' }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="xl:col-span-6 col-span-12">
            <label for="department_id" class="form-label">Department</label>
            <select id="department_id" name="department_id" class="form-control w-full !rounded-md" required data-selected="{{ $selectedDepartmentId }}">
              <option value="">Select department</option>
            </select>
            <div id="core-onboarding-department-hint" class="text-xs text-[#8c9097] mt-1">
              Suggested department based on selected module.
            </div>
          </div>

          <div class="col-span-12">
            <div class="rounded-md border border-defaultborder p-4 bg-light">
              <h6 class="font-semibold mb-2">Module Hint</h6>
              <p id="core-onboarding-module-hint" class="text-sm text-[#4b5563] mb-0">
                Select a module to view its default department.
              </p>
            </div>
          </div>
        @endif

        <div class="xl:col-span-4 col-span-12">
          <label for="first_name" class="form-label">First Name</label>
          <input type="text" id="first_name" name="first_name" class="form-control w-full !rounded-md" value="{{ old('first_name') }}" required>
        </div>

        <div class="xl:col-span-4 col-span-12">
          <label for="middle_name" class="form-label">Middle Name</label>
          <input type="text" id="middle_name" name="middle_name" class="form-control w-full !rounded-md" value="{{ old('middle_name') }}">
        </div>

        <div class="xl:col-span-3 col-span-12">
          <label for="last_name" class="form-label">Last Name</label>
          <input type="text" id="last_name" name="last_name" class="form-control w-full !rounded-md" value="{{ old('last_name') }}" required>
        </div>

        <div class="xl:col-span-1 col-span-12">
          <label for="name_extension" class="form-label">Ext.</label>
          <input type="text" id="name_extension" name="name_extension" class="form-control w-full !rounded-md" value="{{ old('name_extension') }}" placeholder="Jr.">
        </div>

        <div class="xl:col-span-6 col-span-12">
          <label for="email" class="form-label">Email</label>
          <input type="email" id="email" name="email" class="form-control w-full !rounded-md" value="{{ old('email') }}" required>
          <div class="text-xs text-[#8c9097] mt-1">
            Email is the platform identity lookup. Existing platform users will be reused instead of duplicated.
          </div>
        </div>

        <div class="xl:col-span-3 col-span-12">
          <label for="role" class="form-label">{{ $isModuleOnboarding ? 'Module Role' : 'Role' }}</label>
          <select
            id="role"
            name="role"
            class="form-control w-full !rounded-md"
            required
            @unless($isModuleOnboarding) data-selected="{{ old('role') }}" @endunless
          >
            @if ($isModuleOnboarding)
              <option value="">Select role</option>
              @foreach ($roles as $role)
                <option value="{{ $role->name }}" @selected(old('role') === $role->name)>{{ $role->name }}</option>
              @endforeach
            @else
              <option value="">Select role</option>
            @endif
          </select>
        </div>

        <div class="xl:col-span-3 col-span-12">
          <label for="is_active" class="form-label">Module Access Status</label>
          <select id="is_active" name="is_active" class="form-control w-full !rounded-md" required>
            <option value="1" @selected(old('is_active', '1') === '1')>Active</option>
            <option value="0" @selected(old('is_active') === '0')>Inactive</option>
          </select>
          <div class="text-xs text-[#8c9097] mt-1">
            Inactive staff remain assigned, but they cannot actively use the selected module until re-enabled.
          </div>
        </div>

        <div class="col-span-12">
          <div class="rounded-md border border-defaultborder p-4 bg-light">
            <h6 class="font-semibold mb-2">What happens next</h6>
            <ul class="list-disc ms-6 text-sm text-[#4b5563] space-y-1">
              <li>If the email already belongs to a live platform account, the system will reuse it.</li>
              <li>If the email is new, Core creates the platform identity in a controlled way.</li>
              <li>The selected module membership is created or updated, the role is applied, and an account setup email is sent.</li>
            </ul>
          </div>
        </div>

        <div class="col-span-12 flex items-center justify-end gap-2 mt-2">
          <a href="{{ $adminRoutes->route('access.users.index') }}" class="ti-btn ti-btn-light">Cancel</a>
          <button type="submit" id="user-onboarding-submit" class="ti-btn btn-wave bg-primary text-white !font-medium">
            {{ $submitLabel }}
          </button>
        </div>
      </form>
    </div>
  </div>

  <script type="application/json" id="userOnboardingConfig">{!! \Illuminate\Support\Js::encode($onboardingConfig) !!}</script>
</div>
@endsection
