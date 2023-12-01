<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    
    <form action="{{ $resArray['EcpayURL'] }}" method="post" id="ecpay-post">
        <input type="hidden" name="MerchantID" value="{{ $resArray['MerchantID'] }}">
        <input type="hidden" name="MerchantTradeNo" value="{{ $resArray['MerchantTradeNo'] }}">
        <input type="hidden" name="MerchantTradeDate" value="{{ $resArray['MerchantTradeDate'] }}">
        <input type="hidden" name="PaymentType" value="{{ $resArray['PaymentType'] }}">
        <input type="hidden" name="TotalAmount" value="{{ $resArray['TotalAmount'] }}">
        <input type="hidden" name="TradeDesc" value="{{ $resArray['TradeDesc'] }}">
        <input type="hidden" name="ItemName" value="{{ $resArray['ItemName'] }}">
        <input type="hidden" name="ReturnURL" value="{{ $resArray['ReturnURL'] }}">
        <input type="hidden" name="OrderResultURL" value="{{ $resArray['OrderResultURL'] }}">
        <input type="hidden" name="ChoosePayment" value="{{ $resArray['ChoosePayment'] }}">
        <input type="hidden" name="EncryptType" value="{{ $resArray['EncryptType'] }}">
        <input type="hidden" name="CheckMacValue" value="{{ $resArray['CheckMacValue'] }}">
        <input type="hidden" name="NeedExtraPaidInfo" value="{{ $resArray['NeedExtraPaidInfo'] }}">
        <button type="submit">Send</button>
    </form>

    <script>
        window.addEventListener('load', function() {
            // document.getElementById('ecpay-post').submit();
        });
    </script>
</body>
</html>