<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    
    <form action="{{ $result->EcpayURL }}" method="post" id="ecpay-post">
        <input type="hidden" name="MerchantID" value="{{ $result->MerchantID }}">
    </form>

    <script>
        // You could also use JavaScript to submit the form after the page is fully loaded
        // window.addEventListener('load', function() {
        //     document.getElementById('ecpay-post').submit();
        // });
    </script>
</body>
</html>