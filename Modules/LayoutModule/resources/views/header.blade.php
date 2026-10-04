    <header class="pc-header">
        <div class="header-wrapper">
            <!-- [Mobile Media Block] start -->
            {{-- <div class="me-auto pc-mob-drp">
                <ul class="list-unstyled">
                    <!-- ======= Menu collapse Icon ===== -->
                    <li class="pc-h-item pc-sidebar-collapse">
                        <a href="#" class="pc-head-link ms-0" id="sidebar-hide">
                            <i class="ti ti-menu-2"></i>
                        </a>
                    </li>
                    <li class="pc-h-item pc-sidebar-popup">
                        <a href="#" class="pc-head-link ms-0" id="mobile-collapse">
                            <i class="ti ti-menu-2"></i>
                        </a>
                    </li>
                    <li class="dropdown pc-h-item d-inline-flex d-md-none">
                        <a class="pc-head-link dropdown-toggle arrow-none m-0" data-bs-toggle="dropdown" href="#"
                            role="button" aria-haspopup="false" aria-expanded="false">
                            <i class="ph-duotone ph-magnifying-glass"></i>
                        </a>
                        <div class="dropdown-menu pc-h-dropdown drp-search">
                            <form class="px-3">
                                <div class="mb-0 d-flex align-items-center">
                                    <input type="search" class="form-control border-0 shadow-none"
                                        placeholder="Search...">
                                    <button class="btn btn-light-secondary btn-search">Search</button>
                                </div>
                            </form>
                        </div>
                    </li>
                    <li class="pc-h-item d-none d-md-inline-flex">
                        <form class="form-search">
                            <i class="ph-duotone ph-magnifying-glass icon-search"></i>
                            <input type="search" class="form-control" placeholder="Search...">
                            <button class="btn btn-search" style="padding: 0"><kbd>ctrl+k</kbd></button>
                        </form>
                    </li>
                </ul>
            </div> --}}
            <!-- [Mobile Media Block end] -->
            <div class="ms-auto">
                <ul class="list-unstyled">
                    <li class="dropdown pc-h-item d-md-inline-flex">
                         <b>
                            {{ __('messages.welcome') }}  :
                            {{ Auth::guard('admin')->user()->name }}
                        </b>
                    </li>
                    <li class="dropdown pc-h-item header-user-profile">
                        <a class="pc-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#"
                            role="button" aria-haspopup="false" data-bs-auto-close="outside" aria-expanded="false">
                            <img src="{{ asset('assets/images/user/avatar-2.jpg') }}" alt="user-image"
                                class="user-avtar">
                        </a>
                        <div class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown">
                            <div class="dropdown-header d-flex align-items-center justify-content-between">
                                <h5 class="m-0">Profile</h5>
                            </div>
                            <div class="dropdown-body">
                                <div class="profile-notification-scroll position-relative"
                                    style="max-height: calc(100vh - 225px)">
                                    <ul class="list-group list-group-flush w-100">
                                        <li class="list-group-item">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <img src="{{ asset('assets/images/user/avatar-2.jpg') }}"
                                                        alt="user-image" class="wid-50 rounded-circle">
                                                </div>
                                                <div class="flex-grow-1 mx-3">
                                                    <h5 class="mb-0">
                                                        {{ Auth::guard('admin')->user()->name }}
                                                    </h5>
                                                    <a class="link-primary" href="#">
                                                        {{ Auth::guard('admin')->user()->email }}
                                                    </a>
                                                </div>
                                            </div>
                                        </li>

                                        <li class="list-group-item">
                                            <div class="dropdown-item">
                                                <span class="d-flex align-items-center">
                                                    <i class="ph-duotone ph-globe-hemisphere-west"></i>
                                                    <span>Languages</span>
                                                </span>
                                                <span class="flex-shrink-0">
                                                    <select
                                                        class="form-select bg-transparent form-select-sm border-0 shadow-none">
                                                        <option value="1">English</option>
                                                        <option value="2">Spain</option>
                                                        <option value="3">Arbic</option>
                                                    </select>
                                                </span>
                                            </div>
                                            {{-- </li>

                                        <li class="list-group-item"> --}}

                                            {{-- <a href="#" class="dropdown-item">
                                                <span class="d-flex align-items-center">
                                                    <i class="ph-duotone ph-user-circle"></i>
                                                    <span>Edit profile</span>
                                                </span>
                                            </a> --}}

                                            <a href="#" class="dropdown-item">
                                                <span class="d-flex align-items-center">
                                                    <i class="ph-duotone ph-key"></i>
                                                    <span>Change password</span>
                                                </span>
                                            </a>
                                        </li>


                                        {{-- <li class="list-group-item">
                                            <a href="#" class="dropdown-item">
                                                <span class="d-flex align-items-center">
                                                    <i class="ph-duotone ph-gear-six"></i>
                                                    <span>Settings</span>
                                                </span>
                                            </a>
                                        </li> --}}
                                        <li class="list-group-item">
                                            <a href="{{ route('admin.logout') }}" class="dropdown-item"><span
                                                    class="d-flex align-items-center"><i
                                                        class="ph-duotone ph-power"></i>
                                                    <span>Logout</span>
                                                </span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <?php /* <header class="pc-header">
    <div class="header-wrapper">
        <!-- [Mobile Media Block] start -->
        <div class="me-auto pc-mob-drp">
            <ul class="list-unstyled">
                <!-- ======= أيقونة طي القائمة ===== -->
                <li class="pc-h-item pc-sidebar-collapse">
                    <a href="#" class="pc-head-link ms-0" id="sidebar-hide">
                        <i class="ti ti-menu-2"></i>
                    </a>
                </li>
                <!-- ======= أيقونة القائمة المنبثقة للهواتف ===== -->
                <li class="pc-h-item pc-sidebar-popup">
                    <a href="#" class="pc-head-link ms-0" id="mobile-collapse">
                        <i class="ti ti-menu-2"></i>
                    </a>
                </li>
                <!-- ======= أيقونة البحث للهواتف ===== -->
                <li class="dropdown pc-h-item d-inline-flex d-md-none">
                    <a class="pc-head-link dropdown-toggle arrow-none m-0" data-bs-toggle="dropdown" href="#"
                        role="button" aria-haspopup="false" aria-expanded="false">
                        <i class="ph-duotone ph-magnifying-glass"></i>
                    </a>
                </li>
            </ul>
        </div>
        <!-- [Mobile Media Block end] -->

        <div class="ms-auto">
            <ul class="list-unstyled">
                <!-- ======= تبديل الوضع (فاتح/داكن) ===== -->
                <li class="dropdown pc-h-item d-flex align-items-center">
                    <span class="greeting me-3" style="font-size: 1rem; color: #2c3e50; font-weight: 500; display: flex; align-items: center;">
                        <span style="margin-left: 0.5rem; color: #1abc9c;">مرحبا</span>
                        <span class="username" style="font-weight: 600; color: #3498db; margin-left: 0.5rem;">{{ auth()->user()->userable->name }}</span>
                        <span class="role" style="font-weight: 400; color: #95a5a6;">
                            | 
                            @if(optional(auth()->user()->roles->first())->name == 'Admin')
                                مدير
                            @elseif(optional(auth()->user()->roles->first())->name == 'ClubAdmin')
                                إداري نادي
                            @elseif(optional(auth()->user()->roles->first())->name == 'FederationAdmin')
                            موظف الاتحاد
                            @else
                                {{ optional(auth()->user()->roles->first())->name }}
                            @endif
                        </span>
                    </span>
                    
                    {{-- <a class="pc-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#"
                        role="button" aria-haspopup="false" aria-expanded="false">
                        <i class="ph-duotone ph-sun-dim"></i>
                    </a> --}}
                    <div class="dropdown-menu dropdown-menu-end pc-h-dropdown">
                        <a href="#!" class="dropdown-item" onclick="layout_change('dark')">
                            <i class="ph-duotone ph-moon"></i>
                            <span>الوضع الداكن</span>
                        </a>
                        <a href="#!" class="dropdown-item" onclick="layout_change('light')">
                            <i class="ph-duotone ph-sun-dim"></i>
                            <span>الوضع الفاتح</span>
                        </a>
                        <a href="#!" class="dropdown-item" onclick="layout_change_default()">
                            <i class="ph-duotone ph-cpu"></i>
                            <span>الوضع الافتراضي</span>
                        </a>
                    </div>
                </li>

                <li class="dropdown pc-h-item header-user-profile">
                    <a class="pc-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#"
                        role="button" aria-haspopup="true" aria-expanded="false">
                        <img src="{{ asset('assets/images/user/user_avatar.png') }}" alt="صورة المستخدم"
                            class="user-avtar" />
                    </a>
                    <div class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown">
                        <div class="dropdown-header d-flex align-items-center justify-content-between">
                            <h5 class="m-0">الملف الشخصي</h5>
                        </div>
                        <div class="dropdown-body">
                            <div class="profile-notification-scroll position-relative"
                                style="max-height: calc(100vh - 225px)">
                                <ul class="list-group list-group-flush w-100">
                                    <!-- User Info -->
                                    <li class="list-group-item">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <img src="{{ asset('assets/images/user/user_avatar.png') }} "
                                                    alt="صورة المستخدم" class="wid-50 rounded-circle" />
                                            </div>
                                            <div class="flex-grow-1 mx-3">
                                                <h5 class="mb-0"> {{ auth()->user()->username }}</h5>
                                                <p class="mb-0">@if(optional(auth()->user()->roles->first())->name == 'Admin')
                                                    مدير
                                                @elseif(optional(auth()->user()->roles->first())->name == 'ClubAdmin')
                                                    إداري نادي
                                                @elseif(optional(auth()->user()->roles->first())->name == 'FederationAdmin')
                                                    موظف الاتحاد
                                                @else
                                                    {{ optional(auth()->user()->roles->first())->name }}
                                                @endif</p>
                                                <a class="link-primary"
                                                    href="mailto:{{ auth()->user()->email }}">{{ auth()->user()->email }}</a>
                                            </div>

                                        </div>
                                    </li>
                                    <!-- Change Password -->
                                    <li class="list-group-item">
                                        <a href="{{ route('changePassword') }}" class="dropdown-item">
                                            <span class="d-flex align-items-center">
                                                <i class="ph-duotone ph-key"></i>
                                                <span>تغيير كلمة المرور</span>
                                            </span>
                                        </a>
                                    </li>
                                    <!-- Logout -->
                                    <li class="list-group-item">
                                        <a href="{{ route('logout') }}">
                                            <button type="submit" class="dropdown-item text-danger">
                                                <span class="d-flex align-items-center">
                                                    <i class="ph-duotone ph-sign-out"></i>
                                                    <span>تسجيل الخروج</span>
                                                </span>
                                            </button>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </li>

            </ul>
        </div>
    </div>
</header>
*/
    ?>
