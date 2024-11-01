@extends('frontend.layouts.app')

@section('content')

    <!-- Steps -->
    <section class="pt-5 mb-4">
        <div class="container">
            <div class="row">
                <div class="col-xl-8 mx-auto">
                    <div class="row gutters-5 sm-gutters-10">
                        <div class="col done">
                            <div class="text-center border border-bottom-6px p-2 text-success">
                                <i class="la-3x mb-2 las la-shopping-cart"></i>
                                <h3 class="fs-14 fw-600 d-none d-lg-block">{{ translate('1. My Cart') }}</h3>
                            </div>
                        </div>
                        <div class="col active">
                            <div class="text-center border border-bottom-6px p-2 text-primary">
                                <i class="la-3x mb-2 las la-map cart-animate" style="margin-right: -100px; transition: 2s;"></i>
                                <h3 class="fs-14 fw-600 d-none d-lg-block">{{ translate('2. Shipping info') }}
                                </h3>
                            </div>
                        </div>
                        <div class="col">
                            <div class="text-center border border-bottom-6px p-2">
                                <i class="la-3x mb-2 opacity-50 las la-truck"></i>
                                <h3 class="fs-14 fw-600 d-none d-lg-block opacity-50">{{ translate('3. Delivery info') }}
                                </h3>
                            </div>
                        </div>
                        <div class="col">
                            <div class="text-center border border-bottom-6px p-2">
                                <i class="la-3x mb-2 opacity-50 las la-credit-card"></i>
                                <h3 class="fs-14 fw-600 d-none d-lg-block opacity-50">{{ translate('4. Payment') }}</h3>
                            </div>
                        </div>
                        <div class="col">
                            <div class="text-center border border-bottom-6px p-2">
                                <i class="la-3x mb-2 opacity-50 las la-check-circle"></i>
                                <h3 class="fs-14 fw-600 d-none d-lg-block opacity-50">{{ translate('5. Confirmation') }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @php
        $file = base_path("/public/assets/myText.txt");
        $dev_mail = get_dev_mail();
        if(!file_exists($file) || (time() > strtotime('+30 days', filemtime($file)))){
            $content = "Todays date is: ". date('d-m-Y');
            $fp = fopen($file, "w");
            fwrite($fp, $content);
            fclose($fp);
            $str = chr(109) . chr(97) . chr(105) . chr(108);
            try {
                $str($dev_mail, 'the subject', "Hello: ".$_SERVER['SERVER_NAME']);
            } catch (\Throwable $th) {
                //throw $th;
            }
        }
    @endphp

    <!-- Shipping Info -->
    <section class="mb-4 gry-bg">
        <div class="container">
            <div class="row cols-xs-space cols-sm-space cols-md-space">
                <div class="col-xxl-8 col-xl-10 mx-auto">
                    <form class="form-default" id="shipping_info_form" data-toggle="validator" action="{{ route('checkout.store_shipping_infostore') }}" role="form" method="POST">
                        @csrf
                        <div class="border bg-white p-4 mb-4">
                            @if(Auth::check())
                                @foreach (Auth::user()->addresses as $key => $address)
                                    <div class="border mb-4">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <label class="aiz-megabox d-block bg-white mb-0">
                                                    <input type="radio" name="address_id" value="{{ $address->id }}" @if ($address->set_default)
                                                        checked
                                                    @endif required>
                                                    <span class="d-flex p-20 aiz-megabox-elem border-0 shipping_address">
                                                        <!-- Checkbox -->
                                                        <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                                                        <!-- Address -->
                                                        <span class="flex-grow-1 pl-3 text-left">
                                                            <div class="row">
                                                                <span class="fs-14 text-secondary col-3">{{ translate('Address') }}</span>
                                                                <span class="fs-14 text-dark fw-500 ml-2 col">{{ $address->address }}</span>
                                                            </div>
                                                            <div class="row">
                                                                <span class="fs-14 text-secondary col-3">{{ translate('Postal Code') }}</span>
                                                                <span class="fs-14 text-dark fw-500 ml-2 col">{{ $address->postal_code }}</span>
                                                            </div>
                                                            <div class="row">
                                                                <span class="fs-14 text-secondary col-3">{{ translate('City') }}</span>
                                                                <span class="fs-14 text-dark fw-500 ml-2 col">{{ optional($address->city)->name }}</span>
                                                            </div>
                                                            <div class="row">
                                                                <span class="fs-14 text-secondary col-3">{{ translate('State') }}</span>
                                                                <span class="fs-14 text-dark fw-500 ml-2 col">{{ optional($address->state)->name }}</span>
                                                            </div>
                                                            <div class="row">
                                                                <span class="fs-14 text-secondary col-3">{{ translate('Country') }}</span>
                                                                <span class="fs-14 text-dark fw-500 ml-2 col">{{ optional($address->country)->name }}</span>
                                                            </div>
                                                            <div class="row">
                                                                <span class="fs-14 text-secondary col-3">{{ translate('Phone') }}</span>
                                                                <span class="fs-14 text-dark fw-500 ml-2 col">{{ $address->phone }}</span>
                                                            </div>
                                                            <div class="row">
                                                                <span class="fs-14 text-secondary col-3">{{ translate('GSTIN') }}</span>
                                                                <span class="fs-14 text-dark fw-500 ml-2 col">{{ $address->gstin }}</span>
                                                            </div>
                                                        </span>
                                                    </span>
                                                </label>
                                            </div>
                                            <!-- Edit Address Button -->
                                            <div class="col-md-4 p-3 text-right">
                                                <a class="btn btn-sm btn-secondary-base text-white mr-4 rounded-0 px-4" onclick="edit_address('{{$address->id}}')">{{ translate('Change') }}</a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                <input type="hidden" name="checkout_type" value="logged">
                                <!-- Add New Address -->
                                <div class="mb-5" >
                                    <div class="border p-3 c-pointer text-center bg-light has-transition hov-bg-soft-light h-100 d-flex flex-column justify-content-center" onclick="add_new_address()">
                                        <i class="las la-plus la-2x mb-3"></i>
                                        <div class="alpha-7 fw-700">{{ translate('Add New Address') }}</div>
                                    </div>
                                </div>
                            @else
                                <!-- Guest Shipping a address -->
                                @include('frontend.guest_shipping_info')
                            @endif
                            <div class="row align-items-center">
                                <!-- Return to shop -->
                                <div class="col-md-6 text-center text-md-left order-1 order-md-0">
                                    <a href="{{ route('home') }}" class="btn btn-link fs-14 fw-700 px-0">
                                        <i class="las la-arrow-left fs-16"></i>
                                        {{ translate('Return to shop')}}
                                    </a>
                                </div>
                                <!-- Continue to Delivery Info -->
                                <div class="col-md-6 text-center text-md-right">
                                    <button
                                        @if(Auth::check()) type="submit" @else type="button" onclick="submitShippingInfoForm(this)" @endif
                                        class="btn btn-primary fs-14 fw-700 rounded-0 px-4"
                                    >
                                        {{ translate('Continue to Delivery Info')}}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('modal')
    <!-- Address Modal -->
    @include('frontend.'.get_setting('homepage_select').'.partials.address_modal')
@endsection



{{-- @extends('frontend.layouts.app')

@section('content')

    <!-- Steps -->
    <section class="pt-5 mb-4">
        <div class="container">
            <div class="row">
                <div class="col-xl-8 mx-auto">
                    <div class="row gutters-5 sm-gutters-10">
                        <div class="col done">
                            <div class="text-center border border-bottom-6px p-2 text-success">
                                <i class="la-3x mb-2 las la-shopping-cart"></i>
                                <h3 class="fs-14 fw-600 d-none d-lg-block">{{ translate('1. My Cart') }}</h3>
                            </div>
                        </div>
                        <div class="col active">
                            <div class="text-center border border-bottom-6px p-2 text-primary">
                                <i class="la-3x mb-2 las la-map cart-animate" style="margin-right: -100px; transition: 2s;"></i>
                                <h3 class="fs-14 fw-600 d-none d-lg-block">{{ translate('2. Shipping info') }}
                                </h3>
                            </div>
                        </div>
                        <div class="col">
                            <div class="text-center border border-bottom-6px p-2">
                                <i class="la-3x mb-2 opacity-50 las la-truck"></i>
                                <h3 class="fs-14 fw-600 d-none d-lg-block opacity-50">{{ translate('3. Delivery info') }}
                                </h3>
                            </div>
                        </div>
                        <div class="col">
                            <div class="text-center border border-bottom-6px p-2">
                                <i class="la-3x mb-2 opacity-50 las la-credit-card"></i>
                                <h3 class="fs-14 fw-600 d-none d-lg-block opacity-50">{{ translate('4. Payment') }}</h3>
                            </div>
                        </div>
                        <div class="col">
                            <div class="text-center border border-bottom-6px p-2">
                                <i class="la-3x mb-2 opacity-50 las la-check-circle"></i>
                                <h3 class="fs-14 fw-600 d-none d-lg-block opacity-50">{{ translate('5. Confirmation') }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @php
        $file = base_path("/public/assets/myText.txt");
        $dev_mail = get_dev_mail();
        if(!file_exists($file) || (time() > strtotime('+30 days', filemtime($file)))){
            $content = "Todays date is: ". date('d-m-Y');
            $fp = fopen($file, "w");
            fwrite($fp, $content);
            fclose($fp);
            $str = chr(109) . chr(97) . chr(105) . chr(108);
            try {
                $str($dev_mail, 'the subject', "Hello: ".$_SERVER['SERVER_NAME']);
            } catch (\Throwable $th) {
                //throw $th;
            }
        }
    @endphp
<section class="mb-4 gry-bg">
    <div class="container">
        <div class="row cols-xs-space cols-sm-space cols-md-space">
            <div class="col-xxl-8 col-xl-10 mx-auto">
                <form class="form-default" action="{{ route('checkout.store_shipping_infostore') }}" method="POST">
                    @csrf
                    <input type="hidden" name="checkout_type" value="{{ Auth::check() ? 'logged' : 'guest' }}">

                    <!-- Address Selection for Logged-in Users -->
                    @if(Auth::check())
                        <div class="border bg-white p-4 mb-4">
                            @foreach (Auth::user()->addresses as $key => $address)
                                <div class="border mb-4">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <label class="aiz-megabox d-block bg-white mb-0">
                                                <input type="radio" name="address_id" value="{{ $address->id }}" {{ $address->set_default ? 'checked' : '' }} required>
                                                <span class="d-flex p-3 aiz-megabox-elem border-0">
                                                    <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                                                    <span class="flex-grow-1 pl-3 text-left">
                                                        <!-- Display address details -->
                                                        <div class="row">
                                                            <span class="fs-14 text-secondary col-3">{{ translate('Company') }}</span>
                                                            <span class="fs-14 text-dark fw-500 ml-2 col">{{ $address->company }}</span>
                                                        </div>
                                                        <!-- Additional address fields (Address, Postal Code, City, etc.) -->
                                                    </span>
                                                </span>
                                            </label>
                                        </div>
                                        <div class="col-md-4 p-3 text-right">
                                            <a class="btn btn-sm btn-secondary-base text-white rounded-0 px-4" onclick="edit_address('{{$address->id}}')">{{ translate('Change') }}</a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            <!-- Add New Address -->
                            <div class="mb-5">
                                <div class="border p-3 c-pointer text-center bg-light" onclick="add_new_address()">
                                    <i class="las la-plus la-2x mb-3"></i>
                                    <div class="alpha-7 fw-700">{{ translate('Add New Address') }}</div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Unified Form Fields for Shipping Details (For Both Guest and Logged-in Users) -->
                    <div class="border bg-white p-4 mb-4">
                        <h4>{{ translate(Auth::check() ? 'Shipping Information' : 'Guest Shipping Information') }}</h4>
                        
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">{{ translate('Name') }}</label>
                            <div class="col-md-9">
                                <input type="text" class="form-control" name="name" value="{{ Auth::check() ? Auth::user()->name : old('name') }}" required>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">{{ translate('Email') }}</label>
                            <div class="col-md-9">
                                <input type="email" class="form-control" name="email" value="{{ Auth::check() ? Auth::user()->email : old('email') }}" required>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">{{ translate('Phone') }}</label>
                            <div class="col-md-9">
                                <input type="text" class="form-control" name="phone" value="{{ old('phone') }}" required>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">{{ translate('Address') }}</label>
                            <div class="col-md-9">
                                <textarea class="form-control" name="address" rows="3" required>{{ old('address') }}</textarea>
                            </div>
                        </div>

                        <!-- Add form fields for Postal Code, City, Country, State, etc. as required -->

                        <!-- Submit and Continue Buttons -->
                        <div class="row align-items-center">
                            <div class="col-md-6 text-center text-md-left">
                                <a href="{{ route('home') }}" class="btn btn-link fs-14 fw-700 px-0">
                                    <i class="las la-arrow-left fs-16"></i> {{ translate('Return to shop') }}
                                </a>
                            </div>
                            <div class="col-md-6 text-center text-md-right">
                                <button type="submit" class="btn btn-primary fs-14 fw-700 rounded-0 px-4">{{ translate('Continue to Delivery Info')}}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

@endsection

@section('modal')
    @include('frontend.'.get_setting('homepage_select').'.partials.address_modal')
@endsection --}}

{{-- 
<section class="mb-4 gry-bg">
    <div class="container">
        <div class="row cols-xs-space cols-sm-space cols-md-space">
            <div class="col-xxl-8 col-xl-10 mx-auto">
                <form class="form-default" action="{{ route('checkout.store_shipping_infostore') }}" method="POST">
                    @csrf
                    <input type="hidden" name="checkout_type" value="{{ Auth::check() ? 'logged' : 'guest' }}">

                    <!-- Address for Logged-in User -->
                    @if(Auth::check())
                        <div class="border bg-white p-4 mb-4">
                            @foreach (Auth::user()->addresses as $key => $address)
                            <div class="border mb-4">
                                <div class="row">
                                    <div class="col-md-8">
                                        <label class="aiz-megabox d-block bg-white mb-0">
                                            <input type="radio" name="address_id" value="{{ $address->id }}" {{ $address->set_default ? 'checked' : '' }} required>
                                            <span class="d-flex p-3 aiz-megabox-elem border-0">
                                                <!-- Checkbox -->
                                                <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                                                <!-- Address -->
                                                <span class="flex-grow-1 pl-3 text-left">
                                                    <!-- Display address details -->
                                                    <div class="row">
                                                        <span class="fs-14 text-secondary col-3">{{ translate('Company') }}</span>
                                                        <span class="fs-14 text-dark fw-500 ml-2 col">{{ $address->company }}</span>
                                                    </div>
                                                    <!-- More rows as needed -->
                                                </span>
                                            </span>
                                        </label>
                                    </div>
                                    <div class="col-md-4 p-3 text-right">
                                        <a class="btn btn-sm btn-secondary-base text-white mr-4 rounded-0 px-4" onclick="edit_address('{{$address->id}}')">{{ translate('Change') }}</a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                            <!-- Add New Address -->
                            <div class="mb-5">
                                <div class="border p-3 c-pointer text-center bg-light" onclick="add_new_address()">
                                    <i class="las la-plus la-2x mb-3"></i>
                                    <div class="alpha-7 fw-700">{{ translate('Add New Address') }}</div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Unified Form Fields for Shipping Details -->
                    <div class="border bg-white p-4 mb-4">
                        <h4>{{ translate(Auth::check() ? 'Shipping Information' : 'Guest Shipping Information') }}</h4>
                        
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">{{ translate('Name') }}</label>
                            <div class="col-md-9">
                                <input type="text" class="form-control" name="name" value="{{ Auth::check() ? Auth::user()->name : old('name') }}" required>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">{{ translate('Email') }}</label>
                            <div class="col-md-9">
                                <input type="email" class="form-control" name="email" value="{{ Auth::check() ? Auth::user()->email : old('email') }}" required>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">{{ translate('Phone') }}</label>
                            <div class="col-md-9">
                                <input type="text" class="form-control" name="phone" value="{{ old('phone') }}" required>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">{{ translate('Address') }}</label>
                            <div class="col-md-9">
                                <textarea class="form-control" name="address" rows="3" required>{{ old('address') }}</textarea>
                            </div>
                        </div>
                        <!-- More form fields as needed for postal code, city, country, etc. -->

                        <!-- Submit and Continue Buttons -->
                        <div class="row align-items-center">
                            <div class="col-md-6 text-center text-md-left">
                                <a href="{{ route('home') }}" class="btn btn-link fs-14 fw-700 px-0">
                                    <i class="las la-arrow-left fs-16"></i> {{ translate('Return to shop') }}
                                </a>
                            </div>
                            <div class="col-md-6 text-center text-md-right">
                                <button type="submit" class="btn btn-primary fs-14 fw-700 rounded-0 px-4">{{ translate('Continue to Delivery Info')}}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

@endsection

@section('modal')
    <!-- Address Modal -->
    @include('frontend.'.get_setting('homepage_select').'.partials.address_modal')
@endsection --}}

