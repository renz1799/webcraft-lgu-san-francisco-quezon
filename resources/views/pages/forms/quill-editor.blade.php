@extends('layouts.master')

@section('styles')
  
        <!-- Quill CSS -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/quill/quill.snow.css')}}">
        <link rel="stylesheet" href="{{asset('build/assets/libs/quill/quill.bubble.css')}}">
      
@endsection

@section('content')
 
                        <!-- Page Header -->
                        <div class="block justify-between page-header md:flex">
                            <div>
                                <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold"> Quill Editor</h3>
                            </div>
                            <ol class="flex items-center whitespace-nowrap min-w-0">
                                <li class="text-[0.813rem] ps-[0.5rem]">
                                  <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="javascript:void(0);">
                                    Form Editors
                                    <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
                                  </a>
                                </li>
                                <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50 " aria-current="page">
                                    Quill Editor
                                </li>
                            </ol>
                        </div>
                        <!-- Page Header Close -->

                        <!-- Start:: row-1 -->
                        <div class="grid grid-cols-12 gp-6">
                            <div class="xl:col-span-12 col-span-12">
                                <div class="box">
                                    <div class="box-header">
                                        <div class="box-title">
                                            Quill Snow Editor
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <div id="editor">
                                            <h4><b class="ql-size-large">Quill Snow</b> is a free, open source <a href="https://github.com/quilljs/quill/" target="_blank">Quill Editor</a> built for the modern web. With its <a href="https://quilljs.com/docs/modules/" target="_blank">modular architecture</a> and expressive API, it is completely customizable to fit any need.</h4>
                                            <p><br></p>
                                            <ol>
                                                <li class="ql-size-normal">Write text select and edit with multiple options.</li>
                                                <li class="">This is quill snow editor.</li>
                                                <li class="">Easy to customize and flexible.</li>
                                            </ol>
                                            <p><br></p>
                                            <h4>Quill officially supports a standard toolbar theme <a href="https://github.com/quilljs/quill/" target="_blank">"Snow"</a> and a floating tooltip theme <a href="https://github.com/quilljs/quill/" target="_blank">"Bubble"</a></h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End:: row-1 -->

                        <!-- Start:: row-2 -->
                        <div class="grid grid-cols-12 gp-6">
                            <div class="xl:col-span-12 col-span-12">
                                <div class="box">
                                    <div class="box-header">
                                        <div class="box-title">
                                            Quill Bubble Editor
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <div id="editor1">
                                            <h4><b class="ql-size-large">Quill Bubble</b> is a free, open source <a href="https://github.com/quilljs/quill/" target="_blank">Quill Editor</a> built for the modern web. With its <a href="https://quilljs.com/docs/modules/" target="_blank">modular architecture</a> and expressive API, it is completely customizable to fit any need.</h4>
                                            <p><br></p>
                                            <ol>
                                                <li class="ql-size-normal">Write text select and edit with multiple options.</li>
                                                <li class="">This is quill bubble editor.</li>
                                                <li class="">Easy to customize and flexible.</li>
                                            </ol>
                                            <p><br></p>
                                            <h4>Quill officially supports a standard toolbar theme <a href="https://github.com/quilljs/quill/" target="_blank">"Snow"</a> and a floating tooltip theme <a href="https://github.com/quilljs/quill/" target="_blank">"Bubble"</a></h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End:: row-2 -->

@endsection

@section('scripts')

        <!-- Quill Editor JS -->
        <script src="{{asset('build/assets/libs/quill/quill.min.js')}}"></script>

        <!-- Internal Quill JS -->
        @vite('resources/assets/js/quill-editor.js')

        
@endsection