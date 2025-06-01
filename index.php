<?php
include 'db_connection.php'; // db connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name    = $_POST['name'] ?? '';
    $email   = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';

    $stmt = $conn->prepare("INSERT INTO feedbacks (name, email, subject, message) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $subject, $message);

    if ($stmt->execute()) {
        echo "<script>alert('Thank you for your feedback!'); window.location.href='index.php';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Homewatt-Home </title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="styles/landingpage.css">
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="images/dashlogo.png" alt="Smart HomeWatt Logo">
                Smart HomeWatt
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#hero">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#how-it-works">How It Works</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="hero" id="hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-text">
                    <h1>Smart Room Power Monitoring System</h1>
                    <p>Take control of your energy usage with real-time monitoring and intelligent power management for your room.</p>
                    <div class="hero-buttons">
                        <a href="registrationpage.php" class="btn btn-primary btn-lg" target="_blank">Get Started <i class="fas fa-arrow-right"></i></a>
                        <a href="loginpage.php" class="btn btn-outline-light btn-lg ms-3" target="_blank">Log in</a>
                    </div>
                    <div class="stats-container">
                        <!-- <div class="stat-item">
                            <h3>30%</h3>
                            <p>Average Energy Savings</p>
                        </div>
                        <div class="stat-item">
                            <h3>1000+</h3>
                            <p>Active Users</p>
                        </div> -->
                        <div class="stat-item">
                            <h3>24/7</h3>
                            <p>Real-time Monitoring</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 hero-image">
                    <img src="images/dashboardpreview.png" alt="dashboard preview" class="img-fluid rounded shadow">
                </div>
            </div>
        </div>
    </header>

    <!-- Features Section -->
    <section id="features" class="features">
        <div class="container">
            <div class="section-header text-center">
                <h2>Key Features</h2>
                <p>Everything you need to monitor and optimize your energy consumption</p>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3>Real-Time Monitoring</h3>
                        <p>Track power usage, voltage, and current across all your loads in real-time with intuitive dashboard.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-toggle-on"></i>
                        </div>
                        <h3>Remote Control</h3>
                        <p>Turn power on/off your loads remotely from your phone or desktop</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-bell"></i>
                        </div>
                        <h3>Smart Alerts</h3>
                        <p>Receive instant notifications for unusual power consumption or potential electrical issues.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="how-it-works">
        <div class="container">
            <div class="section-header text-center">
                <h2>How It Works</h2>
                <p>Simple setup process to start monitoring your energy usage</p>
            </div>
            <div class="steps-container">
                <div class="step">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h3>Create Your Account</h3>
                        <p>Sign up and create your account to get started with Smart HomeWatt.</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h3>Connect Monitoring Devices</h3>
                        <p>Install our smart sensors in your room and get connected.</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <div class="step-content">
                        <h3>Start Monitoring</h3>
                        <p>Access your dashboard anytime to view real-time data and control your room.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact">
        <div class="container">
            <div class="section-header text-center">
                <h2>Contact Us</h2>
                <p>Have questions? Get in touch with our team</p>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="contact-info">
                        <div class="info-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <h4>Address</h4>
                                <p>123 Portbell road, Nakawa division, UICT</p>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <h4>Email</h4>
                                <p>smarthomewatt@gmail.com</p>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-phone"></i>
                            <div>
                                <h4>Phone</h4>
                                <p>+256 760 536 692</p>
                            </div>
                        </div>
                        <!-- <div class="info-item">
                            <i class="fas fa-clock"></i>
                            <div>
                                <h4>Business Hours</h4>
                                <p>Monday - Friday: 9:00 AM - 5:00 PM</p>
                            </div>
                        </div> -->
                    </div>
                </div>
                <div class="col-lg-6">
                    <form class="contact-form" action="index.php" method="POST">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <input type="text" name="name" class="form-control" placeholder="Name" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <input type="email" name="email" class="form-control" placeholder="Email" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <input type="text" name="subject" class="form-control" placeholder="Subject" required>
                        </div>
                        <div class="form-group">
                            <textarea name="message" class="form-control" rows="5" placeholder="Message" required></textarea>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">Send Message</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <div class="footer-info">
                        <a class="navbar-brand" href="#">
                            <img src="images/dashlogo.png" alt="Smart HomeWatt Logo">
                            Smart HomeWatt
                        </a>
                        <p>
                            Empowering users with intelligent energy monitoring solutions since 2022.
                        </p>
                        <div class="social-links">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 footer-links">
                    <h4>Useful Links</h4>
                    <ul>
                        <li><a href="#home">Home</a></li>
                        <li><a href="#features">Features</a></li>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 footer-links">
                    <h4>Our Services</h4>
                    <ul>
                        <li><a href="#">Energy Monitoring</a></li>
                        <li><a href="#">Power Management</a></li>
                        <li><a href="#">Smart Alerts</a></li>
                        <li><a href="#">Remote Control</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h4>Subscribe to Our Newsletter</h4>
                    <p>Stay updated with our latest features and energy saving tips</p>
                    <form action="" class="subscribe-form">
                        <input type="email" class="form-control" placeholder="Your Email">
                        <button type="submit" class="btn btn-primary">Subscribe</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <div class="copyright">
                    &copy; Copyright <strong><span>Smart HomeWatt</span></strong>. All Rights Reserved
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="landingscript.js"></script>
</body>

</html>