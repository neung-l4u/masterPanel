<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Restaurant 1 – Thai & Asian Food in New York</title>
  <meta name="description" content="Restaurant 1 — Thai & Asian food in New York. Dine-in, takeaway, delivery. Book a table or order online.">

  <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"> -->
  <link href="../assets/libs/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root{
      /* palette กลุ่ม restaurant (เขียว + ดิน) */
      --brand:#728370;       /* primary green */
      --brand-600:#1b5e20;   /* darker */
      --accent:#BE402FBD;      /* warn/notice red */
      --ink:#1a1a1a;
      --muted:#6b7280;
      --paper:#fffced;
      --soft:#f7faf7;
      --radius:12px;
      --shadow:0 10px 25px rgba(0,0,0,.12);
      --card-bg:#2e7d32;
      --hero-overlay:linear-gradient(to bottom, rgba(0,0,0,.45), rgba(0,0,0,.65));
      --footer-bg:#4b504a;
      --bg-color:#fffced;
    }

    body{ color:var(--ink); background:var(--paper); }
    section{ padding:64px 0; }

    .btn-brand{
      background:var(--brand); border:none; color:#fff; box-shadow:var(--shadow);
      transition:.2s transform ease,.2s filter ease;
    }
    .btn-brand:hover{ background:var(--brand-600); color: #fff; filter:brightness(.95); }

    /* Navbar */
    .navbar-blur{ backdrop-filter:saturate(180%) blur(10px); background:rgba(0,0,0,.55); }
    .navbar a{ color:#fff !important; }

    /* HERO */
    .hero{
      position:relative; min-height:72vh; display:grid; place-items:center; text-align:center; color:#fff; height: 90vh;
      background: var(--hero-overlay),
        url("../assets/img/customRes1/hero-bg.webp") center/cover no-repeat;
    }
    .hero h1{ font-weight:700; letter-spacing:.4px; text-shadow:0 2px 12px rgba(0,0,0,.35); }
    .hero .sub{ letter-spacing:.12em; font-size:.8rem; opacity:.9; }

    /* NOTICE BAR (deposit) */
    .notice{
      background:var(--accent); color:#fff; font-size:.9rem; padding:.75rem 1rem; border-radius:5px;
      box-shadow:var(--shadow);
    }

    /* Feature (three category cards) */
    .menu{ background: var(--bg-color); }
    .feature-card{
      position:relative; border:none; overflow:hidden; min-height:260px;
      background:#000;
    }
    .feature-card img{ height: 360px; object-fit:cover; opacity:.7; }
    .feature-card .title{
      position:absolute; padding:1rem 1.25rem; color:#fff;
      font-size:1.2rem; font-weight:600; letter-spacing:.06em;
    }

    /* Testimonial */
    .stars{ color:#ffc107; }

    /* Delivery Panel + Map */
    .panel{
      background:#b1d4b6; border-radius:var(--radius); box-shadow:var(--shadow);
    }

    /* CTA banner with food bg */
    .cta-food{
      background:
        linear-gradient(to bottom, rgba(0,0,0,.65), rgba(0,0,0,.65)),
        url("../assets/img/customRes1/cta-food.webp") center/cover no-repeat;
      color:#fff; text-align:center;
      padding:72px 0;
    }

    /* Opening hours split */
    .hours-photo{
      background:url("../assets/img/customRes1/featured2.webp") center/cover no-repeat;
      height: 100%;
    }

    /* Footer */
    .footer{
      background: var(--footer-bg);
      color:#e8f3ea;
    }
    .footer a{ color:#e8f3ea; text-decoration:none; }
    .footer a:hover{ text-decoration:underline; }

    /* Theme mapper (แชร์กับ color panel ได้) */
    :root{
      --text-color: var(--ink);
      --bg-color:   var(--paper);
      --btn-bg:     var(--brand);
      --card-bg:    var(--card-bg);
      --btn-text:   #ffffff;
      --heading-color:#142b14;
      --footerBg:   var(--footer-bg);
    }
    html, body{ color:var(--text-color); background:var(--bg-color); }
    h1, h2, h3, h4, h5, h6{ color:var(--heading-color); }
    .btn-brand{ background:var(--btn-bg); color:var(--btn-text); }
    .btn{ padding:1rem 1rem; }
  </style>
</head>
<body>

  <!-- NAVBAR -->
  <nav class="navbar navbar-expand-lg navbar-dark navbar-blur fixed-top">
    <div class="container">
      <a class="navbar-brand fw-semibold d-flex align-items-center gap-2" href="#">
        <img src="../assets/img/customRes1/logo-rastaurant-1.png" width="100" height="100" alt="">
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div id="nav" class="collapse navbar-collapse">
        <ul class="navbar-nav w-100 justify-content-center gap-lg-3">
          <li class="nav-item"><a class="nav-link" href="#">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="#">Menu</a></li>
          <li class="nav-item"><a class="nav-link" href="#">About</a></li>
          <li class="nav-item"><a class="nav-link" href="#">Contact</a></li>
        </ul>
        <div class="d-flex flex-column gap-2 mt-3 mt-lg-0" style="width: 250px;">
          <a class="btn btn-brand" href="tel:+11234567890"><i class="bi bi-telephone-fill me-1"></i>+1 123-456-7890</a>
        </div>
      </div>
    </div>
  </nav>

  <!-- HERO -->
  <header id="home" class="hero">
    <div class="container">
      <h1 class="display-4 mb-2 text-white">Restaurant 1</h1>
      <div class="sub mb-4 text-white">THAI &amp; ASIAN FOOD IN NEW YORK</div>
      <div class="d-flex flex-column gap-3 justify-content-center align-items-center">
        <a href="#order" class="btn btn-brand px-4">Order Online</a>
        <a href="#table" class="btn btn-brand px-4">Table Reservation</a>
      </div>

      <div class="d-flex justify-content-center mt-4">
        <div class="notice fs-6">
          If booking more than 10 people, there is a required deposit of $100 for reservation.<br/>NO SPLIT BILL
        </div>
      </div>
    </div>
  </header>

  <!-- INTRO -->
  <section id="about" class="bg-white">
    <div class="container">
      <div class="row g-4 align-items-center">
        <div class="col-lg-6 text-center px-4">
          <p class="mb-0">Discover the Best Food Delivery in New York</p>
          <h2 class="fw-bold">WELCOME TO RESTAURANT 1</h2>
          <p>FINEST FOOD DELIVERY EXPERIENCE!</p>
          <p>
            At Restaurant 1 we offer meals of excellent quality and invite you to try our delicious food. The key to our success is simple: providing quality consistent food that taste great every single time. We pride ourselves on serving our customers delicious genuine dishes like: Thai Eat delicious food. Grab a drink. But most of all, relax! We thank you from the bottom of our hearts for your continued support.
          </p>
          <a class="btn btn-brand" href="#">Read More</a>
        </div>
        <div class="col-lg-6">
          <img class="img-fluid" alt=""
               src="../assets/img/customRes1/zzzzzzzzzzzz-1.jpg">
        </div>
      </div>
    </div>
  </section>

  <!-- FEATURED DISHES -->
  <section id="menu">
    <div class="container">
      <h3 class="fs-2 text-center mb-4">Featured dishes</h3>
      <div class="row g-5">
        <div class="col-md-3">
          <div class="d-flex flex-column justify-content-center align-items-center feature-card">
            <img src="../assets/img/customRes1/featured1.webp" alt="">
            <div class="title">ENTREE</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="d-flex flex-column justify-content-center align-items-center feature-card">
            <img src="../assets/img/customRes1/featured2.webp" alt="">
            <div class="title">STIR-FRIED</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="d-flex flex-column justify-content-center align-items-center feature-card">
            <img src="../assets/img/customRes1/featured3.webp" alt="">
            <div class="title">NOODLES</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="d-flex flex-column justify-content-center align-items-center feature-card">
            <img src="../assets/img/customRes1/featured4.webp" alt="">
            <div class="title">MAIN MEALS</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- TESTIMONIALS -->
  <section id="testimonials" class="bg-white">
    <div class="container">
      <div class="text-center mb-5">
        <h3 class="fw-2">Why People Believe Us</h3>
        <p>Great things our clients say. Leave us your thoughts.</p>
      </div>

      <div class="row g-4">
        <div class="col-6">
          <div class="p-4 review-card h-100">
            <div class="d-flex flex-column align-items-center text-center gap-3 mb-2">
              <i class="bi bi-person-circle fs-1"></i>
              <p>Amazing service, friendly staff and food with quality and good quantity. Don’t miss this if your visiting New York.</p>
              <div class="stars mb-2"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></div>
              <strong>Tamima Shiptu</strong>
            </div>
          </div>
        </div>
        <div class="col-6">
          <div class="p-4 review-card h-100">
            <div class="d-flex flex-column align-items-center text-center gap-3 mb-2">
              <i class="bi bi-person-circle fs-1"></i>
              <p>Absolutely amazing dining experience. The staff were so lovely and friendly. They couldn’t do enough to make our dining experience more perfect. The sweet and sour chicken was 10/10.</p>
              <div class="stars mb-2"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></div>
              <strong>Imme Visser</strong>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-4">
        <div class="col-6">
          <div class="p-4 review-card h-100">
            <div class="d-flex flex-column align-items-center text-center gap-3 mb-2">
              <i class="bi bi-person-circle fs-1"></i>
              <p>Delicious traditional Thai food (one of our favourite cuisines) and friendly hospitality; it makes all the difference when the service is friendly and welcoming. Kim and his team were awesome, and his mum’s dumplings were superb!! We will be back 😊</p>
              <div class="stars mb-2"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></div>
              <strong>Andrew Mulholland</strong>
            </div>
          </div>
        </div>
        <div class="col-6">
          <div class="p-4 review-card h-100">
            <div class="d-flex flex-column align-items-center text-center gap-3 mb-2">
              <i class="bi bi-person-circle fs-1"></i>
              <p>Absolutely the best Thai food we’ve had, so authentic. The staff were fantastic so friendly and engaging. Just sat back and relaxed. The banana fritters with coconut icecream was incredible. Definitely recommend and will be back. Great job guys 10/10</p>
              <div class="stars mb-2"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></div>
              <strong>Julie Smith</strong>
            </div>
          </div>
        </div>
      </div>

      <div class="d-flex flex-column align-items-center text-center mt-4">
        <img src="../assets/img/customMas1/google-logo.png" height="100" alt="Google logo">
        <a class="btn btn-brand mt-3 text-uppercase" href="#">Write us a review</a>
      </div>
    </div>
  </section>

  <!-- DELIVERY MAP + PANEL -->
  <section id="order" class="bg-white">
    <div class="container">
      <div class="row align-items-stretch">
        <div class="col-lg-7">
          <div class="ratio ratio-1x1 rounded-4 overflow-hidden">
            <img src="../assets/img/customRes1/Delivery-min.jpg" alt="Map showing delivery areas">
          </div>
        </div>
        <div class="col-lg-5">
          <div class="panel p-4 h-100 d-flex flex-column">
            <h5 class="fs-2">TAKEAWAY FOOD &amp; DELIVERY</h5>
            <hr class="my-5">
            <p >
              Looking for food delivery in New York? Not everybody knows or has the time to prepare tasty food.<br>
              When you want to get served like a king then food delivery from us will be your best choice.<br>
              Simply select “Delivery” at the checkout screen and we hope you’ll appreciate our food delivery service.
            </p>
            <div>
              <strong class="fs-4">Delivery</strong>
              <div class="mt-4 mb-5">
								<p class="m-text-align-left m-size-14 mb-1">
                  <span class="m-font-size-15 font-size-19 color" style="color: #52e55c;">⦿</span> 
                  New York
                  <span class="m-font-size-14">, Min – $40.00, Fee – $5.00</span>
                </p>
                <p class="m-text-align-left m-size-14 mb-1">
                  <span class="m-font-size-15 font-size-19" style="color: #3eb09b;">⦿</span> 
                  New York
                  <span class="m-font-size-14">, Min – $40.00, Fee – $5.00</span>
                </p>
                <p class="m-text-align-left m-size-14 mb-1">
                  <span class="m-font-size-15 font-size-19" style="color: #fc743a;">⦿</span> 
                  New York
                  <span class="m-font-size-14">, Min – $40.00, Fee – $5.00</span>
                </p>
                <p class="m-text-align-left m-size-14 mb-1">
                  <span class="m-font-size-15 font-size-19" style="color: #f2ac1f;">⦿</span> 
                  New York
                  <span class="m-font-size-14">, Min – $40.00, Fee – $5.00</span>
                </p>
                <p class="m-text-align-left m-size-14 mb-1">
                  <span class="font-size-19 m-font-size-15" style="color: #44c3e5;">⦿</span> 
                  New York
                  <span class="m-font-size-14">, Min – $40.00, Fee – $5.00</span>
                </p>
                <p class="m-text-align-left m-size-14 mb-1">
                  <span class="m-font-size-15 font-size-19" style="color: #a159b3;">⦿</span> 
                  New York
                  <span class="m-font-size-14">, Min – $40.00, Fee – $5.00</span>
                </p>
            </div>
            <div class="mt-auto">
              <a class="btn btn-brand" href="#">Order Online Now</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA FOOD STRIP -->
  <section class="cta-food">
    <div class="container p-5 max-w-600">
      <h4 class="mb-2 fs-2 text-white">Order. Pay. Enjoy: Cash or Card, We Deliver Happiness.</h4>
      <p class="fs-5 mb-3">We accept Takeaway and Home Delivery with Cash or Card.</p>
      <a href="#" class="btn btn-brand">See Menu &amp; Order</a>
    </div>
  </section>

  <!-- OPENING HOURS (photo + card + map) -->
  <section id="table" class="py-0">
    <div class="container-fluid px-0">
      <div class="row">
        <div class="col-4 px-0">
          <div class="hours-photo"></div>
        </div>
        <div class="col-4 d-flex align-items-center justify-content-center px-0 bg-white">
          <div class="p-4 hours-card text-center">
            <h5 class="fs-2">Opening Hours</h5>
            <p class="mb-2">123 Fifth Avenue, New York, NY 10160, United States.</p>
            <ul class="list-unstyled mb-3">
              <li>Mon–Fri: 9am-5pm</li>
              <li>Sat–Sun: 9am-6pm</li>
            </ul>
            <div class="d-flex flex-column gap-3 justify-content-center align-items-center">
              <a href="#order" class="btn btn-brand px-4">Order Online</a>
              <a href="#table" class="btn btn-brand px-4">Table Reservation</a>
            </div>
            <div class="d-flex justify-content-center mt-4">
              <div class="notice fs-6">
                If booking more than 10 people, there is a required deposit of $100 for reservation.<br/>NO SPLIT BILL
              </div>
            </div>
          </div>
        </div>
        <div class="col-4 px-0">
          <div class="ratio ratio-1x1 overflow-hidden" style="width: 100%; height: 100%;">
            <iframe
              src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d6046.067761403483!2d-73.991048!3d40.73928!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c259a3b24b424f%3A0x618680d3f8c2f773!2s123%205th%20Ave%2C%20New%20York%2C%20NY%2010003!5e0!3m2!1sen!2sus!4v1760507284228!5m2!1sen!2sus"
              title="Google Map: New York, NY"
              loading="lazy"
              allowfullscreen
              referrerpolicy="no-referrer-when-downgrade"></iframe>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <section id="contact" class="footer pt-5">
    <div class="container-fluid pb-4">
      <div class="row">
        <div class="col-4 d-flex flex-column align-items-center justify-content-center">
          <img src="../assets/img/customRes1/logo-rastaurant-1.png"
              alt="Res 1 logo"
              width="150" height="150"
              style="object-fit:cover;">
          <p class="fs-2">We Are Open</p>
          <p class="mb-2">123 Fifth Avenue, New York, NY 10160, United States.</p>
        </div>
        <div class="col-4 d-flex align-items-center justify-content-center">
          <div class="p-4 text-center">
            <h5 class="fs-2 text-white pt-4">Contact</h5>
            <p class="mb-2">123-456-7890</p>
            <p class="mb-2">contact@demo.massage</p>
            <ul class="list-unstyled mb-5">
              <li>Mon–Fri: 9am-5pm</li>
              <li>Sat–Sun: 9am-6pm</li>
            </ul>
            <p class="mt-5 pt-5 mb-1">
              © <span id="year">2024</span> Restaurant 1 Website Made by
              <a class="link-light" href="#" rel="noopener">Local For You</a>
            </p>
            <ul class="list-unstyled mb-3">
              <li>
                <a class="link-light" href="#">Privacy Policy</a>
                <span class="mx-2">|</span>
                <a class="link-light" href="#">Terms &amp; Conditions</a>
              </li>
            </ul>
          </div>
        </div>
        <div class="col-4 d-flex align-items-center justify-content-center">
          <div class="p-4 text-center">
            <div class="d-flex align-items-center justify-content-center gap-3 fs-3 mb-3">
              <a href="#" aria-label="Facebook" class="link-light"><i class="bi bi-facebook"></i></a>
              <a href="mailto:info@example.com" aria-label="Email" class="link-light"><i class="bi bi-envelope"></i></a>
            </div>
            <div class="d-flex flex-column gap-3 justify-content-center align-items-center">
              <a href="#order" class="btn btn-brand px-4">Order Online</a>
              <a href="#table" class="btn btn-brand px-4">Table Reservation</a>
            </div>
            <div class="d-flex justify-content-center mt-4">
              <div class="notice fs-6">
                If booking more than 10 people, there is a required deposit of $100 for reservation.<br/>NO SPLIT BILL
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Color Panel (optional, shared mapper) -->
  <script type="module" src="../controllers/colorPanel.js"></script>

  <color-control-panel
    storage-key="siteTheme-massage1"
    controls="text,bg,footerBg,button,link,linkHover,linkVisited,heading"
    position="bottom">
  </color-control-panel>

  <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> -->
  <script src="../assets/libs/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
