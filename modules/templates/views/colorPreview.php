<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Template submission : Preview</title>
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
<div class="row">
    <div class="col">
        <div class="d-flex justify-content-between">
            <h4><i class="bi bi-palette"></i>Template Color Preview</h4>
        </div>
    </div>
</div>

<div class="template-hero d-flex">
    <div class="py-3 w-100">
        <div id="templates" class="row row-cols-2 row-cols-md-4 g-3 g-lg-4 mt-2">
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
</div>
<script src="../assets/libs/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>