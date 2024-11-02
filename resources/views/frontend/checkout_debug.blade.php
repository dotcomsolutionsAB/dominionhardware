<!-- resources/views/frontend/checkout_debug.blade.php -->

@extends('frontend.layouts.app')

@section('content')
    <div class="container mt-5">
        <h2>Checkout Debug Information</h2>
        
        @if(isset($errors) && $errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(isset($message))
            <div class="alert alert-info">{{ $message }}</div>
        @endif

        @if(isset($combined_order_id))
            <div class="alert alert-success">
                Combined Order ID: {{ $combined_order_id }}
            </div>
        @else
            <div class="alert alert-danger">
                Combined Order ID not set in session.
            </div>
        @endif

        <h4>Session Data</h4>
        <pre>{{ print_r($session_data, true) }}</pre>
    </div>
@endsection
