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
<?php
require 'config.php';
$packageId = $_GET['id'] ?? 0;
$stmt = $conn->prepare("SELECT * FROM package WHERE package_id = ?");
$stmt->bind_param("i", $packageId);
$stmt->execute();
$package = $stmt->get_result()->fetch_assoc();
?>
    <div class="review-container">
        <div class="review-card">
            <div class="tour-header">
                <img src="../uploads/<?= htmlspecialchars($package['image']) ?>" class="tour-image" alt="Tour Image">
                <div>
                    <h2 class="tour-title"><?= htmlspecialchars($package['package_name']) ?></h2>
                    <p class="tour-date">Completed: <?= htmlspecialchars($package['end_date']) ?></p>
                </div>
            </div>

            <div class="rating-section">
                <div class="rating-category">
                    <span>Overall Experience</span>
                    <div class="stars" data-category="overall">
                        <i class="fas fa-star star"></i>
                        <i class="fas fa-star star"></i>
                        <i class="fas fa-star star"></i>
                        <i class="fas fa-star star"></i>
                        <i class="fas fa-star star"></i>
                    </div>
                </div>
                <div class="rating-category">
                    <span>Tour Guide</span>
                    <div class="stars" data-category="guide">
                        <i class="fas fa-star star"></i>
                        <i class="fas fa-star star"></i>
                        <i class="fas fa-star star"></i>
                        <i class="fas fa-star star"></i>
                        <i class="fas fa-star star"></i>
                    </div>
                </div>
                <div class="rating-category">
                    <span>Transportation</span>
                    <div class="stars" data-category="transport">
                        <i class="fas fa-star star"></i>
                        <i class="fas fa-star star"></i>
                        <i class="fas fa-star star"></i>
                        <i class="fas fa-star star"></i>
                        <i class="fas fa-star star"></i>
                    </div>
                </div>
            </div>

            <textarea class="review-text" placeholder="Share your experience..."></textarea>

            <div class="photo-upload">
                <i class="fas fa-camera fa-2x" style="color: var(--primary); margin-bottom: 1rem;"></i>
                <p>Click to upload tour photos<br>
                <span style="color: var(--text-light);">(JPEG, PNG up to 5MB each)</span></p>
                <input type="file" id="photoInput" multiple hidden accept="image/*">

            </div>

            <div class="uploaded-photos"></div>

            <button class="submit-btn">
                <i class="fas fa-paper-plane"></i>
                Submit Review
            </button>
        </div>

        <div class="past-reviews">
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

            <h2 style="margin: 3rem 0 2rem;">User Reviews</h2>
            <?php
            $rev = $conn->prepare("SELECT * FROM review WHERE package_id = ?");
            $rev->bind_param("i", $packageId);
            $rev->execute();
            $reviews = $rev->get_result();
            while ($r = $reviews->fetch_assoc()): ?>
            <div class="review-item">
                <div class="review-meta">
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
                <img src="uploads/<?= htmlspecialchars($r['image']) ?>" class="photo-preview" alt="Uploaded Image">


                </div>
                <?php endif; ?>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script>
        document.querySelectorAll('.star').forEach(star => {
            star.addEventListener('click', function() {
                const parent = this.parentElement;
                const stars = parent.querySelectorAll('.star');
                const clickedIndex = Array.from(stars).indexOf(this);
                stars.forEach((s, index) => {
                    s.classList.toggle('active', index <= clickedIndex);
                });
            });
        });

       

        document.querySelector('.submit-btn').addEventListener('click', function() {
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
            setTimeout(() => {
                this.innerHTML = '<i class="fas fa-check"></i> Submitted!';
                this.disabled = true;
            }, 1500);
        });
    </script>
</body>
</html>

<script>
    // Click to open the hidden file input
    document.querySelector(".photo-upload").addEventListener("click", function () {
        document.getElementById("photoInput").click();
    });

    // Handle file preview
    const photoInput = document.getElementById('photoInput');
    const uploadedPhotos = document.querySelector('.uploaded-photos');

    photoInput.addEventListener('change', function (e) {
        Array.from(e.target.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = (event) => {
                const div = document.createElement('div');
                div.style.position = 'relative';
                div.innerHTML = `
                    <img src="${event.target.result}" class="photo-preview">
                    <button class="remove-photo">&times;</button>
                `;
                div.querySelector('.remove-photo').addEventListener('click', () => div.remove());
                uploadedPhotos.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    });
</script>
