<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fake Address Generator</title>
    <link href="../assets/libs/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="../assets/libs/jQuery-v3.7.1/jquery-3.7.1.min.js"></script>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php"><i class="bi bi-wrench"></i> Tools</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarText">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" aria-current="page" href="index.php">Case Converter</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="#">Fake Address</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="name.php">Fake Name</a>
                </li>
            </ul>
            <span class="navbar-text">
                logout
            </span>
        </div>
    </div>
</nav>

<div class="container py-5">

    <header>
        <nav class="mb-4" style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Fake Name Generate</li>
            </ol>
        </nav>
    </header>

    <main>
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