<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Q-Absen</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            width: 100%;
            max-width: 400px;
        }

        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }

        .login-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .login-header p {
            font-size: 14px;
            opacity: 0.9;
        }

        .login-body {
            padding: 40px 30px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .error-message {
            background: #fee2e2;
            border-left: 4px solid #ef4444;
            color: #991b1b;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .login-footer {
            text-align: center;
            padding: 20px 30px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            font-size: 13px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Q-Absen</h1>
            <p>Sistem Absensi Sekolah</p>
        </div>

        <div class="login-body">
            @if ($errors->any())
                <div class="error-message">
                    {{ $errors->first('username') }}
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf
                <input type="hidden" name="login_type" id="login_type" value="default">
                
                <div class="form-group" id="emailGroup" style="display: none;">
                    <label for="email">Email Guru</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="{{ old('email') }}"
                        placeholder="Masukkan email terdaftar"
                    >
                </div>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        value="{{ old('username') }}"
                        placeholder="Masukkan username"
                        required
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div style="position: relative;">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Masukkan password"
                            style="padding-right: 40px;"
                            required
                        >
                        <span id="togglePassword" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #6b7280; display: flex; align-items: center;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </span>
                    </div>
                </div>

                <script>
                    const togglePassword = document.querySelector('#togglePassword');
                    const password = document.querySelector('#password');

                    togglePassword.addEventListener('click', function (e) {
                        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                        password.setAttribute('type', type);
                        
                        // Toggle icon
                        if (type === 'text') {
                            this.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye-off"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>';
                        } else {
                            this.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
                        }
                    });
                </script>

                <button type="submit" class="btn-login">
                    Masuk
                </button>

                <div style="text-align: center; margin-top: 15px;">
                    <a href="javascript:void(0)" id="toggleLoginType" style="color: #667eea; text-decoration: none; font-size: 14px; font-weight: 500;">
                        Masuk sebagai Guru
                    </a>
                </div>
            </form>

            <script>
                const toggleLoginBtn = document.getElementById('toggleLoginType');
                const emailGroup = document.getElementById('emailGroup');
                const emailInput = document.getElementById('email');
                const loginTypeInput = document.getElementById('login_type');

                toggleLoginBtn.addEventListener('click', function() {
                    if (loginTypeInput.value === 'default') {
                        // Switch to Guru Mode
                        loginTypeInput.value = 'guru';
                        emailGroup.style.display = 'block';
                        emailInput.setAttribute('required', 'required');
                        toggleLoginBtn.textContent = 'Masuk sebagai Siswa';
                    } else {
                        // Switch to Default Mode
                        loginTypeInput.value = 'default';
                        emailGroup.style.display = 'none';
                        emailInput.removeAttribute('required');
                        emailInput.value = ''; // clear value just in case
                        toggleLoginBtn.textContent = 'Masuk sebagai Guru';
                    }
                });

                // Preserve state on validation error
                if ("{{ old('login_type') }}" === 'guru') {
                    loginTypeInput.value = 'guru';
                    emailGroup.style.display = 'block';
                    emailInput.setAttribute('required', 'required');
                    toggleLoginBtn.textContent = 'Masuk sebagai Siswa';
                }
            </script>
        </div>

        <div class="login-footer">
            © 2026 Q-Absen. All rights reserved.
        </div>
    </div>
</body>
</html>
