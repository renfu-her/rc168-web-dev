<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Image Upload with QR Code</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div class="container mt-5">
        <div>
            <input type="file" accept="image/*" multiple id="pictureSelect" />
        </div>
        <div>
            <span>QR 內容：</span>
            <a id="result"></a>
        </div>
        <div>
            <canvas id="qrCanvas"></canvas>
        </div>

        <script>
            $(function() {
                $("#pictureSelect").change(function(event) {
                    var file = event.target.files[0];
                    if (window.FileReader) {
                        var fileReader = new FileReader();
                        fileReader.readAsDataURL(file);
                        fileReader.onloadend = function(event) {
                            var base64Data = event.target.result;
                            base64ToqR(base64Data)
                        }
                    }

                })
            });

            function base64ToqR(data) {
                var objCanvas = document.getElementById("qrCanvas");
                var ctx = objCanvas.getContext("2d"); // 返回值是CanvasRenderingContext2D类的对象实例。

                var image = new Image();
                image.src = data;

                image.onload = function() {
                    ctx.drawImage(image, 0, 0, image.width, image.height); // 绘图

                    var imageData = ctx.getImageData(0, 0, image.width, image.height);

                    // QR码解析
                    const code = jsQR(
                        imageData.data, // 图像数据
                        imageData.width, // 宽度
                        imageData.height, // 高度
                        {
                            // 可选的对象
                            inversionAttempts: "dontInvert",
                        }
                    );

                    if (code) {
                        $("#result")[0].innerHTML = code.data;
                    } else {
                        $("#result")[0].innerHTML = "";
                        alert("识别错误")
                    }
                };
            }
        </script>
    </div>
    <script src="{{ asset('css/jsQR.js') }}"></script>
</body>

</html>
