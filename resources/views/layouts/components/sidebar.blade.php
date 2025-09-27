
            <aside class="app-sidebar" id="sidebar">

                <!-- Start::main-sidebar-header -->
                <div class="main-sidebar-header">
                    <a href="{{url('index')}}" class="header-logo">
                        <img src="{{asset('build/assets/images/brand-logos/desktop-logo.png')}}" alt="logo" class="desktop-logo">
                        <img src="{{asset('build/assets/images/brand-logos/toggle-logo.png')}}" alt="logo" class="toggle-logo">
                        <img src="{{asset('build/assets/images/brand-logos/desktop-dark.png')}}" alt="logo" class="desktop-dark">
                        <img src="{{asset('build/assets/images/brand-logos/toggle-dark.png')}}" alt="logo" class="toggle-dark">
                        <img src="{{asset('build/assets/images/brand-logos/desktop-white.png')}}" alt="logo" class="desktop-white">
                        <img src="{{asset('build/assets/images/brand-logos/toggle-white.png')}}" alt="logo" class="toggle-white">
                    </a>
                </div>
                <!-- End::main-sidebar-header -->

                <!-- Start::main-sidebar -->
                <div class="main-sidebar" id="sidebar-scroll">

                    <!-- Start::nav -->
                    <nav class="main-menu-container nav nav-pills flex-column sub-open">
                        <div class="slide-left" id="slide-left"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24"
                                height="24" viewBox="0 0 24 24">
                                <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z"></path>
                            </svg></div>
                        <ul class="main-menu">
                            <!-- Start::slide__category -->
                            <li class="slide__category"><span class="category-name">Main</span></li>
                            <!-- End::slide__category -->

                            <!-- Start::slide -->
                            <li class="slide has-sub">
                                <a href="javascript:void(0);" class="side-menu__item">
                                    <i class="bx bx-home side-menu__icon"></i>
                                    <span class="side-menu__label">Dashboards<span
                                            class="badge !bg-warning/10 !text-warning !py-[0.25rem] !px-[0.45rem] !text-[0.75em] ms-2">12</span></span>
                                    <i class="fe fe-chevron-right side-menu__angle"></i>
                                </a>
                                <ul class="slide-menu child1">
                                    <li class="slide side-menu__label1">
                                        <a href="javascript:void(0)">Dashboards</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('index')}}" class="side-menu__item">CRM</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('index2')}}" class="side-menu__item">Ecommerce</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('index3')}}" class="side-menu__item">Crypto</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('index4')}}" class="side-menu__item">Jobs</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('index5')}}" class="side-menu__item">NFT</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('index6')}}" class="side-menu__item">Sales</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('index7')}}" class="side-menu__item">Analytics</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('index8')}}" class="side-menu__item">Projects</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('index9')}}" class="side-menu__item">HRM</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('index10')}}" class="side-menu__item">Stocks</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('index11')}}" class="side-menu__item">Courses</a>
                                    </li>

                                    <li class="slide">
                                        <a href="{{url('index12')}}" class="side-menu__item">Personal</a>
                                    </li>
                                </ul>
                            </li>
                            <!-- End::slide -->
                            <!-- Start::slide__category -->
                            <li class="slide__category"><span class="category-name">Pages</span></li>
                            <!-- End::slide__category -->

                            <!-- Start::slide -->
                            <li class="slide has-sub">
                                <a href="javascript:void(0);" class="side-menu__item">
                                    <i class="bx bx-file-blank side-menu__icon"></i>
                                    <span class="side-menu__label">Pages<span
                                        class="text-secondary text-[0.75em] rounded-sm !py-[0.25rem] !px-[0.45rem] badge !bg-secondary/10 ms-2">New</span></span>
                                    <i class="fe fe-chevron-right side-menu__angle"></i>
                                </a>
                                <ul class="slide-menu child1">
                                    <li class="slide side-menu__label1"><a href="javascript:void(0)">Pages</a></li>
                                    <li class="slide"><a href="{{url('aboutus')}}" class="side-menu__item">About Us</a></li>
                                    <li class="slide has-sub"><a href="javascript:void(0);" class="side-menu__item">Blog<i
                                                class="fe fe-chevron-right side-menu__angle"></i></a>
                                        <ul class="slide-menu child2">
                                            <li class="slide"><a href="{{url('blog')}}" class="side-menu__item">Blog</a></li>
                                            <li class="slide"><a href="{{url('blog-details')}}" class="side-menu__item">Blog
                                                    Details</a>
                                            </li>
                                            <li class="slide"><a href="{{url('blog-create')}}" class="side-menu__item">Blog Create</a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('chat')}}" class="side-menu__item">Chat</a>
                                    </li>
                                    <li class="slide"><a href="{{url('contacts')}}" class="side-menu__item">Contacts</a></li>
                                    <li class="slide"><a href="{{url('contactus')}}" class="side-menu__item">Contact Us</a></li>
                                    <li class="slide has-sub"><a href="javascript:void(0);" class="side-menu__item">Ecommerce<i
                                                class="fe fe-chevron-right side-menu__angle"></i></a>
                                        <ul class="slide-menu child2">
                                            <li class="slide">
                                                <a href="{{url('add-products')}}" class="side-menu__item">Add Products</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('cart')}}" class="side-menu__item">Cart</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('checkout')}}" class="side-menu__item">Checkout</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('edit-products')}}" class="side-menu__item">Edit Products</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('order-details')}}" class="side-menu__item">Order Details</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('orders')}}" class="side-menu__item">Orders</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('products')}}" class="side-menu__item">Products</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('products-details')}}" class="side-menu__item">Products Details</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('products-list')}}" class="side-menu__item">Products List</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('wishlist')}}" class="side-menu__item">Wishlist</a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="slide has-sub"><a href="javascript:void(0);" class="side-menu__item">EMail<i
                                                class="fe fe-chevron-right side-menu__angle"></i></a>
                                        <ul class="slide-menu child2">
                                            <li class="slide"><a href="{{url('mail')}}" class="side-menu__item">Mail App</a></li>
                                            <li class="slide"><a href="{{url('mail-settings')}}" class="side-menu__item">Mail Settings</a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="slide"><a href="{{url('empty-page')}}" class="side-menu__item">Empty</a></li>
                                    <li class="slide"><a href="{{url('faqs')}}" class="side-menu__item">FAQ's</a></li>
                                    <li class="slide has-sub">
                                        <a href="javascript:void(0);" class="side-menu__item">File Manager
                                            <i class="fe fe-chevron-right side-menu__angle"></i></a>
                                        <ul class="slide-menu child2">
                                            <li class="slide">
                                                <a href="{{url('filemanager')}}" class="side-menu__item">File Manager</a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="slide has-sub"><a href="javascript:void(0);" class="side-menu__item">Invoice<i
                                                class="fe fe-chevron-right side-menu__angle"></i></a>
                                        <ul class="slide-menu child2">
                                            <li class="slide">
                                                <a href="{{url('invoice-create')}}" class="side-menu__item">Create Invoice</a>
                                            </li>

                                            <li class="slide"><a href="{{url('invoice-details')}}" class="side-menu__item">Invoice Details</a>
                                            </li>
                                            <li class="slide"><a href="{{url('invoice-list')}}" class="side-menu__item">Invoice List</a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="slide"><a href="{{url('landing')}}" class="side-menu__item">Landing</a></li>
                                    <li class="slide">
                                        <a href="{{url('landing-jobs')}}" class="side-menu__item">Jobs Landing<span
                                            class="text-secondary text-[0.75em] rounded-sm badge !py-[0.25rem] !px-[0.45rem] !bg-secondary/10 ms-2">New</span></a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('notifications')}}" class="side-menu__item">Notifications</a>
                                    </li>
                                    <li class="slide"><a href="{{url('pricing')}}" class="side-menu__item">Pricing</a></li>
                                    <li class="slide">
                                        <a href="{{url('profile')}}" class="side-menu__item">Profile</a>
                                    </li>
                                    <li class="slide"><a href="{{url('reviews')}}" class="side-menu__item">Reviews</a></li>
                                    <li class="slide"><a href="{{url('team')}}" class="side-menu__item">Team</a></li>
                                    <li class="slide"><a href="{{url('terms')}}" class="side-menu__item">Terms &amp; Conditions</a></li>
                                    <li class="slide"><a href="{{url('timeline')}}" class="side-menu__item">Timeline</a></li>
                                    <li class="slide"><a href="{{url('todo')}}" class="side-menu__item">To Do List</a></li>
                                </ul>
                            </li>
                            <!-- End::slide -->

                            <!-- Start::slide -->
                            <li class="slide has-sub">
                                <a href="javascript:void(0);" class="side-menu__item">
                                    <i class="bx bx-task side-menu__icon"></i>
                                    <span class="side-menu__label">Task<span
                                        class="text-secondary text-[0.75em] rounded-sm badge !py-[0.25rem] !px-[0.45rem] !bg-secondary/10 ms-2">New</span></span>
                                    <i class="fe fe-chevron-right side-menu__angle"></i>
                                </a>
                                <ul class="slide-menu child1">
                                    <li class="slide side-menu__label1">
                                        <a href="javascript:void(0)">Task</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('task-kanban-board')}}" class="side-menu__item">Kanban Board</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('task-listview')}}" class="side-menu__item">List View</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('task-details')}}" class="side-menu__item">Task Details</a>
                                    </li>
                                </ul>
                            </li>
                            <!-- End::slide -->

                            <!-- Start::slide -->
                            <li class="slide has-sub">
                                <a href="javascript:void(0);" class="side-menu__item">
                                    <i class="bx bx-fingerprint side-menu__icon"></i>
                                    <span class="side-menu__label">Authentication</span>
                                    <i class="fe fe-chevron-right side-menu__angle"></i>
                                </a>
                                <ul class="slide-menu child1">
                                    <li class="slide side-menu__label1"><a href="javascript:void(0)">Authentication</a></li>
                                    <li class="slide"><a href="{{url('comingsoon')}}" class="side-menu__item">Coming Soon</a></li>
                                    <li class="slide has-sub"><a href="javascript:void(0);" class="side-menu__item">Create
                                            Password<i class="fe fe-chevron-right side-menu__angle"></i></a>
                                        <ul class="slide-menu child2">
                                            <li class="slide"><a href="{{url('createpassword-basic')}}" class="side-menu__item">Basic</a></li>
                                            <li class="slide"><a href="{{url('createpassword-cover')}}"
                                                    class="side-menu__item">Cover</a></li>
                                        </ul>
                                    </li>
                                    <li class="slide has-sub"><a href="javascript:void(0);" class="side-menu__item">Lockscreen<i
                                                class="fe fe-chevron-right side-menu__angle"></i></a>
                                        <ul class="slide-menu child2">
                                            <li class="slide"><a href="{{url('lockscreen-basic')}}" class="side-menu__item">Basic</a></li>
                                            <li class="slide"><a href="{{url('lockscreen-cover')}}" class="side-menu__item">Cover</a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="slide has-sub"><a href="javascript:void(0);" class="side-menu__item">Reset Password<i
                                                class="fe fe-chevron-right side-menu__angle"></i></a>
                                        <ul class="slide-menu child2">
                                            <li class="slide"><a href="{{url('resetpassword-basic')}}" class="side-menu__item">Basic</a></li>
                                            <li class="slide"><a href="{{url('resetpassword-cover')}}" class="side-menu__item">Cover</a></li>
                                        </ul>
                                    </li>
                                    <li class="slide has-sub"><a href="javascript:void(0);" class="side-menu__item">Sign Up<i
                                                class="fe fe-chevron-right side-menu__angle"></i></a>
                                        <ul class="slide-menu child2">
                                            <li class="slide"><a href="{{url('signup-basic')}}" class="side-menu__item">Basic</a></li>
                                            <li class="slide"><a href="{{url('signup-cover')}}" class="side-menu__item">Cover</a></li>
                                        </ul>
                                    </li>
                                    <li class="slide has-sub"><a href="javascript:void(0);" class="side-menu__item">Sign In<i
                                                class="fe fe-chevron-right side-menu__angle"></i></a>
                                        <ul class="slide-menu child2">
                                            <li class="slide"><a href="{{url('signin-basic')}}" class="side-menu__item">Basic</a></li>
                                            <li class="slide"><a href="{{url('signin-cover')}}" class="side-menu__item">Cover</a></li>
                                        </ul>
                                    </li>
                                    <li class="slide has-sub"><a href="javascript:void(0);" class="side-menu__item">Two Step
                                            Verfication<i class="fe fe-chevron-right side-menu__angle"></i></a>
                                        <ul class="slide-menu child2">
                                            <li class="slide"><a href="{{url('twostep-verification-basic')}}" class="side-menu__item">Basic</a></li>
                                            <li class="slide"><a href="{{url('twostep-verification-cover')}}" class="side-menu__item">Cover</a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="slide"><a href="{{url('under-maintenance')}}" class="side-menu__item">Under Maintenance</a></li>
                                </ul>
                            </li>
                            <!-- End::slide -->

                            <!-- Start::slide -->
                            <li class="slide has-sub">
                                <a href="javascript:void(0);" class="side-menu__item">
                                    <i class="bx bx-error side-menu__icon"></i>
                                    <span class="side-menu__label">Error</span>
                                    <i class="fe fe-chevron-right side-menu__angle"></i>
                                </a>
                                <ul class="slide-menu child1">
                                    <li class="slide side-menu__label1">
                                        <a href="javascript:void(0)">Error</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('error401')}}" class="side-menu__item">401 - Error</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('error404')}}" class="side-menu__item">404 - Error</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('error500')}}" class="side-menu__item">500 - Error</a>
                                    </li>
                                </ul>
                            </li>
                            <!-- End::slide -->

                            <!-- Start::slide__category -->
                            <li class="slide__category"><span class="category-name">General</span></li>
                            <!-- End::slide__category -->

                            <!-- Start::slide -->
                            <li class="slide has-sub">
                                <a href="javascript:void(0);" class="side-menu__item">
                                    <i class="bx bx-box side-menu__icon"></i>
                                    <span class="side-menu__label">Ui Elements</span>
                                    <i class="fe fe-chevron-right side-menu__angle"></i>
                                </a>
                                <ul class="slide-menu child1 mega-menu">
                                    <li class="slide side-menu__label1">
                                        <a href="javascript:void(0)">Ui Elements</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('alerts')}}" class="side-menu__item">Alerts</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('badges')}}" class="side-menu__item">Badges</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('breadcrumbs')}}" class="side-menu__item">Breadcrumbs</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('buttons')}}" class="side-menu__item">Buttons</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('buttongroups')}}" class="side-menu__item">Button Groups</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('blockquotes')}}" class="side-menu__item">Blockquotes</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('cards')}}" class="side-menu__item">Cards</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('dropdowns')}}" class="side-menu__item">Dropdowns</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('indicators')}}" class="side-menu__item">Indicators</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('images-figures')}}" class="side-menu__item">Images &amp; Figures</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('listgroups')}}" class="side-menu__item">List Groups</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('navs-tabs')}}" class="side-menu__item">Navs &amp; Tabs</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('object-fit')}}" class="side-menu__item">Object Fit</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('paginations')}}" class="side-menu__item">Paginations</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('popovers')}}" class="side-menu__item">Popovers</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('progress')}}" class="side-menu__item">Progress</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('spinners')}}" class="side-menu__item">Spinners</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('toasts')}}" class="side-menu__item">Toasts</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('tooltips')}}" class="side-menu__item">Tooltips</a>
                                    </li>
                                </ul>
                            </li>
                            <!-- End::slide -->

                            <!-- Start::slide -->
                            <li class="slide has-sub">
                                <a href="javascript:void(0);" class="side-menu__item">
                                    <i class="bx bx-medal side-menu__icon"></i>
                                    <span class="side-menu__label">Utilities</span>
                                    <i class="fe fe-chevron-right side-menu__angle"></i>
                                </a>
                                <ul class="slide-menu child1">
                                    <li class="slide side-menu__label1">
                                        <a href="javascript:void(0)">Utilities</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('avatars')}}" class="side-menu__item">Avatars</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('borders')}}" class="side-menu__item">Borders</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('colors')}}" class="side-menu__item">Colors</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('columns')}}" class="side-menu__item">Columns</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('flex')}}" class="side-menu__item">Flex</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('grids')}}" class="side-menu__item">Grids</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('typography')}}" class="side-menu__item">Typography</a>
                                    </li>
                                </ul>
                            </li>
                            <!-- End::slide -->

                            <!-- Start::slide -->
                            <li class="slide has-sub">
                                <a href="javascript:void(0);" class="side-menu__item">
                                    <i class="bx bx-file  side-menu__icon"></i>
                                    <span class="side-menu__label">Forms</span>
                                    <i class="fe fe-chevron-right side-menu__angle"></i>
                                </a>
                                <ul class="slide-menu child1">
                                    <li class="slide side-menu__label1">
                                        <a href="javascript:void(0)">Forms</a>
                                    </li>
                                    <li class="slide has-sub">
                                        <a href="javascript:void(0);" class="side-menu__item">Form Elements
                                            <i class="fe fe-chevron-right side-menu__angle"></i></a>
                                        <ul class="slide-menu child2">
                                            <li class="slide">
                                                <a href="{{url('form-inputs')}}" class="side-menu__item">Inputs</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('form-check-radios')}}" class="side-menu__item">Checks &amp; Radios</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('form-switches')}}" class="side-menu__item">Form Switches</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('form-input-groups')}}" class="side-menu__item">Input Groups</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('form-select')}}" class="side-menu__item">Form Select</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('form-range')}}" class="side-menu__item">Range Sliders</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('form-file-uploads')}}" class="side-menu__item">File Uploads</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('form-datetime-pickers')}}" class="side-menu__item">Date, Time Pickers</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('form-color-pickers')}}" class="side-menu__item">Color Pickers</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('form-advanced-select')}}" class="side-menu__item">Advanced Select</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('form-input-numbers')}}" class="side-menu__item">Input Numbers</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('form-passwords')}}" class="side-menu__item">Passwords</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('form-counters-markup')}}" class="side-menu__item">Counters & markup</a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('form-layouts')}}" class="side-menu__item">Form Layouts</a>
                                    </li>
                                    <li class="slide has-sub">
                                        <a href="javascript:void(0);" class="side-menu__item">Form Editors
                                            <i class="fe fe-chevron-right side-menu__angle"></i></a>
                                        <ul class="slide-menu child2">
                                            <li class="slide">
                                                <a href="{{url('quill-editor')}}" class="side-menu__item">Quill Editor</a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('form-validations')}}" class="side-menu__item">Validations</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('form-select2')}}" class="side-menu__item">Select2</a>
                                    </li>
                                </ul>
                            </li>
                            <!-- End::slide -->

                            <!-- Start::slide -->
                            <li class="slide has-sub">
                                <a href="javascript:void(0);" class="side-menu__item">
                                    <i class="bx bx-party side-menu__icon"></i>
                                    <span class="side-menu__label">Advanced Ui</span>
                                    <i class="fe fe-chevron-right side-menu__angle"></i>
                                </a>
                                <ul class="slide-menu child1">
                                    <li class="slide side-menu__label1"><a href="javascript:void(0)">Advanced Ui</a></li>
                                    <li class="slide">
                                        <a href="{{url('accordions-collapse')}}" class="side-menu__item">Accordions &amp; Collapse</a>
                                    </li>
                                    <li class="slide"><a href="{{url('custom-scrollbar')}}" class="side-menu__item">Custom Scrollbar</a></li>
                                    <li class="slide"><a href="{{url('draggable-cards')}}" class="side-menu__item">Draggable Cards</a></li>
                                    <li class="slide">
                                        <a href="{{url('modals-closes')}}" class="side-menu__item">Modals &amp; Closes</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('navbars')}}" class="side-menu__item">Navbars</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('offcanvas')}}" class="side-menu__item">Offcanvas</a>
                                    </li>
                                    <li class="slide"><a href="{{url('ratings')}}" class="side-menu__item">Ratings</a></li>
                                    <li class="slide">
                                        <a href="{{url('scrollspy')}}" class="side-menu__item">Scrollspy</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('stepper')}}" class="side-menu__item">Stepper</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('swiperjs')}}" class="side-menu__item">Swiper JS</a>
                                    </li>


                                </ul>
                            </li>
                            <!-- End::slide -->

                            <!-- Start::slide -->
                            <li class="slide">
                                <a href="{{url('widgets')}}" class="side-menu__item">
                                    <i class="bx bx-gift side-menu__icon"></i>
                                    <span class="side-menu__label">Widgets <span
                                            class="text-danger text-[0.75em] rounded-sm badge !py-[0.25rem] !px-[0.45rem] !bg-danger/10 ms-2">Hot</span></span>
                                </a>
                            </li>
                            <!-- End::slide -->

                            <!-- Start::slide__category -->
                            <li class="slide__category"><span class="category-name">Web Apps</span></li>
                            <!-- End::slide__category -->

                            <!-- Start::slide -->
                            <li class="slide has-sub">
                                <a href="javascript:void(0);" class="side-menu__item">
                                    <i class="bx bx-grid-alt side-menu__icon"></i>
                                    <span class="side-menu__label">Apps<span
                                        class="text-secondary text-[0.75em] rounded-sm badge !py-[0.25rem] !px-[0.45rem] !bg-secondary/10 ms-2">New</span></span>
                                    <i class="fe fe-chevron-right side-menu__angle"></i>
                                </a>
                                <ul class="slide-menu child1">
                                    <li class="slide side-menu__label1">
                                        <a href="javascript:void(0)">Apps</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('full-calendar')}}" class="side-menu__item">Full Calendar</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('gallery')}}" class="side-menu__item">Gallery</a>
                                    </li>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('sweetalerts')}}" class="side-menu__item">Sweetalerts</a>
                                    </li>
                                    <li class="slide has-sub">
                                        <a href="javascript:void(0);" class="side-menu__item">Projects
                                            <i class="fe fe-chevron-right side-menu__angle"></i></a>
                                        <ul class="slide-menu child2">
                                            <li class="slide">
                                                <a href="{{url('projects-list')}}" class="side-menu__item">Projects List</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('projects-overview')}}" class="side-menu__item">Projects Overview</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('projects-create')}}" class="side-menu__item">Projects Create</a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="slide has-sub">
                                        <a href="javascript:void(0);" class="side-menu__item">Jobs
                                            <i class="fe fe-chevron-right side-menu__angle"></i></a>
                                        <ul class="slide-menu child2">
                                            <li class="slide">
                                                <a href="{{url('job-details')}}" class="side-menu__item">Job Details</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('job-company-search')}}" class="side-menu__item">Company Search</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('job-search')}}" class="side-menu__item">Job Search </a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('job-post')}}" class="side-menu__item">Job Post</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('job-list')}}" class="side-menu__item">Job List</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('job-candidate-search')}}" class="side-menu__item">Candidate Search</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('job-candidate-details')}}" class="side-menu__item">Candidate Details</a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="slide has-sub">
                                        <a href="javascript:void(0);" class="side-menu__item">NFT
                                            <i class="fe fe-chevron-right side-menu__angle"></i></a>
                                        <ul class="slide-menu child2">
                                            <li class="slide">
                                                <a href="{{url('nft-marketplace')}}" class="side-menu__item">Market Place</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('nft-details')}}" class="side-menu__item">NFT Details</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('nft-create')}}" class="side-menu__item">Create NFT</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('nft-wallet-integration')}}" class="side-menu__item">Wallet Integration</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('nft-live-auction')}}" class="side-menu__item">Live Auction</a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="slide has-sub">
                                        <a href="javascript:void(0);" class="side-menu__item">CRM
                                            <i class="fe fe-chevron-right side-menu__angle"></i></a>
                                        <ul class="slide-menu child2">
                                            <li class="slide">
                                                <a href="{{url('crm-contacts')}}" class="side-menu__item">Contacts</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('crm-companies')}}" class="side-menu__item">Companies</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('crm-deals')}}" class="side-menu__item">Deals</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('crm-leads')}}" class="side-menu__item">Leads</a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="slide has-sub">
                                        <a href="javascript:void(0);" class="side-menu__item">Crypto
                                            <i class="fe fe-chevron-right side-menu__angle"></i></a>
                                        <ul class="slide-menu child2">
                                            <li class="slide">
                                                <a href="{{url('crypto-transactions')}}" class="side-menu__item">Transactions</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('crypto-currency-exchange')}}" class="side-menu__item">Currency Exchange</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('crypto-buy-sell')}}" class="side-menu__item">Buy &amp; Sell</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('crypto-marketcap')}}" class="side-menu__item">Marketcap</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('crypto-wallet')}}" class="side-menu__item">Wallet</a>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                            <!-- End::slide -->

                            <!-- Start::slide -->
                            <li class="slide has-sub">
                                <a href="javascript:void(0);" class="side-menu__item">
                                    <i class="bx bx-layer side-menu__icon"></i>
                                    <span class="side-menu__label">Nested Menu</span>
                                    <i class="fe fe-chevron-right side-menu__angle"></i>
                                </a>
                                <ul class="slide-menu child1">
                                    <li class="slide side-menu__label1">
                                        <a href="javascript:void(0)">Nested Menu</a>
                                    </li>
                                    <li class="slide">
                                        <a href="javascript:void(0);" class="side-menu__item">Nested-1</a>
                                    </li>
                                    <li class="slide has-sub">
                                        <a href="javascript:void(0);" class="side-menu__item">Nested-2
                                            <i class="fe fe-chevron-right side-menu__angle"></i></a>
                                        <ul class="slide-menu child2">
                                            <li class="slide">
                                                <a href="javascript:void(0);" class="side-menu__item">Nested-2-1</a>
                                            </li>
                                            <li class="slide has-sub">
                                                <a href="javascript:void(0);" class="side-menu__item">Nested-2-2
                                                    <i class="fe fe-chevron-right side-menu__angle"></i></a>
                                                <ul class="slide-menu child3">
                                                    <li class="slide">
                                                        <a href="javascript:void(0);" class="side-menu__item">Nested-2-2-1</a>
                                                    </li>
                                                    <li class="slide">
                                                        <a href="javascript:void(0);" class="side-menu__item">Nested-2-2-2</a>
                                                    </li>
                                                </ul>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                            <!-- End::slide -->

                            <!-- Start::slide__category -->
                            <li class="slide__category"><span class="category-name">Tables &amp; Charts</span></li>
                            <!-- End::slide__category -->

                            <!-- Start::slide -->
                            <li class="slide has-sub">
                                <a href="javascript:void(0);" class="side-menu__item">
                                    <i class="bx bx-table side-menu__icon"></i>
                                    <span class="side-menu__label">Tables<span
                                            class="text-success text-[0.75em] badge !py-[0.25rem] !px-[0.45rem] rounded-sm bg-success/10 ms-2">3</span></span>
                                    <i class="fe fe-chevron-right side-menu__angle"></i>
                                </a>
                                <ul class="slide-menu child1">
                                    <li class="slide side-menu__label1">
                                        <a href="javascript:void(0)">Tables</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('tables')}}" class="side-menu__item">Tables</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('grid-tables')}}" class="side-menu__item">Grid JS Tables</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{url('data-tables')}}" class="side-menu__item">Data Tables</a>
                                    </li>
                                </ul>
                            </li>
                            <!-- End::slide -->

                            <!-- Start::slide -->
                            <li class="slide has-sub">
                                <a href="javascript:void(0);" class="side-menu__item">
                                    <i class="bx bx-bar-chart-square side-menu__icon"></i>
                                    <span class="side-menu__label">Charts</span>
                                    <i class="fe fe-chevron-right side-menu__angle"></i>
                                </a>
                                <ul class="slide-menu child1">
                                    <li class="slide side-menu__label1"><a href="javascript:void(0)">Charts</a></li>
                                    <li class="slide has-sub">
                                        <a href="javascript:void(0);" class="side-menu__item">Apex Charts
                                            <i class="fe fe-chevron-right side-menu__angle"></i></a>
                                        <ul class="slide-menu child2">
                                            <li class="slide">
                                                <a href="{{url('apex-line-charts')}}" class="side-menu__item">Line Charts</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('apex-area-charts')}}" class="side-menu__item">Area Charts</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('apex-column-charts')}}" class="side-menu__item">Column Charts</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('apex-bar-charts')}}" class="side-menu__item">Bar Charts</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('apex-mixed-charts')}}" class="side-menu__item">Mixed Charts</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('apex-rangearea-charts')}}" class="side-menu__item">Range Area Charts</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('apex-timeline-charts')}}" class="side-menu__item">Timeline Charts</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('apex-candlestick-charts')}}" class="side-menu__item">Candlestick
                                                    Charts</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('apex-boxplot-charts')}}" class="side-menu__item">Boxplot Charts</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('apex-bubble-charts')}}" class="side-menu__item">Bubble Charts</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('apex-scatter-charts')}}" class="side-menu__item">Scatter Charts</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('apex-heatmap-charts')}}" class="side-menu__item">Heatmap Charts</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('apex-treemap-charts')}}" class="side-menu__item">Treemap Charts</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('apex-pie-charts')}}" class="side-menu__item">Pie Charts</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('apex-radialbar-charts')}}" class="side-menu__item">Radialbar Charts</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('apex-radar-charts')}}" class="side-menu__item">Radar Charts</a>
                                            </li>
                                            <li class="slide">
                                                <a href="{{url('apex-polararea-charts')}}" class="side-menu__item">Polararea Charts</a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="slide"><a href="{{url('chartjs')}}" class="side-menu__item">Chart JS</a></li>
                                    <li class="slide"><a href="{{url('echartjs')}}" class="side-menu__item">Echarts</a></li>
                                </ul>
                            </li>
                            <!-- End::slide -->

                            <!-- Start::slide__category -->
                            <li class="slide__category"><span class="category-name">Maps &amp; Icons</span></li>
                            <!-- End::slide__category -->

                            <!-- Start::slide -->
                            <li class="slide has-sub">
                                <a href="javascript:void(0);" class="side-menu__item">
                                    <i class="bx bx-map side-menu__icon"></i>
                                    <span class="side-menu__label">Maps</span>
                                    <i class="fe fe-chevron-right side-menu__angle"></i>
                                </a>
                                <ul class="slide-menu child1">
                                    <li class="slide side-menu__label1"><a href="javascript:void(0)">Maps</a></li>
                                    <li class="slide"><a href="{{url('google-maps')}}" class="side-menu__item">Google Maps</a></li>
                                    <li class="slide"><a href="{{url('leaflet-maps')}}" class="side-menu__item">Leaflet Maps</a></li>
                                    <li class="slide"><a href="{{url('vector-maps')}}" class="side-menu__item">Vector Maps</a></li>
                                </ul>
                            </li>
                            <!-- End::slide -->

                            <!-- Start::slide -->
                            <li class="slide">
                                <a href="{{url('icons')}}" class="side-menu__item">
                                    <i class="bx bx-store-alt side-menu__icon"></i>
                                    <span class="side-menu__label">Icons</span>
                                </a>
                            </li>
                            <!-- End::slide -->

                                <!-- Start::slide__category -->
                                @if(auth()->check() && (
                                    auth()->user()->hasRole('admin') || 
                                    auth()->user()->can('view User Lists') || 
                                    auth()->user()->can('manage permissions') || 
                                    auth()->user()->can('manage user registration') ||
                                    auth()->user()->can('view Login Logs') ||
                                    auth()->user()->can('view Audit Logs')
                                ))
                                    <li class="slide__category"><span class="category-name">Users &amp; Permission</span></li>
                                    <!-- End::slide__category -->

                                    <!-- Start::slide -->
                                    <li class="slide has-sub">
                                        <a href="javascript:void(0);" class="side-menu__item">
                                            <i class="bi bi-people side-menu__icon"></i>
                                            <span class="side-menu__label">User/Permissions</span>
                                            <i class="fe fe-chevron-right side-menu__angle"></i>
                                        </a>
                                        <ul class="slide-menu child1">
                                            <li class="slide side-menu__label1"><a href="javascript:void(0)">User Permissions</a></li>

                                            {{-- Check if the user is authenticated and is an admin OR has the 'view User Lists' permission --}}
                                            @if(auth()->check() && (auth()->user()->hasRole('admin') || auth()->user()->can('view User Lists')))
                                                <li class="slide"><a href="{{ url('manage-user-permissions') }}" class="side-menu__item">User</a></li>
                                            @endif

                                            {{-- Check if the user is authenticated and is an admin OR has the 'view User Permissions' permission --}}
                                            @if(auth()->check() && (auth()->user()->hasRole('admin') || auth()->user()->can('view User Permissions')))
                                                <li class="slide"><a href="{{ url('roles') }}" class="side-menu__item">Roles</a></li>
                                            @endif


                                            {{-- Check if the user is authenticated and is an admin OR has the 'view User Permissions' permission --}}
                                            @if(auth()->check() && (auth()->user()->hasRole('admin') || auth()->user()->can('view User Permissions')))
                                                <li class="slide"><a href="{{ url('permissions') }}" class="side-menu__item">Permissions</a></li>
                                            @endif

                                            {{-- Check if the user is authenticated and is an admin OR has the 'view User Registration' permission --}}
                                            @if(auth()->check() && (auth()->user()->hasRole('admin') || auth()->user()->can('view User Registration')))
                                                <li class="slide"><a href="{{ url('sign-up') }}" class="side-menu__item">User Registration</a></li>
                                            @endif

                                            @if(auth()->check() && (auth()->user()->hasRole('admin') || auth()->user()->can('view Login Logs')))
                                                <li class="slide"><a href="{{ url('logs') }}" class="side-menu__item">Logged-in Logs</a></li>
                                            @endif

                                            @if(auth()->check() && (auth()->user()->hasRole('admin') || auth()->user()->can('view Audit Logs')))
                                                <li class="slide"><a href="{{ url('audit-logs') }}" class="side-menu__item">Audit Logs</a></li>
                                            @endif
                                        </ul>
                                    </li>
                                    <!-- End::slide -->
                                @endif


                        </ul>
                        <div class="slide-right" id="slide-right"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24"
                                height="24" viewBox="0 0 24 24">
                                <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"></path>
                            </svg>
                        </div>
                    </nav>
                    <!-- End::nav -->

                </div>
                <!-- End::main-sidebar -->

            </aside>