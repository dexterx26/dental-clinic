<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BrightSmile Dental Clinic Management System</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #042f2e 0%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .login-wrapper {
            width: 100%;
            max-width: 460px;
            background: #ffffff;
            border-radius: var(--radius-lg);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .login-header {
            background: linear-gradient(135deg, #0f766e, #0369a1);
            color: white;
            padding: 36px 32px 30px;
            text-align: center;
        }
        .login-logo {
            width: 56px;
            height: 56px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        .login-header h1 {
            font-size: 22px;
            font-weight: 700;
            color: #ffffff;
        }
        .login-header p {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.8);
            margin-top: 4px;
        }
        .login-body {
            padding: 32px;
        }
        .quick-role-picker {
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }
        .quick-role-picker h4 {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: var(--text-muted);
            margin-bottom: 10px;
            text-align: center;
        }
        .role-btn-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
        }
        .quick-btn {
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 7px 10px;
            font-size: 11.5px;
            font-weight: 600;
            color: #334155;
            cursor: pointer;
            text-align: left;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .quick-btn:hover {
            background: #e0f2fe;
            border-color: #38bdf8;
            color: #0369a1;
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-header">
            <div class="login-logo">
                <i class="fa-solid fa-tooth"></i>
            </div>
            <h1>BrightSmile Dental</h1>
            <p>Clinical Practice & Management System (Phase 1)</p>
        </div>

        <div class="login-body">
            @if(session('error'))
                <div class="alert alert-danger" style="margin-bottom: 16px;">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success" style="margin-bottom: 16px;">
                    <i class="fa-solid fa-circle-check"></i>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger" style="margin-bottom: 16px;">
                    <div>{{ $errors->first() }}</div>
                </div>
            @endif

            <form action="{{ route('login.submit') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label required" for="email">Staff Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" required placeholder="name@dentalclinic.com" value="{{ old('email', 'admin@dentalclinic.com') }}">
                </div>

                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <label class="form-label required" for="password">Password</label>
                        <span style="font-size: 12px; color: var(--text-muted);">Default: password123</span>
                    </div>
                    <input type="password" id="password" name="password" class="form-control" required value="password123">
                </div>

                <div class="form-check" style="margin-bottom: 20px;">
                    <input type="checkbox" name="remember" id="remember" checked>
                    <label for="remember" style="font-size: 13px; color: var(--text-muted); cursor: pointer;">Stay signed in for 30 days</label>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 11px; font-size: 14px;">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i>
                    <span>Secure Sign In</span>
                </button>
            </form>

            {{-- 1-Click Role Switcher for instant demo & evaluation --}}
            <div class="quick-role-picker">
                <h4>Instant Demo Sign-In (Select Role)</h4>
                <div class="role-btn-grid">
                    <button type="button" class="quick-btn" onclick="setRole('admin@dentalclinic.com')">
                        <i class="fa-solid fa-shield-halved" style="color:#0f766e;"></i> Admin
                    </button>
                    <button type="button" class="quick-btn" onclick="setRole('dentist@dentalclinic.com')">
                        <i class="fa-solid fa-user-doctor" style="color:#0284c7;"></i> Dentist (Dr. Reyes)
                    </button>
                    <button type="button" class="quick-btn" onclick="setRole('receptionist@dentalclinic.com')">
                        <i class="fa-solid fa-address-book" style="color:#10b981;"></i> Receptionist
                    </button>
                    <button type="button" class="quick-btn" onclick="setRole('cashier@dentalclinic.com')">
                        <i class="fa-solid fa-cash-register" style="color:#f59e0b;"></i> Cashier
                    </button>
                    <button type="button" class="quick-btn" style="grid-column: span 2;" onclick="setRole('assistant@dentalclinic.com')">
                        <i class="fa-solid fa-hand-holding-medical" style="color:#8b5cf6;"></i> Dental Assistant (Elena)
                    </button>
                </div>
            </div>

            <div style="margin-top: 20px; text-align: center; font-size: 11px; color: #94a3b8;">
                <i class="fa-solid fa-lock"></i> Protected by Philippine RA 10173 & HTTPS Encrypted Session
            </div>
        </div>
    </div>

    <script>
        function setRole(email) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = 'password123';
            document.querySelector('form').submit();
        }
    </script>
</body>
</html>
