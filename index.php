<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tourism Homepage</title>
    <link rel="stylesheet" href="indexStyle.css" />
  </head>
  <body>

  <?php include 'navBar.php'; ?>
    <section class="hero">
      <div class="hero__slideshow">
        <div
          class="hero__slide active"
          style="background-image: url(./Photos/gettyimages-1778481312-612x612.jpg)"
        ></div>
        <div
          class="hero__slide"
          style="background-image: url(./Photos/gettyimages-458404729-612x612.jpg)"
        ></div>
        <div
          class="hero__slide"
          style="background-image: url(./Photos/gettyimages-585595276-612x612.jpg)"
        ></div>
      </div>
      <div class="hero__overlay"></div>

      <div class="hero__content">
        <h1 class="hero__heading">Medium length hero heading goes here</h1>
        <p class="hero__text">
          Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse
          varius enim in eros elementum tristique.
        </p>

        <div class="hero__cta-group">
          <button class="hero__button hero__button--primary">
            Get Started
          </button>
          <button class="hero__button hero__button--secondary">
            Learn More
          </button>
        </div>

        <div class="hero__tabs">
          <div class="hero__tab active"></div>
          <div class="hero__tab"></div>
          <div class="hero__tab"></div>
        </div>
      </div>
    </section>
    <section class="services">
      <div class="services__container">
        <h2 class="services__title">Experience Lebanon Like Never Before</h2>
        <p class="services__subtitle">
          Discover our curated journeys through Lebanon's rich heritage and
          natural wonders
        </p>

        <div class="services__grid">
     
          <article class="service-card">
            <div
              class="service-card__image"
              style="
                background-image: url(./Photos/c0c0657e2756177fbd4cdd9872e1eabf.jpg);
              "
            ></div>
            <div class="service-card__content">
              <h3>Historical Expeditions</h3>
              <p>
                Explore ancient Phoenician cities, Roman temples, and Crusader
                castles with expert guides
              </p>
              <a href="#" class="service-card__cta">View Tours →</a>
            </div>
          </article>

          
          <article class="service-card">
            <div
              class="service-card__image"
              style="
                background-image: url(./Photos/159c4ce48474476275ebec8e7a557768.jpg);
              "
            ></div>
            <div class="service-card__content">
              <h3>Mountain Escapes</h3>
              <p>
                Hike through cedar reserves, ski the Qadisha Valley, or
                paraglide over coastal cliffs
              </p>
              <a href="#" class="service-card__cta">View Adventures →</a>
            </div>
          </article>

         
          <article class="service-card">
            <div
              class="service-card__image"
              style="
                background-image: url(./Photos/7df9136bb3e2ebc9bdca525fa6660374.jpg);
              "
            ></div>
            <div class="service-card__content">
              <h3>Culinary Journeys</h3>
              <p>
                From Beirut street food to vineyard tours - taste Lebanon's
                legendary cuisine
              </p>
              <a href="#" class="service-card__cta">Explore Foods →</a>
            </div>
          </article>

        
          <article class="service-card">
            <div
              class="service-card__image"
              style="
                background-image: url(./Photos/618d2b6fa4cc8464262aba90b3189ec8.jpg);
              "
            ></div>
            <div class="service-card__content">
              <h3>Luxury Retreats</h3>
              <p>
                Stay in Ottoman-era mansions or modern beachfront resorts with
                curated experiences
              </p>
              <a href="#" class="service-card__cta">View Stays →</a>
            </div>
          </article>
        </div>
      </div>
    </section>
    <?php
require 'config.php';

$query = "SELECT * FROM package ORDER BY package_id DESC LIMIT 4";
$result = $conn->query($query);
?>

<section class="packages">
  <div class="packages__container">
    <h2 class="packages__title">Explore Our Signature Tours</h2>
    <p class="packages__subtitle">
      Curated experiences showcasing Lebanon's diverse beauty and rich heritage
    </p>

    <div class="packages__grid">
      <?php while ($row = $result->fetch_assoc()): ?>
        <article class="package-card">
          <div
            class="package-card__image"
            style="background-image: url(./uploads/<?= htmlspecialchars($row['image']) ?>);">
            <?php if ($row['package_type'] === 'premium'): ?>
              <span class="package-badge">Best Seller</span>
            <?php elseif ($row['package_type'] === 'new'): ?>
              <span class="package-badge">New</span>
            <?php endif; ?>
          </div>
          <div class="package-card__content">
            <h3><?= htmlspecialchars($row['package_name']) ?></h3>
            <p><?= htmlspecialchars($row['description']) ?></p>
            <div class="package-details">
              <span class="price">$<?= htmlspecialchars($row['unit_price']) ?></span>
              <span class="duration"><?= htmlspecialchars($row['average_duration']) ?> Days</span>
            </div>
            <a href="details.php?id=<?= $row['package_id'] ?>" class="package-cta" style="text-decoration: none;">View Details</a>
          </div>
        </article>
      <?php endwhile; ?>
    </div>

    <div class="packages__cta">
  <button class="view-more" onclick="window.location.href='travel_page.php'">View All Packages</button>
