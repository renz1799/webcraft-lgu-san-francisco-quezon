@if(auth()->check() && auth()->user()->hasRole('Administrator'))
  <div id="registerUserModal" class="hs-overlay hidden ti-modal [--overlay-backdrop:static]">
    <div class="hs-overlay-open:mt-7 ti-modal-box mt-0 ease-out">
      <div class="ti-modal-content">
        <div class="ti-modal-header">
          <h6 class="modal-title text-[1rem] font-semibold">User Registration</h6>
          <button type="button" class="hs-dropdown-toggle !text-[1rem] !font-semibold !text-defaulttextcolor" data-hs-overlay="#registerUserModal">
            <span class="sr-only">Close</span>
            <i class="ri-close-line"></i>
          </button>
        </div>

        <div class="ti-modal-body px-4">
          <form id="registerUserForm" action="{{ route('register') }}" method="POST" class="space-y-4">
            @csrf

            <div>
              <label for="register-username" class="form-label">Username</label>
              <input type="text" id="register-username" name="username" class="form-control w-full !rounded-md" placeholder="Username" required>
            </div>

            <div>
              <label for="register-email" class="form-label">Email</label>
              <input type="email" id="register-email" name="email" class="form-control w-full !rounded-md" placeholder="Email" required>
            </div>

            <div>
              <label for="register-role" class="form-label">Role</label>
              <select id="register-role" name="role" class="form-control w-full !rounded-md" required>
                <option value="">Select role</option>
              </select>
            </div>

            <div>
              <label for="register-password" class="form-label">Password</label>
              <input type="password" id="register-password" name="password" class="form-control w-full !rounded-md" placeholder="Password" required>
            </div>

            <div>
              <label for="register-password-confirmation" class="form-label">Confirm Password</label>
              <input type="password" id="register-password-confirmation" name="password_confirmation" class="form-control w-full !rounded-md" placeholder="Confirm password" required>
            </div>
          </form>
        </div>

        <div class="ti-modal-footer">
          <button type="button" class="hs-dropdown-toggle ti-btn btn-wave ti-btn-secondary-full align-middle" data-hs-overlay="#registerUserModal">
            Close
          </button>
          <button type="button" id="registerUserSubmit" class="ti-btn btn-wave bg-primary text-white !font-medium">
            Create Account
          </button>
        </div>
      </div>
    </div>
  </div>

  <script>
    window.__registerUserModal = {
      optionsUrl: @json(route('register.options')),
      submitUrl: @json(route('register')),
    };
  </script>
@endif
