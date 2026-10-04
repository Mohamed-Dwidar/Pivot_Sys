@extends('layoutmodule::login')

@section('content')
    <div class="auth-main v1">
        <div class="auth-wrapper">
            <div class="auth-form">
                <div class="card my-5">
                    <div class="card-body">

                        <form method="POST" action='{{ route('admin.loginpost') }}' id="form-login">
                            @csrf

                            
                            <div class="text-center">
                                <img src="{{ asset('assets/images/logo.png') }}" alt="logo" class="img-fluid mb-3"
                                    style="width: 150px;">
                                {{-- <h4 class="f-w-500 mb-1">Login with your email</h4> --}}
                                <p class="mb-3">Admin Panel Login</p>
                            </div>
                            @if ($errors->any())
                                <div class="alert alert-warning mb-2" role="alert">
                                    <strong>Error!</strong>
                                    {{ $errors->first() }}
                                </div>
                            @endif
                            <div class="mb-3">
                                <input type="email" class="form-control" name="email" id="email"
                                    value="{{ old('email') }}" placeholder="Email Address">
                            </div>
                            @if ($errors->has('email'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('email') }}</strong>
                                </span>
                            @endif
                            <div class="mb-3">
                                <input type="password" class="form-control" id="password" name="password"
                                    placeholder="Password">
                            </div>
                            <div class="d-flex mt-1 justify-content-between align-items-center">
                                <div class="form-check">
                                    <input class="form-check-input input-primary" type="checkbox" id="customCheckc1"
                                        checked="">
                                    <label class="form-check-label text-muted" for="customCheckc1">Remember me?</label>
                                </div>
                                {{--   <a
                                href="../pages/forgot-password-v1.html">
                                <h6 class="f-w-400 mb-0">Forgot Password?</h6>
                            </a> --}}
                            </div>
                            <div class="d-grid mt-4"><button type="submit" class="btn btn-primary">Login</button></div>
                            {{-- <div class="saprator my-3"><span>Or continue with</span></div> --}}
                            <div class="text-center mb-5">
                                &nbsp;
                                {{-- <ul class="list-inline mx-auto mt-3 mb-0">
                                <li class="list-inline-item"><a href="https://www.facebook.com/"
                                        class="avtar avtar-s rounded-circle bg-facebook" target="_blank"><i
                                            class="fab fa-facebook-f text-white"></i></a></li>
                                <li class="list-inline-item"><a href="https://twitter.com/"
                                        class="avtar avtar-s rounded-circle bg-twitter" target="_blank"><i
                                            class="fab fa-twitter text-white"></i></a></li>
                                <li class="list-inline-item"><a href="https://myaccount.google.com/"
                                        class="avtar avtar-s rounded-circle bg-googleplus" target="_blank"><i
                                            class="fab fa-google text-white"></i></a></li>
                            </ul> --}}
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            {{-- <div class="auth-sidefooter"><img src="{{ asset('assets/images/logo-dark.svg') }}"
                    class="img-brand img-fluid" alt="images">
                <hr class="mb-3 mt-4">
                <div class="row">
                    <div class="col my-1">
                        <p class="m-0">Made with ♥ by Team <a href="https://themeforest.net/user/phoenixcoded"
                                target="_blank">Phoenixcoded</a></p>
                    </div>
                    <div class="col-auto my-1">
                        <ul class="list-inline footer-link mb-0">
                            <li class="list-inline-item"><a href="../index.html">Home</a></li>
                            <li class="list-inline-item"><a href="https://pcoded.gitbook.io/light-able/"
                                    target="_blank">Documentation</a></li>
                            <li class="list-inline-item"><a href="https://phoenixcoded.support-hub.io/"
                                    target="_blank">Support</a></li>
                        </ul>
                    </div>
                </div>
            </div> --}}
        </div>
    </div>

    <?php /*
    <div class="auth-main v2">
      <div class="bg-overlay"></div>
      <div class="auth-wrapper">
  
          <div class="auth-form">
              <div class="card my-5 mx-3">
                  <div class="card-body">
                      <!-- Logo -->
                      <div class="text-center mb-4">
                          <img src="{{ asset('/assets/images/logo.png') }}" alt="Logo" class="img-fluid" style="max-width: 120px;">
                      </div>
  
                      <!-- Header -->
                      <h4 class="f-w-500 mb-4 text-center">
                          <i class="fas fa-user-lock me-2 text-primary"></i> Admin Login
                      </h4>
  
                      <!-- Username Input with Icon -->
                      <div class="input-group mb-3 mt-4">
                          <span class="input-group-text"><i class="fas fa-user"></i></span>
                          <input type="text" class="form-control" id="floatingInput" placeholder="إسم المستخدم">
                      </div>
  
                      <!-- Password Input with Icon -->
                      <div class="input-group mb-3">
                          <span class="input-group-text"><i class="fas fa-lock"></i></span>
                          <input type="password" class="form-control" id="floatingInput1" placeholder="كلمة المرور">
                      </div>
  
                      <!-- Remember Me & Forgot Password -->
                      {{-- <div class="d-flex mt-1 justify-content-between align-items-center">
                          <div class="form-check">
                              <input class="form-check-input input-primary" type="checkbox" id="customCheckc1" checked="">
                              <label class="form-check-label text-muted" for="customCheckc1">
                                  تذكرني؟
                              </label>
                          </div>
                          <a href="../pages/forgot-password-v2.html">
                              <h6 class="text-secondary f-w-400 mb-0">
                                  <i class="fas fa-key me-1"></i> هل نسيت كلمة المرور؟
                              </h6>
                          </a>
                      </div> --}}
  
                      <!-- Login Button -->
                      <div class="d-grid mt-4">
                          <button type="button" class="btn btn-primary">
                              <i class="fas fa-sign-in-alt me-2"></i> Login
                          </button>
                      </div>
                  </div>
              </div>
          </div>
  
      </div>
  </div> */
    ?>
@endsection
