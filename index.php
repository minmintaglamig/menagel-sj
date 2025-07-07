<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css">
    <link rel="icon" href="pictures/logo.png" type= "image">
    <link rel="stylesheet" href="css/index.css">
    <title>WiFi Business System</title>
</head>
<body>
    <header>
        <div class="logo">Menagel SJ</div>
        <button class="menu-toggle" onclick="toggleMenu()">
            <i class='bx bx-menu'></i>
        </button>
        <nav>
            <ul id="menu">
                <li><a href="#home">Home</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#promos">Promos</a></li>
                <li><a href="#contact">Contact</a></li>
                <li class="apply-now"><a href="applicationform.php">Apply Now</a></li>
            </ul>
        </nav>
    </header>

    <section class="landing" id="home">
        <h1> Menagel SJ </h1>
        <p> "Connecting Your Home to a World of Possibilities." </p>
        <div class="buttons">
            <a href="login.php">Log-In</a>
            <a href="signup.php">Sign-Up</a>
        </div>
    </section>

    <section class="about" id="about">
        <h2>About Us</h2>
        <p>We are a dedicated provider of reliable Wi-Fi services, committed to ensuring that our customers stay connected to the digital world without interruptions. Our plans are tailored to meet various internet needs, from basic browsing to high-speed streaming. With excellent customer support and competitive pricing, we aim to create a seamless experience for everyone.</p>
    </section>

    <section class="promos" id="promos">
    <h2>Our Promos</h2>
    <div class="cards">
        <div class="card red">
            <p class="tip"><strong>Unli Plan</strong>: ₱800</p>
            <p class="second-text"><strong>Up to</strong>: 20 Mbps</p>
            <p class="second-text">Monthly</p>
        </div>
        <div class="card blue">
            <p class="tip"><strong>Unli Plan</strong>: ₱1000</p>
            <p class="second-text"><strong>Up to</strong>: 40 Mbps</p>
            <p class="second-text">Monthly</p>
        </div>
        <div class="card green">
            <p class="tip"><strong>Unli Plan</strong>: ₱1500</p>
            <p class="second-text"><strong>Up to</strong>: 70 Mbps</p>
            <p class="second-text">Monthly</p>
        </div>
        <div class="card yellow">
            <p class="tip"><strong>Unli Plan</strong>: ₱2000</p>
            <p class="second-text"><strong>Up to</strong>: 100 Mbps</p>
            <p class="second-text">Monthly</p>
        </div>
    </div>
</section>

    <?php
        include('includes/dbh.php');

        $ads_query = "SELECT * FROM tbladvertisement WHERE is_visible = 1 ORDER BY created_at DESC";
        $ads_result = $conn->query($ads_query);
    ?>

    <section class="advertisements" id="advertisements" <?php echo ($ads_result->num_rows > 0) ? '' : 'style="display:none;"'; ?>>
        <h2>Advertisements</h2>
        <div class="advertisement-container">
            <?php while ($ad = $ads_result->fetch_assoc()): ?>
                <div class="advertisement-item">
                    <?php if (!empty($ad['image'])): ?>
                        <img src="uploads/advertisement/<?= htmlspecialchars($ad['image']) ?>" alt="Advertisement">
                    <?php endif; ?>
                    <h3><strong><?= htmlspecialchars($ad['title']) ?></strong></h3>
                    <p><i><?= htmlspecialchars($ad['content']) ?></i></p>
                </div>
            <?php endwhile; ?>
        </div>
    </section>

    <section class="contact" id="contact">
        <h2> Contact Us </h2>
        <p>
            <a href="https://www.facebook.com/profile.php?id=61558697566643" target="_blank">
                <i class='bx bxl-facebook' style="font-size: 2em; color: #4267B2;"></i> Menagel SJ
            </a>
        </p>
        <p>
            <i class='bx bxs-phone' style="font-size: 1.5em; color: #6D2323;"></i> +63----------
        </p>
        <p>
            <i class='bx bxs-map' style="font-size: 1.5em; color: #6D2323;"></i> Caingin Santa Rosa
        </p>
    </section>

    <?php include('faq_widget.php'); ?>

    <footer>
        @2025 Cyril Ash Managalino & Jarmine Nicole Perez collaboration with Menagel SJ. All rights reserved.
    </footer>

    <script>
        function toggleMenu() {
            document.getElementById("menu").classList.toggle("show");
        }

        document.addEventListener("DOMContentLoaded", function() {
            let announcementContainer = document.getElementById("announcement-container");
            let announcementSection = document.getElementById("announcements");
            
            let announcements = [];
            
            if (announcements.length > 0) {
                announcementSection.style.display = "block";
                announcementContainer.innerHTML = announcements.join(" ");
            }
        });
    </script>
</body>
</html>