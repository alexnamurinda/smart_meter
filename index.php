<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="kooza_landingpage.css">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <title>smart meter project-kooza electric company</title>
    <link rel="stylesheet" href="style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet"
        href="https://use.fontawesome.com/releases/v5.5.0/css/all.css"
        integrity="sha384- 
        B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU"
        crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

    <?php
    // //PDO database connection
    // include 'databasecreation.php';
    // include 'db.php';

    $statusMessage = '';

    // contact form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Get the input values
        $name = $_POST['name'];
        $email = $_POST['email'];
        $subject = $_POST['subject'];
        $message = $_POST['message'];
        $sql = "INSERT INTO feedbacks (client_name, client_email, feedback_subject, feedback_message) VALUES (:name, :email, :subject, :message)";

        try {
            // Prepare the statement
            $stmt = $conn->prepare($sql);

            // Bind parameters to the statement
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':subject', $subject);
            $stmt->bindParam(':message', $message);

            $stmt->execute();

            // Set success message with user input
            $statusMessage = "Message sent successfully! We shall contact you soon!";
        } catch (PDOException $e) {
            $statusMessage = "Error: " . $e->getMessage();
        }
    }
    ?>

    <script>
        // Function to show the popup and hide it after 2 seconds
        function showPopup(message) {
            const popup = document.getElementById("popup");
            popup.innerText = message;
            popup.style.display = "block";

            // Hide popup after 2 seconds
            setTimeout(() => {
                popup.style.display = "none";
            }, 2000);
        }
    </script>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="logo">
            <img src="images/logo.png" alt="Logo" class="logo-image">
        </div>
        <div class="nav-links">
            <a href="#home">Home</a>
            <a href="#services">Services</a>
            <a href="#about">About Us</a>
            <a href="#how-it-works">How It Works</a>
            <a href="#pricing-section">Pricing</a>
            <a href="#contact-section">Contact</a>
        </div>
        <div class="menu-btn">☰</div>
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-bg"></div>
        <div class="hero-content" data-aos="fade-up">
            <h1 id="demo"></h1>
            <p>Track power usage, get alerts on low balances, and control your energy consumption with ease</p>
            <a href="getstarted.php" target="_blank" class="cta-button">Get Started</a>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services" id="services">
        <h2 style="text-align: center; margin-bottom: 3rem;">Our Services</h2>
        <div class="services-grid">
            <div class="service-card real-weather" data-aos="fade-up">
                <div class="service-card-image">
                    <img src="images/smartmeter.jpeg" alt="smartmeter Image" class="service-image">
                </div>

                <div class="service-icon">
                    <i class='bx bx-chip'></i>
                </div>
                <h3>Smart Energy Metering</h3>
                <p>Our Smart Meter solution provides individual energy tracking within multi-user buildings. <br /><br /> Each resident's power usage is measured and billed separately, ensuring fair and accurate billing across shared spaces.</p>
            </div>
            <div class="service-card field-sensor" data-aos="fade-up">
                <div class="service-card-image">
                    <img src="images/payer.png" alt="smart payment" class="service-image">
                </div>

                <div class="service-icon">
                    <i class='bx bx-credit-card'></i>
                </div>
                <h3>Automatic Payment & Disconnection</h3>
                <p>With our integrated mobile payment options, users can conveniently pay for energy usage via mobile money. <br /><br />The system ensures continuous service by automatically disconnecting users with insufficient balances until payments are made.</p>
            </div>
            <div class="service-card experts" data-aos="fade-up">
                <div class="service-card-image">
                    <img src="images/mornitoring.png" alt="Real time Monitoring" class="service-image">
                </div>

                <div class="service-icon">
                    <i class='bx bx-bar-chart-alt'></i>
                </div>
                <h3>Real-Time Usage Monitoring</h3>
                <p>Track and manage energy consumption in real time. <br /><br />Our system allows users to monitor their energy usage through a user-friendly online platform, providing insights that help optimize consumption and reduce unnecessary costs.</p>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about" id="about">
        <div class="about-content">
            <h2 style="text-align: center; margin-bottom: 2rem;">About Us</h2>
            <p style="text-align: center; margin-bottom: 3rem;">Kooza Electric is a pioneering technology company dedicated to creating innovative solutions that address pressing societal challenges. <br />Our projects span across multiple sectors, including energy, agriculture, healthcare, and ICT, with a shared vision of empowering communities and transforming lives.</p>

            <div class="mission-vision">
                <div class="mission-card" data-aos="zoom-in">
                    <div class="mission-vision-card-image">
                        <img src="images/mission_icon.png" alt="Mission Icon">
                    </div>
                    <h3>Our Mission</h3>
                    <p>To develop sustainable, technology-driven solutions that improve resource efficiency, promote equitable access to essential services, and empower individuals to thrive in shared spaces.</p>
                </div>
                <div class="vision-card" data-aos="zoom-in">
                    <div class="mission-vision-card-image">
                        <img src="images/vision_icon.png" alt="Vision Icon">
                    </div>
                    <h3>Our Vision</h3>
                    <p>To be a leader in innovative technology solutions that simplify complex systems, enhance quality of life, and foster sustainable development across sectors and communities.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="how-it-works" id="how-it-works">
        <h2 style="text-align: center; margin-bottom: 3rem;">How It Works</h2>
        <div class="steps-container">
            <div class="step-card" data-aos="fade-down">
                <h3>Step 1: Hardware Installation and Sign up</h3>
                <p>The Smart Meter is installed at your building to track energy usage for all users. <br>Each user then registers by providing their mobile number to create an account.</p>
            </div>
            <div class="step-card" data-aos="fade-up">
                <h3>Step 2: Real-Time Energy Monitoring</h3>
                <p>The system continuously tracks each user’s energy consumption and provides real-time data to monitor usage and costs via a central online platform.</p>
            </div>
            <div class="step-card" data-aos="fade-down" data-aos-delay="100">
                <h3>Step 3: Mobile Payments</h3>
                <p>Users can easily make payments for their energy consumption through mobile money. They will also receive alerts when their balance is running low.</p>
            </div>
            <div class="step-card" data-aos="fade-up" data-aos-delay="200">
                <h3>Step 4: Automatic Disconnection & Reconnection</h3>
                <p>When a user’s balance reaches zero, the system automatically disconnects the energy supply. Upon payment, the energy is reconnected, ensuring continuous service.</p>
            </div>
        </div>
    </section>

    <div class="pricing-section" id="pricing-section">
        <h2>Pricing Plans</h2>
        <div class="pricing-cards">
            <!-- Platinum Pricing Card -->
            <div class="pricing-card platinum">
                <div class="card-header">
                    <h3>Premium Pricing</h3>
                    <i class="fas fa-star fa-2x"></i> <!-- Font Awesome Star Icon -->
                </div>
                <p class="description">Hardware meter + Lifetime Subscription.</p>
                <p class="price">$1500 USD</p>
                <p class="maintenance">With maintenance service for 1 year.</p>
                <a href="#contact-section" style="text-decoration: none;"><button class="btn">Choose Plan</button></a>
            </div>

            <!-- Rental Pricing Card -->
            <div class="pricing-card rental">
                <div class="card-header">
                    <h3>Rental Pricing</h3>
                    <i class="fas fa-file-invoice-dollar fa-2x"></i>
                </div>
                <p class="description">Rent the hardware + Monthly Subscription.</p>
                <p class="price">$25 USD/month</p>
                <a href="#contact-section" style="text-decoration: none;"><button class="btn">Choose Plan</button></a>
            </div>
        </div>
    </div>

    <section class="team-section">
        <h2 style="text-align: center; margin-bottom: 3rem;">Meet Our Team</h2>
        <div class="team-grid">
            <!-- Team Member 1 -->
            <div class="team-card" data-aos="fade-up" data-aos-delay="100">
                <div class="team-card-image">
                    <img src="images/staff1.jpeg" alt="profile photo">
                    <div class="social-overlay">
                        <a href="#" class="social-icon"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-github"></i></a>
                    </div>
                </div>
                <div class="team-card-content">
                    <h3>Huzaifa Sserugo</h3>
                    <p>CEO</p>
                </div>
            </div>

            <!-- Team Member 2 -->
            <div class="team-card" data-aos="fade-up">
                <div class="team-card-image">
                    <img src="images/developer.jpg" alt="developer">
                    <div class="social-overlay">
                        <a href="https://www.linkedin.com/in/namurinda-alex-25217a255/" target="_blank" class="social-icon"><i class="fab fa-linkedin"></i></a>
                        <a href="https://x.com/namurindaalex43" target="_blank" class="social-icon"><i class="fab fa-twitter"></i></a>
                        <a href="https://github.com/namurindaalex" target="_blank" class="social-icon"><i class="fab fa-github"></i></a>
                    </div>
                </div>
                <div class="team-card-content">
                    <h3>Namurinda Alex</h3>
                    <p>Head Developer</p>
                </div>
            </div>

            <!-- Team Member 3 -->
            <div class="team-card" data-aos="fade-up" data-aos-delay="300">
                <div class="team-card-image">
                    <img src="images/will.jpg" alt="Williams">
                    <div class="social-overlay">
                        <a href="https://www.linkedin.com/in/ahaisibwe-william-376a42298/" target="_blank" class="social-icon"><i class="fab fa-linkedin"></i></a>
                        <a href="https://x.com/EngWilliam11" target="_blank" class="social-icon"><i class="fab fa-twitter"></i></a>
                        <a href="https://github.com/Willyahaisibwe" target="_blank" class="social-icon"><i class="fab fa-github"></i></a>
                    </div>
                </div>
                <div class="team-card-content">
                    <h3>Ahaisibwe William </h3>
                    <p>Head of Electronics</p>
                </div>
            </div>
        </div>
    </section>

    <section class="contact-section" id="contact-section">
        <?php if ($statusMessage): ?>
            <div id="popup" class="popup"></div>
            <script>
                // Display popup with PHP message
                showPopup("<?php echo $statusMessage; ?>");
            </script>
        <?php endif; ?>

        <div class="contact-container">
            <h2 style="text-align: center; margin-bottom: 1rem;">Contact Us</h2>
            <p style="text-align: center; margin-bottom: 3rem;">Have questions? We'd love to hear from you.</p>

            <form class="contact-form" action="" method="POST" data-aos="fade-up">
                <div class="form-group">
                    <input type="text" class="form-control" name="name" placeholder="Name" required>
                    <label class="floating-label" for="name">Name</label>
                </div>

                <div class="form-group">
                    <input type="email" class="form-control" name="email" placeholder="Email" required>
                    <label class="floating-label" for="email">Email</label>
                </div>

                <div class="form-group">
                    <input type="text" class="form-control" name="subject" placeholder="Subject" required>
                    <label class="floating-label" for="subject">Subject</label>
                </div>

                <div class="form-group">
                    <textarea class="form-control" name="message" placeholder="Message" required></textarea>
                    <label class="floating-label" for="message">Message</label>
                </div>

                <button type="submit" class="btn2">Send Message</button>
            </form>
        </div>

        <style>
            .popup {
                display: none;
                position: fixed;
                top: 20px;
                left: 50%;
                transform: translateX(-50%);
                background-color: #f6921e;
                color: white;
                padding: 15px;
                border-radius: 5px;
                z-index: 1000;
                white-space: pre-line;
            }
        </style>
    </section>


    <script src="https://static.elfsight.com/platform/platform.js" async></script>
