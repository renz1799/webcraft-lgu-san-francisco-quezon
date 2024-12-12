@extends('layouts.master')

@section('styles')
 
        <!-- Choices Css -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/choices.js/public/assets/styles/choices.min.css')}}">

        <!-- Tom Select Css -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/tom-select/css/tom-select.default.min.css')}}">

        <link rel="stylesheet" href="{{asset('build/assets/libs/quill/quill.snow.css')}}">
        <link rel="stylesheet" href="{{asset('build/assets/libs/quill/quill.bubble.css')}}">

        <!-- Filepond CSS -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/filepond/filepond.min.css')}}">
        <link rel="stylesheet" href="{{asset('build/assets/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.css')}}">
        <link rel="stylesheet" href="{{asset('build/assets/libs/filepond-plugin-image-edit/filepond-plugin-image-edit.min.css')}}">

        <!-- Date & Time Picker CSS -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/flatpickr/flatpickr.min.css')}}">
        
@endsection

@section('content')

                        <!-- Page Header -->
                        <div class="block justify-between page-header md:flex">
                            <div>
                                <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold"> Projects Create </h3>
                            </div>
                            <ol class="flex items-center whitespace-nowrap min-w-0">
                                <li class="text-[0.813rem] ps-[0.5rem]">
                                  <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="javascript:void(0);">
                                    Projects
                                    <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
                                  </a>
                                </li>
                                <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50 " aria-current="page">
                                 Projects Create
                                </li>
                            </ol>
                        </div>
                        <!-- Page Header Close -->

                        <!-- Start::row-1 -->
                        <div class="grid grid-cols-12 gap-6">
                            <div class="xl:col-span-12 col-span-12">
                                <div class="box custom-box">
                                    <div class="box-header">
                                        <div class="box-title">
                                            Create Project
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <div class="grid grid-cols-12 gap-4">
                                            <div class="xl:col-span-4 col-span-12">
                                                <label for="input-label" class="form-label">Project Name :</label>
                                                <input type="text" class="form-control" id="input-label" placeholder="Enter Project Name">
                                            </div>
                                            <div class="xl:col-span-4 col-span-12">
                                                <label for="input-label1" class="form-label">Project Manager :</label>
                                                <input type="text" class="form-control" id="input-label1" placeholder="Project Manager Name">
                                            </div>
                                            <div class="xl:col-span-4 col-span-12">
                                                <label for="input-label1" class="form-label">Client / Stakeholder :</label>
                                                <input type="text" class="form-control" placeholder="Enter Client Name">
                                            </div>
                                            <div class="xl:col-span-12 col-span-12 mb-4">
                                                <label class="form-label">Project Description :</label>
                                                <div  id="project-descriptioin-editor">
                                                    <p>lorem Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 2000 years old. Richard McClintock, a Latin professor at Hampden-Sydney College in Virginia, looked up one of the more obscure Latin words, consectetur, from a Lorem Ipsum passage, and going through the cites of the word in classical literature, discovered the undoubtable source. Lorem Ipsum comes from sections 1.10.32 and 1.10.33.</p>
                                                    <p><br></p>
                                                    <ol>
                                                        <li class="ql-size-normal">Conducting a comprehensive analysis of the existing website design.</li>
                                                        <li class="">Collaborating with the UI/UX team to develop wireframes and mockups.</li>
                                                        <li class="">Iteratively refining the design based on feedback..</li>
                                                        <li class="">Implementing the finalized design changes using HTML, CSS, and JavaScript.</li>
                                                        <li class="">Testing the website across different devices and browsers.</li>
                                                    </ol>
                                                </div>
                                            </div>

                                            <div class="xl:col-span-6 col-span-12">
                                                <label class="form-label">Start Date :</label>
                                                <div class="form-group">
                                                    <div class="input-group">
                                                        <div class="input-group-text text-muted !border-e-0"> <i class="ri-calendar-line"></i> </div>
                                                        <input type="text" class="form-control" id="startDate" placeholder="Choose date and time">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="xl:col-span-6 col-span-12">
                                                <label class="form-label">End Date :</label>
                                                <div class="form-group">
                                                    <div class="input-group">
                                                        <div class="input-group-text text-muted !border-e-0"> <i class="ri-calendar-line"></i> </div>
                                                        <input type="text" class="form-control" id="endDate" placeholder="Choose date and time">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="xl:col-span-6 col-span-12">
                                                <label class="form-label">Status :</label>
                                                <select class="form-control" data-trigger name="choices-single-default" id="choices-single-default">
                                                    <option value="Inprogress">Inprogress</option>
                                                    <option value="On-hold">On-hold</option>
                                                    <option value="Completed">Completed</option>
                                                </select>
                                            </div>
                                            <div class="xl:col-span-6 col-span-12">
                                                <label class="form-label">Priority :</label>
                                                <select class="form-control" data-trigger name="choices-single-default2" id="choices-single-default2">
                                                    <option value="High">High</option>
                                                    <option value="Medium">Medium</option>
                                                    <option value="Low">Low</option>
                                                </select>
                                            </div>
                                            <div class="xl:col-span-6 col-span-12">
                                                <label class="form-label">Assigned To</label>
                                                <select class="form-control" name="assigned-team-members" id="assigned-team-members" multiple>
                                                    <option value="Choice 1" selected>Angelina May</option>
                                                    <option value="Choice 2">Kiara advain</option>
                                                    <option value="Choice 3">Hercules Jhon</option>
                                                    <option value="Choice 4">Mayor Kim</option>
                                                    <option value="Choice 4" selected>Alexa Biss</option>
                                                    <option value="Choice 4">Karley Dia</option>
                                                    <option value="Choice 4">Kim Jong</option>
                                                    <option value="Choice 4">Darren Sami</option>
                                                    <option value="Choice 4">Elizabeth</option>
                                                    <option value="Choice 4">Bear Gills</option>
                                                    <option value="Choice 4" selected>Alex Carey</option>
                                                </select>
                                            </div>
                                            <div class="xl:col-span-6 col-span-12">
                                                <label class="form-label">Tags</label>
                                                <input class="form-control" id="choices-text-unique-values" type="text" value="Marketing, Sales, Development, Design, Research" placeholder="This is a placeholder">
                                            </div>
                                            <div class="xl:col-span-12 col-span-12">
                                                <label class="form-label">Attachments</label>
                                                <input type="file" class="multiple-filepond" name="filepond" id="file-input" multiple data-allow-reorder="true" data-max-file-size="3MB" data-max-files="6">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="box-footer">
                                        <button type="button" class="ti-btn ti-btn-primary btn-wave ms-auto float-right">Create Project</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--End::row-1 -->

