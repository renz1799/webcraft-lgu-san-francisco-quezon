<footer class="footer mt-auto xl:ps-[15rem] font-normal font-inter bg-white text-defaultsize leading-normal text-[0.813rem] shadow-[0_0_0.4rem_rgba(0,0,0,0.1)] dark:bg-bodybg py-4 text-center">
  <div class="container">
    <span class="text-gray dark:text-defaulttextcolor/50">
      © {{ now()->year }}
      <a href="{{ config('app.url') }}" class="text-defaulttextcolor font-semibold dark:text-defaulttextcolor">
        {{ config('app.name', 'Webcraft') }}
      </a>.
      All rights reserved
      <span class="hidden md:inline">— Made with <i class="bi bi-heart-fill text-danger align-middle"></i> using Laravel.</span>
    </span>
  </div>
</footer>
