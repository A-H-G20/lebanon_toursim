<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tour Reviews - Lebanon Tours</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/reviewPage.css">
</head>

<body>
<style>
    
</style>
    <?php
    require 'config.php';
    $packageId = $_GET['id'] ?? 0;
    $stmt = $conn->prepare("SELECT 
    p.*, 
    c.name AS city_name, 
    s.name AS state_name, 
    pt.id AS package_type_id,
    pt.name AS package_type_name
FROM package p
LEFT JOIN city c ON p.city_id = c.id
LEFT JOIN state s ON p.state_id = s.id
LEFT JOIN package_type pt ON p.package_type = pt.id
WHERE p.package_id = ?
");
    $stmt->bind_param("i", $packageId);
    $stmt->execute();
    $package = $stmt->get_result()->fetch_assoc();
    ?>

    <div class="review-container">
        <div class="review-card">
            <div class="tour-header">
                <img src="uploads/<?= htmlspecialchars($package['image']) ?>" class="tour-image" alt="Tour Image">

                <div class="tour-details">
                    <h2 class="tour-title"><?= htmlspecialchars($package['package_name']) ?></h2>
                    <p class="tour-date"><i class="fas fa-calendar-alt"></i> <?= htmlspecialchars($package['start_date']) ?> → <?= htmlspecialchars($package['end_date']) ?></p>

                    <div class="tour-info-grid">
                    <p><i class="fas fa-map-marker-alt"></i> <strong>Location:</strong> <?= htmlspecialchars($package['city_name']) ?>, <?= htmlspecialchars($package['state_name']) ?></p>

                        <p><i class="fas fa-dollar-sign"></i> <strong>Price:</strong> $<?= htmlspecialchars($package['unit_price']) ?></p>
                        
                        <div class="input-group">
  <label for="spotCount"><i class="fas fa-users"></i> Number of Spots</label>
  <div class="spot-stepper">
    <button type="button" id="decrease">−</button>
    <input 
      type="number" 
      id="spotCount" 
      name="spot_count" 
      value="1" 
      min="1" 
      max="<?= $package['available_spots'] ?>" 
      required
    >
    <button type="button" id="increase">+</button>
  </div>
</div>


<p style="font-size: 1.1rem; font-weight: bold; margin-top: 1rem;">
  💵 Total Price: <span id="totalPrice">$<?= htmlspecialchars($package['unit_price']) ?></span>
</p>

<input type="hidden" name="total_price" id="totalPriceInput" value="<?= $package['unit_price'] ?>">

                        <p><strong>Package Type:</strong> <?= htmlspecialchars($package['package_type_name']) ?></p>

                        <p><i class="fas fa-clock"></i> <strong>Avg. Duration:</strong> <?= htmlspecialchars($package['average_duration']) ?> hours</p>
                    </div>
                    <p style="margin-top:1rem;"><?= nl2br(htmlspecialchars($package['description'])) ?></p>
                    
                </div>
                 
            </div>


           
            <h2 style="margin-bottom: 2rem;">Tour Activities</h2>
            <?php
            $act = $conn->prepare("SELECT * FROM activity WHERE package_id = ?");
            $act->bind_param("i", $packageId);
            $act->execute();
            $activities = $act->get_result();
            while ($a = $activities->fetch_assoc()): ?>
                <div class="review-item">
                    <div class="review-meta">
                        <strong><?= htmlspecialchars($a['name']) ?></strong>
                        <span class="review-date"><?= htmlspecialchars($a['from_time']) ?> - <?= htmlspecialchars($a['to_time']) ?></span>
                    </div>
                    <p><?= htmlspecialchars($a['description']) ?></p>
                </div>
            <?php endwhile; ?>
            <a href="booking.php?package_id=<?= $package['package_id'] ?>" class="book-btn">Book</a>

        </div>

        <div class="past-reviews">
      
        <h3>Submit Your Review</h3>

<input type="text" id="reviewerName" class="review-text" placeholder="Your Name..." style="margin-bottom:1rem;">

<div class="rating-section">
    <div class="rating-category">
        <span>Rate This Tour</span>
        <div class="stars" id="ratingStars">
            <i class="fas fa-star star" data-value="1"></i>
            <i class="fas fa-star star" data-value="2"></i>
            <i class="fas fa-star star" data-value="3"></i>
            <i class="fas fa-star star" data-value="4"></i>
            <i class="fas fa-star star" data-value="5"></i>
        </div>
    </div>
</div>

<textarea id="reviewDescription" class="review-text" placeholder="Share your experience..."></textarea>

<div class="photo-upload">
    <i class="fas fa-camera fa-2x" style="color: var(--primary); margin-bottom: 1rem;"></i>
    <p>Click to upload a review photo<br>
        <span style="color: var(--text-light);">(JPEG, PNG up to 5MB)</span>
    </p>
    <input type="file" id="photoInput" hidden accept="image/*">
</div>

<div class="uploaded-photos" id="photoPreview"></div>

<button class="submit-btn" id="submitReviewBtn">
    <i class="fas fa-paper-plane"></i> Submit Review
</button>
            <h2 style="margin: 3rem 0 2rem;">User Reviews</h2>
            <?php
            $rev = $conn->prepare("SELECT * FROM review WHERE package_id = ? ORDER BY review_id DESC");
            $rev->bind_param("i", $packageId);
            $rev->execute();
            $reviews = $rev->get_result();
            while ($r = $reviews->fetch_assoc()): ?>
                <div class="review-item">
                    <div class="review-meta">
                        <strong><?= htmlspecialchars($r['name']) ?></strong>
                        <div class="stars">
                            <?php for ($i = 0; $i < $r['rating']; $i++): ?>
                                <i class="fas fa-star" style="color: var(--accent);"></i>
                            <?php endfor; ?>
                        </div>
                        <span class="review-date"><?= date('F Y') ?></span>
                    </div>
                    <p>"<?= htmlspecialchars($r['review_description']) ?>"</p>
                    <?php if ($r['image']): ?>
                        <div class="uploaded-photos">
                            <img src="uploads/<?= htmlspecialchars($r['image']) ?>" class="photo-preview" alt="Review Image">
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script>
        // Rating stars logic
        let selectedRating = 0;
        document.querySelectorAll('#ratingStars .star').forEach(star => {
            star.addEventListener('click', function() {
                selectedRating = this.getAttribute('data-value');
                document.querySelectorAll('#ratingStars .star').forEach(s => s.classList.remove('active'));
                for (let i = 0; i < selectedRating; i++) {
                    document.querySelectorAll('#ratingStars .star')[i].classList.add('active');
                }
            });
        });

        // Photo upload preview
        document.querySelector(".photo-upload").addEventListener("click", function() {
            document.getElementById("photoInput").click();
        });

        document.getElementById('photoInput').addEventListener('change', function(e) {
            const photoPreview = document.getElementById('photoPreview');
            photoPreview.innerHTML = '';
            Array.from(e.target.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = (event) => {
                    const img = document.createElement('img');
                    img.src = event.target.result;
                    img.className = "photo-preview";
                    photoPreview.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        });

        // Submit Review button logic
        document.getElementById('submitReviewBtn').addEventListener('click', async function() {
            const name = document.getElementById('reviewerName').value.trim();
            const description = document.getElementById('reviewDescription').value.trim();
            const photo = document.getElementById('photoInput').files[0];

            if (!name || !description || selectedRating == 0) {
                alert("Please complete all fields!");
                return;
            }

            const formData = new FormData();
            formData.append('name', name);
            formData.append('description', description);
            formData.append('rating', selectedRating);
            formData.append('package_id', <?= $packageId ?>);
            if (photo) formData.append('photo', photo);

            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
            this.disabled = true;

            const response = await fetch('submit_review.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.text();
            if (result == 'success') {
                this.innerHTML = '<i class="fas fa-check"></i> Submitted!';
                window.location.reload();
            } else {
                this.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Review';
                this.disabled = false;
                alert('Error submitting review');
            }
        });
    </script>

</body>

</html>
<STYle>
    .tour-header {
        display: flex;
        gap: 2rem;
        flex-wrap: wrap;
        align-items: flex-start;
        background: #f9f9f9;
        padding: 1.5rem;
        border-radius: 16px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.06);
        margin-bottom: 2rem;
    }

    .tour-image {
        width: 300px;
        height: auto;
        border-radius: 12px;
        object-fit: cover;
    }

    .tour-details {
        flex: 1;
        min-width: 280px;
    }

    .tour-title {
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        color: #333;
    }

    .tour-date {
        font-size: 1rem;
        color: #666;
        margin-bottom: 1rem;
    }

    .tour-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.7rem 2rem;
        font-size: 1rem;
    }

    .tour-info-grid p {
        margin: 0;
        color: #444;
    }

    .tour-info-grid i {
        margin-right: 6px;
        color: var(--primary, #2a6559);
    }
    /* Container for the stepper */
.spot-stepper {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-top: 0.5rem;
}

/* Input field */
.spot-stepper input[type="number"] {
  width: 60px;
  text-align: center;
  font-size: 1rem;
  padding: 0.3rem 0.5rem;
  border: 1px solid #ccc;
  border-radius: 8px;
  background-color: #f9f9f9;
  outline: none;
}

/* Plus and minus buttons */
.spot-stepper button {
  padding: 0.4rem 1rem;
  font-size: 1.1rem;
  border: none;
  background-color: var(--primary, #2a6559);
  color: white;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.2s ease;
}

.spot-stepper button:hover {
  background-color: #0c7b82;
}

</STYle>

<script>
  const countInput = document.getElementById("spotCount");
  const increaseBtn = document.getElementById("increase");
  const decreaseBtn = document.getElementById("decrease");
  const totalPrice = document.getElementById("totalPrice");
  const totalPriceInput = document.getElementById("totalPriceInput");

  const maxSpots = parseInt(countInput.max);
  const pricePerSpot = <?= $package['unit_price'] ?>;

  function updatePrice() {
    const count = parseInt(countInput.value);
    const total = count * pricePerSpot;
    totalPrice.textContent = `$${total}`;
    totalPriceInput.value = total;
  }

  increaseBtn.addEventListener("click", () => {
    let val = parseInt(countInput.value);
    if (val < maxSpots) {
      countInput.value = val + 1;
      updatePrice();
    }
  });

  decreaseBtn.addEventListener("click", () => {
    let val = parseInt(countInput.value);
    if (val > 1) {
      countInput.value = val - 1;
      updatePrice();
    }
  });

  countInput.addEventListener("input", () => {
    let val = parseInt(countInput.value);
    if (val < 1) countInput.value = 1;
    if (val > maxSpots) countInput.value = maxSpots;
    updatePrice();
  });

  // Initial call
  updatePrice();
</script>
