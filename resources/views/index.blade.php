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
        <form action="/upload" method="post" enctype="multipart/form-data" class="row g-3" id="upload-form">
            @csrf
            <div class="col-auto">
                <label for="image" class="form-label">選擇圖片</label>
                <input type="file" class="form-control" name="image" id="image" accept="image/*" capture="camera">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary mb-3">上傳</button>
            </div>
        </form>
    </div>

    <script src="{{ asset('css/jsQR.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#image').on('change', function(e) {
                const file = e.target.files[0];

                reader.onload = function(event) {
                    const image = new Image();
                    image.onload = function() {
                        const canvas = document.createElement('canvas');
                        canvas.width = image.width;
                        canvas.height = image.height;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(image, 0, 0);
                        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                        const code = jsQR(imageData.data, imageData.width, imageData.height);
                        if (code) {
                            console.log("Found QR code", code.data);
                            // 你可以在這裡添加更多的邏輯，例如將 QR code 的數據添加到表單中
                        }
                    };
                    image.src = event.target.result;
                };

                reader.readAsDataURL(file);
            });

            $('#upload-form').on('submit', function(e) {
                // 在這裡，你可以添加更多的上傳邏輯
            });
        });
    </script>
</body>
</html>
