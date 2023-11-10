<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    
    <form action="{{ $result['EcpayURL'] }}" method="post" id="ecpay-post">
        <input type="hidden" name="MerchantID" value="{{ $result['MerchantID'] }}">
        <input type="hidden" name="MerchantTradeNo" value="{{ $result['MerchantTradeNo'] }}">
        <input type="hidden" name="MerchantTradeDate" value="{{ $result['MerchantTradeDate'] }}">
        <input type="hidden" name="PaymentType" value="{{ $result['PaymentType'] }}">
        <input type="hidden" name="TotalAmount" value="{{ $result['TotalAmount'] }}">
        <input type="hidden" name="TradeDesc" value="{{ $result['TradeDesc'] }}">
        <input type="hidden" name="ItemName" value="{{ $result['ItemName'] }}">
        <input type="hidden" name="ReturnURL" value="{{ $result['ReturnURL'] }}">
        <input type="hidden" name="OrderResultURL" value="{{ $result['OrderResultURL'] }}">
        <input type="hidden" name="ChoosePayment" value="{{ $result['ChoosePayment'] }}">
        <input type="hidden" name="EncryptType" value="{{ $result['EncryptType'] }}">
        <input type="hidden" name="CheckMacValue" value="{{ $result['CheckMacValue'] }}">
    </form>

    <script>
        // You could also use JavaScript to submit the form after the page is fully loaded
        window.addEventListener('load', function() {
            document.getElementById('ecpay-post').submit();
        });
    </script>
</body>
</html>