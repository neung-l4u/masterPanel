<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../assets/libs/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/libs/bootstrap-5.3.3-dist/bootstrap-icons-1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

    <title>L4U Template Color Preview</title>
    <style>
        /* ---- Design tokens (hook into your color system as needed) ---- */
        :root{
            --surface: #ffffff;
            --ink-600: #1f2937;
            --ink-400: #6b7280;
            --brand: var(--brand, #2563eb);          /* falls back if not defined globally */
            --brand-600: var(--brand-600, #1e40af);
            --ring: rgba(37, 99, 235, .35);
            --shadow: 0 6px 20px rgba(0,0,0,.08);
            --radius: 16px;
        }

        .navbar {position: absolute; top: 0; width: 100%;}

        .template-hero{
            min-height: 100vh;
        }

        /* ---- Template Card ---- */
        .template-card{
            display: block;
            text-decoration: none;
            color: inherit;
            background: var(--surface);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            transition: transform .18s ease, box-shadow .18s ease;
            position: relative;
            overflow: hidden;
            outline: none;
        }
        .template-card:focus-visible{
            box-shadow: var(--shadow), 0 0 0 6px var(--ring);
            transform: translateY(-2px);
        }
        .template-card:hover{
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(0,0,0,.12);
        }

        .template-thumb{
            background: #0b1220;
        }
        .template-thumb > img{
            width: 100%;
            height: 400px;
            object-fit: cover;
            object-position: top;
            display: block;
        }

        .template-meta{
            padding: .9rem 1rem 1.1rem;
            text-align: center;
        }

        .template-title{
            display:block;
            font-weight: 700;
            color: var(--ink-600);
            line-height: 1.15;
        }

        /* spacing helper for larger screens */
        @media (min-width: 992px){
            .py-lg-6{ padding-top: 4.5rem!important; padding-bottom: 4.5rem!important; }
        }
    </style>
</head>
<body style="min-height: 100vh;">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
        </div>
    </nav>

    <div class="container">
        <main>
            <section class="template-hero d-flex align-items-center">
            <div class="container py-3">
                <div class="row align-items-center g-4">
                <!-- Left Content -->
                    <div class="col-sm-12 col-md-7 col-lg-10 text-center text-md-start mb-4 mb-md-0">
                        <h3 class="display-4">Local For You</h3>
                        <h1 class="display-4 fw-bold">Template Color Preview</h1>
                        <p class="text-muted mt-3">
                        Explore and preview color palettes for our website templates. Experiment with different hues, tones, and moods — and see how each palette transforms the look and feel of your design.
                        </p>
                    </div>

                    <!-- Right Image -->
                    <div class="col-sm-12 col-md-5 col-lg-2 text-center">
                        <img src="../assets/img/l4u-feedback.png" alt="l4u-feedback" class="w-100 hero-img">
                    </div>
                </div>

                <!-- Templates Grid -->
                <div id="templates" class="row row-cols-2 row-cols-md-4 g-3 g-lg-4 mt-4 mt-lg-5">
                <div class="col">
                    <a href="customRes1.php" class="template-card" aria-label="Open Restaurant Template 1">
                    <div class=" template-thumb">
                        <img src="../assets/img/Res1Home-min.png" alt="Restaurant 1 preview" loading="lazy" decoding="async">
                    </div>
                    <div class="template-meta">
                        <span class="template-title">Restaurant - 1</span>
                    </div>
                    </a>
                </div>

                <div class="col pt-5">
                    <a href="customRes2.php" class="template-card" aria-label="Open Restaurant Template 2">
                    <div class=" template-thumb">
                        <img src="../assets/img/Res2Home-min.png" alt="Restaurant 2 preview" loading="lazy" decoding="async">
                    </div>
                    <div class="template-meta">
                        <span class="template-title">Restaurant - 2</span>
                    </div>
                    </a>
                </div>

                <div class="col">
                    <a href="customRes3.php" class="template-card" aria-label="Open Restaurant Template 3">
                    <div class=" template-thumb">
                        <img src="../assets/img/Res3Home-min.png" alt="Restaurant 3 preview" loading="lazy" decoding="async">
                    </div>
                    <div class="template-meta">
                        <span class="template-title">Restaurant - 3</span>
                    </div>
                    </a>
                </div>

                <div class="col pt-5">
                    <a href="customMas1.php" class="template-card" aria-label="Open Massage Template 1">
                    <div class=" template-thumb">
                        <img src="../assets/img/Mas1Home-min.png" alt="Massage 1 preview" loading="lazy" decoding="async">
                    </div>
                    <div class="template-meta">
                        <span class="template-title">Massage - 1</span>
                    </div>
                    </a>
                </div>
                </div>
            </div>
            </section>
        </main>
    </div><!-- container-->

<script src="../assets/libs/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>