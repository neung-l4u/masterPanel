<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-LGKDYHL23T"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-LGKDYHL23T');
    </script>
    <meta charset="UTF-8">
    <title>Fake Address Generator</title>
    <link href="../assets/libs/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="../assets/libs/jQuery-v3.7.1/jquery-3.7.1.min.js"></script>
</head>
<body>

    <main class="container py-5">
        <section class="container" style="min-height: 50vh;">
            <h3 class="mb-4"><i class="bi bi-people"></i>   Generate Fake Name</h3>
            <!-- Radio buttons -->
            <div class="mb-3">
                <label class="form-label fw-bold">Select Country</label><br>
                <?php
                $countries = ['Australia', 'New Zealand', 'USA', 'UK', 'Canada', 'Thailand'];
                foreach ($countries as $i => $country) {
                    echo '<div class="form-check form-check-inline">';
                    echo '<input class="form-check-input" onClick="genName();" type="radio" name="country" id="country_' . $i . '" value="' . $country . '"' . ($i === 0 ? ' checked' : '') . '>';
                    echo '<label class="form-check-label" for="country_' . $i . '">' . $country . '</label>';
                    echo '</div>';
                }
                ?>
            </div>

            <!-- Generate Button -->
            <button id="generateBtn" class="btn btn-primary mb-3">Generate</button>

            <!-- Result Area -->
            <textarea aria-label="result" id="resultText" class="form-control" rows="2" readonly></textarea>
            <div id="statusMsg" class="text-success fw-bold m-2" style="display:none; float: right;">Copied to clipboard!</div>

        </section>
        <script>
            function genName() {
                const country = $('input[name="country"]:checked').val();

                $.ajax({
                    url: '../assets/API/generate_name.php',
                    type: 'POST',
                    data: { country: country },
                    success: function (data) {
                        $('#resultText').val(data).select();
                        document.execCommand("copy");
                        $("#statusMsg").fadeIn().delay(1000).fadeOut();
                    },
                    error: function () {
                        alert('Error generating name.');
                    }
                });
            }//genName

            $('#generateBtn').on('click', function () {
                genName();
            });
        </script>


</body>
</html>