@endsection

@section('scripts')
  
          <!-- Choices JS -->
          <script src="{{asset('build/assets/libs/choices.js/public/assets/scripts/choices.min.js')}}"></script>

          <!-- Date & Time Picker JS -->
          <script src="{{asset('build/assets/libs/flatpickr/flatpickr.min.js')}}"></script>

          <!-- Quill Editor JS -->
          <script src="{{asset('build/assets/libs/quill/quill.min.js')}}"></script>

          <!-- Filepond JS -->
          <script src="{{asset('build/assets/libs/filepond/filepond.min.js')}}"></script>
          <script src="{{asset('build/assets/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.js')}}"></script>
          <script src="{{asset('build/assets/libs/filepond-plugin-image-exif-orientation/filepond-plugin-image-exif-orientation.min.js')}}"></script>
          <script src="{{asset('build/assets/libs/filepond-plugin-file-validate-size/filepond-plugin-file-validate-size.min.js')}}"></script>
          <script src="{{asset('build/assets/libs/filepond-plugin-file-encode/filepond-plugin-file-encode.min.js')}}"></script>
          <script src="{{asset('build/assets/libs/filepond-plugin-image-edit/filepond-plugin-image-edit.min.js')}}"></script>
          <script src="{{asset('build/assets/libs/filepond-plugin-file-validate-type/filepond-plugin-file-validate-type.min.js')}}"></script>
          <script src="{{asset('build/assets/libs/filepond-plugin-file-validate-type/filepond-plugin-file-validate-type.min.js')}}"></script>
          <script src="{{asset('build/assets/libs/filepond-plugin-image-crop/filepond-plugin-image-crop.min.js')}}"></script>
          <script src="{{asset('build/assets/libs/filepond-plugin-image-resize/filepond-plugin-image-resize.min.js')}}"></script>
          <script src="{{asset('build/assets/libs/filepond-plugin-image-transform/filepond-plugin-image-transform.min.js')}}"></script>

          <!-- Create Project JS -->
          @vite('resources/assets/js/create-project.js')

          
@endsection