<div class="elfsight-app-ed47ff79-c1aa-40ee-a5cb-1190ecb3d578" data-elfsight-app-lazy></div>

    <!-- Footer -->
    <footer id="contact">
        <div class="footer-content">
            <!--
            <div class="logo-footer">
                <div class="logo-footer-image">
                    <img src="images/logo.png" alt="">
                </div>
                <p>Protecting your bills through technology</p>
            </div>
            -->
            <div class="quick-links">
                <h2>Quick Links</h2>
                <p><a href="#home" style="color: white;">Home</a></p>
                <p><a href="#services" style="color: white;">Services</a></p>
                <p><a href="#about" style="color: white;">About Us</a></p>
                <p><a href="#how-it-works" style="color: white;">How It Works</a></p>
            </div>
            <div class="contact-us-footer">
                <h2>Contact Us</h2>
                <p><i class='bx bxs-envelope'></i>koozaec@gmail.com</p>
                <p><i class='bx bxs-phone-call'></i>Phone: +256780393671</p>
                <p><i class="fas fa-map-marker-alt"></i>Spring Road, Plot 145, Bugolobi</p>
            </div>
            <div class="socials">
                <h2>Follow us</h2>
                <p><a href="#"><i class='fab fa-facebook' style="color: #fff;"></i> Facebook</a></p>
                <p><a href="#"><i class='fab fa-instagram' style="color: #fff;"></i> Instagram</a></p>
                <p><a href="#"><i class="fab fa-linkedin" style="color: #fff;"></i> LinkedIn</a></p>
                <p><a href="#"><i class="fab fa-twitter" style="color: #fff;"></i> Twitter</a></p>
            </div>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        // Initialize AOS animation library
        AOS.init({
            duration: 1000,
            once: true
        });

        // Mobile menu toggle
        const menuBtn = document.querySelector('.menu-btn');
        const navLinks = document.querySelector('.nav-links');

        menuBtn.addEventListener('click', () => {
            navLinks.classList.toggle('active');
        });

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
                navLinks.classList.remove("active");
            });
        });
    </script>
    <script>
        var i = 0;
        var txt = 'Manage Your Energy Usage with Real-Time Monitoring';
        var speed = 50;

        function typeWriter() {
            if (i < txt.length) {
                document.getElementById("demo").innerHTML += txt.charAt(i);
                i++;
                setTimeout(typeWriter, speed);
            }
        }
        window.onload = typeWriter();
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        AOS.init({
            once: false,
            offset: 120,
            duration: 500,
            easing: 'ease-in-out',
        });
    </script>
</body>
</html>