<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Admin - CAN 2025 Kinshasa</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1e40af;
            --primary-light: #3b82f6;
            --primary-dark: #1e3a8a;
            --success: #10b981;
            --error: #ef4444;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --accent: #f59e0b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #1e3a8a 0%, #3730a3 50%, #7c3aed 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            position: relative;
            overflow: hidden;
        }

        body::before,
        body::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            opacity: 0.1;
        }

        body::before {
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(255,255,255,0.3) 0%, transparent 70%);
            top: -100px;
            right: -100px;
            animation: float 15s ease-in-out infinite;
        }

        body::after {
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(245,158,11,0.2) 0%, transparent 70%);
            bottom: -50px;
            left: -50px;
            animation: float 20s ease-in-out infinite reverse;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(30px, -30px) scale(1.1); }
        }

        .login-container {
            width: 100%;
            max-width: 440px;
            position: relative;
            z-index: 1;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow:
                0 20px 60px rgba(0, 0, 0, 0.3),
                0 0 0 1px rgba(255, 255, 255, 0.1);
            padding: 3rem 2.5rem;
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            border-radius: 0 0 4px 4px;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .logo-container {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 20px;
            margin-bottom: 1.5rem;
            box-shadow: 0 8px 24px rgba(30, 64, 175, 0.4);
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .logo-container svg {
            width: 45px;
            height: 45px;
            fill: white;
        }

        .login-title {
            font-size: 1.875rem;
            font-weight: 800;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
            letter-spacing: -0.03em;
        }

        .login-subtitle {
            font-size: 0.95rem;
            color: var(--gray-500);
            font-weight: 500;
        }

        .badge {
            display: inline-block;
            padding: 0.375rem 0.875rem;
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: white;
            font-size: 0.75rem;
            font-weight: 700;
            border-radius: 20px;
            margin-top: 0.75rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
        }

        .alert {
            padding: 1rem 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            font-size: 0.875rem;
            font-weight: 500;
            animation: slideDown 0.4s ease-out;
            line-height: 1.5;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .alert-icon {
            flex-shrink: 0;
            font-size: 1.125rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
            transition: color 0.2s;
        }

        .form-input {
            width: 100%;
            padding: 0.875rem 1rem;
            font-size: 0.95rem;
            border: 2px solid var(--gray-200);
            border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            font-family: inherit;
            background: var(--gray-50);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 4px rgba(30, 64, 175, 0.1);
            transform: translateY(-1px);
        }

        .form-input::placeholder {
            color: var(--gray-400);
        }

        .form-checkbox {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            margin-bottom: 1.75rem;
        }

        .form-checkbox input[type="checkbox"] {
            width: 1.125rem;
            height: 1.125rem;
            border: 2px solid var(--gray-300);
            border-radius: 5px;
            cursor: pointer;
            accent-color: var(--primary);
            transition: all 0.2s;
        }

        .form-checkbox input[type="checkbox"]:checked {
            transform: scale(1.1);
        }

        .form-checkbox label {
            font-size: 0.875rem;
            color: var(--gray-600);
            cursor: pointer;
            user-select: none;
            font-weight: 500;
        }

        .btn {
            width: 100%;
            padding: 1rem 1.5rem;
            font-size: 1rem;
            font-weight: 700;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-family: inherit;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            box-shadow: 0 4px 14px rgba(30, 64, 175, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(30, 64, 175, 0.5);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-content {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .footer {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--gray-200);
            text-align: center;
        }

        .footer p {
            font-size: 0.75rem;
            color: var(--gray-400);
        }

        .security-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1rem;
            font-size: 0.75rem;
            color: var(--gray-400);
        }

        .security-badge svg {
            width: 14px;
            height: 14px;
            fill: var(--success);
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 2rem 1.75rem;
            }

            .login-title {
                font-size: 1.625rem;
            }

            .logo-container {
                width: 70px;
                height: 70px;
            }

            .logo-container svg {
                width: 40px;
                height: 40px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <!-- Header -->
            <div class="login-header">
                <div class="logo-container">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5zm0 18c-3.18 0-6-2.39-6-5.5V7.98l6-3.12 6 3.12v6.52c0 3.11-2.82 5.5-6 5.5z"/>
                    </svg>
                </div>
                <h1 class="login-title">CAN 2025 Kinshasa</h1>
                <p class="login-subtitle">Connexion au Backoffice</p>
                <span class="badge">Admin</span>
            </div>

            <!-- Alerts -->
            <div class="alert alert-error" style="display: none;" id="errorAlert">
                <span class="alert-icon">⚠</span>
                <span id="errorMessage"></span>
            </div>

            <!-- Form -->
            <form method="POST" action="/admin/login" id="loginForm">
                <!-- Email -->
                <div class="form-group">
                    <label for="email" class="form-label">Adresse email</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        class="form-input"
                        placeholder="admin@can2025.cd"
                        required
                        autofocus
                        autocomplete="username"
                    >
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password" class="form-label">Mot de passe</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        class="form-input"
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                    >
                </div>

                <!-- Remember Me -->
                <div class="form-checkbox">
                    <input id="remember" type="checkbox" name="remember">
                    <label for="remember">Se souvenir de moi</label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary">
                    <span class="btn-content">
                        <span>Se connecter</span>
                        <span>→</span>
                    </span>
                </button>
            </form>

            <!-- Footer -->
            <div class="footer">
                <div class="security-badge">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/>
                    </svg>
                    <span>Connexion sécurisée SSL</span>
                </div>
                <p style="margin-top: 1rem;">© 2024 CAN 2025 Kinshasa. Tous droits réservés.</p>
            </div>
        </div>
    </div>

    <script>
        // Animation sur focus des inputs
        document.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('focus', function() {
                const label = this.parentElement.querySelector('.form-label');
                if (label) {
                    label.style.color = 'var(--primary)';
                }
            });

            input.addEventListener('blur', function() {
                if (!this.value) {
                    const label = this.parentElement.querySelector('.form-label');
                    if (label) {
                        label.style.color = '';
                    }
                }
            });
        });

        // Ripple effect sur le bouton
        document.querySelector('.btn-primary').addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;

            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.classList.add('ripple');

            this.appendChild(ripple);

            setTimeout(() => ripple.remove(), 600);
        });

        // Auto-dismiss alerts après 5 secondes
        function autoDismissAlert(alertId) {
            setTimeout(() => {
                const alert = document.getElementById(alertId);
                if (alert && alert.style.display !== 'none') {
                    alert.style.animation = 'slideDown 0.3s ease-out reverse';
                    setTimeout(() => {
                        alert.style.display = 'none';
                    }, 300);
                }
            }, 5000);
        }

        // Vérifier les alertes au chargement
        if (document.getElementById('errorAlert').style.display !== 'none') {
            autoDismissAlert('errorAlert');
        }
    </script>
</body>
</html>
