@extends('frontend.layouts.app')

@section('content')
    <section class="my-4 gry-bg">
        <div class="container">
            <div class="row cols-xs-space cols-sm-space cols-md-space">
                <div class="col-xxl-8 col-xl-10 mx-auto">
                    <form class="form-default" action="{{ route('payment.checkout') }}" method="POST" id="checkout-form">
                        @csrf
                        <div class="accordion" id="accordioncCheckoutInfo">

                            <!-- Shipping Info -->
                            <div class="card rounded-0 border shadow-none mb-4">
                                <div class="card-header py-3" id="headingShippingInfo" type="button" data-toggle="collapse" data-target="#collapseShippingInfo" aria-expanded="true">
                                    <div class="d-flex align-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20">
                                            <path d="M58,48A10,10,0,1,0,68,58,10,10,0,0,0,58,48ZM56.457,61.543a.663.663,0,0,1-.423.212.693.693,0,0,1-.428-.216l-2.692-2.692.856-.856,2.269,2.269,6-6.043.841.87Z" transform="translate(-48 -48)" fill="#9d9da6"/>
                                        </svg>
                                        <span class="ml-2 fs-19 fw-700">{{ translate('Shipping Info') }}</span>
                                    </div>
                                    <i class="las la-angle-down fs-18"></i>
                                </div>
                                <div id="collapseShippingInfo" class="collapse show" aria-labelledby="headingShippingInfo">
                                    <div class="card-body" id="shipping_info">
                                        @include('frontend.shipping_info', ['address_id' => $address_id ?? null])
                                        {{ dd($address_id) }}

                                    </div>
                                </div>
                            </div>

                            <!-- Delivery Info -->
                            <div class="card rounded-0 border shadow-none mb-4">
                                <div class="card-header py-3" id="headingDeliveryInfo" type="button" data-toggle="collapse" data-target="#collapseDeliveryInfo" aria-expanded="true">
                                    <div class="d-flex align-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20">
                                            <path d="M58,48A10,10,0,1,0,68,58,10,10,0,0,0,58,48ZM56.457,61.543a.663.663,0,0,1-.423.212.693.693,0,0,1-.428-.216l-2.692-2.692.856-.856,2.269,2.269,6-6.043.841.87Z" transform="translate(-48 -48)" fill="#9d9da6"/>
                                        </svg>
                                        <span class="ml-2 fs-19 fw-700">{{ translate('Delivery Info') }}</span>
                                    </div>
                                    <i class="las la-angle-down fs-18"></i>
                                </div>
                                <div id="collapseDeliveryInfo" class="collapse show" aria-labelledby="headingDeliveryInfo">
                                    <div class="card-body" id="delivery_info">
                                        @include('frontend.delivery_info', ['carts' => $carts ?? null, 'carrier_list' => $carrier_list ?? null, 'shipping_info' => $shipping_info ?? null])
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Info -->
                            <div class="card rounded-0 border shadow-none mb-0">
                                <div class="card-header py-3" id="headingPaymentInfo" type="button" data-toggle="collapse" data-target="#collapsePaymentInfo" aria-expanded="true">
                                    <div class="d-flex align-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20">
                                            <path d="M58,48A10,10,0,1,0,68,58,10,10,0,0,0,58,48ZM56.457,61.543a.663.663,0,0,1-.423.212.693.693,0,0,1-.428-.216l-2.692-2.692.856-.856,2.269,2.269,6-6.043.841.87Z" transform="translate(-48 -48)" fill="#9d9da6"/>
                                        </svg>
                                        <span class="ml-2 fs-19 fw-700">{{ translate('Payment') }}</span>
                                    </div>
                                    <i class="las la-angle-down fs-18"></i>
                                </div>
                                <div id="collapsePaymentInfo" class="collapse show" aria-labelledby="headingPaymentInfo">
                                    <div class="card-body" id="payment_info">
                                        @include('frontend.payment_select', ['carts' => $carts ?? null, 'total' => $total ?? null])
                                        <div class="pt-2">
                                            <label class="aiz-checkbox">
                                                <input type="checkbox" required id="agree_checkbox" onchange="stepCompletionPaymentInfo()">
                                                <span class="aiz-square-check"></span>
                                                <span>{{ translate('I agree to the') }}</span>
                                            </label>
                                            <a href="{{ route('terms') }}" class="fw-700">{{ translate('terms and conditions') }}</a>,
                                            <a href="{{ route('returnpolicy') }}" class="fw-700">{{ translate('return policy') }}</a> &
                                            <a href="{{ route('privacypolicy') }}" class="fw-700">{{ translate('privacy policy') }}</a>
                                        </div>
                                        <div class="row align-items-center pt-3">
                                            <div class="col-6">
                                                <a href="{{ route('home') }}" class="btn btn-link fs-14 fw-700 px-0">
                                                    <i class="las la-arrow-left fs-16"></i>
                                                    {{ translate('Return to shop') }}
                                                </a>
                                            </div>
                                            <div class="col-6 text-right">
                                                <button type="button" onclick="submitOrder(this)" id="submitOrderBtn" class="btn btn-primary fs-14 fw-700">{{ translate('Complete Order') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Cart Summary -->
                <div class="col-lg-4 mt-lg-0 mt-4" id="cart_summary">
                    @include('frontend.'.get_setting('homepage_select').'.partials.cart_summary', ['proceed' => 0, 'carts' => $carts ?? null])
                </div>
            </div>
        </div>
    </section>
@endsection

@section('modal')
    @if(Auth::check())
        @include('frontend.partials.address.address_modal')
    @endif
@endsection

@section('script')
    <script type="text/javascript">
        // Your JavaScript and jQuery code goes here
    </script>
@endsection
