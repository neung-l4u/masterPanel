<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Massage 1 – Thai & Asian Massage in New York</title>
  <meta name="description" content="Welcome to Massage 1 — Thai & Asian massage in New York. Dine-in, takeaway, delivery. Book a session online.">

  <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"> -->
  <link href="../assets/libs/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root{
      /* พาเลตเดิมของเพจ */
      --brand:#41085a;          /* primary */
      --brand-600:#851979;      /* darker */
      --accent:#e07a5f;         /* accent */
      --ink:#1a1a1a;
      --muted:#6b7280;
      --paper:#ffffff;
      --soft:#faf7ff;
      --radius:10px;
      --shadow:0 10px 25px rgba(0,0,0,.12);
      --cardBg:#84398b;
      --footer-bg:#84398b;
    }

    /* =============== Base =============== */
    body{ color:var(--ink); background:var(--paper); }

    .btn-brand{
      background:var(--brand); border:none; color:#fff;
      box-shadow:var(--shadow);
      transition:.2s transform ease,.2s filter ease;
    }
    .btn-brand:hover{ background:var(--brand-600); color:#fff; transform:translateY(-2px); filter:brightness(.95); }

    /* Navbar */
    .navbar-blur{
      backdrop-filter:saturate(180%) blur(10px);
      background:rgba(10,10,20,.55);
    }
    .navbar a{ color:#fff !important; }
    .navbar .btn{ padding:.5rem 1rem; }

    /* Hero */
    .hero{
      position:relative; min-height:70vh; display:grid; place-items:center; color:#fff;
      background:
        linear-gradient(to bottom, rgb(0 0 0 / 55%), rgb(0 0 0 / 70%)), 
        url(../assets/img/customMas1/hero-bg.png) center / cover no-repeat;
    }
    .hero h1{ font-weight:700; letter-spacing:.5px; }
    .floating-cta{ position:absolute; right:1rem; top:1rem; }

    /* Cards */
    .service-card{
      background-image: url(../assets/img/customMas1/Customized-bg.png);
      background-position: top right;
      background-size: cover;
      border: none;
      border-radius: var(--radius);
    }

    .service-card::before{
      content: "";
      position: absolute;
      inset: 0;
      background-color: var(--cardBg);
      opacity: 0.9;
      mix-blend-mode: normal;
      z-index: 0;
      border:none;
      border-radius: var(--radius);
    }
    .service-card .card-body{ position:relative; }

    /* Section paddings */
    section{ padding:64px 0; }

    /* Testimonial */
    .testimonials {
      background-image: url(../assets/img/customMas1/Black-and-White.png);
      background-position: top center;
      background-size: cover;
    }

    .review-card{ border-radius:var(--radius); box-shadow:var(--shadow); }
    .stars{ color:#ffc107; }

    /* Booking widget mock */
    .booking-shell{ border-radius:var(--radius); box-shadow:var(--shadow); min-height: 550px; }
    .booking-sidebar{
      background:#17295a; color:#fff; border-top-left-radius:var(--radius); border-bottom-left-radius:var(--radius);
    }
    .booking-step{ padding:.5rem 1rem; border-left:.25rem solid transparent; font-size: 14px; }
    .booking-step.active{ background:rgba(255,255,255,.06); border-left-color:#7c4dff; }

    /* Footer */
    .footer{
      background-image: url(../assets/img/customMas1/Customized-bg.png);
      background-position: top center;
      background-size: cover;
    }

    .footer::before{
      content: "";
      position: absolute;
      inset: 0;
      background: var(--footer-bg);
      opacity: 0.9;
      mix-blend-mode: normal;
      z-index: 0;
    }

    .footer > *{ position: relative; z-index: 1; }

    .footer a{ color:#ffffff; text-decoration:none; }
    .small-muted{ color:var(--muted); font-size:.9rem; }

    /* =============== THEME MAPPER → ผูกกับ color-control-panel.js =============== */
    :root{
      /* map ตัวแปรแผงควบคุม → ธีมของเพจ */
      --text-color: var(--ink);
      --bg-color:   var(--paper);
      --btn-bg:     var(--brand);
      --btn-text:   #ffffff;
      --link:         var(--brand);
      --link-hover:   var(--brand-600);
      --link-visited: color-mix(in srgb, var(--brand) 55%, rebeccapurple);
      --heading-color:#140a29;
      --cardBg:    var(--cardBg);
      --footerBg:   var(--footer-bg);
    }

    /* ใช้ตัวแปรที่ map กับองค์ประกอบจริง */
    html, body{ color: var(--text-color); background: var(--bg-color); }
    h1, h2, h3, h4, h5, h6{ color: var(--heading-color); }
    /* ให้ปุ่มหลักวิ่งตาม --btn-* */
    .btn-brand{ background: var(--btn-bg); color: var(--btn-text); }
    .btn.btn-light, .btn.btn-outline-light{ color:#111; }
    .btn.btn.btn-outline-light:hover{ color:#111 !important;}
    .service-card::before{background-color: var(--cardBg);}
  </style>
</head>
<body>

  <!-- NAVBAR -->
  <nav class="navbar navbar-expand-lg navbar-dark navbar-blur fixed-top">
    <div class="container">
      <a class="navbar-brand fw-semibold" href="#"><img src="../assets/img/customMas1/massage-logo.png" alt="Mas 1 logo" width="100" height="100" style="border-radius:10px;object-fit:cover;"></a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div id="nav" class="collapse navbar-collapse">
        <ul class="navbar-nav w-100 justify-content-center gap-lg-3">
          <li class="nav-item"><a class="nav-link" href="#">HOME</a></li>
          <li class="nav-item"><a class="nav-link" href="#">SERVICES</a></li>
          <li class="nav-item"><a class="nav-link" href="#">ABOUT</a></li>
          <li class="nav-item"><a class="nav-link" href="#">CONTACT</a></li>
        </ul>
        <div class="d-flex flex-column gap-2 mt-3 mt-lg-0" style="width: 200px;">
          <a class="btn btn-sm btn-brand" href="#booking">Book Now</a>
          <a class="btn btn-sm btn-outline-light" href="#">Buy Gift Voucher</a>
        </div>
      </div>
    </div>
  </nav>

  <!-- HERO -->
  <header class="hero">
    <div class="container">
      <span class="text-white mb-3 fs-2">Welcome</span>
      <h1 class="display-4 mb-3 text-white">Massage 1</h1>
      <div class="d-flex gap-3">
        <a href="#" class="btn btn-light btn-brand btn-md text-white py-2 px-5">Know More</a>
      </div>
    </div>
  </header>

  <!-- SERVICES -->
  <section id="services">
    <div class="container">

      <div class="row g-5">
        <div class="col-md-4">
          <div class="card service-card h-100 py-4">
            <div class="d-flex card-body p-4">
              <div class="d-flex flex-column align-content-between gap-3 flex-grow-1">
                <div class="text-white">
                  <img src="../assets/img/customMas1/service1.jpg" alt="" class="mb-3 w-25">
                  <p class="mb-3 fs-4">Traditional Thai</p>
                  <p>Treat yourself to our package and indulge in a luxurious spa experience like no other. Enjoy a blissful full-body Thai and oil massage to ease tension and promote relaxation, followed by a express facial massage to rejuvenate your skin.</p>
                </div>
                <div class="mt-auto">
                  <a href="#" class="btn btn-light btn-md px-4">Learn More</a>  
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card service-card h-100 py-4">
            <div class="d-flex card-body p-4">
               <div class="d-flex flex-column align-content-between gap-3 flex-grow-1">
                <div class="text-white">
                  <img src="../assets/img/customMas1/service2.webp" alt="" class="mb-3 w-25">
                  <p class="mb-3 fs-4">Foot Reflexology</p>
                  <p>Experience the ultimate pampering package! Enjoy a deeply relaxing full-body Thai and oil massage, followed by a rejuvenating Thai foot massage. Our expert therapists will leave you feeling renewed, ready to take on the world.</p>
                </div>
                <div class="mt-auto">
                  <a href="#" class="btn btn-light btn-md px-4">Learn More</a>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card service-card h-100 py-4">
            <div class="d-flex card-body p-4">
              <div class="d-flex flex-column align-content-between gap-3 flex-grow-1">
                <div class="text-white">
                  <img src="../assets/img/customMas1/service3.jpg" alt="" class="mb-3 w-25">
                  <p class="mb-3 fs-4">Foot Reflexology</p>
                  <p>Indulge in the ultimate luxury package. Enjoy a full-body Thai and oil massage to ease tension and promote relaxation, followed by a rejuvenating body scrub and moisturizer treatment to leave your skin silky smooth.</p>
                </div>
                <div class="mt-auto">
                  <a href="#" class="btn btn-light btn-md px-4">Learn More</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ABOUT -->
  <section id="about" class="bg-white about">
    <div class="container">
      <div class="row g-4 align-items-center">
        <div class="col-lg-6">
          <span class="mb-3 fs-5">Welcome to</span>
          <h1 class="display-4 mb-3 fs-1 fw-bold">Massage 1</h1>
          <p>Welcome to Massage 1, Revitalizing Authentic Massage. Leave your stress at our doorstep. We offer Thai massage in Massage 1 with a modern, family-friendly perspective.</p>
          <a href="#" class="btn btn-light btn-brand btn-md text-white py-2 px-5">About Us</a>
        </div>
        <div class="col-lg-6">
          <div class="row g-3">
            <div class="col-6">
              <img class="img-fluid rounded-3" src="../assets/img/customMas1/photo01.png" alt="">
            </div>
            <div class="col-6 pt-5">
              <img class="img-fluid rounded-3" src="../assets/img/customMas1/photo02.png" alt="">
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- TESTIMONIALS -->
  <section id="testimonials" class="testimonials">
    <div class="container">
      <div class="mb-5">
        <h1 class="display-4 mb-3 fs-1">Our Testimonials</h1>
        <p>Almost a hundred positive reviews on Google, Facebook and Socials.<br>
        We assure you our best services at all time.<br>
        Here’s what some of our recent customers say about us.</p>
      </div>

      <div class="row g-4">
        <div class="col-md-4">
          <div class="p-4 bg-white review-card h-100">
            <div class="stars mb-2"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></div>
            <p class="mb-3">Very relaxing and welcoming Thai massage minia is amazing with her technique special touches leave me refreshed I have been coming here for many years now always a pleasure</p>
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-person-circle fs-1 text-secondary"></i>
              <div><strong>User1</strong><div class="small text-muted">Customer</div></div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="p-4 bg-white review-card h-100">
            <div class="stars mb-2"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></div>
            <p class="mb-3">“My first time today and i highly recommend this place. Such nice and very effective massage service.<br>
            Will definitely be going back again.<br>
            Thank you guys! 😊 …”</p>
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-person-circle fs-1 text-secondary"></i>
              <div><strong>User2</strong><div class="small text-muted">Customer</div></div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="p-4 bg-white review-card h-100">
            <div class="stars mb-2"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></div>
            <p class="mb-3">“My husband is massage lover and quite fussy about anything, we have tried different places around Gold Coast, not ever once can make him 100% happy ! But this time he was happy and good price!”</p>
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-person-circle fs-1 text-secondary"></i>
              <div><strong>User3</strong><div class="small text-muted">Customer</div></div>
            </div>
          </div>
        </div>
      </div>

      <div class="mt-4">
        <img src="../assets/img/customMas1/google-logo.png" height="100" alt="Google logo">
      </div>
    </div>
  </section>

  <!-- TEAM / CTA -->
  <section class="bg-light">
    <div class="container">
      <div class="row g-5 align-items-center">
        <div class="col-lg-6">
          <img class="img-fluid rounded-4 shadow" src="../assets/img/customMas1/team1.png" alt="">
        </div>
        <div class="col-lg-6">
          <span class="mb-3 fs-5">Our Team</span>
          <h1 class="display-4 mb-3 fs-1 fw-bold">Massage 1</h1>
          <p>Our dedicated team of Thai Massage Therapists have practiced their skills on many of our clients who live and work in Gold Coast or travelers visiting our city. They use the traditional Thai techniques to relieve discomfort from muscular pain, shoulder stiffness, foot soreness, headaches and stress related conditions. Massage 1 is available for individuals, couples, families or large groups. We look forward to seeing and serving you soon.</p>
          <a href="#" class="btn btn-light btn-brand btn-md text-white py-2 px-5">Book an appointment</a>
        </div>
      </div>
    </div>
  </section>

  <!-- BOOKING -->
  <section id="booking">
    <div class="container">
      <div class="row g-4">
        <div class="col-lg-6">
          <div class="p-4 h-100">
            <span class="mb-3 fs-5">What Are You Waiting For...</span>
            <h1 class="display-4 mb-3 fs-1 fw-bold">Make an appointment</h1>
            <p>Don’t let stress, tension, or pain hold you back any longer. Book now and experience the ultimate in relaxation and rejuvenation with our expert massage services.</p>
            <hr class="my-5">
            <ul class="list-unstyled d-flex flex-column gap-3">
              <li>
                <div class="d-flex">
                  <i class="bi bi-geo-alt me-2 fs-5"></i>
                  <div class="col-11 py-1">
                    OUR LOCATION<br>
                    <span>123 Fifth Avenue, New York, NY</span>
                  </div>
                </div>
              </li>
              <li>
                <div class="d-flex">
                  <i class="bi bi-clock me-2 fs-5"></i>
                  <div class="col-11 py-1">
                    OPENING HOURS<br>
                    <span>Mon-Fri: 9 am - 5 pm<br>Sat-Sun: 9 am - 6 pm</span>
                  </div>
                </div>
              </li>
              <li>
                <div class="d-flex">
                  <i class="bi bi-telephone me-2 fs-5"></i>
                  <div class="col-11 py-1">
                    CONTACT<br>
                    <span>Phone : 123-456-7890<br>Email: contact@demo.massage</span>
                  </div>
                </div>
              </li>
            </ul>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="booking-shell d-lg-flex overflow-hidden">
            <div class="booking-sidebar p-4 col-lg-4">
              <h6 class="text-uppercase text-white-50 mb-3">Booking Steps</h6>
              <div class="booking-step active"><i class="bi bi-1-circle me-2"></i>Select Service</div>
              <div class="booking-step"><i class="bi bi-2-circle me-2"></i>Staff Selection</div>
              <div class="booking-step"><i class="bi bi-3-circle me-2"></i>Date &amp; Time</div>
              <div class="booking-step"><i class="bi bi-4-circle me-2"></i>Your Information</div>
            </div>
            <div class="bg-white p-4 col-lg-8 d-flex">
              <form class="d-flex flex-column align-content-between gap-3 flex-grow-1">
                <div class="col-12">
                  <label class="form-label">Service:</label>
                  <select class="form-select">
                    <option selected>Select Service</option>
                    <option>Traditional Thai (60m)</option>
                    <option>Foot Reflexology (45m)</option>
                    <option>Swedish Massage (60m)</option>
                  </select>
                </div>
                <div class="col-12">
                  <label class="form-label">Employee:</label>
                  <select class="form-select">
                    <option selected>Select Employee</option>
                    <option>Mina</option>
                    <option>Som</option>
                    <option>June</option>
                  </select>
                </div>
                <div class="col-12 text-end mt-auto">
                  <button type="button" class="btn btn-brand">Continue</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- PROMO STRIP -->
  <section class="py-3" style="background:#e2e2e4;">
    <div class="container d-flex align-items-center justify-content-between py-3">
      <div class="d-flex flex-column gap-2">
        <h2 class="fs-2">Get 5% Off 1st online booking for any massage.</h2>
        <span class="fs-4">Massage 1 - The trusted place for massage therapy</span>
      </div>
      <div>
        <a class="btn btn-brand btn-lg" href="#" style="color:#fff;">Book an Appointment</a>
      </div>
    </div>
  </section>

  <!-- CONTACT / MAP -->
  <section id="contact" class="text-white p-0" style="position: relative;">
    <div class="footer py-5">
      <div class="container pb-5">
        <div class="row g-4 pb-5">
          <!-- Left -->
          <div class="col-lg-7"  style="z-index: 1;">
            <div class="col-12 text-center text-white">
              <img src="../assets/img/customMas1/massage-logo.png"
                  alt="Mas 1 logo"
                  width="150" height="150"
                  style="border-radius:10px;object-fit:cover;">
              <p class="mt-3 mb-1">
                © <span id="year">2024</span> Massage 1 Website Made by
                <a class="link-light" href="#" rel="noopener">Local For You</a>
              </p>
              <ul class="list-unstyled mb-3">
                <li>
                  <a class="link-light" href="#">Privacy Policy</a>
                  <span class="mx-2">|</span>
                  <a class="link-light" href="#">Terms &amp; Conditions</a>
                </li>
              </ul>
              <ul class="navbar-nav d-flex flex-row w-100 justify-content-center gap-3">
                <li class="nav-item"><a class="nav-link px-2 text-white" href="#">HOME</a></li>
                <li class="nav-item"><a class="nav-link px-2 text-white" href="#">SERVICES</a></li>
                <li class="nav-item"><a class="nav-link px-2 text-white" href="#">ABOUT</a></li>
                <li class="nav-item"><a class="nav-link px-2 text-white" href="#">CONTACT</a></li>
              </ul>
            </div>
            <hr class="border-light-subtle my-4">
            <div class="row g-4">
              <div class="col-12 col-md-6">
                <div class="d-flex align-items-start gap-3">
                  <div>
                    <i class="bi bi-geo-alt fs-3" aria-hidden="true"></i><br>
                    <span class="mb-1 fs-5">Location:</span>
                    <address class="mb-3 mb-md-4">
                      123 Fifth Avenue,<br>
                      New York, NY 10160, United States
                    </address>
                    <div class="d-flex align-items-center gap-3 fs-5">
                      <a href="#" aria-label="Facebook" class="link-light"><i class="bi bi-facebook"></i></a>
                      <a href="mailto:info@example.com" aria-label="Email" class="link-light"><i class="bi bi-envelope"></i></a>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="d-flex align-items-start gap-3">
                  <div>
                    <i class="bi bi-telephone fs-3" aria-hidden="true"></i><br>
                    <span class="mb-1 fs-5">Contact:</span>
                    <p class="mb-2">Phone: <a class="link-light" href="tel:1234567890">123-456-7890</a></p>
                    <span class="mb-1 fs-5">Opening Hours:</span>
                    <ul class="list-unstyled mb-0">
                      <li>Mon–Fri: 9 am - 5 pm</li>
                      <li>Sat–Sun: 9 am - 6 pm</li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Right: Map -->
          <div class="col-lg-5">
            <div class="ratio ratio-4x3 overflow-hidden" style="width: 100%; height: 100%;">
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
    </div>
  </section>

  <script type="module" src="../controllers/colorPanel.js"></script>

  <color-control-panel
    storage-key="siteTheme-massage1"
    controls="text,bg,cardBg,footerBg,button,link,linkHover,linkVisited,heading"
    position="bottom">
  </color-control-panel>

  <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> -->
  <script src="../assets/libs/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
