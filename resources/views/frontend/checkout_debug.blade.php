<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout Debug</title>
</head>
<body>
    <h1>Checkout Debug Information</h1>
    
    <h2>Message:</h2>
    <p>{{ $message ?? 'No message available' }}</p>

    <h2>Combined Order ID:</h2>
    <p>{{ $combined_order_id ?? 'N/A' }}</p>

    <h2>Session Data:</h2>
    <pre>{!! print_r($session_data ?? 'No session data available', true) !!}</pre>

    @if(isset($errors) && $errors->count() > 0)
        <h2>Errors:</h2>
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @else
        <p>No errors found.</p>
    @endif
</body>
</html>
