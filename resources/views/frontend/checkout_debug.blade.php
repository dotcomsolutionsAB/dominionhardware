<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout Debug</title>
</head>
<body>
    <h1>Checkout Debug Information</h1>
    
    <h2>Message:</h2>
    <p>{{ $message }}</p>

    <h2>Combined Order ID:</h2>
    <p>{{ $combined_order_id }}</p>

    <h2>Session Data:</h2>
    <pre>{{ print_r($session_data, true) }}</pre>

    @if(isset($errors) && count($errors) > 0)
        <h2>Errors:</h2>
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif
</body>
</html>
