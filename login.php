<?php
session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    $stmt = $conn->prepare("
        SELECT u.*, c.status as candidate_status 
        FROM users u 
        LEFT JOIN candidates c ON u.id = c.user_id 
        WHERE u.email = ?
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        // Check if candidate is approved
        if ($user['role'] === 'candidate' && $user['candidate_status'] !== 'approved') {
            $error = "Your candidate account is pending approval. Please wait for admin approval.";
            
            // Log failed login attempt
            $stmt = $conn->prepare("
                INSERT INTO audit_logs (user_id, action, details, ip_address) 
                VALUES (?, 'LOGIN_FAILED', ?, ?)
            ");
            $stmt->execute([
                $user['id'],
                "Candidate account not yet approved",
                $_SERVER['REMOTE_ADDR']
            ]);
            
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            
            // Log successful login
            $stmt = $conn->prepare("
                INSERT INTO audit_logs (user_id, action, details, ip_address) 
                VALUES (?, 'LOGIN', ?, ?)
            ");
            $stmt->execute([
                $user['id'],
                "User logged in successfully",
                $_SERVER['REMOTE_ADDR']
            ]);

            // Redirect based on role
            switch($user['role']) {
                case 'admin':
                    header('Location: admin/dashboard.php');
                    break;
                case 'candidate':
                    header('Location: candidate/dashboard.php');
                    break;
                case 'student':
                    header('Location: student/dashboard.php');
                    break;
            }
            exit;
        }
    } else {
        $error = "Invalid email or password";
        
        // Log failed login attempt
        $stmt = $conn->prepare("
            INSERT INTO audit_logs (action, details, ip_address) 
            VALUES ('LOGIN_FAILED', ?, ?)
        ");
        $stmt->execute([
            "Failed login attempt for email: $email",
            $_SERVER['REMOTE_ADDR']
        ]);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login - E-Voting System</title>
    <link rel="icon" href="https://mchs.mw/img/mchs_logo.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #024731;
            --secondary-color: #921c2d;
            --accent-color: #bc1a2e;
            --light-color: #efe9e8;
            --text-color: #024731;
        }

        body {
            background: linear-gradient(135deg, #f5f5f5 0%, #ffffff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-container {
            max-width: 900px;
            max-height: 520px;
            margin: 20px auto;
            display: flex;
            flex-direction: row;
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
            border-radius: 20px;
            overflow: hidden;
            position: relative;
            background: white;
        }

        .login-image {
            flex: 0.9;
            background: rgb(0, 32, 63);
            padding: 5px 20px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .login-image h2 {
            color: white;
            font-weight: 600;
            margin-bottom: 5px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
            font-size: 1.45rem;
        }

        .login-image .institution-name {
            color: rgba(255, 255, 255, 0.95);
            font-size: 1.2rem;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 120px;
        }

        .welcome-features {
            margin-top: 20px;
        }

        .feature-item {
            color: rgba(255, 255, 255, 0.9);
            margin: 8px 0;
            padding: 2px 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1rem;
        }

        .feature-item i {
            width: 25px;
            height: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }

        .feature-item:hover i {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.1);
        }

        /* Animation for feature items */
        .feature-item {
            opacity: 0;
            transform: translateX(-20px);
            animation: fadeInRight 0.5s ease forwards;
        }

        .feature-item:nth-child(1) { animation-delay: 0.2s; }
        .feature-item:nth-child(2) { animation-delay: 0.4s; }
        .feature-item:nth-child(3) { animation-delay: 0.6s; }

        @keyframes fadeInRight {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Responsive adjustments */
        @media (max-width: 900px) {
            .login-container {
                max-width: 95%;
                margin: 10px auto;
            }
        }

        @media (max-width: 768px) {
            .login-container {
                flex-direction: column !important;
                max-height: none;
                margin: 10px;
            }

            .login-image {
                order: -1 !important;
                width: 100%;
                border-radius: 15px 15px 0 0;
                padding: 10px;
                max-height: 420px;
            }

            .login-form {
                order: 2 !important;
                width: 100%;
                border-radius: 0 0 15px 15px;
                padding: 15px;
            }

            .logo-container {
                width: 65px;
                height: 65px;
                margin-bottom: 8px;
                align-self: flex-start;
                margin-left: 35px;
            }

            .login-image h2 {
                font-size: 1.2rem;
                margin-bottom: 5px;
            }

            .institution-name, .welcome-features {
                align-self: center;
            }

            .feature-item {
                font-size: 0.95rem;
                padding: 4px 8px;
                margin: 4px 0;
            }

            .feature-item i {
                width: 20px;
                height: 20px;
                font-size: 0.7rem;
            }

            .login-form {
                padding: 15px;
            }

            .login-header {
                font-size: 1.2rem;
            }

            .login-subtext {
                font-size: 0.8rem;
            }

            .role-selector {
                gap: 8px;
                margin-top: 15px;
                margin-bottom: 25px;
            }

            .role-option {
                padding: 8px;
                border-radius: 12px;
            }

            .role-option i {
                font-size: 18px;
                margin-bottom: 4px;
            }

            .role-option div {
                font-size: 0.75rem;
            }

            .input-group {
                margin-bottom: 12px;
            }

            .form-control {
                padding: 8px 10px;
                font-size: 0.9rem;
                border-radius: 8px;
            }

            .input-group label {
                font-size: 0.85rem;
            }

            .btn-primary {
                padding: 8px;
                font-size: 0.9rem;
            }

            .home-icon-link {
                width: 32px;
                height: 32px;
                top: 10px;
                right: 10px;
            }

            .home-icon-link i {
                font-size: 14px;
            }
        }

        @media (max-width: 480px) {
            .login-container {
                margin: 5px;
                border-radius: 12px;
            }

            .logo-container {
                width: 50px;
                height: 50px;
            }

            .login-image h2 {
                font-size: 1.1rem;
            }

            .institution-name {
                font-size: 1rem;
            }

            .feature-item {
                font-size: 0.9rem;
                padding: 3px 6px;
            }

            .role-selector {
                gap: 6px;
                margin-bottom: 20px;
            }

            .role-option {
                padding: 6px;
            }

            .role-option i {
                font-size: 16px;
            }

            .role-option div {
                font-size: 0.7rem;
            }

            .form-control {
                padding: 7px;
                font-size: 0.85rem;
            }

            .btn-primary {
                padding: 7px;
                font-size: 0.85rem;
            }
        }

        @media (max-height: 600px) and (orientation: landscape) {
            .login-container {
                flex-direction: row;
                max-height: 95vh;
            }

            .login-image {
                padding: 10px;
            }

            .logo-container {
                width: 70px;
                height: 70px;
                margin-bottom: 5px;
            }

            .feature-item {
                margin: 2px 0;
            }

            .welcome-features {
                margin-top: 5px;
            }

            .login-form {
                padding: 10px 15px;
            }

            .role-selector {
                margin: 10px 0;
            }
        }

        @media (max-width: 320px) {
            .login-container {
                margin: 3px;
            }

            .logo-container {
                width: 45px;
                height: 45px;
                margin-left: 15px;
            }

            .login-image h2 {
                font-size: 1rem;
            }

            .role-selector {
                flex-wrap: wrap;
                justify-content: center;
            }

            .role-option {
                flex: 0 0 calc(33.33% - 8px);
                min-width: 80px;
            }
        }

        .login-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgb(0, 32, 63), rgb(0, 45, 98));
            opacity: 0.6;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .login-form {
            flex: 1.1;
            padding: 20px 35px;
            display: flex;
            flex-direction: column;
            background: white;
            position: relative;
        }

        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }

        .shape {
            position: absolute;
            background: rgba(2, 71, 49, 0.1);
            border-radius: 50%;
            animation: float-shapes 15s infinite ease-in-out;
        }

        @keyframes float-shapes {
            0% { 
                transform: translateY(0) rotate(0deg) scale(1); 
                opacity: 0.3;
            }
            33% { 
                transform: translateY(-15px) rotate(120deg) scale(1.1); 
                opacity: 0.5;
            }
            66% { 
                transform: translateY(10px) rotate(240deg) scale(0.9); 
                opacity: 0.4;
            }
            100% { 
                transform: translateY(0) rotate(360deg) scale(1); 
                opacity: 0.3;
            }
        }

        .role-selector {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-top: 15px;
            margin-bottom: 25px;
            position: relative;
            z-index: 1;
        }

        .role-option {
            text-align: center;
            cursor: pointer;
            padding: 13px;
            border-radius: 15px;
            transition: all 0.3s;
            border: 2px solid transparent;
            background: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
            color: #333;
        }

        .role-option::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            opacity: 0;
            transition: opacity 0.3s;
            z-index: -1;
        }

        .role-option:hover::before,
        .role-option.active::before {
            opacity: 0.1;
        }

        .role-option:hover {
            transform: translateY(-5px) scale(1.05);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .role-option i {
            font-size: 23px;
            margin-bottom: 6px;
            color: var(--primary-color);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .role-option:hover i {
            transform: scale(1.2) rotate(360deg);
        }

        .role-option div {
            font-size: 0.87rem;
        }

        /* Updated Input Group Styling */
        .input-group {
            position: relative;
            margin-bottom: 25px;
            width: 100%;
        }

        .form-control {
            height: 48px;
            font-size: 1.1rem;
            padding: 8px 20px 8px 45px;
            border-radius: 12px;
            border: 2px solid #eee;
            transition: all 0.3s;
            background: #f8f9fa;
            width: 100%;
            color: #333;
        }

        .input-group label {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 1.1rem;
            color: #666;
            transition: all 0.3s;
            pointer-events: none;
            padding: 0 5px;
            background: transparent;
        }

        .input-group label i {
            font-size: 1rem;
            color: #666;
            transition: all 0.3s;
        }

        /* Active/Focus State */
        .input-group input:focus + label,
        .input-group input:not(:placeholder-shown) + label {
            top: 0;
            font-size: 0.85rem;
            color: var(--primary-color);
            transform: translateY(-50%) scale(0.9);
            background: white;
            left: 12px;
        }

        .input-group input:focus + label i,
        .input-group input:not(:placeholder-shown) + label i {
            color: var(--primary-color);
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .form-control {
                height: 50px;
                font-size: 1rem;
                padding: 10px 15px 10px 40px;
            }
            
            .input-group label {
                font-size: 1rem;
                left: 12px;
            }
            
            .input-group label i {
                font-size: 0.9rem;
            }
        }

        @media (max-width: 576px) {
            .form-control {
                height: 48px;
                font-size: 16px; /* Prevent zoom on iOS */
                padding: 10px 12px 10px 35px;
            }
            
            .input-group label {
                font-size: 0.95rem;
                left: 10px;
            }
            
            .input-group label i {
                font-size: 0.85rem;
            }
        }

        .button-wrapper {
            margin-top: 5px;
            display: flex;
            justify-content: center;
        }

        .btn-primary {
            width: 100% !important;
            background: rgb(0, 32, 63) !important;
            border: none;
            padding: 6px;
            border-radius: 6px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s;
            margin: 15px auto 0;
            font-size: 0.85rem;
            line-height: 1;
        }

        .btn-primary i {
            font-size: 0.8rem;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(2, 71, 49, 0.3);
        }

        .loading-spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid #fff;
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg) scale(1); }
            50% { transform: rotate(180deg) scale(1.1); }
            100% { transform: rotate(360deg) scale(1); }
        }

        .alert {
            border: none;
            border-radius: 10px;
            padding: 15px 20px;
            margin: 0 0 20px 0;
            position: relative;
            overflow: hidden;
            width: 100%;
            font-size: 1.1rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 10;
            opacity: 1;
            transition: opacity 0.5s ease-in-out;
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .alert::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 4px;
            height: 100%;
            background: currentColor;
        }

        .alert-danger {
            background-color: rgba(146, 28, 45, 0.1);
            border-left: 4px solid var(--secondary-color);
            color: var(--secondary-color);
        }

        .alert i {
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        @media (max-width: 768px) {
            .alert {
                padding: 12px 15px;
                font-size: 1rem;
            }
            
            .alert i {
                font-size: 1.1rem;
            }
        }

        @media (max-width: 480px) {
            .alert {
                padding: 10px 12px;
                font-size: 0.95rem;
            }
            
            .alert i {
                font-size: 1rem;
            }
        }

        .alert.fade-out {
            opacity: 0;
        }

        .animated-svg {
            filter: drop-shadow(0 0 10px rgba(255,255,255,0.3));
        }

        .shield-group {
            transform-origin: center;
            animation: pulse 3s infinite ease-in-out;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .particles circle {
            filter: blur(1px);
        }

        .connecting-lines line {
            stroke-dasharray: 5,5;
            animation: dash 20s linear infinite;
        }

        @keyframes dash {
            to {
                stroke-dashoffset: 1000;
            }
        }

        .logo-container {
            width: 65px;  /* Increased from 55px */
            height: 65px;
            position: absolute;
            top: 15px;
            left: 15px;
            margin: 0;
            perspective: 1000px;
        }

        .animated-logo {
            width: 65px !important;  /* Increased from 55px */
            height: 65px !important;
            object-fit: contain;
            padding: 4px;
            border-radius: 50%;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }

        @keyframes logoAnimation {
            0% {
                transform: scale(1) rotate(0deg);
                filter: brightness(1);
            }
            25% {
                transform: scale(1.05) rotate(2deg);
                filter: brightness(1.2);
            }
            50% {
                transform: scale(1.02) rotate(-2deg);
                filter: brightness(1.1);
            }
            75% {
                transform: scale(1.05) rotate(1deg);
                filter: brightness(1.15);
            }
            100% {
                transform: scale(1) rotate(0deg);
                filter: brightness(1);
            }
        }

        .logo-container::before {
            content: '';
            position: absolute;
            top: -18px;
            left: -18px;
            right: -18px;
            bottom: -18px;
            background: radial-gradient(circle at center, 
                rgba(2, 71, 49, 0.2) 0%, 
                transparent 75%);
            border-radius: 25px;
            animation: pulseGlow 2s infinite;
            z-index: -1;
        }

        @keyframes pulseGlow {
            0% {
                transform: scale(1);
                opacity: 0.5;
            }
            50% {
                transform: scale(1.1);
                opacity: 0.2;
            }
            100% {
                transform: scale(1);
                opacity: 0.5;
            }
        }

        .logo-container {
            animation: float 6s infinite ease-in-out;
        }

        @keyframes float {
            0% {
                transform: translateY(0) translateX(0);
            }
            25% {
                transform: translateY(-8px) translateX(3px);
            }
            50% {
                transform: translateY(0) translateX(-3px);
            }
            75% {
                transform: translateY(8px) translateX(3px);
            }
            100% {
                transform: translateY(0) translateX(0);
            }
        }

        .logo-container:hover .animated-logo {
            animation-play-state: paused;
            transform: scale(1.1);
            transition: transform 0.3s ease;
        }

        h3, .text-muted {
            color: var(--text-color) !important;
        }

        .role-option:hover {
            border-color: var(--primary-color);
        }

        .role-option.active {
            background-color: rgba(2, 71, 49, 0.1);
            border-color: var(--primary-color);
        }

        .home-icon-link {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(255, 255, 255, 0.9);
            color: var(--primary-color);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            text-decoration: none;
            z-index: 10;
        }

        .home-icon-link i {
            font-size: 18px;
            transition: all 0.3s ease;
        }

        .home-icon-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            background: white;
        }

        .home-icon-link:hover i {
            transform: scale(1.1);
            color: var(--secondary-color);
        }

        .home-icon-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            background: white;
        }

        .home-icon-link:hover i {
            transform: scale(1.1);
            color: var(--secondary-color);
        }

        .home-icon-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            background: white;
        }

        .home-icon-link:hover i {
            transform: scale(1.1);
            color: var(--secondary-color);
        }

        @media (max-width: 768px) {
            .home-icon-link {
                top: 10px;
                right: 10px;
                width: 35px;
                height: 35px;
            }

            .home-icon-link i {
                font-size: 16px;
            }
        }

        /* Text Animation Keyframes */
        @keyframes slideInFadeUp {
            0% {
                opacity: 0;
                transform: translateY(20px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes glowPulse {
            0% {
                text-shadow: 0 0 5px rgba(255,255,255,0.1);
            }
            50% {
                text-shadow: 0 0 15px rgba(255,255,255,0.3);
            }
            100% {
                text-shadow: 0 0 5px rgba(255,255,255,0.1);
            }
        }

        /* Animated Text Classes */
        .animated-text {
            animation: 
                slideInFadeUp 0.8s ease-out forwards,
                glowPulse 3s infinite;
            opacity: 0;
        }

        .animated-text-delay-1 {
            animation: 
                slideInFadeUp 0.8s ease-out 0.2s forwards,
                glowPulse 3s infinite 0.2s;
            opacity: 0;
        }

        .animated-text-delay-2 {
            animation: 
                slideInFadeUp 0.8s ease-out 0.4s forwards,
                glowPulse 3s infinite 0.4s;
            opacity: 0;
        }

        .animated-text-delay-3 {
            animation: 
                slideInFadeUp 0.8s ease-out 0.6s forwards,
                glowPulse 3s infinite 0.6s;
            opacity: 0;
        }

        .animated-text-delay-4 {
            animation: 
                slideInFadeUp 0.8s ease-out 0.8s forwards,
                glowPulse 3s infinite 0.8s;
            opacity: 0;
        }

        /* Feature items hover effect */
        .feature-item {
            transition: all 0.3s ease;
            padding: 8px 15px;
            border-radius: 20px;
        }

        .feature-item:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }

        .feature-item i {
            transition: all 0.3s ease;
        }

        .feature-item:hover i {
            transform: scale(1.2) rotate(360deg);
        }

        /* Institution name styling */
        .institution-name {
            position: relative;
            padding-bottom: 10px;
            margin-top: 60px;
        }

        .institution-name::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 2px;
            background: rgba(255, 255, 255, 0.5);
            animation: lineGrow 0.8s ease-out 0.8s forwards;
        }

        @keyframes lineGrow {
            to {
                width: 50%;
            }
        }

        /* Welcome text special styling */
        h2.animated-text {
            font-weight: 600;
            letter-spacing: 1px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .feature-item {
                font-size: 0.85rem;
                padding: 6px 10px;
            }

            .institution-name {
                font-size: 1.1rem;
                margin-top: 45px;
            }

            h2.animated-text {
                font-size: 1.5rem;
            }
        }

        .text-center.mb-4 {
            margin-top: 20px;
            margin-bottom: 20px !important;
        }

        .text-center.mb-4 h3 {
            font-size: 1.3rem;
            margin-bottom: 5px;
        }

        .text-center.mb-4 p {
            font-size: 0.9rem;
            margin-bottom: 0;
        }

        /* Adjust input group to balance spacing */
        .input-group:last-of-type {
            margin-bottom: 10px;
        }

        /* Update form container to accommodate new spacing */
        #loginForm {
            margin-top: -10px;
        }

        /* Optional: Add a wrapper for the button */
        .button-wrapper {
            margin-top: 20px;
        }

        /* Enhanced styling for login header text */
        .login-header {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 8px;
            position: relative;
            display: inline-block;
            padding-bottom: 10px;
        }

        .login-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            border-radius: 2px;
            animation: headerLineGrow 0.8s ease-out forwards;
        }

        .login-subtext {
            font-size: 0.95rem;
            color: #666;
            font-weight: 500;
            letter-spacing: 0.3px;
            margin-top: 5px;
            opacity: 0;
            animation: fadeIn 0.5s ease-out 0.3s forwards;
        }

        @keyframes headerLineGrow {
            from {
                width: 0;
                opacity: 0;
            }
            to {
                width: 50px;
                opacity: 1;
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-5px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Hover effect for header */
        .login-header:hover::after {
            background: linear-gradient(90deg, var(--secondary-color), var(--primary-color));
            transition: background 0.3s ease;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .login-header {
                font-size: 1.3rem;
            }

            .login-subtext {
                font-size: 0.85rem;
            }

            .login-header::after {
                width: 40px;
            }
        }

        /* Add these styles */
        .forgot-password-wrapper {
            text-align: right;
            margin-bottom: 15px;
            margin-top: 5px;
            position: relative;
            z-index: 2;
        }

        .forgot-password-link {
            position: relative;
            background: transparent;
            color: var(--primary-color);
            padding: 8px 15px;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .forgot-password-link i {
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .forgot-password-link:hover {
            transform: translateY(-2px);
            color: var(--secondary-color);
        }

        .forgot-password-link:hover i {
            transform: scale(1.1) rotate(45deg);
            color: var(--secondary-color);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .forgot-password-link {
                padding: 6px 12px;
                font-size: 0.85rem;
            }
            
            .forgot-password-link i {
                font-size: 0.85rem;
            }
        }

        /* Update button wrapper margin */
        .button-wrapper {
            margin-top: 5px;
        }

        /* Add these new styles */
        .moving-objects {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none;
        }

        .moving-object {
            position: absolute;
            opacity: 0.15;
            pointer-events: none;
        }

        /* Different shapes */
        .circle {
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.8);
        }

        .square {
            background: rgba(255, 255, 255, 0.8);
        }

        .triangle {
            width: 0;
            height: 0;
            border-left: 10px solid transparent;
            border-right: 10px solid transparent;
            border-bottom: 17px solid rgba(255, 255, 255, 0.8);
        }

        .plus {
            color: rgba(255, 255, 255, 0.8);
            font-size: 20px;
            font-weight: bold;
        }

        /* Animations for different objects */
        @keyframes floatUpDown {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        @keyframes diagonal {
            0% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(20px, -20px) rotate(180deg); }
            100% { transform: translate(0, 0) rotate(360deg); }
        }

        @keyframes zigzag {
            0% { transform: translate(0, 0); }
            25% { transform: translate(15px, -15px); }
            50% { transform: translate(0, -30px); }
            75% { transform: translate(-15px, -15px); }
            100% { transform: translate(0, 0); }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.15; }
            50% { transform: scale(1.5); opacity: 0.3; }
        }

        /* Add this inside the login-image div, after the welcome-features div */
        .moving-objects {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none;
        }

        .moving-object {
            position: absolute;
            opacity: 0.15;
            pointer-events: none;
        }

        /* Different shapes */
        .circle {
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.8);
        }

        .square {
            background: rgba(255, 255, 255, 0.8);
        }

        .triangle {
            width: 0;
            height: 0;
            border-left: 10px solid transparent;
            border-right: 10px solid transparent;
            border-bottom: 17px solid rgba(255, 255, 255, 0.8);
        }

        .plus {
            color: rgba(255, 255, 255, 0.8);
            font-size: 20px;
            font-weight: bold;
        }

        /* Animations for different objects */
        @keyframes floatUpDown {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        @keyframes diagonal {
            0% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(20px, -20px) rotate(180deg); }
            100% { transform: translate(0, 0) rotate(360deg); }
        }

        @keyframes zigzag {
            0% { transform: translate(0, 0); }
            25% { transform: translate(15px, -15px); }
            50% { transform: translate(0, -30px); }
            75% { transform: translate(-15px, -15px); }
            100% { transform: translate(0, 0); }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.15; }
            50% { transform: scale(1.5); opacity: 0.3; }
        }

        /* Add this CSS for better mobile responsiveness */
        @media (max-width: 768px) {
            .moving-objects {
                display: none; /* Hide on very small screens to improve performance */
            }
            
            @media (min-width: 480px) and (max-width: 768px) {
                .moving-objects {
                    display: block; /* Show on larger mobile screens */
                }
                
                .moving-object {
                    opacity: 0.1; /* Reduce opacity on mobile */
                }
                
                /* Reduce animation distance for mobile */
                @keyframes floatUpDown {
                    0%, 100% { transform: translateY(0) rotate(0deg); }
                    50% { transform: translateY(-10px) rotate(180deg); }
                }
                
                @keyframes diagonal {
                    0% { transform: translate(0, 0) rotate(0deg); }
                    50% { transform: translate(10px, -10px) rotate(180deg); }
                    100% { transform: translate(0, 0) rotate(360deg); }
                }
                
                @keyframes zigzag {
                    0% { transform: translate(0, 0); }
                    25% { transform: translate(8px, -8px); }
                    50% { transform: translate(0, -16px); }
                    75% { transform: translate(-8px, -8px); }
                    100% { transform: translate(0, 0); }
                }
            }
        }

        /* Update only the login-image section */
        .login-image {
            flex: 0.9;
            background: rgb(0, 32, 63);
            padding: 5px 20px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        /* Update the before pseudo-element to maintain the gradient effect but with blue */
        .login-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgb(0, 32, 63), rgb(0, 45, 98));
            opacity: 0.6;
        }

        /* Optional: Adjust the moving objects' color to match the new theme */
        .moving-object.circle,
        .moving-object.square,
        .moving-object.triangle {
            background: rgba(255, 255, 255, 0.6);
        }

        .moving-object.plus {
            color: rgba(255, 255, 255, 0.6);
        }

        /* Responsive Media Queries */
        @media (max-width: 1200px) {
            .login-container {
                max-width: 90%;
            }
            .login-image {
                flex: 0.8;
                padding: 15px;
            }
            .logo-container {
                width: 60px;  /* Increased from 50px */
                height: 60px;
            }
            .login-image h2 {
                font-size: 1.2rem;
            }
            .institution-name {
                font-size: 0.95rem;
            }
            .feature-item {
                font-size: 0.85rem;
            }
            .feature-item i {
                width: 24px;
                height: 24px;
                font-size: 0.8rem;
            }
        }

        @media (max-width: 992px) {
            .login-container {
                max-width: 95%;
            }
            .login-image {
                flex: 0.7;
                padding: 12px;
                max-height: 400px;
            }
            .logo-container {
                width: 58px;  /* Increased from 48px */
                height: 58px;
            }
            .welcome-features {
                margin-top: 10px;
            }
            .login-image h2 {
                font-size: 1.3rem;
                margin-bottom: 8px;
            }
            .institution-name {
                font-size: 1rem;
                margin-bottom: 10px;
            }
            .feature-item {
                font-size: 0.9rem;
                margin: 4px 0;
            }
            .feature-item i {
                width: 26px;
                height: 26px;
                font-size: 0.85rem;
            }
        }

        @media (max-width: 768px) {
            .login-image {
                flex: 0.6;
                padding: 8px;
                max-height: 320px;
            }
            .logo-container {
                width: 55px;  /* Increased from 45px */
                height: 55px;
                top: 10px;
                left: 10px;
            }
            .login-image h2 {
                font-size: 1.2rem;
                margin-bottom: 5px;
            }
            .institution-name {
                font-size: 1.1rem;
                margin-bottom: 5px;
            }
            .welcome-features {
                margin-top: 5px;
            }
            .feature-item {
                font-size: 0.95rem;
                margin: 2px 0;
                padding: 3px 8px;
            }
            .feature-item i {
                width: 20px;
                height: 20px;
                font-size: 0.75rem;
            }
        }

        @media (max-width: 576px) {
            .login-image {
                flex: 0.5;
                padding: 5px;
                max-height: 380px;
            }
            .logo-container {
                width: 52px;  /* Increased from 42px */
                height: 52px;
                top: 8px;
                left: 8px;
            }
            .login-image h2 {
                font-size: 1.1rem;
                margin-bottom: 3px;
            }
            .institution-name {
                font-size: 1rem;
                margin-bottom: 3px;
            }
            .welcome-features {
                margin-top: 3px;
            }
            .feature-item {
                font-size: 0.9rem;
                margin: 1px 0;
                padding: 2px 6px;
            }
            .feature-item i {
                width: 18px;
                height: 18px;
                font-size: 0.7rem;
            }
        }

        @media (max-width: 320px) {
            .login-image {
                padding: 3px;
                max-height: 340px;
            }
            .logo-container {
                width: 45px;
                height: 45px;
                top: 5px;
                left: 5px;
            }
            .login-image h2 {
                font-size: 1rem;
                margin-bottom: 2px;
            }
            .institution-name {
                font-size: 0.75rem;
                margin-bottom: 2px;
            }
            .feature-item {
                font-size: 0.7rem;
                margin: 1px 0;
                padding: 2px 5px;
            }
            .feature-item i {
                width: 16px;
                height: 16px;
                font-size: 0.65rem;
            }
        }

        /* Portrait and Landscape Orientations */
        @media (orientation: landscape) and (max-height: 600px) {
            .login-image {
                max-height: 320px;
                padding: 6px;
            }
            .logo-container {
                width: 70px;
                height: 70px;
                margin-bottom: 5px;
            }
            .welcome-features {
                margin-top: 5px;
            }
            .feature-item {
                padding: 2px 5px;
                margin: 1px 0;
            }
            .login-image h2 {
                font-size: 1.5rem;
            }
            .institution-name {
                font-size: 1.1rem;
            }
            .feature-item {
                font-size: 1rem;
            }
        }

        /* High-resolution Displays */
        @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
            .animated-logo {
                image-rendering: -webkit-optimize-contrast;
            }
        }

        /* Dark Mode Support */
        @media (prefers-color-scheme: dark) {
            .form-control {
                background-color: rgba(255, 255, 255, 0.05);
                color: #fff;
            }
            .input-group-text {
                background-color: rgba(255, 255, 255, 0.1);
                border-color: rgba(255, 255, 255, 0.2);
            }
        }

        /* Print Styles */
        @media print {
            .login-container {
                box-shadow: none;
            }
            .btn-primary,
            .forgot-password-link,
            .home-icon-link {
                display: none;
            }
        }

        /* Slightly reduced logo sizes */
        @media (max-width: 1200px) {
            .login-image img {
                width: 110px;
                height: 110px;
            }
        }

        @media (max-width: 992px) {
            .login-image img {
                width: 125px;
                height: 125px;
            }
        }

        @media (max-width: 768px) {
            .login-image img {
                width: 140px;
                height: 140px;
            }
        }

        @media (max-width: 576px) {
            .login-image img {
                width: 155px;
                height: 155px;
            }
        }

        /* Landscape mode adjustment */
        @media (orientation: landscape) and (max-height: 600px) {
            .login-image img {
                width: 120px;
                height: 120px;
            }
        }

        /* Add this to ensure button is centered */
        .d-grid {
            display: flex !important;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-image">
                <a href="index.php" class="home-icon-link" onclick="return confirm('Are you sure you want to go back to the home page?');">
                    <i class="fas fa-home"></i>
                </a>
                
                <div class="logo-container">
                    <img src="includes/image/favicon.png" alt="Logo" class="animated-logo">
                </div>
                <p class="text-center institution-name animated-text-delay-1">
                    <i class="fas fa-university me-2"></i>
                    Malawi College of Health Sciences
                </p>
                <div class="welcome-features">
                    <p class="feature-item animated-text-delay-2">
                        <i class="fas fa-heartbeat"></i>
                        Excellence in Healthcare Education
                    </p>
                    <p class="feature-item animated-text-delay-3">
                        <i class="fas fa-vote-yea"></i>
                        Student Voice Matters
                    </p>
                    <p class="feature-item animated-text-delay-4">
                        <i class="fas fa-users"></i>
                        Building Future Leaders
                    </p>
                </div>
                <div class="moving-objects">
                    <!-- Circles -->
                    <div class="moving-object circle" style="top: 10%; left: 10%; width: 15px; height: 15px; animation: floatUpDown 4s infinite ease-in-out;"></div>
                    <div class="moving-object circle" style="top: 70%; right: 15%; width: 10px; height: 10px; animation: diagonal 5s infinite ease-in-out;"></div>
                    <div class="moving-object circle" style="top: 40%; left: 80%; width: 12px; height: 12px; animation: zigzag 6s infinite ease-in-out;"></div>
                    
                    <!-- Squares -->
                    <div class="moving-object square" style="bottom: 20%; left: 20%; width: 12px; height: 12px; animation: diagonal 7s infinite ease-in-out;"></div>
                    <div class="moving-object square" style="top: 30%; right: 25%; width: 8px; height: 8px; animation: floatUpDown 5s infinite ease-in-out;"></div>
                    
                    <!-- Triangles -->
                    <div class="moving-object triangle" style="top: 60%; left: 30%; animation: zigzag 8s infinite ease-in-out;"></div>
                    <div class="moving-object triangle" style="bottom: 30%; right: 40%; animation: floatUpDown 6s infinite ease-in-out;"></div>
                    
                    <!-- Plus signs -->
                    <div class="moving-object plus" style="top: 25%; left: 60%; animation: pulse 4s infinite ease-in-out;">+</div>
                    <div class="moving-object plus" style="bottom: 15%; right: 20%; animation: diagonal 7s infinite ease-in-out;">+</div>
                    
                    <!-- Additional animated elements -->
                    <div class="moving-object circle" style="top: 85%; left: 45%; width: 6px; height: 6px; animation: pulse 5s infinite ease-in-out;"></div>
                    <div class="moving-object square" style="top: 15%; right: 55%; width: 10px; height: 10px; animation: zigzag 9s infinite ease-in-out;"></div>
                    <div class="moving-object triangle" style="top: 45%; left: 75%; animation: diagonal 6s infinite ease-in-out; transform: rotate(45deg);"></div>
                </div>
            </div>
            
            <div class="login-form">
                <div class="floating-shapes">
                    <div class="shape" style="left: 10%; top: 10%; width: 50px; height: 50px;"></div>
                    <div class="shape" style="right: 15%; top: 20%; width: 40px; height: 40px;"></div>
                    <div class="shape" style="left: 20%; bottom: 20%; width: 45px; height: 45px;"></div>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger" id="alertMessage" role="alert">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <div class="text-center mb-4">
                    <h3 class="login-header">Login to Your Account</h3>
                    <p class="login-subtext">Select your role and enter credentials</p>
                </div>
                
                <div class="role-selector">
                    <div class="role-option" onclick="selectRole('student')">
                        <i class="fas fa-user-graduate"></i>
                        <div>Student</div>
                    </div>
                    <div class="role-option" onclick="selectRole('candidate')">
                        <i class="fas fa-user-tie"></i>
                        <div>Candidate</div>
                    </div>
                    <div class="role-option" onclick="selectRole('admin')">
                        <i class="fas fa-user-shield"></i>
                        <div>Admin</div>
                    </div>
                </div>

                <form method="POST" action="login.php" id="loginForm" class="px-2">
                    <div class="input-group input-group-sm mb-3">
                        <span class="input-group-text bg-light border-end-0 py-2">
                            <i class="fas fa-envelope text-muted" style="font-size: 1.1rem;"></i>
                        </span>
                        <input type="email" 
                               class="form-control form-control-sm border-start-0 py-2" 
                               name="email" 
                               required 
                               placeholder="Email">
                    </div>
                    
                    <div class="input-group input-group-sm mb-3">
                        <span class="input-group-text bg-light border-end-0 py-2">
                            <i class="fas fa-lock text-muted" style="font-size: 1.1rem;"></i>
                        </span>
                        <input type="password" 
                               class="form-control form-control-sm border-start-0 py-2" 
                               name="password" 
                               required 
                               placeholder="Password">
                    </div>
                    
                    <div class="forgot-password-wrapper">
                        <a href="forgot-password.php" class="btn btn-link btn-sm p-0 text-decoration-none small" onclick="return confirm('Are you sure you want to reset your password?');">
                            <i class="fas fa-key"></i>
                            Forgot Password?
                        </a>
                    </div>
                    
                    <div class="d-grid gap-2 mt-3">
                        <button type="submit" class="btn btn-primary py-2 fs-5 fw-semibold" id="loginButton">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function selectRole(role) {
            document.querySelectorAll('.role-option').forEach(option => {
                option.classList.remove('active');
            });
            event.currentTarget.classList.add('active');
        }

        // Form submission animation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const button = document.getElementById('loginButton');
            const spinner = button.querySelector('.spinner-border');
            button.disabled = true;
            spinner.classList.remove('d-none');
        });

        // Floating labels animation
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            input.addEventListener('blur', function() {
                if (!this.value) {
                    this.parentElement.classList.remove('focused');
                }
            });
        });

        // Auto-hide alert after 3 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alert = document.getElementById('alertMessage');
            if (alert) {
                setTimeout(function() {
                    alert.classList.add('fade-out');
                    setTimeout(function() {
                        alert.style.display = 'none';
                    }, 500); // Wait for fade animation to complete
                }, 3000); // 3 seconds delay
            }
        });
    </script>
</body>
</html>
