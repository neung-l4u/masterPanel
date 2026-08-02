<head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>Template submission : Customized Template</title>
     <style>
     /* ---- Design tokens (hook into your color system as needed) ---- */
     :root {
          --surface: #ffffff;
          --ink-600: #1f2937;
          --ink-400: #6b7280;
          --brand: var(--brand, #2563eb);
          /* falls back if not defined globally */
          --brand-600: var(--brand-600, #1e40af);
          --ring: rgba(37, 99, 235, .35);
          --shadow: 0 6px 20px rgba(0, 0, 0, .08);
          --radius: 16px;
     }

     /* ---- Template Card ---- */
     .template-card {
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

     .template-card:focus-visible {
          box-shadow: var(--shadow), 0 0 0 6px var(--ring);
          transform: translateY(-2px);
     }

     .template-card:hover {
          transform: translateY(-4px);
          box-shadow: 0 12px 32px rgba(0, 0, 0, .12);
     }

     .template-thumb {
          background: #0b1220;
     }

     .template-thumb>img {
          width: 100%;
          height: 400px;
          object-fit: cover;
          object-position: top;
          display: block;
     }

     .template-meta {
          padding: .9rem 1rem 1.1rem;
          text-align: center;
     }

     .template-title {
          display: block;
          font-weight: 700;
          color: var(--ink-600);
          line-height: 1.15;
     }

     /* spacing helper for larger screens */
     @media (min-width: 992px) {
          .py-lg-6 {
               padding-top: 4.5rem !important;
               padding-bottom: 4.5rem !important;
          }
     }
     </style>
</head>
<div class="row">
     <div class="col">
          <div class="d-flex justify-content-between">
               <h4><i class="bi bi-file-richtext"></i> Customized Template</h4>
          </div>
     </div>
</div>

<div class="template-hero d-flex justify-content-center py-4">
     <div class="w-100" style="max-width: 1200px;">
          <div id="templates" class="row row-cols-2 row-cols-md-4 g-3 g-lg-4 justify-content-center">
               <?php
               $customizedTemplates = ['1', '1.2', '2', '3', '4', '5', '5.5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '17', '18'];
               foreach ($customizedTemplates as $tpl) {
                    ?>
               <div class="col">
                    <a href="customized-template/Template<?php echo $tpl; ?>.html" class="template-card"
                         aria-label="Open Template <?php echo $tpl; ?>">
                         <div class="template-thumb">
                              <img src="../assets/img/custom-template/thumbnail<?php echo $tpl; ?> (Custom).png"
                                   alt="Template <?php echo $tpl; ?> preview" loading="lazy" decoding="async">
                         </div>
                         <div class="template-meta">
                              <span class="template-title">Template <?php echo $tpl; ?></span>
                         </div>
                    </a>
               </div>
               <?php } ?>
          </div>
     </div>
</div>


<script src="../assets/libs/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>