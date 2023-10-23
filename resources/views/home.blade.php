<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>

<body>

    <div class="container">
        <form action="https://api-dev.besttour.com.tw/api/payment/line-pay/receiver" method="post">
            <div class="col-12">
                <div class="mb-3 row">
                    <label for="staticEmail" class="col-sm-2 col-form-label">orderNo</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control-plaintext" name="orderNo"
                            value="473194">
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="inputPassword" class="col-sm-2 col-form-label">orderType</label>
                    <div class="col-sm-10">
                        <input type="number" class="form-control" name="orderType" value="1">
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="inputPassword" class="col-sm-2 col-form-label">amounts</label>
                    <div class="col-sm-10">
                        <input type="number" class="form-control" name="amounts" value="1000">
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="inputPassword" class="col-sm-2 col-form-label">description</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="description" value="測試測試">
                    </div>
                </div>
                <button type="submit" class="btn btn-info">Submit</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
</body>

</html>