</div>

  </div>
</section>

    <section class="gallery">
      <div class="gallery__container">
        <h2 class="gallery__title">Discover Lebanon's Hidden Gems</h2>
        <p class="gallery__subtitle">
          Explore breathtaking landscapes, ancient heritage sites, and vibrant
          cultural experiences that make Lebanon unique
        </p>

        <div class="gallery__grid">
          <div class="gallery-item">
            <img
              src="./Photos/e9637da78a59f6d03c1483050f48159c.jpg"
              alt="Lebanese coastline"
              class="gallery-image"
            />
            <div class="image-overlay">
              <span>Coastal Wonders</span>
            </div>
          </div>
          <div class="gallery-item">
            <img
              src="./Photos/b3254f8127f3153f865bd16f78410557.jpg"
              alt="Lebanese mountains"
              class="gallery-image"
            />
            <div class="image-overlay">
              <span>Mountain Escapes</span>
            </div>
          </div>
          <div class="gallery-item">
            <img
              src="./Photos/55687ad25d74cce8140a487c2897f67f.jpg"
              alt="Historical site"
              class="gallery-image"
            />
            <div class="image-overlay">
              <span>Ancient Heritage</span>
            </div>
          </div>
          <div class="gallery-item">
            <img
              src="./Photos/3899817ea0b56bc3538021fefb1d38aa.jpg"
              alt="Lebanese culture"
              class="gallery-image"
            />
            <div class="image-overlay">
              <span>Cultural Treasures</span>
            </div>
          </div>
        </div>

        <div class="gallery__cta">
          <button class="gallery-button">View All Photos</button>
        </div>
      </div>
    </section>
    <footer class="footer">
        <div class="footer__container">
            <div class="footer__main">
                <div class="footer__brand">
                    <div class="footer__logo">Tourism</div>
                    <p class="footer__tagline">Discover the Soul of Lebanon</p>
                </div>
                
                <nav class="footer__nav">
                    <div class="footer__column">
                        <h4 class="footer__heading">Explore</h4>
                        <ul class="footer__list">
                            <li><a href="#" class="footer__link">Homepage</a></li>
                            <li><a href="#" class="footer__link">Packages</a></li>
                        </ul>
                    </div>
                </nav>
            </div>
            
            <div class="footer__legal">
                <p class="footer__copyright">© 2025 Tourism. All rights reserved.</p>
                <div class="footer__legal-links">
                    <a href="#" class="footer__legal-link">Privacy Policy</a>
                    <a href="#" class="footer__legal-link">Terms of Service</a>
                    <a href="#" class="footer__legal-link">Cookies Settings</a>
                </div>
            </div>
        </div>
    </footer>
    <script>
      const slides = document.querySelectorAll(".hero__slide");
      const tabs = document.querySelectorAll(".hero__tab");
      let currentSlide = 0;

      function showSlide(index) {
        slides.forEach((slide) => slide.classList.remove("active"));
        tabs.forEach((tab) => tab.classList.remove("active"));

        slides[index].classList.add("active");
        tabs[index].classList.add("active");
      }

      function nextSlide() {
        currentSlide = (currentSlide + 1) % slides.length;
        showSlide(currentSlide);
      }

      let slideInterval = setInterval(nextSlide, 5000);

      tabs.forEach((tab, index) => {
        tab.addEventListener("click", () => {
          clearInterval(slideInterval);
          currentSlide = index;
          showSlide(currentSlide);
          slideInterval = setInterval(nextSlide, 5000);
        });
      });

      const hero = document.querySelector(".hero");
      hero.addEventListener("mouseenter", () => clearInterval(slideInterval));
      hero.addEventListener(
        "mouseleave",
        () => (slideInterval = setInterval(nextSlide, 5000))
      );
    </script>
  </body>
  </html>