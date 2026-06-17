<!DOCTYPE html>
<html lang="en">
  <head>
  <meta charset="UTF-8">
  <title>Beerta Daarusalaam – Sports Facility Booking</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="assets/css/home_page.css">


  </head>
  <body>

    <!-- HEADER -->
    <header class="main-header" id="header">
      <div class="header-inner">
        <a href="#" class="logo">
          <div class="logo-icon">
            <img src="assets/logo_bd.png" alt="Logo">
          </div>
          <span class="logo-name">Beerta <span>Daarusalaam</span></span>
        </a>

        <nav class="nav-links" id="navMenu">
          <a href="#" class="active">Home</a>
          <a href="customer/view_facilities.php">Facilities</a>
          <a href="#hiw">How it Works</a>
          <a href="auth/login.php">Login</a>
          <a href="auth/register.php" class="btn-nav-register"><i class="bi bi-person-plus"></i> Register</a>
        </nav>

        <div class="menu-toggle" onclick="document.getElementById('navMenu').classList.toggle('open')">
          <i class="bi bi-list"></i>
        </div>
      </div>
    </header>

    <!-- HERO -->
    <section class="hero">
      <div class="hero-bg"></div>
      <div class="hero-grid"></div>
      <div class="orb orb-1"></div>
      <div class="orb orb-2"></div>

      <div class="hero-content">
        <div class="hero-badge"><i class="bi bi-lightning-charge-fill"></i> Mogadishu's #1 Sports Platform</div>

        <h1 class="hero-title">
          BOOK YOUR<br>
          <span class="line-accent">PERFECT</span><br>
          <span class="line-outline">ARENA</span>
        </h1>

        <p class="hero-sub">
          Football fields, basketball courts, and swimming pools —
          real-time availability, instant booking, secure payments.
        </p>

        <div class="hero-actions">
          <a href="customer/view_facilities.php" class="btn-hero-primary">
            <i class="bi bi-search"></i> Browse Facilities
          </a>
          <a href="auth/register.php" class="btn-hero-secondary">
            Get Started Free <i class="bi bi-arrow-right"></i>
          </a>
        </div>
      </div>

      <div class="hero-stats-strip">
        <div class="stat-item">
          <div class="stat-num">20+</div>
          <div class="stat-label">Bookings Made</div>
        </div>
        <div class="stat-item">
          <div class="stat-num">3</div>
          <div class="stat-label">Facility Types</div>
        </div>
        <div class="stat-item">
          <div class="stat-num">24/7</div>
          <div class="stat-label">Online Booking</div>
        </div>
        <div class="stat-item">
          <div class="stat-num">98%</div>
          <div class="stat-label">Satisfaction</div>
        </div>
      </div>
    </section>

    <!-- FACILITIES -->
    <section style="background:var(--surface); border-top:1px solid var(--border); padding:100px 0;">
    <div class="section">
      <div class="reveal">
        <span class="section-label">Our Venues</span>
        <h2 class="section-title">World-Class<br><span>Facilities</span></h2>
        <p class="section-desc">Choose from our premium sports venues, each maintained to professional standards and available to book 24/7.</p>
      </div>

      <div class="fac-slider-wrap reveal">
 
        <!-- SLIDES -->
        <div class="fac-slider" id="facSlider">
          <!-- Slide 1 — Football -->
            <div class="fac-slide active" data-index="0">
                <div class="slide-img-wrap">
                    <img src="assets/uploads/futsal.jpg"
                        alt="Football Field"
                        onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                    <div class="slide-img-fallback" style="display:none">⚽</div>
                </div>
                <div class="slide-overlay"></div>
                <div class="slide-content">
                    <div class="slide-tag">⚡ Available Now</div>
                    <h3 class="slide-name">Football Field</h3>
                    <div class="slide-meta">
                        <span><i class="bi bi-people-fill"></i> Up to 22 players</span>
                        <span><i class="bi bi-geo-alt-fill"></i> North Wing</span>
                    </div>
                    <a href="customer/view_facilities.php" class="slide-btn">
                        Book Now <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
    
            <!-- Slide 2 — Basketball -->
            <div class="fac-slide" data-index="1">
                <div class="slide-img-wrap">
                    <img src="assets/uploads/basketball.jpeg"
                        alt="Basketball Court"
                        onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                    <div class="slide-img-fallback" style="display:none">🏀</div>
                </div>
                <div class="slide-overlay"></div>
                <div class="slide-content">
                    <div class="slide-tag">🔥 Hot Pick</div>
                    <h3 class="slide-name">Basketball Court</h3>
                    <div class="slide-meta">
                        <span><i class="bi bi-people-fill"></i> Up to 10 players</span>
                        <span><i class="bi bi-geo-alt-fill"></i> South Wing</span>
                    </div>
                    <a href="customer/view_facilities.php" class="slide-btn">
                        Book Now <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
    
            <!-- Slide 3 — Swimming Pool -->
            <div class="fac-slide" data-index="2">
                <div class="slide-img-wrap">
                    <img src="assets/uploads/swimming_pool.jpg"
                        alt="Swimming Pool"
                        onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                    <div class="slide-img-fallback" style="display:none">🏊</div>
                </div>
                <div class="slide-overlay"></div>
                <div class="slide-content">
                    <div class="slide-tag">⭐ Premium</div>
                    <h3 class="slide-name">Swimming Pool</h3>
                    <div class="slide-meta">
                        <span><i class="bi bi-droplet-fill"></i> Olympic size</span>
                        <span><i class="bi bi-star-fill"></i> Top rated</span>
                    </div>
                    <a href="customer/view_facilities.php" class="slide-btn">
                        Book Now <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
    
            
        </div><!-- /.fac-slider -->
    
        <!-- Controls -->
        <button class="slider-arrow slider-prev" id="sliderPrev" aria-label="Previous">
            <i class="bi bi-chevron-left"></i>
        </button>
        <button class="slider-arrow slider-next" id="sliderNext" aria-label="Next">
            <i class="bi bi-chevron-right"></i>
        </button>
    
        <!-- Dots -->
        <div class="slider-dots" id="sliderDots">
            <button class="dot active" data-index="0"></button>
            <button class="dot" data-index="1"></button>
            <button class="dot" data-index="2"></button>
        </div>
    
        <!-- Progress bar -->
        <div class="slider-progress">
            <div class="slider-progress-fill" id="sliderProgress"></div>
        </div>
  
      </div>
    </div>
    </section>

    <!-- HOW IT WORKS -->
    <section class="hiw-section" id="hiw">
      <div class="hiw-inner">
        <div class="hiw-header reveal">
          <span class="section-label">Simple Process</span>
          <h2 class="section-title">Ready in <span>5 Steps</span></h2>
          <p style="color:var(--muted);font-size:15px;max-width:420px;margin:.5rem auto 0;line-height:1.7;">
            From signing up to stepping onto the field — it takes less than 3 minutes.
          </p>
        </div>

        <div class="steps-track reveal">
          <div class="step-item">
            <div class="step-dot">
              <i class="bi bi-person-plus-fill"></i>
              <div class="step-num">1</div>
            </div>
            <div class="step-title">Register</div>
            <div class="step-desc">Create your free account in seconds</div>
          </div>
          <div class="step-item">
            <div class="step-dot">
              <i class="bi bi-search"></i>
              <div class="step-num">2</div>
            </div>
            <div class="step-title">Explore</div>
            <div class="step-desc">Browse available facilities & slots</div>
          </div>
          <div class="step-item">
            <div class="step-dot">
              <i class="bi bi-calendar2-check-fill"></i>
              <div class="step-num">3</div>
            </div>
            <div class="step-title">Book</div>
            <div class="step-desc">Choose your date and time slot</div>
          </div>
          <div class="step-item">
            <div class="step-dot">
              <i class="bi bi-credit-card-2-front-fill"></i>
              <div class="step-num">4</div>
            </div>
            <div class="step-title">Pay</div>
            <div class="step-desc">Secure deposit-based payment</div>
          </div>
          <div class="step-item">
            <div class="step-dot">
              <i class="bi bi-patch-check-fill"></i>
              <div class="step-num">5</div>
            </div>
            <div class="step-title">Play</div>
            <div class="step-desc">Get confirmed & show up ready</div>
          </div>
        </div>
      </div>
    </section>

    <!-- FEATURES -->
    <section class="features-section">
      <div class="features-grid">
        <div class="features-left reveal">
          <span class="section-label">Why Choose Us</span>
          <h2 class="section-title">Everything You<br><span>Need to Play</span></h2>
          <p class="section-desc">Our platform is built for players, teams, and communities who demand the best booking experience.</p>

          <div class="feat-cards">
            <div class="feat-card">
              <div class="feat-icon"><i class="bi bi-lightning-charge-fill"></i></div>
              <div>
                <h4>Real-Time Availability</h4>
                <p>Live schedule updates with instant conflict detection. No double bookings, ever.</p>
              </div>
            </div>
            <div class="feat-card">
              <div class="feat-icon"><i class="bi bi-shield-fill-check"></i></div>
              <div>
                <h4>Secure Payments</h4>
                <p>Deposit-based system with full transaction history and instant confirmation.</p>
              </div>
            </div>
            <div class="feat-card">
              <div class="feat-icon"><i class="bi bi-clock-history"></i></div>
              <div>
                <h4>Booking History</h4>
                <p>Manage, reschedule, and review all your bookings from one clean dashboard.</p>
              </div>
            </div>
            <div class="feat-card">
              <div class="feat-icon"><i class="bi bi-chat-dots-fill"></i></div>
              <div>
                <h4>24/7 Chatbot Support</h4>
                <p>Instant AI-powered assistance guides you through every step of the booking.</p>
              </div>
            </div>
          </div>
        </div>
        <div class="features-right reveal">
        
          <!-- Phone -->
          <div class="phone">
            <div class="phone-frame">
        
              <!-- Dynamic island -->
              <div class="p-island"></div>
        
              <!-- Screen -->
              <div class="p-screen">
        
                <!-- Status bar -->
                <div class="p-statusbar">
                  <span class="p-time">9:41</span>
                  <div class="p-icons">
                    <i class="bi bi-reception-4"></i>
                    <i class="bi bi-wifi"></i>
                    <i class="bi bi-battery-half"></i>
                  </div>
                </div>
        
                <!-- Header -->
                <div class="p-header">
                  <span class="p-logo">Sports booking system</span>
                  <div class="p-bell">
                    <i class="bi bi-bell-fill"></i>
                    <div class="p-bell-dot"></div>
                  </div>
                </div>
        
                <!-- Greeting -->
                <div class="p-greet">
                  <span class="p-greet-sub">Good evening,</span>
                  <span class="p-greet-name">Mohamed Abdullah 👋</span>
                </div>
        
                <!-- Hero booking card -->
                <div class="p-hero">
                  <span class="p-hero-date">Tonight · May 15</span>
                  <span class="p-hero-title">Court A · Futsal</span>
                  <div class="p-hero-row">
                    <button class="p-book-btn">
                      <i class="bi bi-play-fill"></i> Book Now
                    </button>
                    <span class="p-time-pill">8:00 – 9:30 PM</span>
                  </div>
                </div>
        
                <!-- Stats -->
                <div class="p-stats">
                  <div class="p-stat">
                    <b>12</b><span>Sessions</span>
                  </div>
                  <div class="p-stat">
                    <b>5</b><span>Courts</span>
                  </div>
                  <div class="p-stat">
                    <b>98%</b><span>Uptime</span>
                  </div>
                </div>
        
                <!-- Features list -->
                <p class="p-sec-label">Features</p>
        
                <div class="p-feats">
        
                  <div class="p-feat">
                    <div class="p-feat-icon g">
                      <i class="bi bi-lightning-charge-fill"></i>
                    </div>
                    <div class="p-feat-text">
                      <span>Live Availability</span>
                      <small>Zero conflicts, always</small>
                    </div>
                    <span class="p-badge live">Live</span>
                  </div>
        
                  <div class="p-feat">
                    <div class="p-feat-icon b">
                      <i class="bi bi-shield-fill-check"></i>
                    </div>
                    <div class="p-feat-text">
                      <span>Secure Payments</span>
                      <small>Encrypted & instant</small>
                    </div>
                    <span class="p-badge safe">Safe</span>
                  </div>
        
                  <div class="p-feat">
                    <div class="p-feat-icon o">
                      <i class="bi bi-clock-history"></i>
                    </div>
                    <div class="p-feat-text">
                      <span>Booking History</span>
                      <small>All sessions, one place</small>
                    </div>
                    <span class="p-badge fast">Fast</span>
                  </div>
        
                  <div class="p-feat">
                    <div class="p-feat-icon p">
                      <i class="bi bi-chat-dots-fill"></i>
                    </div>
                    <div class="p-feat-text">
                      <span>24/7 AI Support</span>
                      <small>Always here to help</small>
                    </div>
                    <span class="p-badge newbadge">New</span>
                  </div>
        
                </div>
        
                <!-- Bottom nav -->
                <div class="p-nav">
                  <div class="p-nav-item active">
                    <i class="bi bi-house-fill"></i>
                    <div class="p-nav-dot"></div>
                  </div>
                  <div class="p-nav-item">
                    <i class="bi bi-calendar3"></i>
                  </div>
                  <div class="p-nav-item">
                    <i class="bi bi-people"></i>
                  </div>
                  <div class="p-nav-item">
                    <i class="bi bi-search"></i>
                  </div>
                </div>
        
              </div>
              <!-- /p-screen -->
            </div>
          </div>
          <!-- /phone -->
        
        </div>
      </div>
    </section>
    

    <!-- TESTIMONIALS -->
    <section class="social-section">
      <div class="social-inner">
        <div class="reveal">
          <span class="section-label">Community Love</span>
          <h2 class="section-title">What Players <span>Say</span></h2>
        </div>
        <div class="testimonial-grid reveal">
          <div class="test-card">
            <div class="stars">★★★★★</div>
            <p class="test-text">"Booking was incredibly smooth. Found an open football field, paid the deposit, and got a confirmation in under 2 minutes. This is the future."</p>
            <div class="test-author">
              <div class="test-avatar">AM</div>
              <div>
                <div class="test-name">Ahmed Mohamed</div>
                <div class="test-role">Football Team Captain</div>
              </div>
            </div>
          </div>
          <div class="test-card">
            <div class="stars">★★★★★</div>
            <p class="test-text">"I use the basketball court every weekend. The live availability feature saves me so much time — no more calling ahead to check if it's free."</p>
            <div class="test-author">
              <div class="test-avatar">FH</div>
              <div>
                <div class="test-name">Fatima Hassan</div>
                <div class="test-role">Basketball Player</div>
              </div>
            </div>
          </div>
          <div class="test-card">
            <div class="stars">★★★★★</div>
            <p class="test-text">"The chatbot support guided me through my first booking step by step. Great platform for the community — finally a proper sports booking system in Mogadishu."</p>
            <div class="test-author">
              <div class="test-avatar">OA</div>
              <div>
                <div class="test-name">Omar Abdi</div>
                <div class="test-role">Swimming Enthusiast</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="cta-section reveal">
      <div class="cta-box">
        <h2 class="cta-title">READY TO<br><span>PLAY?</span></h2>
        <p class="cta-sub">Join hundreds of players already booking through Beerta Daarusalaam. It's free to register.</p>
        <div class="cta-actions">
          <a href="auth/register.php" class="btn-hero-primary">
            <i class="bi bi-person-plus-fill"></i> Create Free Account
          </a>
          <a href="customer/view_facilities.php" class="btn-hero-secondary">
            View Facilities <i class="bi bi-arrow-right"></i>
          </a>
        </div>
      </div>
    </section>

    <!-- FOOTER -->
    <footer class="site-footer">
      <div class="footer-inner">
        <div>
          <div class="footer-brand-name">Beerta <span>Daarusalaam</span></div>
          <p class="footer-desc">A modern sports facility booking platform delivering easy access, real-time availability, and secure payments for the Mogadishu community.</p>
          <p style="font-size:12px;color:var(--muted);">Trusted by hundreds of local users</p>
        </div>

        <div class="footer-col">
          <h5>Navigation</h5>
          <ul>
            <li><a href="#">Home</a></li>
            <li><a href="customer/view_facilities.php">Facilities</a></li>
            <li><a href="#hiw">How It Works</a></li>
            <li><a href="auth/login.php">Login</a></li>
            <li><a href="auth/register.php">Register</a></li>
          </ul>
        </div>

        <div class="footer-col">
          <h5>Facilities</h5>
          <ul>
            <li>Football Fields</li>
            <li>Basketball Courts</li>
            <li>Swimming Pool</li>
            <li>Community Sports</li>
          </ul>
        </div>

        <div class="footer-col">
          <h5>Contact</h5>
          <div class="footer-contact-item"><i class="bi bi-geo-alt-fill"></i> Mogadishu, Somalia</div>
          <div class="footer-contact-item"><i class="bi bi-telephone-fill"></i> +252 61 1330011</div>
          <div class="footer-contact-item"><i class="bi bi-envelope-fill"></i> support@beertadaarusalaam.com</div>
        </div>
      </div>

      <div class="footer-bottom">
        <span>© 2026 Beerta Daarusalaam. All Rights Reserved.</span>
        <div class="footer-bottom-links">
          <a href="#">Privacy Policy</a>
          <a href="#">Terms of Service</a>
        </div>
      </div>
    </footer>

   

    <script>
      // Header scroll effect
      const header = document.getElementById('header');
      window.addEventListener('scroll', () => {
        header.classList.toggle('scrolled', window.scrollY > 20);
      });

      // Scroll reveal
      const revealEls = document.querySelectorAll('.reveal');
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(e => {
          if (e.isIntersecting) {
            e.target.classList.add('visible');
            observer.unobserve(e.target);
          }
        });
      }, { threshold: 0.12 });
      revealEls.forEach(el => observer.observe(el));

      // Facility slider
      (function () {
        const slides    = document.querySelectorAll('.fac-slide');
        const dots      = document.querySelectorAll('.dot');
        const progress  = document.getElementById('sliderProgress');
        const INTERVAL  = 4500; // ms per slide
        let current     = 0;
        let timer       = null;
        let elapsed     = 0;
        let rafId       = null;
        let lastTime    = null;
    
        function goTo(idx) {
            slides[current].classList.remove('active');
            slides[current].classList.add('exit');
            dots[current].classList.remove('active');
    
            setTimeout(() => slides[(idx + slides.length - 1) % slides.length === current
                ? (current + slides.length - 1) % slides.length
                : current]?.classList.remove('exit'), 700);
    
            current = (idx + slides.length) % slides.length;
            slides[current].classList.add('active');
            dots[current].classList.add('active');
    
            elapsed  = 0;
            lastTime = null;
            progress.style.width = '0%';
        }
    
        function startProgress() {
            cancelAnimationFrame(rafId);
            function tick(ts) {
                if (!lastTime) lastTime = ts;
                elapsed += ts - lastTime;
                lastTime = ts;
                const pct = Math.min((elapsed / INTERVAL) * 100, 100);
                progress.style.width = pct + '%';
                if (elapsed < INTERVAL) {
                    rafId = requestAnimationFrame(tick);
                } else {
                    goTo(current + 1);
                    rafId = requestAnimationFrame(tick);
                }
            }
            rafId = requestAnimationFrame(tick);
        }
    
        // Init
        slides[0].classList.add('active');
        dots[0].classList.add('active');
        startProgress();
    
        // Arrows
        document.getElementById('sliderPrev').addEventListener('click', () => {
            cancelAnimationFrame(rafId);
            goTo(current - 1);
            startProgress();
        });
        document.getElementById('sliderNext').addEventListener('click', () => {
            cancelAnimationFrame(rafId);
            goTo(current + 1);
            startProgress();
        });
    
        // Dots
        dots.forEach(dot => {
            dot.addEventListener('click', () => {
                cancelAnimationFrame(rafId);
                goTo(+dot.dataset.index);
                startProgress();
            });
        });
    
        // Pause on hover
        const wrap = document.querySelector('.fac-slider-wrap');
        wrap.addEventListener('mouseenter', () => cancelAnimationFrame(rafId));
        wrap.addEventListener('mouseleave', () => { lastTime = null; startProgress(); });
    
        // Touch swipe
        let touchX = 0;
        wrap.addEventListener('touchstart', e => touchX = e.touches[0].clientX);
        wrap.addEventListener('touchend',   e => {
            const diff = touchX - e.changedTouches[0].clientX;
            if (Math.abs(diff) > 40) {
                cancelAnimationFrame(rafId);
                goTo(diff > 0 ? current + 1 : current - 1);
                startProgress();
            }
        });
      })();
    </script>
  </body>
</html>
