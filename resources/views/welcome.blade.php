<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BulkSMS CRM - Multi-Channel Messaging Platform</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
        <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html {
            scroll-behavior: smooth;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            line-height: 1.6;
            color: #333;
            overflow-x: hidden;
        }
        
        /* Navigation */
        nav {
            position: fixed;
            top: 0;
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0,0,0,0.05);
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        nav.scrolled {
            box-shadow: 0 2px 30px rgba(0,0,0,0.1);
        }
        
        .nav-container {
            max-width: 100%;
            margin: 0;
            padding: 1rem 0.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }
        
        .nav-links a {
            text-decoration: none;
            color: #555;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .nav-links a:hover {
            color: #667eea;
        }
        
        .btn-outline {
            background: transparent;
            color: #667eea;
            border: 2px solid #667eea;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-outline:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            border: 2px solid transparent;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn:hover,
        .btn:focus {
            text-decoration: none;
            outline: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            border-color: transparent;
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
        }
        
        .btn-primary:active {
            transform: translateY(-1px);
        }
        
        .btn-secondary {
            background: rgba(255,255,255,0.15);
            color: #ffffff;
            border: 2px solid rgba(255,255,255,0.6);
            backdrop-filter: blur(8px);
        }
        
        .btn-secondary:hover {
            background: white;
            color: #667eea;
            border-color: white;
        }
        
        /* Hero Section */
        .hero {
            padding: 150px 0.5rem 100px;
            background: 
                linear-gradient(135deg, rgba(102, 126, 234, 0.95) 0%, rgba(118, 75, 162, 0.95) 100%),
                url('https://images.unsplash.com/photo-1557804506-669a67965ba0?w=1920&q=80') center/cover;
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
            animation: fadeIn 1s ease;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            animation: backgroundScroll 60s linear infinite;
        }
        
        @keyframes backgroundScroll {
            0% { background-position: 0 0; }
            100% { background-position: 1000px 1000px; }
        }
        
        .hero-content {
            max-width: 100%;
            margin: 0;
            position: relative;
            z-index: 1;
        }
        
        .hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            line-height: 1.2;
            animation: fadeInUp 0.8s ease;
        }
        
        .hero p {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            opacity: 0.95;
            animation: fadeInUp 0.8s ease 0.2s backwards;
        }
        
        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeInUp 0.8s ease 0.4s backwards;
        }
        
        .hero-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin-top: 4rem;
            animation: fadeInUp 0.8s ease 0.6s backwards;
        }
        
        .stat {
            text-align: center;
            animation: float 3s ease-in-out infinite;
        }
        
        .stat:nth-child(1) { animation-delay: 0s; }
        .stat:nth-child(2) { animation-delay: 0.5s; }
        .stat:nth-child(3) { animation-delay: 1s; }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            display: block;
        }
        
        .stat-label {
            opacity: 0.9;
            font-size: 0.95rem;
        }
        
        /* Features Section */
        .features {
            padding: 100px 0.5rem;
            background: 
                linear-gradient(rgba(248, 249, 250, 0.97), rgba(248, 249, 250, 0.97)),
                url('https://images.unsplash.com/photo-1551434678-e076c223a692?w=1920&q=80') center/cover fixed;
            position: relative;
        }
        
        .features::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 20% 50%, rgba(102, 126, 234, 0.05) 0%, transparent 50%),
                        radial-gradient(circle at 80% 80%, rgba(118, 75, 162, 0.05) 0%, transparent 50%);
            pointer-events: none;
        }
        
        .container {
            max-width: 100%;
            margin: 0;
            position: relative;
            z-index: 1;
        }
        
        .section-header {
            text-align: center;
            margin-bottom: 4rem;
            animation: fadeInUp 0.8s ease;
        }
        
        .section-tag {
            color: #667eea;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.9rem;
        }
        
        .section-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin: 1rem 0;
            color: #1a1a1a;
        }
        
        .section-description {
            font-size: 1.1rem;
            color: #666;
            max-width: 100%;
            margin: 0;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }
        
        .feature-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(233, 236, 239, 0.8);
            position: relative;
            overflow: hidden;
            animation: slideUp 0.6s ease backwards;
        }
        
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(102, 126, 234, 0.1), transparent);
            transition: left 0.5s ease;
        }
        
        .feature-card:hover::before {
            left: 100%;
        }
        
        .feature-card:nth-child(1) { animation-delay: 0.1s; }
        .feature-card:nth-child(2) { animation-delay: 0.2s; }
        .feature-card:nth-child(3) { animation-delay: 0.3s; }
        .feature-card:nth-child(4) { animation-delay: 0.4s; }
        .feature-card:nth-child(5) { animation-delay: 0.5s; }
        .feature-card:nth-child(6) { animation-delay: 0.6s; }
        .feature-card:nth-child(7) { animation-delay: 0.7s; }
        .feature-card:nth-child(8) { animation-delay: 0.8s; }
        .feature-card:nth-child(9) { animation-delay: 0.9s; }
        
        .feature-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 12px 40px rgba(102, 126, 234, 0.2);
            border-color: rgba(102, 126, 234, 0.3);
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .feature-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            color: white;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            z-index: 1;
        }
        
        .feature-card:hover .feature-icon {
            transform: rotateY(360deg) scale(1.1);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        
        .feature-card h3 {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #1a1a1a;
        }
        
        .feature-card p {
            color: #666;
            line-height: 1.8;
        }
        
        /* Why Choose Us Section */
        .why-choose-us {
            padding: 80px 0;
            background: #f8f9fa;
        }
        
        .why-choose-us .section-header {
            text-align: center;
            margin-bottom: 60px;
        }
        
        .why-choose-us .section-header h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 1rem;
        }
        
        .why-choose-us .section-header p {
            font-size: 1.2rem;
            color: #666;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .benefit-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid #e9ecef;
        }
        
        .benefit-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }
        
        .benefit-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: white;
        }
        
        .benefit-card h3 {
            font-size: 1.3rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 1rem;
        }
        
        .benefit-card p {
            color: #666;
            line-height: 1.6;
            margin: 0;
        }
        
        /* Hero Note */
        .hero-note {
            text-align: center;
            margin-top: 1.5rem;
        }
        
        .hero-note p {
            color: #10b981;
            font-weight: 500;
            font-size: 0.95rem;
        }
        
        .hero-note i {
            margin-right: 0.5rem;
        }
        .how-it-works {
            padding: 100px 0.5rem;
            background: 
                linear-gradient(rgba(255, 255, 255, 0.98), rgba(255, 255, 255, 0.98)),
                url('https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=1920&q=80') center/cover fixed;
            position: relative;
        }
        
        .how-it-works::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(180deg, transparent 0%, rgba(102, 126, 234, 0.03) 100%);
            pointer-events: none;
        }
        
        .steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
            margin-top: 4rem;
        }
        
        .step {
            text-align: center;
            position: relative;
            z-index: 1;
            animation: fadeInUp 0.8s ease backwards;
            transition: all 0.3s ease;
        }
        
        .step:nth-child(1) { animation-delay: 0.2s; }
        .step:nth-child(2) { animation-delay: 0.4s; }
        .step:nth-child(3) { animation-delay: 0.6s; }
        .step:nth-child(4) { animation-delay: 0.8s; }
        
        .step:hover {
            transform: scale(1.05);
        }
        
        .step-number {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 800;
            margin: 0 auto 1.5rem;
            transition: all 0.4s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            animation: pulse 2s ease-in-out infinite;
        }
        
        .step:hover .step-number {
            transform: scale(1.2);
            box-shadow: 0 8px 30px rgba(102, 126, 234, 0.5);
        }
        
        @keyframes pulse {
            0%, 100% {
                box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            }
            50% {
                box-shadow: 0 4px 25px rgba(102, 126, 234, 0.5);
            }
        }
        
        .step h3 {
            font-size: 1.3rem;
            margin-bottom: 1rem;
            color: #1a1a1a;
        }
        
        .step p {
            color: #666;
        }
        
        /* Channels Section */
        .channels {
            padding: 100px 0.5rem;
            background: 
                linear-gradient(135deg, rgba(30, 60, 114, 0.95) 0%, rgba(42, 82, 152, 0.95) 100%),
                url('https://images.unsplash.com/photo-1563986768609-322da13575f3?w=1920&q=80') center/cover fixed;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .channels::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            top: -50%;
            left: -50%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: moveBackground 20s linear infinite;
            pointer-events: none;
        }
        
        @keyframes moveBackground {
            0% { transform: translate(0, 0); }
            100% { transform: translate(50px, 50px); }
        }
        
        .channels-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
            position: relative;
            z-index: 1;
        }
        
        .channel-card {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            padding: 2rem;
            border-radius: 12px;
            text-align: center;
            border: 1px solid rgba(255,255,255,0.2);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            animation: fadeInScale 0.8s ease backwards;
            cursor: pointer;
        }
        
        .channel-card:nth-child(1) { animation-delay: 0.2s; }
        .channel-card:nth-child(2) { animation-delay: 0.4s; }
        .channel-card:nth-child(3) { animation-delay: 0.6s; }
        
        .channel-card:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-10px) scale(1.05);
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
            border-color: rgba(255,255,255,0.4);
        }
        
        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.8);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        .channel-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: white;
            transition: all 0.4s ease;
            display: inline-block;
        }
        
        .channel-card:hover .channel-icon {
            transform: scale(1.2) rotate(5deg);
            filter: drop-shadow(0 0 20px rgba(255,255,255,0.5));
        }
        
        .channel-card h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        
        /* Contact Section */
        .contact {
            padding: 100px 0.5rem;
            background: 
                linear-gradient(rgba(248, 249, 250, 0.96), rgba(248, 249, 250, 0.96)),
                url('https://images.unsplash.com/photo-1556761175-5973dc0f32e7?w=1920&q=80') center/cover fixed;
            position: relative;
        }
        
        .contact::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 10% 20%, rgba(102, 126, 234, 0.05) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(118, 75, 162, 0.05) 0%, transparent 40%);
            pointer-events: none;
        }
        
        .contact-wrapper {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 4rem;
            margin-top: 3rem;
            position: relative;
            z-index: 1;
        }
        
        .contact-info h3 {
            font-size: 1.5rem;
            margin-bottom: 2rem;
            color: #1a1a1a;
        }
        
        .contact-item {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            align-items: flex-start;
            animation: slideInLeft 0.8s ease backwards;
            transition: all 0.3s ease;
        }
        
        .contact-item:nth-child(2) { animation-delay: 0.1s; }
        .contact-item:nth-child(3) { animation-delay: 0.2s; }
        .contact-item:nth-child(4) { animation-delay: 0.3s; }
        .contact-item:nth-child(5) { animation-delay: 0.4s; }
        
        .contact-item:hover {
            transform: translateX(10px);
        }
        
        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .contact-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
            color: white;
            transition: all 0.4s ease;
        }
        
        .contact-item:hover .contact-icon {
            transform: rotate(360deg) scale(1.1);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        
        .contact-item h4 {
            margin-bottom: 0.5rem;
            color: #1a1a1a;
        }
        
        .contact-item a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        
        .contact-item a:hover {
            text-decoration: underline;
        }
        
        .contact-item p {
            color: #666;
            margin: 0;
        }
        
        .contact-form-wrapper {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            animation: slideInRight 0.8s ease backwards;
            transition: all 0.3s ease;
            border: 1px solid rgba(102, 126, 234, 0.1);
        }
        
        .contact-form-wrapper:hover {
            box-shadow: 0 8px 30px rgba(102, 126, 234, 0.15);
            transform: translateY(-5px);
        }
        
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .contact-form .form-group {
            margin-bottom: 1.5rem;
        }
        
        .contact-form label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
        }
        
        .contact-form input,
        .contact-form textarea {
            width: 100%;
            padding: 0.875rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.3s ease;
            background: white;
        }
        
        .contact-form input:focus,
        .contact-form textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }
        
        .contact-form input:hover,
        .contact-form textarea:hover {
            border-color: #667eea;
        }
        
        .contact-form textarea {
            resize: vertical;
        }
        
        .btn-block {
            width: 100%;
            padding: 1rem;
        }
        
        .btn-large {
            padding: 1rem 2.5rem;
            font-size: 1.1rem;
        }
        
        /* Testimonials Section */
        .testimonials {
            padding: 100px 0.5rem;
            background: 
                linear-gradient(rgba(255, 255, 255, 0.98), rgba(255, 255, 255, 0.98)),
                url('https://images.unsplash.com/photo-1600880292203-757bb62b4baf?w=1920&q=80') center/cover fixed;
            position: relative;
        }
        
        .testimonials::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 50% 50%, rgba(102, 126, 234, 0.03) 0%, transparent 70%);
            pointer-events: none;
        }
        
        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
            position: relative;
            z-index: 1;
        }
        
        .testimonial-card {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: all 0.4s ease;
            border: 1px solid #e9ecef;
            animation: fadeInUp 0.8s ease backwards;
        }
        
        .testimonial-card:nth-child(1) { animation-delay: 0.2s; }
        .testimonial-card:nth-child(2) { animation-delay: 0.4s; }
        .testimonial-card:nth-child(3) { animation-delay: 0.6s; }
        
        .testimonial-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 40px rgba(102, 126, 234, 0.15);
        }
        
        .testimonial-stars {
            color: #fbbf24;
            margin-bottom: 1rem;
            font-size: 1rem;
        }
        
        .testimonial-text {
            font-size: 1rem;
            line-height: 1.8;
            color: #555;
            margin-bottom: 1.5rem;
            font-style: italic;
        }
        
        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .author-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.1rem;
        }
        
        .testimonial-author h4 {
            margin: 0;
            font-size: 1rem;
            color: #1a1a1a;
        }
        
        .testimonial-author p {
            margin: 0;
            font-size: 0.9rem;
            color: #888;
        }

        /* CTA Section */
        .cta-section {
            padding: 100px 0.5rem;
            background: 
                linear-gradient(135deg, rgba(102, 126, 234, 0.95) 0%, rgba(118, 75, 162, 0.95) 100%),
                url('https://images.unsplash.com/photo-1552664730-d307ca884978?w=1920&q=80') center/cover fixed;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .cta-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 50% 50%, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: pulse 4s ease-in-out infinite;
        }
        
        .cta-content {
            position: relative;
            z-index: 1;
            animation: fadeInUp 0.8s ease;
        }
        
        .cta-content h2 {
            font-size: 3rem;
            font-weight: 800;
            color: white;
            margin-bottom: 1rem;
        }
        
        .cta-content p {
            font-size: 1.3rem;
            color: rgba(255,255,255,0.95);
            margin-bottom: 2rem;
        }
        
        .cta-buttons {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .cta-section .btn-secondary {
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            color: white;
            border: 2px solid white;
        }
        
        .cta-section .btn-secondary:hover {
            background: white;
            color: #667eea;
        }
        
        .cta-note {
            text-align: center;
            margin-top: 1.5rem;
        }
        
        .cta-note p {
            color: #10b981;
            font-weight: 500;
            font-size: 0.95rem;
        }
        
        .cta-note i {
            margin-right: 0.5rem;
        }

        /* Support Chat Widget */
        .support-widget {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            font-family: 'Inter', sans-serif;
        }
        
        .support-toggle {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: pulse 2s ease-in-out infinite;
        }
        
        .support-toggle:hover {
            transform: scale(1.15);
            box-shadow: 0 8px 30px rgba(102, 126, 234, 0.5);
            animation: none;
        }
        
        .support-widget.open .support-toggle {
            background: #e74c3c;
        }
        
        .support-widget.open .support-toggle i {
            transform: rotate(45deg);
        }
        
        .support-chat {
            position: absolute;
            bottom: 80px;
            right: 0;
            width: 350px;
            height: 500px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            transform: scale(0);
            opacity: 0;
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .support-widget.open .support-chat {
            transform: scale(1);
            opacity: 1;
        }
        
        .support-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .support-logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .support-logo .logo-icon {
            width: 30px;
            height: 30px;
            background: rgba(255,255,255,0.2);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .support-agents {
            display: flex;
            gap: -5px;
        }
        
        .agent-avatar {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            border: 2px solid white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            margin-left: -5px;
        }
        
        .support-greeting {
            padding: 1rem;
            text-align: center;
        }
        
        .support-greeting h3 {
            margin-bottom: 0.5rem;
            color: #333;
        }
        
        .support-greeting p {
            color: #666;
            font-size: 0.9rem;
        }
        
        .support-options {
            padding: 0 1rem 1rem;
        }
        
        .support-option {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            width: 100%;
            text-align: left;
        }
        
        .support-option:hover {
            background: #e9ecef;
            transform: translateY(-2px);
        }
        
        .support-option-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        
        .support-option h4 {
            margin: 0;
            color: #333;
            font-size: 0.9rem;
        }
        
        .support-option p {
            margin: 0;
            color: #666;
            font-size: 0.8rem;
        }
        
        .support-option-icon {
            color: #667eea;
            font-size: 1.2rem;
        }
        
        .support-search {
            margin-bottom: 1rem;
        }
        
        .support-search input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.9rem;
        }
        
        .support-help-items {
            max-height: 150px;
            overflow-y: auto;
        }
        
        .help-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            font-size: 0.85rem;
            color: #666;
        }
        
        .help-item:hover {
            color: #667eea;
        }
        
        .help-item:last-child {
            border-bottom: none;
        }
        
        .support-nav {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: #f8f9fa;
            display: flex;
            border-top: 1px solid #eee;
        }
        
        .support-nav-item {
            flex: 1;
            padding: 0.75rem;
            text-align: center;
            background: none;
            border: none;
            cursor: pointer;
            color: #666;
            font-size: 0.8rem;
            transition: color 0.3s ease;
        }
        
        .support-nav-item.active {
            color: #667eea;
            background: white;
        }
        
        .support-nav-item i {
            display: block;
            margin-bottom: 0.25rem;
            font-size: 1.2rem;
        }
        
        @media (max-width: 480px) {
            .support-chat {
                width: 300px;
                height: 450px;
                right: -25px;
            }
        }
        
        /* Footer */
        footer {
            background: 
                linear-gradient(135deg, rgba(26, 26, 26, 0.98) 0%, rgba(0, 0, 0, 0.98) 100%),
                url('https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=1920&q=80') center/cover;
            color: white;
            padding: 3rem 0.5rem 1rem;
            position: relative;
        }
        
        footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 30% 50%, rgba(102, 126, 234, 0.1) 0%, transparent 50%);
            pointer-events: none;
        }
        
        .footer-content {
            max-width: 100%;
            margin: 0;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
            position: relative;
            z-index: 1;
        }
        
        .footer-section h4 {
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }
        
        .footer-section ul {
            list-style: none;
        }
        
        .footer-section ul li {
            margin-bottom: 0.5rem;
        }
        
        .footer-section a {
            color: #aaa;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer-section a:hover {
            color: #667eea;
        }
        
        .footer-about p {
            margin-bottom: 1.5rem;
        }
        
        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .social-links a {
            width: 40px;
            height: 40px;
            background: rgba(102, 126, 234, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #667eea;
            transition: all 0.3s ease;
            border: 1px solid rgba(102, 126, 234, 0.2);
        }
        
        .social-links a:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .footer-newsletter {
            max-width: 300px;
        }
        
        .newsletter-form {
            display: flex;
            margin-top: 1rem;
            gap: 0.5rem;
        }
        
        .newsletter-form input {
            flex: 1;
            padding: 0.75rem;
            border: 1px solid #444;
            border-radius: 6px;
            background: rgba(255,255,255,0.05);
            color: white;
            font-size: 0.9rem;
        }
        
        .newsletter-form input::placeholder {
            color: #888;
        }
        
        .newsletter-form input:focus {
            outline: none;
            border-color: #667eea;
            background: rgba(255,255,255,0.1);
        }
        
        .newsletter-form button {
            padding: 0.75rem 1rem;
            border-radius: 6px;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid #333;
            color: #888;
            position: relative;
            z-index: 1;
        }
        
        .footer-bottom-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .footer-badges {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .footer-badges .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(102, 126, 234, 0.1);
            border-radius: 20px;
            font-size: 0.85rem;
            color: #aaa;
            border: 1px solid rgba(102, 126, 234, 0.2);
        }
        
        .footer-badges .badge i {
            color: #667eea;
        }
        
        /* Back to Top Button */
        .back-to-top {
            position: fixed;
            bottom: 90px;
            right: 20px;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 999;
        }
        
        .back-to-top.visible {
            opacity: 1;
            visibility: visible;
        }
        
        .back-to-top:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        }
        
        /* Scroll Progress Indicator */
        .scroll-progress {
            position: fixed;
            top: 0;
            left: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            width: 0%;
            z-index: 10000;
            transition: width 0.1s ease;
            box-shadow: 0 2px 10px rgba(102, 126, 234, 0.5);
        }
        
        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Mobile Menu */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #333;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .hero p {
                font-size: 1.1rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .nav-links {
                display: none;
            }
            
            .mobile-menu-btn {
                display: block;
            }
            
            .nav-links.active {
                display: flex;
                flex-direction: column;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: white;
                padding: 1rem;
                box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            }
            
            .hero-buttons {
                flex-direction: column;
                align-items: stretch;
            }
            
            .features-grid,
            .steps,
            .channels-grid,
            .benefits-grid,
            .testimonials-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .contact-wrapper {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
        }
        
        @media (max-width: 480px) {
            .hero {
                padding: 120px 0.5rem 60px;
            }
            
            .hero h1 {
                font-size: 2rem;
            }
            
            .features,
            .how-it-works,
            .channels,
            .contact,
            .testimonials,
            .cta-section {
                padding: 60px 0.5rem;
            }
            
            .testimonials-grid,
            .features-grid,
            .steps,
            .channels-grid,
            .benefits-grid {
                grid-template-columns: 1fr;
            }
            
            .contact-form-wrapper {
                padding: 1.5rem;
            }
            
            .cta-content h2 {
                font-size: 2rem;
            }
            
            .cta-content p {
                font-size: 1rem;
            }
            
            .footer-bottom-content {
                flex-direction: column;
                text-align: center;
            }
            
            .footer-badges {
                justify-content: center;
            }
            
            .back-to-top {
                bottom: 80px;
                right: 15px;
            }
        }
        </style>
    </head>
    <body>
    <!-- Navigation -->
    <nav id="navbar">
        <div class="nav-container">
            <div class="logo">BulkSMS CRM</div>
            <button class="mobile-menu-btn" onclick="toggleMobileMenu()">☰</button>
            <div class="nav-links" id="navLinks">
                <a href="#features">Features</a>
                <a href="#how-it-works">How It Works</a>
                <a href="#channels">Channels</a>
                <a href="#pricing">Pricing</a>
                <a href="#contact">Contact Us</a>
                <a href="{{ route('tenant.register') }}" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Sign Up
                </a>
                <a href="{{ route('login') }}" class="btn btn-outline">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
            </div>
        </div>
    </nav>
    
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Multi-Channel Messaging Platform</h1>
            <p>Send SMS, WhatsApp, and Email at scale. Manage contacts, create campaigns, and track delivery with our powerful CRM platform.</p>
            <div class="hero-buttons">
                <a href="{{ route('tenant.register') }}" class="btn btn-primary" role="button">
                    <i class="fas fa-rocket"></i> Start Free Trial
                </a>
                <a href="#how-it-works" class="btn btn-secondary" role="button">
                    <i class="fas fa-play"></i> See How It Works
                </a>
            </div>
            <div class="hero-note">
                <p><i class="fas fa-check-circle"></i> No credit card required • Setup in 2 minutes • Free trial available</p>
            </div>
            <div class="hero-stats">
                <div class="stat">
                    <span class="stat-number">99.9%</span>
                    <span class="stat-label">Delivery Rate</span>
                </div>
                <div class="stat">
                    <span class="stat-number">3</span>
                    <span class="stat-label">Communication Channels</span>
                </div>
                <div class="stat">
                    <span class="stat-number">24/7</span>
                    <span class="stat-label">Support Available</span>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <div class="section-header">
                <div class="section-tag">Features</div>
                <h2 class="section-title">Everything You Need to Connect</h2>
                <p class="section-description">Powerful features designed to help you communicate better with your audience</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-comments"></i></div>
                    <h3>Multi-Channel Support</h3>
                    <p>Send messages via SMS, WhatsApp, and Email from a single unified platform. Switch between channels seamlessly.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-users"></i></div>
                    <h3>Contact Management</h3>
                    <p>Organize contacts by departments, import from CSV, and maintain a comprehensive contact database.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-rocket"></i></div>
                    <h3>Bulk Campaigns</h3>
                    <p>Create and send bulk campaigns to thousands of recipients. Schedule messages for optimal delivery times.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
                    <h3>Real-Time Analytics</h3>
                    <p>Track delivery status, open rates, and engagement metrics with comprehensive analytics dashboard.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-file-alt"></i></div>
                    <h3>Message Templates</h3>
                    <p>Create reusable templates with variable substitution for faster, consistent communication.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-code"></i></div>
                    <h3>API Integration</h3>
                    <p>Powerful REST API with rate limiting and webhook support for seamless integration with your systems.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-wallet"></i></div>
                    <h3>Wallet System</h3>
                    <p>Built-in wallet with M-Pesa integration for easy top-ups and transparent billing.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-inbox"></i></div>
                    <h3>Inbox Management</h3>
                    <p>Manage two-way conversations and replies in a unified inbox with conversation threading.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                    <h3>Secure & Compliant</h3>
                    <p>Enterprise-grade security with API authentication, rate limiting, and comprehensive audit trails.</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Pricing Section -->
    <section id="pricing" style="padding: 100px 0.5rem; background: #ffffff;">
        <div class="container">
            <div class="section-header">
                <div class="section-tag">Our Packages</div>
                <h2 class="section-title">Flexible, Pay‑as‑You‑Go Pricing</h2>
                <p class="section-description">Top‑ups never expire. Contact us for high‑volume discounts.</p>
            </div>
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; box-shadow:0 4px 20px rgba(0,0,0,0.06);">
                    <thead>
                        <tr style="background:#56c0ef; color:#0a2540; text-align:left;">
                            <th style="padding:14px; font-weight:700;">Top‑up Amount</th>
                            <th style="padding:14px; font-weight:700;">Price per SMS</th>
                            <th style="padding:14px; font-weight:700;">Min Messages</th>
                            <th style="padding:14px; font-weight:700;">Max Messages</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="background:#f8fbff; border-bottom:1px solid #eef2f7;">
                            <td style="padding:14px;">KES 1 - KES 9,000</td>
                            <td style="padding:14px;">KES 0.75</td>
                            <td style="padding:14px;">1</td>
                            <td style="padding:14px;">12,000</td>
                        </tr>
                        <tr style="background:#ffffff; border-bottom:1px solid #eef2f7;">
                            <td style="padding:14px;">KES 9,001 – KES 18,000</td>
                            <td style="padding:14px;">KES 0.70</td>
                            <td style="padding:14px;">12,859</td>
                            <td style="padding:14px;">25,714</td>
                        </tr>
                        <tr style="background:#f8fbff; border-bottom:1px solid #eef2f7;">
                            <td style="padding:14px;">KES 18,001 – KES 37,000</td>
                            <td style="padding:14px;">KES 0.65</td>
                            <td style="padding:14px;">27,694</td>
                            <td style="padding:14px;">56,923</td>
                        </tr>
                        <tr style="background:#ffffff; border-bottom:1px solid #eef2f7;">
                            <td style="padding:14px;">KES 37,001 – KES 75,000</td>
                            <td style="padding:14px;">KES 0.60</td>
                            <td style="padding:14px;">61,668</td>
                            <td style="padding:14px;">125,000</td>
                        </tr>
                        <tr style="background:#f8fbff;">
                            <td style="padding:14px;">KES 75,001 – KES 150,000</td>
                            <td style="padding:14px;">KES 0.55</td>
                            <td style="padding:14px;">136,365</td>
                            <td style="padding:14px;">272,727</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div style="text-align:center; margin-top:2rem;">
                <a href="{{ route('tenant.register') }}" class="btn btn-primary btn-large"><i class="fas fa-user-plus"></i> Create Free Account</a>
            </div>
        </div>
    </section>


    <!-- How It Works -->
    <section class="how-it-works" id="how-it-works">
        <div class="container">
            <div class="section-header">
                <div class="section-tag">Simple Process</div>
                <h2 class="section-title">How It Works</h2>
                <p class="section-description">From campaign creation to delivery tracking - see how our platform works</p>
            </div>
            <div class="steps">
                <div class="step">
                    <div class="step-number">1</div>
                    <h3>Manage Contacts</h3>
                    <p>Organize your customer database with tags, departments, and custom fields. Import from CSV or add manually.</p>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <h3>Create Campaigns</h3>
                    <p>Design your message using templates, personalization, and multi-channel options. Schedule for optimal delivery times.</p>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <h3>Send Messages</h3>
                    <p>Deliver SMS, WhatsApp, and Email campaigns instantly or schedule them for later. Our system handles the heavy lifting.</p>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <h3>Track Results</h3>
                    <p>Monitor delivery rates, open rates, and engagement metrics in real-time. Get detailed analytics and reports.</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Channels -->
    <section class="channels" id="channels">
        <div class="container">
            <div class="section-header">
                <div class="section-tag" style="color: rgba(255,255,255,0.8);">Communication Channels</div>
                <h2 class="section-title" style="color: white;">Reach Your Audience Everywhere</h2>
                <p class="section-description" style="color: rgba(255,255,255,0.9);">Choose the best channel for your message</p>
            </div>
            <div class="channels-grid">
                <div class="channel-card">
                    <div class="channel-icon"><i class="fas fa-sms"></i></div>
                    <h3>SMS</h3>
                    <p>Instant delivery with 99.9% success rate. Perfect for time-sensitive messages and notifications.</p>
                </div>
                <div class="channel-card">
                    <div class="channel-icon"><i class="fab fa-whatsapp"></i></div>
                    <h3>WhatsApp</h3>
                    <p>Rich media messages with interactive buttons. Engage with your audience where they are.</p>
                </div>
                <div class="channel-card">
                    <div class="channel-icon"><i class="fas fa-envelope"></i></div>
                    <h3>Email</h3>
                    <p>Professional HTML emails with tracking. Ideal for detailed communications and newsletters.</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Why Choose Us Section -->
    <section class="why-choose-us">
        <div class="container">
            <div class="section-header">
                <h2>Why Choose BulkSMS CRM?</h2>
                <p>Trusted by businesses across Kenya and beyond for reliable messaging solutions</p>
            </div>
            <div class="benefits-grid">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Enterprise Security</h3>
                    <p>Bank-level encryption, API authentication, and comprehensive audit trails to keep your data safe.</p>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <h3>Lightning Fast Setup</h3>
                    <p>Get started in under 2 minutes with our streamlined onboarding process. No technical expertise required.</p>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Real-Time Analytics</h3>
                    <p>Track delivery rates, engagement metrics, and campaign performance with detailed analytics dashboard.</p>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3>24/7 Support</h3>
                    <p>Dedicated support team available around the clock to help you succeed with your messaging campaigns.</p>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3>Multi-Channel Reach</h3>
                    <p>Send SMS, WhatsApp, and Email from one platform. Reach your customers wherever they are.</p>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <h3>Transparent Pricing</h3>
                    <p>No hidden fees, no contracts. Pay only for what you use with our flexible pricing model.</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Contact Section -->
    <section class="contact" id="contact">
        <div class="container">
            <div class="section-header">
                <div class="section-tag">Get in Touch</div>
                <h2 class="section-title">Ready to Transform Your Communication?</h2>
                <p class="section-description">Contact us today to learn how we can help your business</p>
            </div>

            <div class="contact-wrapper">
                <div class="contact-info">
                    <h3>Contact Information</h3>
                    <div class="contact-item">
                        <div class="contact-icon"><i class="fas fa-envelope"></i></div>
                        <div>
                            <h4>Email</h4>
                            <a href="mailto:mathiasodhis@gmail.com">mathiasodhis@gmail.com</a>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon"><i class="fas fa-phone"></i></div>
                        <div>
                            <h4>Phone</h4>
                            <a href="tel:+254728883160">+254 728 883 160</a>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon"><i class="fas fa-map-marker-alt"></i></div>
                        <div>
                            <h4>Address</h4>
                            <p>Nairobi, Kenya</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon"><i class="fas fa-clock"></i></div>
                        <div>
                            <h4>Business Hours</h4>
                            <p>Mon - Fri: 8:00 AM - 6:00 PM EAT</p>
                        </div>
                    </div>
            </div>

                <div class="contact-form-wrapper">
                    <form class="contact-form" id="contactForm">
                        <div class="form-group">
                            <label for="name">Full Name *</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number *</label>
                            <input type="tel" id="phone" name="phone" required>
                        </div>
                        <div class="form-group">
                            <label for="company">Company Name</label>
                            <input type="text" id="company" name="company">
                        </div>
                        <div class="form-group">
                            <label for="message">Message *</label>
                            <textarea id="message" name="message" rows="4" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Testimonials Section -->
    <section class="testimonials">
        <div class="container">
            <div class="section-header">
                <div class="section-tag">Testimonials</div>
                <h2 class="section-title">What Our Clients Say</h2>
                <p class="section-description">Trusted by businesses across Kenya and beyond</p>
            </div>
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <div class="testimonial-stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">"BulkSMS CRM has transformed how we communicate with our customers. The multi-channel approach has increased our engagement by 300%!"</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">JK</div>
                        <div>
                            <h4>John Kamau</h4>
                            <p>CEO, TechStart Kenya</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">"The API integration was seamless. We had our system up and running in less than an hour. Outstanding support team!"</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">SM</div>
                        <div>
                            <h4>Sarah Mwangi</h4>
                            <p>CTO, FinTech Solutions</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">"Affordable, reliable, and feature-rich. The wallet system makes managing costs so much easier. Highly recommended!"</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">DO</div>
                        <div>
                            <h4>David Ochieng</h4>
                            <p>Marketing Director, Retail Plus</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Start Sending Messages Today</h2>
                <p>Join hundreds of businesses already using our platform to communicate better with their customers.</p>
                <div class="cta-buttons">
                    <a href="{{ route('tenant.register') }}" class="btn btn-primary btn-large">
                        <i class="fas fa-rocket"></i> Start Your Free Trial
                    </a>
                    <a href="{{ route('api.documentation') }}" class="btn btn-secondary btn-large">
                        <i class="fas fa-code"></i> View API Docs
                    </a>
                </div>
                <div class="cta-note">
                    <p><i class="fas fa-shield-alt"></i> Secure • <i class="fas fa-clock"></i> Quick Setup • <i class="fas fa-gift"></i> Free Trial</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-section footer-about">
                <h4>BulkSMS CRM</h4>
                <p>Multi-channel messaging platform for modern businesses. Built with Laravel.</p>
                <div class="social-links">
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" aria-label="GitHub"><i class="fab fa-github"></i></a>
                </div>
            </div>
            <div class="footer-section">
                <h4>Product</h4>
                <ul>
                    <li><a href="#features">Features</a></li>
                    <li><a href="#how-it-works">How It Works</a></li>
                    <li><a href="#channels">Channels</a></li>
                    <li><a href="{{ route('api.documentation') }}">API Documentation</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Company</h4>
                <ul>
                    <li><a href="#features">About Us</a></li>
                    <li><a href="#contact">Contact</a></li>
                    <li><a href="mailto:mathiasodhis@gmail.com">Privacy Policy</a></li>
                    <li><a href="mailto:mathiasodhis@gmail.com">Terms of Service</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Support</h4>
                <ul>
                    <li><a href="#contact">Contact Us</a></li>
                    <li><a href="{{ route('api.documentation') }}">API Documentation</a></li>
                    <li><a href="mailto:mathiasodhis@gmail.com">Email Support</a></li>
                    <li><a href="tel:+254728883160">Call Us</a></li>
                    <li><a href="{{ route('login') }}">Login</a></li>
                </ul>
            </div>
            <div class="footer-section footer-newsletter">
                <h4>Stay Updated</h4>
                <p>Subscribe to our newsletter for updates and tips.</p>
                <form class="newsletter-form" id="newsletterForm">
                    <input type="email" placeholder="Enter your email" required>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i></button>
                </form>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <p>&copy; {{ date('Y') }} BulkSMS CRM by Matech Technologies. All rights reserved.</p>
                <div class="footer-badges">
                    <span class="badge"><i class="fas fa-shield-alt"></i> Secure</span>
                    <span class="badge"><i class="fas fa-bolt"></i> Fast</span>
                    <span class="badge"><i class="fas fa-check-circle"></i> Reliable</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button id="backToTop" class="back-to-top" aria-label="Back to top">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Scroll Progress Indicator -->
    <div class="scroll-progress" id="scrollProgress"></div>
    
    <!-- Support Chat Widget -->
    <div class="support-widget" id="supportWidget">
        <button class="support-toggle" onclick="toggleSupport()">
            <i class="fas fa-comment-dots"></i>
        </button>
        
        <div class="support-chat">
            <div class="support-header">
                <div class="support-logo">
                    <div class="logo-icon">S</div>
                    <div>
                        <div style="font-size: 0.8rem; font-weight: 600;">Support Team</div>
                        <div style="font-size: 0.7rem; opacity: 0.9;">Usually replies in minutes</div>
                    </div>
                </div>
                <div class="support-agents">
                    <div class="agent-avatar">A1</div>
                    <div class="agent-avatar">A2</div>
                    <div class="agent-avatar">A3</div>
                </div>
                <button onclick="toggleSupport()" style="background: none; border: none; color: white; font-size: 1.2rem; cursor: pointer;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="support-greeting">
                <h3>Hi there! 👋</h3>
                <p>How can we help you today?</p>
            </div>
            
            <div class="support-options">
                <button class="support-option" onclick="startMessage()">
                    <div class="support-option-header">
                        <h4>Send us a message</h4>
                        <i class="fas fa-paper-plane support-option-icon"></i>
                    </div>
                    <p>We typically reply in under 2 minutes</p>
                </button>
                
                <div class="support-search">
                    <input type="text" placeholder="Search for help..." onkeyup="searchHelp(this.value)">
                </div>
                
                <div class="support-help-items" id="helpItems">
                    <div class="help-item" onclick="openHelp('sms-setup')">
                        <span>How to setup SMS messaging</span>
                        <i class="fas fa-chevron-right"></i>
                    </div>
                    <div class="help-item" onclick="openHelp('whatsapp-integration')">
                        <span>WhatsApp integration guide</span>
                        <i class="fas fa-chevron-right"></i>
                    </div>
                    <div class="help-item" onclick="openHelp('api-documentation')">
                        <span>API documentation and setup</span>
                        <i class="fas fa-chevron-right"></i>
                    </div>
                    <div class="help-item" onclick="openHelp('billing-pricing')">
                        <span>Billing and pricing information</span>
                        <i class="fas fa-chevron-right"></i>
                    </div>
                    <div class="help-item" onclick="openHelp('contact-sales')">
                        <span>Contact our sales team</span>
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </div>
                
                <button class="support-option" onclick="toggleFeatureRequest()">
                    <div class="support-option-header">
                        <h4>Have a feature request?</h4>
                        <i class="fas fa-chevron-down support-option-icon"></i>
                    </div>
                </button>
            </div>
            
            <div class="support-nav">
                <button class="support-nav-item active" onclick="switchTab('home')">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </button>
                <button class="support-nav-item" onclick="switchTab('messages')">
                    <i class="fas fa-comments"></i>
                    <span>Messages</span>
                </button>
                <button class="support-nav-item" onclick="switchTab('help')">
                    <i class="fas fa-question-circle"></i>
                    <span>Help</span>
                </button>
            </div>
        </div>
    </div>
    
    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
        
        // Smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // Mobile menu toggle
        function toggleMobileMenu() {
            const navLinks = document.getElementById('navLinks');
            navLinks.classList.toggle('active');
        }
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const navLinks = document.getElementById('navLinks');
            const mobileBtn = document.querySelector('.mobile-menu-btn');
            
            if (!event.target.closest('.nav-container')) {
                navLinks.classList.remove('active');
            }
        });
        
        // Contact form handling
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const name = formData.get('name');
            const email = formData.get('email');
            const phone = formData.get('phone');
            const company = formData.get('company');
            const message = formData.get('message');
            
            // Create mailto link with form data
            const subject = encodeURIComponent('Inquiry from ' + name + (company ? ' - ' + company : ''));
            const body = encodeURIComponent(
                'Name: ' + name + '\n' +
                'Email: ' + email + '\n' +
                'Phone: ' + phone + '\n' +
                (company ? 'Company: ' + company + '\n' : '') +
                '\nMessage:\n' + message
            );
            
            window.location.href = 'mailto:mathiasodhis@gmail.com?subject=' + subject + '&body=' + body;
            
            // Show success message
            alert('Thank you for your interest! Your default email client will open with the message. You can also reach us directly at:\n\nEmail: mathiasodhis@gmail.com\nPhone: +254 728 883 160');
            
            // Reset form
            this.reset();
        });
        
        // Support Widget Functions
        function toggleSupport() {
            const widget = document.getElementById('supportWidget');
            widget.classList.toggle('open');
        }
        
        function startMessage() {
            // Open contact form in a new tab or scroll to contact section
            document.getElementById('contactForm').scrollIntoView({ behavior: 'smooth' });
            toggleSupport();
        }
        
        function searchHelp(query) {
            const helpItems = document.getElementById('helpItems');
            const items = helpItems.querySelectorAll('.help-item');
            
            items.forEach(item => {
                const text = item.textContent.toLowerCase();
                if (text.includes(query.toLowerCase())) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = query ? 'none' : 'flex';
                }
            });
        }
        
        function openHelp(topic) {
            // You can customize these URLs based on your documentation
            const helpUrls = {
                'sms-setup': '{{ route("api.documentation") }}',
                'whatsapp-integration': '{{ route("api.documentation") }}',
                'api-documentation': '{{ route("api.documentation") }}',
                'billing-pricing': '#contact',
                'contact-sales': '#contact'
            };
            
            const url = helpUrls[topic] || '#contact';
            
            if (url.startsWith('#')) {
                document.querySelector(url).scrollIntoView({ behavior: 'smooth' });
                toggleSupport();
            } else {
                window.open(url, '_blank');
            }
        }
        
        function toggleFeatureRequest() {
            alert('Feature requests are welcome! Please contact us through the contact form below or email us directly at mathiasodhis@gmail.com');
            toggleSupport();
            document.getElementById('contactForm').scrollIntoView({ behavior: 'smooth' });
        }
        
        function switchTab(tab) {
            // Remove active class from all nav items
            document.querySelectorAll('.support-nav-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Add active class to clicked item
            event.target.closest('.support-nav-item').classList.add('active');
            
            // You can add tab switching logic here if needed
            if (tab === 'messages') {
                startMessage();
            } else if (tab === 'help') {
                document.getElementById('helpItems').scrollIntoView({ behavior: 'smooth' });
            }
        }
        
        // Close support widget when clicking outside
        document.addEventListener('click', function(event) {
            const widget = document.getElementById('supportWidget');
            if (!event.target.closest('.support-widget')) {
                widget.classList.remove('open');
            }
        });
        
        // Scroll Progress Indicator
        window.addEventListener('scroll', function() {
            const scrollProgress = document.getElementById('scrollProgress');
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const scrollHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            const progress = (scrollTop / scrollHeight) * 100;
            scrollProgress.style.width = progress + '%';
        });
        
        // Back to Top Button
        const backToTopBtn = document.getElementById('backToTop');
        
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopBtn.classList.add('visible');
            } else {
                backToTopBtn.classList.remove('visible');
            }
        });
        
        backToTopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        // Newsletter Form
        document.getElementById('newsletterForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const email = this.querySelector('input[type="email"]').value;
            alert('Thank you for subscribing! We will send updates to: ' + email);
            this.reset();
        });
        
        // Intersection Observer for Scroll Animations (optional enhancement)
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);
        
        // Observe elements for scroll animations
        document.querySelectorAll('.feature-card, .step, .channel-card, .testimonial-card').forEach(el => {
            observer.observe(el);
        });
    </script>
    </body>
</html>
