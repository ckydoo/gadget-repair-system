<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RepairHub - Gadget Repair Management System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        /* Navigation */
        header {
            background-color: #0052CC;
            color: white;
            padding: 1rem 0;
            position: sticky;
            top: 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        nav {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: #B3D9FF;
        }

        .auth-links {
            display: flex;
            gap: 1rem;
        }

        .auth-links a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 3px;
            transition: background-color 0.3s;
        }

        .auth-links a:first-child {
            color: #0052CC;
            background-color: white;
            font-weight: bold;
        }

        .auth-links a:first-child:hover {
            background-color: #E8F0FF;
        }

        .auth-links a:last-child {
            border: 2px solid white;
        }

        .auth-links a:last-child:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        /* Hero Section */
        .hero {
            background-color: #0052CC;
            color: white;
            padding: 5rem 2rem;
            text-align: center;
        }

        .hero-content {
            max-width: 900px;
            margin: 0 auto;
        }

        .hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            letter-spacing: -1px;
        }

        .hero p {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            line-height: 1.8;
            color: #E8F0FF;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 1rem 2.5rem;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background-color: #FFFFFF;
            color: #0052CC;
        }

        .btn-primary:hover {
            background-color: #E8F0FF;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: transparent;
            color: #FFFFFF;
            border: 2px solid #FFFFFF;
        }

        .btn-secondary:hover {
            background-color: #FFFFFF;
            color: #0052CC;
            transform: translateY(-2px);
        }

        /* Features Section */
        .features {
            padding: 4rem 2rem;
            background-color: #F8FAFB;
        }

        .section-title {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-title h2 {
            font-size: 2.5rem;
            color: #0052CC;
            margin-bottom: 0.5rem;
        }

        .section-title p {
            font-size: 1.1rem;
            color: #666;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            background-color: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
            border-left: 4px solid #0052CC;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 82, 204, 0.2);
        }

        .feature-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #0052CC;
        }

        .feature-card h3 {
            color: #0052CC;
            margin-bottom: 1rem;
            font-size: 1.3rem;
        }

        .feature-card p {
            color: #666;
            line-height: 1.7;
        }

        /* How It Works */
        .how-it-works {
            padding: 4rem 2rem;
            background-color: white;
        }

        .steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .step {
            text-align: center;
            padding: 2rem;
            background-color: #F0F4FF;
            border-radius: 8px;
            position: relative;
        }

        .step-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            background-color: #0052CC;
            color: white;
            border-radius: 50%;
            font-weight: bold;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .step h3 {
            color: #0052CC;
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }

        .step p {
            color: #666;
        }

        /* Benefits Section */
        .benefits {
            padding: 4rem 2rem;
            background-color: #F8FAFB;
        }

        .benefits-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .benefits-list {
            list-style: none;
        }

        .benefits-list li {
            padding: 1rem 0;
            border-bottom: 1px solid #E0E7FF;
            color: #333;
            font-size: 1.1rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }

        .benefits-list li:before {
            content: "✓";
            color: #0052CC;
            font-weight: bold;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .benefits-image {
            text-align: center;
            font-size: 4rem;
        }

        /* CTA Section */
        .cta-section {
            background-color: #0052CC;
            color: white;
            padding: 4rem 2rem;
            text-align: center;
        }

        .cta-section h2 {
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
        }

        .cta-section p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            color: #E8F0FF;
        }

        /* Footer */
        footer {
            background-color: #003D99;
            color: white;
            padding: 2rem;
            text-align: center;
        }

        footer p {
            margin-bottom: 0.5rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2rem;
            }

            .hero p {
                font-size: 1rem;
            }

            .section-title h2 {
                font-size: 2rem;
            }

            .nav-links {
                gap: 1rem;
                font-size: 0.9rem;
            }

            .hero-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }

            .benefits-content {
                grid-template-columns: 1fr;
            }

            .auth-links {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <header>
        <nav>
            <div class="logo">🔧 RepairHub</div>
            <ul class="nav-links">
                <li><a href="#features">Features</a></li>
                <li><a href="#how-it-works">How It Works</a></li>
                <li><a href="#benefits">Benefits</a></li>
            </ul>
            <div class="auth-links">
                @auth
                    <a href="{{ url('/dashboard') }}">Dashboard</a>
                    <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn" style="background: none; border: none; padding: 0.5rem 1rem; cursor: pointer; color: white;">
                            Logout
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}">Login</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}">Register</a>
                    @endif
                @endauth
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Professional Gadget Repair Management</h1>
            <p>Seamlessly book repairs, track progress in real-time, and manage your device maintenance with our intelligent repair management system.</p>
            <div class="hero-buttons">
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn btn-primary">Go to Dashboard</a>
                @else
                    <a href="{{ route('register') }}" class="btn btn-primary">Get Started</a>
                    <a href="{{ route('login') }}" class="btn btn-secondary">Login</a>
                @endauth
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <div class="section-title">
                <h2>Powerful Features</h2>
                <p>Everything you need to manage device repairs efficiently</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">📱</div>
                    <h3>Easy Booking</h3>
                    <p>Simple and intuitive interface to book your device repair or service within minutes. Select device type and describe the issue.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📍</div>
                    <h3>Smart Location Tracking</h3>
                    <p>Integrated Google Maps API for automatic distance calculation and transparent transportation fee calculation at $0.75/km.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">👨‍🔧</div>
                    <h3>Smart Technician Assignment</h3>
                    <p>Automatic assignment to the most available technician based on specialization, workload, and availability for optimal service.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🔄</div>
                    <h3>Real-Time Updates</h3>
                    <p>WebSocket-powered live updates keep you informed about your repair progress at every stage of the process.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">💳</div>
                    <h3>Secure Payment</h3>
                    <p>Safe and secure payment processing integrated with service cost, diagnostics, and transportation fees.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3>Detailed Invoicing</h3>
                    <p>Comprehensive invoice generation including materials, labour charges, and complete service breakdown.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="how-it-works" id="how-it-works">
        <div class="container">
            <div class="section-title">
                <h2>How It Works</h2>
                <p>Simple steps to get your device repaired</p>
            </div>
            <div class="steps">
                <div class="step">
                    <div class="step-number">1</div>
                    <h3>Register & Login</h3>
                    <p>Create your account and log in to access the booking platform with your personal details.</p>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <h3>Select Service Type</h3>
                    <p>Choose between service or repair, select your device category, and provide device details.</p>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <h3>Get Quote & Payment</h3>
                    <p>View transparent pricing including service cost, diagnostics fee, and optional transport charges. Pay securely online.</p>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <h3>Real-Time Tracking</h3>
                    <p>Receive unique task ID and track your repair progress in real-time as technicians update the system.</p>
                </div>
                <div class="step">
                    <div class="step-number">5</div>
                    <h3>Complete & Collect</h3>
                    <p>Get your device back with a detailed invoice and warranty information. SMS reminders if collection is delayed.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="benefits" id="benefits">
        <div class="container">
            <div class="section-title">
                <h2>Why Choose RepairHub?</h2>
                <p>Experience professional device repair management</p>
            </div>
            <div class="benefits-content">
                <ul class="benefits-list">
                    <li><strong>Transparent Pricing</strong> - No hidden costs. Know exactly what you're paying for.</li>
                    <li><strong>Expert Technicians</strong> - Devices assigned to specialists with relevant expertise.</li>
                    <li><strong>Real-Time Communication</strong> - Stay updated throughout the entire repair process.</li>
                    <li><strong>Flexible Transport Options</strong> - Choose pickup, delivery, or bring it in yourself.</li>
                    <li><strong>Warranty Coverage</strong> - Complete protection and warranty tracking on all repairs.</li>
                    <li><strong>24/7 Booking</strong> - Book your repair anytime, anywhere from your device.</li>
                    <li><strong>Automated Reminders</strong> - SMS notifications to keep you informed about your booking.</li>
                    <li><strong>Professional Documentation</strong> - Detailed invoices and complete service records.</li>
                </ul>
                <div class="benefits-image">
                    🛠️
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2>Ready to Get Started?</h2>
            <p>Join thousands of satisfied customers who trust RepairHub for their device repairs</p>
            @auth
                <a href="{{ url('/dashboard') }}" class="btn btn-primary">Go to Dashboard</a>
            @else
                <a href="{{ route('register') }}" class="btn btn-primary">Book Your Repair Now</a>
            @endauth
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <p>&copy; 2025 RepairHub - Gadget Repair Management System</p>
        <p>Professional. Reliable. Transparent.</p>
    </footer>
</body>
</html>
