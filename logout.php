<?php
session_start();

// Destroy session
session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>লগ আউট - অন্তঃকণ্ঠ</title>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #8e44ad;
            --primary-dark: #732d91;
            --primary-light: #f3e6f8;
            --secondary: #3498db;
            --bg-light: #f8f9fa;
            --bg-white: #ffffff;
            --text-dark: #2c3e50;
            --text-light: #7f8c8d;
            --shadow-lg: 0 10px 25px rgba(0,0,0,0.1);
            --radius-lg: 20px;
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Hind Siliguri', sans-serif;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            overflow: hidden;
            position: relative;
        }

        /* Floating bubbles background */
        .bubble {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 15s infinite linear;
            z-index: 0;
        }

        @keyframes float {
            0% { transform: translateY(0) rotate(0deg); opacity: 1; }
            100% { transform: translateY(-1000px) rotate(720deg); opacity: 0; }
        }

        /* Main container */
        .logout-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: var(--radius-lg);
            padding: 3rem;
            text-align: center;
            max-width: 600px;
            width: 90%;
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
            z-index: 1;
            animation: fadeInUp 0.8s ease-out;
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

        .logout-container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, rgba(255,255,255,0) 70%);
            z-index: -1;
        }

        /* Logo */
        .logo {
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            font-weight: 700;
        }

        .logo i {
            font-size: 2.8rem;
            color: var(--primary-light);
        }

        /* Logout icon */
        .logout-icon {
            font-size: 6rem;
            margin: 1.5rem 0;
            color: rgba(255, 255, 255, 0.9);
            animation: pulse 2s infinite ease-in-out;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 0.8; }
            50% { transform: scale(1.1); opacity: 1; }
            100% { transform: scale(1); opacity: 0.8; }
        }

        /* Content */
        h1 {
            font-size: 2.2rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            line-height: 1.7;
            opacity: 0.9;
        }

        /* Buttons */
        .buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 1.5rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding: 1rem 2rem;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 500;
            text-decoration: none;
            transition: var(--transition);
            min-width: 200px;
        }

        .btn-primary {
            background: var(--bg-white);
            color: var(--primary-dark);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .btn-primary:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            background: var(--bg-light);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid white;
            color: white;
        }

        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-5px);
        }

        /* Countdown */
        .countdown {
            font-size: 1.3rem;
            margin-top: 2rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .countdown i {
            font-size: 1.5rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .logout-container {
                padding: 2rem;
            }
            
            .logo {
                font-size: 2rem;
            }
            
            .logo i {
                font-size: 2.2rem;
            }
            
            .logout-icon {
                font-size: 4.5rem;
            }
            
            h1 {
                font-size: 1.8rem;
            }
            
            p {
                font-size: 1.1rem;
            }
            
            .btn {
                padding: 0.875rem 1.5rem;
                font-size: 1rem;
                min-width: 160px;
            }
            
            .buttons {
                flex-direction: column;
                align-items: center;
                gap: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <!-- Floating bubbles background -->
    <div id="bubbles-container"></div>
    
    <!-- Main content -->
    <div class="logout-container">
        <div class="logo">
            <i class="fas fa-heart"></i>
            <span>অন্তঃকণ্ঠ</span>
        </div>
        
        <div class="logout-icon">
            <i class="fas fa-sign-out-alt"></i>
        </div>
        
        <h1>সফলভাবে লগ আউট হয়েছেন</h1>
        <p>আপনার সুরক্ষার জন্য আমরা আপনার সেশন শেষ করেছি। আবার কবিতার জগতে ফিরে আসার জন্য লগ ইন করুন।</p>
        
        <div class="countdown" id="countdown">
            <i class="fas fa-clock"></i>
            <span>৫ সেকেন্ডে হোমপেজে ফেরত যাচ্ছেন...</span>
        </div>
        
        <div class="buttons">
            <a href="index.php" class="btn btn-primary">
                <i class="fas fa-home"></i> হোমপেজে ফিরে যান
            </a>
            <a href="login.php" class="btn btn-outline">
                <i class="fas fa-sign-in-alt"></i> আবার লগ ইন করুন
            </a>
        </div>
    </div>
    
    <script>
        // Create floating bubbles
        function createBubbles() {
            const container = document.getElementById('bubbles-container');
            const bubbleCount = 15;
            
            for (let i = 0; i < bubbleCount; i++) {
                const bubble = document.createElement('div');
                bubble.classList.add('bubble');
                
                // Random size between 20px and 100px
                const size = Math.random() * 80 + 20;
                bubble.style.width = `${size}px`;
                bubble.style.height = `${size}px`;
                
                // Random position
                bubble.style.left = `${Math.random() * 100}%`;
                bubble.style.bottom = `-${size}px`;
                
                // Random animation duration between 10s and 20s
                const duration = Math.random() * 10 + 10;
                bubble.style.animationDuration = `${duration}s`;
                
                // Random delay
                bubble.style.animationDelay = `${Math.random() * 5}s`;
                
                container.appendChild(bubble);
            }
        }
        
        // Countdown for automatic redirect
        function startCountdown() {
            let seconds = 5;
            const countdownElement = document.getElementById('countdown');
            const countdownInterval = setInterval(() => {
                seconds--;
                countdownElement.innerHTML = `<i class="fas fa-clock"></i><span>${seconds} সেকেন্ডে হোমপেজে ফেরত যাচ্ছেন...</span>`;
                
                if (seconds <= 0) {
                    clearInterval(countdownInterval);
                    window.location.href = 'index.php';
                }
            }, 1000);
        }
        
        // Initialize everything when page loads
        window.onload = function() {
            createBubbles();
            startCountdown();
        };
    </script>
</body>
</html>