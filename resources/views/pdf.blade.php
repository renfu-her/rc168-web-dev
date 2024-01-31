<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>PDF</title>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.6.347/pdf.min.js"
        integrity="sha512-Z8CqofpIcnJN80feS2uccz+pXWgZzeKxDsDNMD/dJ6997/LSRY+W4NmEt9acwR+Gt9OHN0kkI1CTianCwoqcjQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

</head>

<body>
    <script>
        var url = "{{ asset('pdf/Eg01.pdf') }}";
        PDFJS.workerSrc = "//mozilla.github.io/pdf.js/build/pdf.worker.js";
        var loadingTask = PDFJS.getDocument(url);
        loadingTask.promise.then(
            function(pdf) {
                console.log("PDF loaded");
                var pageNumber = 1;
                pdf.getPage(pageNumber).then(function(page) {
                    console.log("Page loaded");
                    var scale = 1.5;
                    var viewport = page.getViewport(scale);
                    var canvas = document.getElementById("example");
                    var context = canvas.getContext("2d");
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;
                    var renderContext = {
                        canvasContext: context,
                        viewport: viewport
                    };
                    var renderTask = page.render(renderContext);
                    renderTask.then(function() {
                        console.log("Page rendered");
                    });
                });
            },
            function(reason) {
                console.error(reason);
            }
        );
    </script>
</body>

</html>
