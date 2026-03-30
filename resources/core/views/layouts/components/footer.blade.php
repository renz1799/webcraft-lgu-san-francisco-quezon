<footer class="footer mt-auto xl:ps-[15rem] font-normal font-inter bg-white text-defaultsize leading-normal text-[0.813rem] shadow-[0_0_0.4rem_rgba(0,0,0,0.1)] dark:bg-bodybg py-4 text-center">
  <div class="container">
    @php($footerBrand = 'Webcraft Web Development Services')
    <span class="text-gray dark:text-defaulttextcolor/50">
      &copy; {{ now()->year }}
      <span class="text-defaulttextcolor font-semibold dark:text-defaulttextcolor">
        {{ $footerBrand }}
      </span>.
      All rights reserved.
      <span class="hidden md:inline">Platform design and development by {{ $footerBrand }}.</span>
    </span>
  </div>
</footer>
