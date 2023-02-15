<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Request for Credit Check on {{ $customer_name }}</title>
</head>

<body>
    <p>Hello, </p>
    <p>
        A request has been made to perform a credit check on {{ $customer_name }}, who has applied for
    </p>

    <ul style="list-style: none;">
        <li>Product Name: {{ $product_name }}</li>
        <li>Product Price: {{ $product_price }}</li>

        <li></li>
        
        <li>Vendor Name: {{ $vendor_name }}</li>
        <li>Vendor Phone Number: {{ $vendor_phone_number }}</li>

        <li></li>

        <li>Customer Name: {{ $customer_name }}</li>
        <li>Customer Phone Number: {{ $customer_phone_number }}</li>
        
        <li></li>
    </ul>
    <p>
        To access the customer's information, please use the following link <a href="{{ $url }}"
            target="_blank">View Pending
            Credit Check</a>
    </p>
    <p>
        If you require any further information, please do not hesitate to contact the technology team.
    </p>
    <p>Sincerely, </p>
    <p>Altara Team</p>
</body>

</html>
