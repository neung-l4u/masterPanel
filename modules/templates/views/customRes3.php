<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Restaurant 3 – Discover Thai</title>
  <meta name="description" content="Restaurant 3 — Discover authentic Thai food. Dine-in, takeaway, delivery. Book a table or order online.">

  <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"> -->
  <link href="../assets/libs/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root{
      --brand:#f0c545;        
      --brand-600:#d5a92e;    
      --accent:#8c6a21;      
      --ink:#ffffff;        
      --muted:#b7b2a2;
      --paper:#0d0d0d;      
      --paper-2:#1f1810;     
      --paper-3:#2a2319;    
      --radius:12px;
      --shadow:0 10px 25px rgba(0,0,0,.18);
      --footer-bg:#1e1810;
    }

    body{ color:var(--ink); background:var(--paper); }
    section{ padding:64px 0; }

    .btn-brand{
      width: 200px;
      padding: 1em 0em 1em 0em;
      color: #000000; text-transform: uppercase; font-size: 14px;
      background:var(--brand); border:none;
      box-shadow:var(--shadow); transition:.2s transform,.2s filter;
      border-radius: 0px;
    }
    .btn-brand:hover{color: #000000; background:var(--brand-600); transform:translateY(-2px); filter:brightness(.98); }

    .btn-ghost{
      background:transparent; border:1px solid var(--brand); color:var(--brand);
    }
    .btn-ghost:hover{ background:var(--brand); color:#111; }

    /* Navbar */
    .navbar-blur{ backdrop-filter:saturate(180%) blur(8px); background:rgba(0,0,0,.6); }
    .nav-link{ color:#ffffff !important; }
    .navbar .btn{ padding:.6rem 1rem; }

    /* Hero */
    .hero{
      min-height:100vh; display:grid; place-items:center; text-align:center;
    }
    .hero .container{ max-width: 600px; }
    .hero .logo{ width:200px; height:200px; object-fit:contain; }
    .hero h1{ letter-spacing:.12em; color:var(--brand); }

    /* Image band (2 ภาพ) */
    .band img{ width:100%; height:320px; object-fit:cover; }

    /* About + authentic dishes */
    .about-dark{ background:var(--paper-2); }
    .about .container{ max-width:600px; }
    .authentic .logo{ width:200px; height:200px; object-fit:contain; }

    /* Menu grid */
    .menu { background:var(--paper-2); }
    .menu .col-3{
      width: 24%;
    }
    .feature-card{
      position:relative; border:none; overflow:hidden; min-height:300px;
    }
    .feature-card img{ height: 400px; object-fit:cover; opacity:.7; }
    .feature-card .details{
      position:absolute; width:100%;
    }
    .feature-card .title{
      color:#fff; font-size:1.2rem; letter-spacing:.06em;
    }
    .feature-card .desc{
      font-size:1rem;
    }

    /* Reviews */
    .reviews{position:relative;}
    .reviews blockquote{color:var(--ink);}
    .review-card{ max-width:720px; }
    .stars i{ color:var(--brand); font-size:0.8rem; }
    .review-card hr{ border-color: var(--brand); }
    .reviews .name{color: var(--brand); }

    /* Book a table block */
    .highlight{
      background-color: transparent;
      background-image: linear-gradient(90deg, #F1D33F 30%, #D8833000 25%);
    }

    /* Facebook strip */
    .fb-strip img{ height:100%; width:100%; }

    /* Footer */
    .footer{ background:var(--bg-color); }
    .footer .card{margin-left: 60px; margin-right: -40px; background:var(--footer-bg);}
    .footer .right{margin-left: -40px;}
    .footer .link{ color:#ffffff; text-decoration:none; }
    .footer li i{ color:var(--brand); }
    .footer li span{ color:var(--ink)}
    .footer .copyright{ padding-left: 60px; padding-right: 40px;}
    .footer .copyright a{ color:var(--brand); text-decoration:none; }

    hr { border-color: var(--brand); }

    /* ===== Theme mapper (ร่วมกับ color panel) ===== */
    :root{
      --text-color: var(--ink);
      --bg-color:   var(--paper);
      --btn-bg:     var(--brand);
      --btn-text:   #000000;
      --heading-color: var(--brand);
      --footerBg:   var(--footer-bg);
      --link: var(--brand);
      --link-hover: var(--brand-600);
      --link-visited: color-mix(in srgb, var(--brand) 55%, goldenrod);
    }
    html, body{ color:var(--text-color); background:var(--bg-color); }
    h1, h2, h3, h4, h5, h6{ color: var(--heading-color); }
    .btn-brand{ background:var(--btn-bg); color:var(--btn-text); }
    .btn{ padding:.9rem 1.1rem; }
  </style>
</head>
<body>

  <!-- NAVBAR -->
  <nav class="navbar navbar-expand-lg navbar-dark navbar-blur fixed-top">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center gap-2" href="#">
        <img src="../assets/img/customRes3/logo-restaurant3.png" width="100" height="100" alt="">
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
        <div class="d-flex gap-3 mt-3 mt-lg-0" style="width:400px;">
          <a class="btn btn-brand" href="#">Order Online</a>
          <a class="btn btn-brand" href="#">Table Reservation</a>
        </div>
      </div>
    </div>
  </nav>

  <!-- HERO -->
  <header class="hero">
    <div class="container  text-center">
      <img class="logo logo-round mb-3" src="../assets/img/customRes3/logo-restaurant3.png" alt="logo">
      <h1 class="display-6 mb-2">DISCOVER THAI</h1>
      <p class="mb-4">Discover authentic Thai food in the heart of Bohemia . We use the fresh high quality local ingredients to create our Thai signature dishes.</p>
      <div class="d-flex flex-column justify-content-center align-items-center gap-3 mt-3 mt-lg-0" style="width:auto;">
        <a class="btn btn-brand" href="#">Order Online</a>
        <a class="btn btn-brand" href="#">Table Reservation</a>
      </div>
    </div>
  </header>

  <!-- IMAGE BAND -->
  <section class="band py-0">
    <div class="container-fluid px-0">
      <div class="row g-0">
        <div class="col-12"><img src="../assets/img/customRes3/hero-bg.webp" alt=""></div>
      </div>
    </div>
  </section>

  <!-- ABOUT DARK -->
  <section id="about" class="about-dark py-5">
    <div class="container text-center py-5">
      <div class="row align-items-center py-5">
        <div class="col-12">
          <p class="mb-3">Restaurant 3 is known for traditional cuisine with a warm welcoming ambience. We set standards for quality and service through our attention to detail. We established Restaurant 3 to celebrate the delicate ingredients and dishes of our culture. Our menu incorporates modern flavours with traditional, combining to make the perfect dining experience.</p>
          <a class="btn btn-brand" href="#">About Us</a>
        </div>
      </div>
    </div>
  </section>

  <!-- AUTHENTIC DISHES STRIP -->
  <section class="authentic py-5">
    <div class="container">
      <div class="row g-4 align-items-center">
        <div class="col-lg-6 d-flex flex-column gap-3">
          <img class="logo" src="../assets/img/customRes3/logo-restaurant3.png" alt="">
          <div>
            <div class="mb-2">Opening Hours</div>
            <div>Mon-Fri: 9am-5pm<br>Sat-Sun: 9am-6pm</div>
            <h1 class="display-6 mb-2">AUTHENTIC<br>THAI<br>DISHES</h1>
          </div>
        </div>
        <div class="col-lg-6">
          <img class="img-fluid rounded-3 w-75" src="../assets/img/customRes3/dishes1.jpg" alt="">
        </div>
      </div>
    </div>
  </section>

  <!-- MENU GRID -->
  <section id="menu" class="menu">
    <div class="container">
      <h3 class="display-6 text-center mb-4">OUR MENU</h3>
      <div class="row d-flex justify-content-between g-3">
        <div class="col-3">
          <div class="d-flex flex-column justify-content-center align-items-center feature-card">
            <img src="../assets/img/customRes3/menu1.webp" alt="">
            <div class="details text-center">
              <h6 class="title text-uppercase">Appetizer</h6>
              <p class="desc text-center">Perfect starters for the family</p>
            </div>
          </div>
        </div>
        <div class="col-3">
          <div class="d-flex flex-column justify-content-center align-items-center feature-card">
            <img src="../assets/img/customRes3/menu2.webp" alt="">
            <div class="details text-center">
              <h6 class="title text-uppercase">Dessert</h6>
              <p class="desc text-center">Fresh made to order signatures</p>
            </div>
          </div>
        </div>
        <div class="col-3">
          <div class="d-flex flex-column justify-content-center align-items-center feature-card">
            <img src="../assets/img/customRes3/dishes4.jpg" alt="">
            <div class="details text-center">
              <h6 class="title text-uppercase">Stir fry</h6>
              <p class="desc text-center">Traditional recipes served hot</p>
            </div>
          </div>
        </div>
        <div class="col-3">
          <div class="d-flex flex-column justify-content-center align-items-center feature-card">
            <img src="../assets/img/customRes3/menu4.jpg" alt="">
            <div class="details text-center">
              <h6 class="title text-uppercase">Noodles</h6>
              <p class="desc text-center">Customer favourites with flavour</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- REVIEWS -->
  <section class="reviews">
    <div class="container py-5 d-flex justify-content-center">
      <div class="col-6 review-card text-center">
        <h3 class="display-6 text-center mb-2">Reviews</h3>
        <p class="fw-semibold text-white mb-2">Our average customer rating is 4.9 / 5</p>
        <div class="stars mb-2"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></div>
        <p class="mb-4">
          "Take it from a Thai person that this is the most authentic Thai food you can find on LI. My husband is American and he can take way more spice than I can and we asked for Thai spicy. I recommend you get homemade coconut ice cream with stick rice at the end of your meal. Awesome staff, clean place and great food!."
        </p>
        <div class="d-flex flex-row gap-3 align-items-center justify-content-center">
          <i class="bi bi-person-circle fs-1 text-white"></i>
          <span class="name">Poy T Granati</span>
        </div>
        <hr class="py-2">
        <div class="stars mb-2"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></div>
        <p class="mb-4">
          "Some of the best Thai good I've had. This is small restaurant serving absolutely amazing dishes. There are only a couple of tables so it is mostly takeout. My favorite is their Pad CU, but everything I've had has been delicious."
        </p>
        <div class="d-flex flex-row gap-3 align-items-center justify-content-center">
          <i class="bi bi-person-circle fs-1 text-white"></i>
          <span class="name">Lisa Votino</span>
        </div>
        <hr class="py-2">
        <div class="d-flex flex-column align-items-center justify-content-center gap-2">
          <img src="../assets/img/customRes2/google-logo.png" height="100" alt="Google">
        </div>
      </div>
    </div>
  </section>

  <!-- BOOK A TABLE (gold flag + dish) -->
  <section class="highlight pt-0 mb-5">
    <div class="container">
      <div class="row g-4 align-items-center">
        <div class="col-lg-6">
          <img src="../assets/img/customRes3/dish-cut-out-1.webp" class="img-fluid" alt="">
        </div>
        <div class="col-lg-6">
          <div class="p-4 p-md-5 right d-flex flex-column align-items-end justify-content-end text-end">
            <h5 class="fs-3 fw-semibold">Book a Table in<br> Real Time</h5>
            <p class="mb-4">With real-time table booking, you can easily reserve your seat at your favorite dining establishment in just a few clicks. Reserve your seat with a few taps – book a table in real time!</p>
            <div>
              <a class="btn btn-brand" href="#">Table Reservation</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- FACEBOOK STRIP (image collage) -->
  <section class="fb-strip pb-0">
    <div class="container-fluid px-0">
      <div class="row d-flex align-items-center g-0">
        <div class="col"><img src="../assets/img/customRes2/slider.png" alt=""></div>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <section id="contact" class="footer pt-5">
    <div class="container pb-5">
      <div class="row g-4">
        <div class="col-6">
          <div class="card p-4 h-100">
            <h5 class="fs-3 fw-semibold">CONTACT US</h5>
            <ul class="list-unstyled mb-3">
              <li><i class="bi bi-geo-alt me-2"></i><span>123 Fifth Avenue, New York, NY 10160, United States</span></li>
              <li><i class="bi bi-telephone me-2"></i><span>123-456-7890</span></li>
              <li><i class="bi bi-envelope me-2"></i><span>contact@demo.massage</span></li>
            </ul>
            <h5 class="fs-3 fw-semibold">OPENING HOURS</h5>
            <div class="mb-3"><span>Mon-Fri: 9am-5pm<br>Sat-Sun: 9am-6pm</span></div>
            <h5 class="fs-3 fw-semibold">FOLLOW US</h5>
            <div class="d-flex gap-3 fs-4 mb-3">
              <a class="link" href="#"><i class="bi bi-facebook"></i></a>
              <a class="link" href="#"><i class="bi bi-instagram"></i></a>
              <a class="link" href="mailto:info@example.com"><i class="bi bi-envelope"></i></a>
            </div>
            <div class="my-3 d-flex flex-column gap-2">
              <a class="btn btn-brand" href="#">Order Online</a>
              <a class="btn btn-brand" href="#">Table reservation</a>
            </div>
            <hr class="py-2 w-75">
            <ul class="navbar-nav d-flex flex-row w-100 gap-3">
              <li class="nav-item"><a class="nav-link px-2 text-white" href="#">Home</a></li>
              <li class="nav-item"><a class="nav-link px-2 text-white" href="#">About</a></li>
              <li class="nav-item"><a class="nav-link px-2 text-white" href="#">Contact</a></li>
            </ul>
          </div>
        </div>
        <div class="col-6 d-flex flex-column align-items-center right">
            <img class="mb-4" src="../assets/img/customRes3/logo-restaurant3.png" width="200" height="200" alt="">
            <div class="ratio ratio-4x3 rounded-3 overflow-hidden">
              <iframe
                src="https://www.google.com/maps?q=123+Fifth+Avenue,+New+York,+NY&output=embed"
                loading="lazy" allowfullscreen referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
      </div>
    </div>
    <div class=" py-3">
      <div class="container d-flex justify-content-between text-center text-white copyright">
        <div>
        Copyright © <span id="y"></span> Restaurant 3 | Website Maintained by <a>Local For You</a>
        </div>
        <div>
        <a href="#">Privacy Policy</a> | <a href="#">Terms &amp; Conditions</a>
        </div>
      </div>
    </div>
  </section>



  

  <!-- Color Panel -->
  <script type="module" src="../controllers/colorPanel.js"></script>
  <color-control-panel
    storage-key="siteTheme-restaurant3"
    controls="text,bg,cardBg,footerBg,button,link,linkHover,linkVisited,heading"
    position="bottom">
  </color-control-panel>

  <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> -->
  <script src="../assets/libs/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>