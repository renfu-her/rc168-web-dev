<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    
    <form action="{{ $data[''] }}" method="post">
        <input type="hidden" name="MerchantID" value="">
        <input type="hidden" name="MerchantTradeNo" value="">
        <input type="hidden" name="MerchantTradeDate" value="">
        <input type="hidden" name="PaymentType" value="">
        <input type="hidden" name="TotalAmount" value="">
        <input type="hidden" name="TradeDesc" value="">
        <input type="hidden" name="ItemName" value="">
        <input type="hidden" name="ReturnURL" value="">
        <input type="hidden" name="OrderResultURL" value="">
        <input type="hidden" name="ChoosePayment" value="">
        <input type="hidden" name="EncryptType" value="">
        <input type="hidden" name="CheckMacValue" value="">
    </form>
</body>
</html>