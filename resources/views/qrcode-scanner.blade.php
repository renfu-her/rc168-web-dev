<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>QRCode</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="{{ asset('js/html5-qrcode.min.js') }}"></script>

</head>

<body>

    <video id="video" width="100%" height="auto"></video>
    <div id="qrCodeResult"></div>

    <script>
        $(document).ready(function() {
            // 獲取相機視頻元素和 QR Code 結果元素
            // 獲取相機視頻元素和 QR Code 結果元素
            const videoElement = $('#video')[0];
            const qrCodeResultElement = $('#qrCodeResult');

            // 初始化 html5-qrcode
            const html5QrCode = new Html5Qrcode();

            // 啟動相機並讀取 QR Code
            html5QrCode.start({
                videoId: 'video',
                qrCodeSuccessCallback: onScanSuccess,
                qrCodeErrorCallback: onScanError
            }, {
                fps: 10,
                qrbox: 250
            });

            // 處理讀取到的 QR Code
            function onScanSuccess(qrCodeMessage) {
                console.log(qrCodeMessage);
                // 根據需要處理 QR Code 訊息的邏輯
            }

            // 處理相機讀取錯誤
            function onScanError(error) {
                console.error(error);
            }
        });
    </script>
</body>

</html>
