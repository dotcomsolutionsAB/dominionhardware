@extends('frontend.layouts.app')

@section('content')
<section class="order-summary-section">
    <div class="container">
        <h3>{{ translate('Order Summary') }}</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>{{ translate('Product') }}</th>
                    <th>{{ translate('Quantity') }}</th>
                    <th>{{ translate('Price') }}</th>
                    <th>{{ translate('Total') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($carts as $cartItem)
                    <tr>
                        <td>{{ $cartItem->product->name }}</td>
                        <td>{{ $cartItem->quantity }}</td>
                        <td>{{ single_price($cartItem->price) }}</td>
                        <td>{{ single_price($cartItem->quantity * $cartItem->price) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="order-totals">
            <p>{{ translate('Subtotal') }}: {{ single_price($subtotal) }}</p>
            <p>{{ translate('Tax') }}: {{ single_price($tax) }}</p>
            <p>{{ translate('Shipping') }}: {{ single_price($shipping) }}</p>
            <h4>{{ translate('Total') }}: {{ single_price($total) }}</h4>
        </div>

        <form action="{{ route('payment.checkout') }}" method="POST">
            @csrf
            <input type="hidden" name="payment_option" value="{{ $request->payment_option }}">
            <button type="submit" class="btn btn-primary">{{ translate('Confirm Order') }}</button>
        </form>
    </div>
</section>
@endsection
