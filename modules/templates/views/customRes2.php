<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Restaurant 2 – Thai Cuisine in New York</title>
  <meta name="description" content="Restaurant 2 — Thai cuisine. Dine-in, takeaway, delivery. Book a table or order online.">

  <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"> -->
  <link href="../assets/libs/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root{
      /* palette กลุ่ม restaurant2 (เหลือง-เทา) */
      --brand:#ffd936;         /* primary yellow */
      --brand-600:#d1a500;     /* darker */
      --accent:#212529cc;      /* ข้อความบนแบนเนอร์มืด */
      --ink:#1a1a1a;
      --muted:#6b7280;
      --paper:#fffdf5;
      --soft:#fafafa;
      --radius:12px;
      --shadow:0 10px 25px rgba(0,0,0,.12);
      --card-bg:#f4c400;
      --footer-bg:#2f2f33;
      --hero-overlay:linear-gradient(to bottom, rgba(0,0,0,.35), rgba(0,0,0,.45));
      --bg-color:#ffffff;
    }

    body{ color:var(--ink); background:var(--bg-color); }
    section{ padding:64px 0; }

    .btn-brand{
      background:var(--brand); border:none; color:#000; box-shadow:var(--shadow);
      transition:.2s transform ease,.2s filter ease;
      width: max-content;
    }
    .btn-brand:hover{ background:var(--brand-600); transform:translateY(-2px); filter:brightness(.98); }

    /* Navbar (คงโครงเดิม) */
    .navbar-blur{ backdrop-filter:saturate(180%) blur(10px); background:#2f2f33; }
    .navbar .nav-item{ color:#fff !important; }
    .btn-call{ background:#000; color:#fff; }

    /* Top gallery strip (3 ภาพ) */
    .hero-strip{ padding-top:120px; } /* เว้นให้พ้น navbar fixed-top */
    .hero-3 img{ height:220px; width:100%; object-fit:cover; }

    /* Info Icons Row */
    .info-icons .icon{ color: var(--brand); width:42px; height:42px; display:grid; place-items:center;}
    .info-icons .card{ border:none; background:#fff; }
    .info-icons .small{ color:var(--text-color); }

    /* Discover block (ครึ่งเหลือง ครึ่งรูป) */
    .discover .card{ border:none; overflow:hidden; box-shadow:var(--shadow); }
    .discover .card p{ color:var(--text-color); }
    .discover .left{ background:var(--brand); }
    .discover .left a{ color:#111; text-decoration:underline; }

    /* Featured menu (grid) */
    .featured .thumb{ aspect-ratio: 4/5; width:100%; object-fit:cover; border-radius:8px; box-shadow:var(--shadow); }
    .featured h3{ letter-spacing:.02em; }

    /* Benefits mini icons row */
    .mini-benefit .badge{ background:transparent; border:1px solid #e6e6e6; color:#555; }

    /* Fully Licensed banner */
    .licensed{
      background:
        linear-gradient(to bottom, rgba(0,0,0,.35), rgba(0,0,0,.35)),
        url("../assets/img/customRes2/licensed.webp") center/cover no-repeat;
      color:#fff; text-align:center; padding:96px 0;
    }

    /* Testimonials section (ภาพพื้นหลังเข้ม) */
    .reviews{
      position:relative;
      background:
        linear-gradient(to bottom, rgba(0,0,0,.55), rgba(0,0,0,.75)),
        url("../assets/img/customRes2/reviews.webp") center/cover no-repeat;
      color:#fff;
    }
    .review-card{ max-width:720px; }
    .stars i{ color:var(--brand); font-size:0.8rem; }
    .review-card hr{ border-color: var(--brand); }

    /* Map + dine with us */
    .mapbox .ratio{ border-radius:var(--radius); overflow:hidden; box-shadow:var(--shadow); }

    /* Highlight dish (พื้นเหลืองฝั่งขวา) */
    .highlight{
      background-color: transparent;
      background-image: linear-gradient(90deg, #F1D33F 25%, #D8833000 25%);
    }

    /* Facebook strip */
    .fb-strip img{ height:100%; width:100%; }

    /* Promo band */
    .promo{
      position:relative;
      background:
        linear-gradient(180deg, rgba(0,0,0,.5), rgba(0,0,0,.65)),
        url("../assets/img/customRes2/22.jpg") center/cover no-repeat;
      color:#fff;
    }

    /* Footer */
    .footer{
      background: var(--footer-bg);
      color:#e8f3ea;
    }
    .footer a{ color:#e8f3ea; text-decoration:none; }
    .footer a:hover{ text-decoration:underline; }

    /* ===== Theme mapper (ใช้ร่วม color panel) ===== */
    :root{
      --text-color: var(--ink);
      --bg-color:   var(--bg-color);
      --btn-bg:     var(--brand);
      --btn-text:   #111111;
      --heading-color:#222;
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
      <a class="navbar-brand fw-semibold d-flex align-items-center gap-2" href="#">
        <img src="../assets/img/customRes2/restaurant-2.png" width="100" height="100" alt="">
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div id="nav" class="collapse navbar-collapse">
        <ul class="navbar-nav w-100 justify-content-center gap-lg-3">
          <li class="nav-item"><a class="nav-link" href="#">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="#featured">Menu</a></li>
          <li class="nav-item"><a class="nav-link" href="#discover">About</a></li>
          <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
        </ul>
        <div class="d-flex gap-2 mt-3 mt-lg-0" style="width:auto;">
          <a class="btn btn-brand" href="#order">Order Online</a>
          <a class="btn btn-brand" href="#table">Table Reservation</a>
        </div>
      </div>
    </div>
  </nav>

  <!-- TOP GALLERY (3 IMAGES) -->
  <section class="hero-strip pb-0">
    <div class="container-fluid px-0">
      <div class="row g-0 hero-3">
        <div class="col-12"><img src="../assets/img/customRes2/image-slider-3.webp" alt=""></div>
      </div>
    </div>
  </section>

  <!-- INFO ICONS ROW -->
  <section class="py-4 info-icons">
    <div class="container-fluid">
      <div class="row g-3 row-cols-1 row-cols-md-3">
        <div class="col border-end">
          <div class="card p-4 h-100 text-center">
            <div class="d-flex align-items-center gap-2">
              <div class="col-4">
                <div class="icon"><img src="../assets/img/customRes2/icon6.svg" alt="" width="60"></div>
              </div>
              <div class="col-8">
                <h6 class="mb-1 fs-5">Opening Hours</h6>
                <div class="small fw-semibold">Monday-Friday</div>
                <div class="small">09:00 am – 17:00 pm</div>
                <div class="small fw-semibold">Saturday-Sunday</div>
                <div class="small">09:00 am – 18:00 pm</div>
              </div>
            </div>
          </div>
        </div>
        <div class="col border-end">
          <div class="card p-4 h-100 text-center">
            <div class="d-flex align-items-center gap-2">
              <div class="col-4">
                <div class="icon"><img src="../assets/img/customRes2/Deliver.svg" alt="" width="60"></div>
              </div>
              <div class="col-8">
                <h6 class="mb-1 fs-5">Pickup & Delivery Service</h6>
                <div class="small fw-semibold">Monday-Friday</div>
                <div class="small">09:00 am – 17:00 pm</div>
                <div class="small fw-semibold">Saturday-Sunday</div>
                <div class="small">09:00 am – 18:00 pm</div>
              </div>
            </div>
          </div>
        </div>
        <div class="col">
          <div class="card p-4 h-100 text-center gap-4">
            <div class="d-flex align-items-center gap-2">
              <div class="col-4">
                <div class="icon"><img src="../assets/img/customRes2/contact.svg" alt="" width="60"></div>
              </div>
              <div class="col-8">
                <div class="small">123-456-7890</div>
                <div class="small">contact@demo.massage</div>
              </div>
            </div>
            <div class="d-flex align-items-center gap-2">
              <div class="col-4">
                <div class="icon"><img src="../assets/img/customRes2/map.svg" alt="" width="60"></div>
              </div>
              <div class="col-8">
                <div class="small">123 Fifth Avenue, New York, NY 10160, United States.</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- DISCOVER (yellow + image) -->
  <section id="discover" class="discover">
    <div class="container">
      <div class="card overflow-hidden">
        <div class="row g-0">
          <div class="col-lg-6 p-4 p-md-5 left d-flex flex-column justify-content-center">
            <h5 class="mb-2">Discover</h5>
            <h3 class="fw-bold">Thai Cuisine</h3>
            <p>Welcome to Restaurant 2! We take pride in bringing you the freshest and most authentic Thai cuisine in the Newtown. Our dishes are crafted using traditional recipes and the finest ingredients available. Whether you choose to dine in or take away, our friendly service comes with a smile. From our family to yours, we extend our heartfelt gratitude for your support, and we look forward to serving you your next delicious meal!</p>
            <a href="#">About Us</a>
          </div>
          <div class="col-lg-6">
            <img src="../assets/img/customRes2/top-view-hotpot-dishes.jpg" class="w-100 h-100" style="object-fit:cover;" alt="">
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- FEATURED MENU (3×3 GRID) -->
  <section id="featured" class="featured">
    <div class="container">
      <h3 class="text-center mb-4">Featured Menu</h3>
      <div class="row g-3">
        <div class="col-6 col-md-3"><img class="thumb" src="../assets/img/customRes2/Photo-7-min.jpg" alt=""></div>
        <div class="col-6 col-md-3"><img class="thumb" src="../assets/img/customRes2/Photo-8-min.jpg" alt=""></div>
        <div class="col-6 col-md-3"><img class="thumb" src="../assets/img/customRes2/Photo-9-min.jpg" alt=""></div>
        <div class="col-6 col-md-3"><img class="thumb" src="../assets/img/customRes2/Photo-17-min.jpg" alt=""></div>
        <div class="col-6 col-md-3"><img class="thumb" src="../assets/img/customRes2/Photo-24-min.jpg" alt=""></div>
        <div class="col-6 col-md-3"><img class="thumb" src="../assets/img/customRes2/Photo-35-min.jpg" alt=""></div>
        <div class="col-6 col-md-3"><img class="thumb" src="../assets/img/customRes2/Photo-36-min.jpg" alt=""></div>
        <div class="col-6 col-md-3"><img class="thumb" src="../assets/img/customRes2/Photo-39-min.jpg" alt=""></div>
      </div>
    </div>
  </section>

  <!-- MINI BENEFITS (icons text) -->
  <section class="mini-benefit py-4">
    <div class="container d-flex flex-wrap gap-3 justify-content-center pb-5">
      <div class="row">
        <div class="col-6 d-flex align-items-center justify-content-center gap-2">
          <div class="col-4">
            <div class="icon"><img src="../assets/img/customRes2/Deliver.svg" alt="" width="60"></div>
          </div>
          <div class="col-8">
            <div class="small">We deliver (See delivery fee when ordering)</div>
          </div>
        </div>
        <div class="col-6 d-flex align-items-center justify-content-center gap-2">
          <div class="col-4">
            <div class="icon"><img src="../assets/img/customRes2/Covid.svg" alt="" width="60"></div>
          </div>
          <div class="col-8">
            <div class="small">We are going COVID safe by sanitizing regularly and offering contact-less payment.</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- FULLY LICENSED BANNER -->
  <section class="licensed mb-5">
    <div class="container">
      <h4 class="mb-0 fw-semibold text-white">Fully Licensed</h4>
    </div>
  </section>

  <!-- REVIEWS (dark bg) -->
  <section class="reviews">
    <div class="container py-5 d-flex justify-content-end">
      <div class="col-6 review-card">
        <h5 class="fw-semibold text-white mb-3">Reviews</h5>
        <p class="fw-semibold text-white mb-4">Our average customer rating is 4.6 / 5</p>
        <div class="stars mb-2"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></div>
        <blockquote class="mb-4">
          Great Thai place in Geelong, we’d ordered one entrees which is battered fish ($10.9) the fish is crispy and affordable price. For our mains, I ordered Kua Gai and my partner ordered Bbq chicken with rice. The food was delicious and Kua Gai is much better when dine-in✨
        </blockquote>
        <div class="d-flex flex-row gap-3 align-items-center">
          <i class="bi bi-person-circle fs-1"></i>
          <span>User 1</span>
        </div>
        <hr class="py-2">
        <div class="stars mb-2"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></div>
        <blockquote class="mb-4">
          This Thai spot serves up all the classics you’d expect to find in a Thai eatery. The moment you step inside, the fragrant spices hit you, and the dishes are just as tasty as your Thai friends promised. Can’t wait to go back and try more!
        </blockquote>
        <div class="d-flex flex-row gap-3 align-items-center">
          <i class="bi bi-person-circle fs-1"></i>
          <span>User 2</span>
        </div>
        <hr class="py-2">
        <div class="d-flex flex-column align-items-end justify-content-end gap-2">
          <img src="../assets/img/customRes2/google-logo.png" height="100" alt="Google"><br>
          <a href="#" class="text-white">Read More Reviews</a>
        </div>
      </div>
    </div>
  </section>

  <!-- MAP + DINE WITH US -->
  <section class="mapbox mb-5">
    <div class="container">
      <div class="row g-4 align-items-center">
        <div class="col-lg-6">
          <div class="ratio ratio-4x3">
            <iframe
                src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d6046.067761403483!2d-73.991048!3d40.73928!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c259a3b24b424f%3A0x618680d3f8c2f773!2s123%205th%20Ave%2C%20New%20York%2C%20NY%2010003!5e0!3m2!1sen!2sus!4v1760507284228!5m2!1sen!2sus"
                title="Google Map: New York, NY"
                loading="lazy"
                allowfullscreen
                referrerpolicy="no-referrer-when-downgrade"></iframe>
          </div>
        </div>
        <div class="col-lg-6">
          <h5 class="fw-semibold mb-2">Come dine with us!</h5>
          <a href="#" class="text-black">Book A Table Now!</a>
        </div>
      </div>
    </div>
  </section>

  <!-- HIGHLIGHT DISH (left image + right yellow panel) -->
  <section class="highlight pt-0">
    <div class="container">
      <div class="row g-4 align-items-center">
        <div class="col-lg-6">
          <img src="../assets/img/customRes2/dish-cutout.png" class="img-fluid" alt="">
        </div>
        <div class="col-lg-6">
          <div class="p-4 p-md-5 right d-flex flex-column align-items-end justify-content-end text-end">
            <h5 class="fs-3 fw-semibold">Book a Table in<br> Real Time</h5>
            <p class="mb-4">With real-time table booking, you can easily reserve your seat at your favorite dining establishment in just a few clicks. Reserve your seat with a few taps – book a table in real time!</p>
            <div>
              <a href="#" class="text-black pe-3">Order Now!</a>
              <img src="../assets/img/customRes2/Arrow-Dark-1.png" height="75" alt="">
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

  <!-- PROMO BAND -->
  <section class="promo py-5">
    <div class="container d-flex flex-row py-4">
      <div class="d-flex flex-column gap-2 p">
        <h4 class="mb-2 text-white fw-semibold">NEW! ONLINE ORDERING</h4>
        <p class="mb-3 pe-5">Online ordering NOW enabled for pick-up. Just tell us what you want and we’ll prepare it as fast as we can. All orders are manually confirmed by us directly. Find out in real-time when your food is ready. Watch on-screen when your food is ready for pickup.</p>
      </div>
      <div class="d-flex flex-column gap-2 align-items-center">
        <a href="#" class="btn btn-brand px-4">Order Online</a>
        <a href="#" class="btn btn-brand px-4">Table Reservation</a>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <section id="contact" class="footer pt-5">
    <div class="container pb-4">
      <div class="row d-flex justify-content-center mb-2">
          <img src="../assets/img/customRes2/restaurant-2.png" alt="" style="width: auto; height: 100px;">
      </div>
      <div class="row g-4 align-items-start">
        <div class="col-lg-4 text-center">
          <p class="fs-5 fw-semibold mt-2">We are Open</p>
          <div class="text-center">
            <div class="small fw-semibold">Monday-Friday</div>
            <div class="small">09:00 am – 17:00 pm</div>
            <div class="small fw-semibold mt-4">Saturday-Sunday</div>
            <div class="small">09:00 am – 18:00 pm</div>
          </div>
          <p class="small mb-0 mt-4">123 Fifth Avenue, New York, NY 10160, United States.</p>
        </div>
        <div class="col-lg-4 text-center">
          <p class="fs-5 fw-semibold mt-2">Contact</p>
          <p class="mb-1"><a href="tel:+11234567890">123-456-7890</a></p>
          <p class="mb-1"><a href="mailto:contact@demo.massage">contact@demo.massage</a></p>
        </div>
        <div class="col-lg-4 text-center">
          <div class="d-flex align-items-center justify-content-center gap-3 fs-4 mb-3">
            <a class="link-light" href="#"><i class="bi bi-facebook"></i></a>
            <a class="link-light" href="mailto:info@example.com"><i class="bi bi-envelope"></i></a>
          </div>
          <div class="d-flex flex-column gap-2 align-items-center">
            <button href="#" class="btn btn-brand px-4">Order Online</button>
            <button href="#" class="btn btn-brand px-4">Table Reservation</button>
          </div>
        </div>
      </div>
      <div class="row d-flex justify-content-center text-center mb-2">
        <p class="mt-3 mb-1 small">© <span id="year">2024</span> Restaurant 2 Website Made by
        <a class="link-light" href="#" rel="noopener">Local For You</a></p>
        <p class="small"><a class="link-light" href="#">Privacy Policy</a> | <a class="link-light" href="#">Terms &amp; Conditions</a></p>
      </div>
    </div>
  </section>

  <!-- Color Panel (mapping เหมือน restaurant1) -->
  <script type="module" src="../controllers/colorPanel.js"></script>
  <color-control-panel
    storage-key="siteTheme-restaurant2"
    controls="text,bg,cardBg,footerBg,button,link,linkHover,linkVisited,heading"
    position="bottom">
  </color-control-panel>

  <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> -->
  <script src="../assets/libs/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
