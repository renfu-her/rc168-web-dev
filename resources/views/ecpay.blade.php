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
        <input name="MerchantID" value="{{ $resArray['MerchantID'] }}">
        <input name="MerchantTradeNo" value="{{ $resArray['MerchantTradeNo'] }}">
        <input name="MerchantTradeDate" value="{{ $resArray['MerchantTradeDate'] }}">
        <input name="PaymentType" value="{{ $resArray['PaymentType'] }}">
        <input name="TotalAmount" value="{{ $resArray['TotalAmount'] }}">
        <input name="TradeDesc" value="{{ $resArray['TradeDesc'] }}">
        <input name="ItemName" value="{{ $resArray['ItemName'] }}">
        <input name="ReturnURL" value="{{ $resArray['ReturnURL'] }}">
        <input name="OrderResultURL" value="{{ $resArray['OrderResultURL'] }}">
        <input name="ChoosePayment" value="{{ $resArray['ChoosePayment'] }}">
        <input name="EncryptType" value="{{ $resArray['EncryptType'] }}">
        <input name="CheckMacValue" value="{{ $resArray['CheckMacValue'] }}">
        <input name="NeedExtraPaidInfo" value="{{ $resArray['NeedExtraPaidInfo'] }}">
        <button type="submit">送出</button> 
    </form>

    <script>
        window.addEventListener('load', function() {
            // document.getElementById('ecpay-post').submit();
        });
    </script>
</body>
</html>