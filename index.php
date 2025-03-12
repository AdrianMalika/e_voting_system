<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>E-Voting System - Secure Digital Democracy</title>
        <link rel="icon" href="https://mchs.mw/img/mchs_logo.png" type="image/png">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
        <style>
            
            .hero-section {
                background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(17, 66, 85, 0.6), rgba(55, 139, 218, 0.5)),
                            url("includes/image/g1.jpg");
                background-size: cover;
                background-position: center;
                color: white;
                padding: 150px 0;
                font-size: 0.9rem;
            }
            .feature-icon {
                font-size: 2rem;
                color: #0d6efd;
                margin-bottom: 1rem;
            }
        
            .custom-shape {
                position: absolute;
                bottom: 0;
                left: 0;
                width: 100%;
                overflow: hidden;
                line-height: 0;
            }
            .custom-shape svg {
                position: relative;
                display: block;
                width: calc(100% + 1.3px);
                height: 50px;
            }
            .navbar-brand {
                font-weight: bold;
                font-size: 1.2rem;
            }
            .card {
                transition: transform 0.3s ease;
            }
            .card:hover {
                transform: scale(1.05);
            }
            .card h3.h5 {
                font-size: 1.2rem; /* Adjust the size as needed */
            }
            .hero-section h1 {
                font-size: 2.5rem; /* Adjust the size as needed */
            }
            .testimonial-card {
                transition: transform 0.3s ease;
                border-radius: 10px;
                padding: 20px;
                color: white;
            }
            .testimonial-card-1 {
                background: rgba(255, 99, 71, 0.8); /* Tomato */
            }
            .testimonial-card-2 {
                background: rgba(30, 144, 255, 0.8); /* Dodger Blue */
            }
            .testimonial-card-3 {
                background: rgba(60, 179, 113, 0.8); /* Medium Sea Green */
            }
            .testimonial-icon {
                font-size: 2.5rem; /* Increased size for better visibility */
                margin-bottom: 10px; /* Space between icon and text */
                color: black; /* Set icon color to black */
                background-color: rgba(255, 255, 255, 0.8); /* Light background for contrast */
                border-radius: 50%; /* Make the icon round */
                padding: 15px; /* Increased padding for a larger circular area */
                display: inline-block; /* Ensure the background wraps around the icon */
            }
            .underline {
                position: relative;
                display: inline-block;
                margin-bottom: 20px; /* Space below the header */
                text-align: center; /* Center the text */
            }
            .underline::after {
                content: '';
                position: absolute;
                left: 50%; /* Center the line */
                transform: translateX(-50%); /* Adjust to center */
                width: 50%; /* Set the width of the underline */
                height: 4px; /* Thickness of the underline */
                background-color: purple; /* Changed color to purple */
                border-radius: 2px; /* Rounded edges for the underline */
                bottom: -10px; /* Adjust this value to move the line closer or further from the text */
            }
            /* Ensure smooth scrolling is enabled */
            html {
                scroll-behavior: smooth; /* Enables smooth scrolling */
            }
            /* Add this CSS rule to change hyperlink color to black */
            .navbar-nav .nav-link {
                color: black; /* Set hyperlink color to black */
                font-weight: bold; /* Make hyperlink text bold */
            }
            .navbar-nav .nav-link:hover {
                color: #555; /* Optional: Change color on hover for better visibility */
            }
            /* From Uiverse.io by vikramsinghnegi */
            .center-body {
                display: flex;
                justify-content: center;
                align-items: center;
                width: 100%;
                height: 100%;
                background: #262626;
            }

            .loader-container {
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                height: 100vh;
            }

            .loader {
                --path: #2f3545;
                --dot: rgb(191, 64, 191);
                --duration: 3s;
                width: 44px;
                height: 44px;
                position: relative;
                display: inline-block;
                margin: 0 16px;
            }

            .loader:before {
                content: "";
                width: 6px;
                height: 6px;
                border-radius: 50%;
                position: absolute;
                display: block;
                background: var(--dot);
                top: 37px;
                left: 19px;
                transform: translate(-18px, -18px);
                animation: dotRect var(--duration) cubic-bezier(0.785, 0.135, 0.15, 0.86) infinite;
            }

            .loader svg {
                display: block;
                width: 100%;
                height: 100%;
            }

            .loader svg rect,
            .loader svg polygon,
            .loader svg circle {
                fill: none;
                stroke: var(--path);
                stroke-width: 10px;
                stroke-linejoin: round;
                stroke-linecap: round;
            }

            .loader svg polygon {
                stroke-dasharray: 145 76 145 76;
                stroke-dashoffset: 0;
                animation: pathTriangle var(--duration) cubic-bezier(0.785, 0.135, 0.15, 0.86) infinite;
            }

            .loader svg rect {
                stroke-dasharray: 192 64 192 64;
                stroke-dashoffset: 0;
                animation: pathRect 3s cubic-bezier(0.785, 0.135, 0.15, 0.86) infinite;
            }

            .loader svg circle {
                stroke-dasharray: 150 50 150 50;
                stroke-dashoffset: 75;
                animation: pathCircle var(--duration) cubic-bezier(0.785, 0.135, 0.15, 0.86) infinite;
            }

            .loader.triangle {
                width: 48px;
            }

            .loader.triangle:before {
                left: 21px;
                transform: translate(-10px, -18px);
                animation: dotTriangle var(--duration) cubic-bezier(0.785, 0.135, 0.15, 0.86) infinite;
            }

            @keyframes pathTriangle {
                33% {
                    stroke-dashoffset: 74;
                }
                66% {
                    stroke-dashoffset: 147;
                }
                100% {
                    stroke-dashoffset: 221;
                }
            }

            @keyframes dotTriangle {
                33% {
                    transform: translate(0, 0);
                }
                66% {
                    transform: translate(10px, -18px);
                }
                100% {
                    transform: translate(-10px, -18px);
                }
            }

            @keyframes pathRect {
                25% {
                    stroke-dashoffset: 64;
                }
                50% {
                    stroke-dashoffset: 128;
                }
                75% {
                    stroke-dashoffset: 192;
                }
                100% {
                    stroke-dashoffset: 256;
                }
            }

            @keyframes dotRect {
                25% {
                    transform: translate(0, 0);
                }
                50% {
                    transform: translate(18px, -18px);
                }
                75% {
                    transform: translate(0, -36px);
                }
                100% {
                    transform: translate(-18px, -18px);
                }
            }

            @keyframes pathCircle {
                25% {
                    stroke-dashoffset: 125;
                }
                50% {
                    stroke-dashoffset: 175;
                }
                75% {
                    stroke-dashoffset: 225;
                }
                100% {
                    stroke-dashoffset: 275;
                }
            }

            .content {
                display: none;
            }

            .loaded .loader-container {
                display: none;
            }

            .loaded .content {
                display: block;
            }

            /* From Uiverse.io by clarencedion */
            .flex {
                display: flex;
            }
            .items-center {
                align-items: center;
            }
            .justify-center {
                justify-content: center;
            }
            .min-h-screen {
                min-height: 100vh;
            }
            .relative {
                position: relative;
            }
            .animate-spin {
                animation: spin 1s linear infinite;
            }
            .animate-pulse {
                animation: pulse 1s infinite;
            }
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            @keyframes pulse {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.5; }
            }

            /* From Uiverse.io by Cksunandh */
            .cssloader {
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                height: 100vh;
            }

            .triangle1 {
                width: 0;
                height: 0;
                border-style: solid;
                border-width: 50px 50px 0 0;
                border-color: #8086e0 transparent transparent transparent;
                margin: 0 auto;
                animation: shk1 1s ease-in-out infinite normal;
            }

            .triangle2 {
                width: 0;
                height: 0;
                border-style: solid;
                border-width: 0 0 50px 50px;
                border-color: transparent transparent #6554b388 transparent;
                margin: -50px auto 0;
                animation: shk2 1s ease-in-out infinite alternate;
            }

            @keyframes shk1 {
                0% {
                    transform: rotate(-360deg);
                }
                100% {
                    /* No change */
                }
            }

            @keyframes shk2 {
                0% {
                    transform: rotate(360deg);
                }
                100% {
                    /* No change */
                }
            }

            .text {
                color: #949494;
                margin: 30px auto;
                text-align: center;
                font-weight: 500;
                letter-spacing: 4px;
            }

            /* Add styles for the back to top button */
            .back-to-top {
                position: fixed;
                bottom: 30px;
                right: 30px;
                background-color: #0d6efd; /* Button color */
                color: white; /* Icon color */
                border: none;
                border-radius: 50%;
                padding: 0;
                font-size: 1.5rem;
                display: none; /* Hidden by default */
                z-index: 1000; /* Ensure it is above other elements */
                width: 50px;
                height: 50px;
            }
        </style>
    </head>
    <body>
        <div class="cssloader">
            <div class="triangle1"></div>
            <div class="triangle2"></div>
            <p class="text">Loading Please Wait</p>
        </div>

        <div class="content" id="main-content" style="display: none;">
            <nav class="navbar navbar-expand-lg" style="background: white;">
                <div class="container">
                    <a class="navbar-brand" href="#">
                        <img src="includes/image/favicon.png" alt="MCHS Logo" style="height: 40px; margin-right: 10px;">
                        Malawi College of Health Sciences
                    </a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav ms-auto">
                            <li class="nav-item">
                                <a class="nav-link" href="#features">Features</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#how-it-works">How It Works</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#testimonials">Testimonials</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link " href="#contact-us" id="get-started-btn">Contact Us</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <!-- Hero Section -->
            <section class="hero-section position-relative" id="home">
                <div class="container text-center">
                    <h1 class="display-4 mb-4" style="color: #FFFFFF;"><strong>Transform Democracy with E-Voting for Malawi College of Health Sciences</strong></h1>
                    <p class="lead mb-4" style="color: #FFFFFF;"><strong>Secure and efficient electronic voting for Malawi College of Health Sciences.</strong></p>
                    <a href="login.php" class="btn btn-primary btn-lg me-3" id="start-voting-btn">Start Voting</a>
                    <a href="#features" class="btn btn-outline-light btn-lg">Discover Features</a>
                </div>
                <div class="custom-shape">
                    <svg viewBox="0 0 1200 120" preserveAspectRatio="none">
                        <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V120H0V0C50.45,20.48,121.09,43.44,321.39,56.44Z" fill="#ffffff"></path>
                    </svg>
                </div>
            </section>

            <!-- Features Section -->
            <section class="py-5 bg-light" id="features">
                <div class="container text-center">
                    <h2 class="mb-5 underline">Key Features</h2>
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <i class="fas fa-shield-alt feature-icon"></i>
                                    <h3 class="h5">Secure & Private for Malawi College of Health Sciences</h3>
                                    <p>Advanced encryption and blockchain technology ensure vote integrity and voter privacy.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <i class="fas fa-mobile-alt feature-icon"></i>
                                    <h3 class="h5">Easy Access for Malawi College of Health Sciences</h3>
                                    <p>Vote securely from any device with our user-friendly interface.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <i class="fas fa-chart-bar feature-icon"></i>
                                    <h3 class="h5">Real-time Results for Malawi College of Health Sciences</h3>
                                    <p>Get immediate, accurate election results with comprehensive analytics.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- How It Works Section -->
            <section id="how-it-works" class="bg-light py-5">
                <div class="container">
                    <div class="text-center">
                        <h2 class="mb-5 underline">How It Works</h2>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <img src="includes/image/1.jpg" alt="How it works" class="img-fluid rounded shadow" style="max-width: 80%;">
                        </div>
                        <div class="col-md-6">
                            <div class="steps">
                                <h4 class="mb-3"><span class="badge bg-primary me-2">1</span>Register</h4>
                                <p class="mb-4">Create your organization's account and set up your election parameters for Malawi College of Health Sciences.</p>
                                
                                <h4 class="mb-3"><span class="badge bg-primary me-2">2</span>Verify Voters</h4>
                                <p class="mb-4">Import your voter list and send secure authentication credentials for Malawi College of Health Sciences.</p>
                                
                                <h4 class="mb-3"><span class="badge bg-primary me-2">3</span>Launch Election</h4>
                                <p class="mb-4">Start your election and monitor real-time participation for Malawi College of Health Sciences.</p>
                                
                                <h4 class="mb-3"><span class="badge bg-primary me-2">4</span>Get Results</h4>
                                <p>Access detailed reports and certified election results instantly for Malawi College of Health Sciences.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Testimonials Section -->
            <section class="py-5" id="testimonials">
                <div class="container text-center">
                    <h2 class="mb-5 underline">What Our Users Say</h2>
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="card h-100 border-0 shadow-sm testimonial-card testimonial-card-1">
                                <div class="card-body text-center">
                                    <i class="fas fa-user-circle testimonial-icon"></i>
                                    <h5 class="card-title">John Doe</h5>
                                    <h6 class="card-subtitle mb-2">Student, Malawi College of Health Sciences</h6>
                                    <p class="card-text">"The e-voting system has transformed our election process. It's secure and easy to use!"</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100 border-0 shadow-sm testimonial-card testimonial-card-2">
                                <div class="card-body text-center">
                                    <i class="fas fa-user-circle testimonial-icon"></i>
                                    <h5 class="card-title">Jane Smith</h5>
                                    <h6 class="card-subtitle mb-2">Alumni, Malawi College of Health Sciences</h6>
                                    <p class="card-text">"I love how accessible the voting system is. I can vote from anywhere!"</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100 border-0 shadow-sm testimonial-card testimonial-card-3">
                                <div class="card-body text-center">
                                    <i class="fas fa-user-circle testimonial-icon"></i>
                                    <h5 class="card-title">Michael Johnson</h5>
                                    <h6 class="card-subtitle mb-2">Faculty, Malawi College of Health Sciences</h6>
                                    <p class="card-text">"The real-time results feature is fantastic! It keeps everyone informed."</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Contact Us Section -->
            <section class="py-5 bg-light" id="contact-us">
                <div class="container text-center">
                    <h2 class="mb-5 underline">Contact Us</h2>
                    <p>If you have any questions or feedback, feel free to reach out to us!</p>
                    <form>
                        <div class="mb-3">
                            <input type="text" class="form-control" placeholder="Your Name" required>
                        </div>
                        <div class="mb-3">
                            <input type="email" class="form-control" placeholder="Your Email" required>
                        </div>
                        <div class="mb-3">
                            <textarea class="form-control" rows="4" placeholder="Your Message" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </form>
                </div>
            </section>

            <!-- Footer -->
            <footer class="bg-dark text-white py-4" style="background-color: #000000;">
                <div class="container">
                    <div class="row">
                        <div class="col-md-6">
                            <h5><i class="fas fa-vote-yea me-2"></i>E-Voting for Malawi College of Health Sciences</h5>
                            <p>Transforming democracy through secure digital voting</p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <ul class="list-inline mb-0">
                                <li class="list-inline-item"><a href="#" class="text-white">Privacy Policy</a></li>
                                <li class="list-inline-item"><a href="#" class="text-white">Terms of Service</a></li>
                                <li class="list-inline-item"><a href="#" class="text-white">Contact</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="text-end mt-3">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                    <p class="text-center mt-3">&copy; <span id="current-year"></span> Malawi College of Health Sciences. All rights reserved.</p>
                </div>
            </footer>
        </div>

        <button class="back-to-top" id="backToTopBtn" title="Back to Top" style="width: 50px; height: 50px; padding: 0;">
            <i class="fas fa-arrow-alt-circle-up"></i>
        </button>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
        <script>
            document.getElementById('current-year').textContent = new Date().getFullYear();
            // Ensure this script runs after the DOM is fully loaded
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('a.nav-link, a.btn').forEach(anchor => {
                    anchor.addEventListener('click', function(e) {
                        e.preventDefault();
                        const targetId = this.getAttribute('href');
                        const targetElement = document.querySelector(targetId);
                        if (targetElement) {
                            targetElement.scrollIntoView({ behavior: 'smooth' });
                        }
                    });
                });
            });
            document.getElementById('start-voting-btn').addEventListener('click', function(e) {
                // Ensure the default action is not prevented
                window.location.href = 'login.php'; // Redirect to login.php
            });

            // Show the main content after 3 seconds
            setTimeout(function() {
                document.querySelector('.cssloader').style.display = 'none'; // Hide loader
                document.getElementById('main-content').style.display = 'block'; // Show the main content
            }, 4000); // 3000 milliseconds = 3 seconds

            // Show the button when scrolling down
            window.onscroll = function() {
                const backToTopBtn = document.getElementById('backToTopBtn');
                if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
                    backToTopBtn.style.display = "block";
                } else {
                    backToTopBtn.style.display = "none";
                }
            };

            // Scroll to the top when the button is clicked
            document.getElementById('backToTopBtn').addEventListener('click', function() {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        </script>
    </body>
    </html>
