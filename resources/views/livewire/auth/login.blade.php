<div>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap');

        :root {
            --bg-deep: #0f0f13;
            --bg-surface: #18181f;
            --bg-elevated: #1e1e28;
            --bg-input: #16161e;
            --pink-core: #e8457e;
            --pink-light: #f472a8;
            --pink-muted: rgba(232, 69, 126, 0.15);
            --pink-glow: rgba(232, 69, 126, 0.3);
            --pink-subtle: rgba(232, 69, 126, 0.06);
            --border-dim: rgba(255, 255, 255, 0.05);
            --border-pink: rgba(232, 69, 126, 0.25);
            --text-white: #f0eef5;
            --text-mid: #9896a6;
            --text-dim: #5c5a6b;
            --danger: #ef6461;
            --success: #5bcea6;
        }

        /* ── Reset for login page ── */
        body.g-sidenav-show { background: var(--bg-deep) !important; }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        .lp-scene {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            position: relative;
            overflow: hidden;
            font-family: 'Outfit', sans-serif;
        }

        /* ── Ambient background ── */
        .lp-glow-a {
            position: absolute;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(232, 69, 126, 0.06) 0%, transparent 65%);
            top: -15%;
            right: -10%;
            pointer-events: none;
        }

        .lp-glow-b {
            position: absolute;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(232, 69, 126, 0.04) 0%, transparent 65%);
            bottom: -20%;
            left: -8%;
            pointer-events: none;
        }

        /* Diagonal grid lines */
        .lp-grid {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.012) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.012) 1px, transparent 1px);
            background-size: 48px 48px;
            transform: skewY(-2deg) scale(1.1);
            pointer-events: none;
        }

        /* ── Main container ── */
        .lp-container {
            width: 100%;
            max-width: 440px;
            position: relative;
            z-index: 2;
        }

        /* Staggered entrance */
        .lp-anim-1 { animation: lpSlide 0.7s cubic-bezier(0.22, 1, 0.36, 1) 0.05s both; }
        .lp-anim-2 { animation: lpSlide 0.7s cubic-bezier(0.22, 1, 0.36, 1) 0.15s both; }
        .lp-anim-3 { animation: lpSlide 0.7s cubic-bezier(0.22, 1, 0.36, 1) 0.25s both; }

        @keyframes lpSlide {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ── Brand Strip ── */
        .lp-brand {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 32px;
        }

        .lp-logo {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, var(--pink-core), #c2255c);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 24px var(--pink-glow);
            position: relative;
        }

        /* Inner geometric cut */
        .lp-logo::before {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            border: 2.5px solid rgba(255,255,255,0.9);
            border-radius: 4px;
            transform: rotate(45deg);
        }

        .lp-brand-text h1 {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--text-white);
            letter-spacing: -0.01em;
            line-height: 1.2;
        }

        .lp-brand-text span {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.68rem;
            color: var(--text-dim);
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        /* ── Card ── */
        .lp-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-dim);
            border-radius: 16px;
            overflow: hidden;
            position: relative;
        }

        /* Top accent line */
        .lp-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 24px;
            right: 24px;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--pink-core), transparent);
            border-radius: 0 0 2px 2px;
        }

        .lp-card-inner {
            padding: 40px 32px 32px;
        }

        /* Card heading */
        .lp-heading {
            margin-bottom: 28px;
        }

        .lp-heading h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-white);
            margin-bottom: 6px;
            letter-spacing: -0.02em;
        }

        .lp-heading p {
            font-size: 0.88rem;
            color: var(--text-dim);
            font-weight: 400;
        }

        /* ── Alert ── */
        .lp-alert {
            background: rgba(91, 206, 166, 0.08);
            border: 1px solid rgba(91, 206, 166, 0.2);
            border-radius: 10px;
            padding: 10px 14px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.82rem;
            color: var(--success);
            font-weight: 500;
        }

        .lp-alert button {
            margin-left: auto;
            background: none;
            border: none;
            color: var(--success);
            cursor: pointer;
            opacity: 0.5;
            font-size: 16px;
            transition: opacity 0.2s;
        }

        .lp-alert button:hover { opacity: 1; }

        /* ── Fields ── */
        .lp-field { margin-bottom: 18px; }

        .lp-label {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 7px;
        }

        .lp-label span.dot {
            width: 5px;
            height: 5px;
            background: var(--pink-core);
            border-radius: 50%;
            flex-shrink: 0;
        }

        .lp-label span.text {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.7rem;
            font-weight: 500;
            color: var(--text-mid);
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .lp-input-wrap {
            position: relative;
        }

        .lp-input-wrap input {
            width: 100%;
            background: var(--bg-input);
            border: 1.5px solid var(--border-dim);
            border-radius: 10px;
            padding: 13px 16px;
            font-size: 0.9rem;
            font-family: 'Outfit', sans-serif;
            color: var(--text-white);
            outline: none;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .lp-input-wrap input:focus {
            border-color: var(--pink-core);
            box-shadow: 0 0 0 3px var(--pink-muted);
        }

        .lp-input-wrap input::placeholder {
            color: var(--text-dim);
            font-weight: 300;
        }

        .lp-error {
            font-size: 0.78rem;
            color: var(--danger);
            margin-top: 5px;
            font-weight: 500;
        }

        /* ── Meta row ── */
        .lp-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 22px 0 28px;
        }

        /* Custom checkbox */
        .lp-check {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }

        .lp-check input[type="checkbox"] {
            appearance: none;
            -webkit-appearance: none;
            width: 18px;
            height: 18px;
            border: 1.5px solid var(--text-dim);
            border-radius: 5px;
            background: transparent;
            cursor: pointer;
            position: relative;
            transition: all 0.2s;
            flex-shrink: 0;
        }

        .lp-check input:checked {
            background: var(--pink-core);
            border-color: var(--pink-core);
        }

        .lp-check input:checked::after {
            content: '';
            position: absolute;
            left: 5px;
            top: 1.5px;
            width: 5px;
            height: 10px;
            border: solid #fff;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .lp-check span {
            font-size: 0.84rem;
            color: var(--text-mid);
            user-select: none;
        }

        .lp-forgot {
            font-size: 0.82rem;
            color: var(--pink-light);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .lp-forgot:hover { color: var(--pink-core); }

        /* ── Button ── */
        .lp-btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 600;
            font-family: 'Outfit', sans-serif;
            color: #fff;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, var(--pink-core) 0%, #c2255c 100%);
            box-shadow: 0 4px 20px var(--pink-glow);
            letter-spacing: 0.02em;
        }

        .lp-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 32px rgba(232, 69, 126, 0.4);
        }

        .lp-btn:active { transform: translateY(0); }

        /* Shimmer effect on hover */
        .lp-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.5s ease;
        }

        .lp-btn:hover::before { left: 100%; }

        .lp-btn .lp-spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: lpSpin 0.6s linear infinite;
            margin-right: 8px;
            vertical-align: middle;
        }

        @keyframes lpSpin { to { transform: rotate(360deg); } }

        /* ── Decorative tag ── */
        .lp-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--pink-subtle);
            border: 1px solid rgba(232, 69, 126, 0.1);
            border-radius: 20px;
            padding: 5px 12px;
            margin-bottom: 20px;
        }

        .lp-tag .tag-dot {
            width: 6px;
            height: 6px;
            background: var(--pink-core);
            border-radius: 50%;
            animation: lpPulse 2s ease-in-out infinite;
        }

        @keyframes lpPulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }

        .lp-tag span {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.65rem;
            color: var(--pink-light);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-weight: 500;
        }

        /* ── Footer ── */
        .lp-footer {
            text-align: center;
            margin-top: 24px;
        }

        .lp-footer span {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.65rem;
            color: var(--text-dim);
            letter-spacing: 0.06em;
        }

        /* ── Responsive ── */
        @media (max-width: 480px) {
            .lp-card-inner { padding: 32px 20px 24px; }
            .lp-heading h2 { font-size: 1.3rem; }
        }
    </style>

    <div class="lp-scene">
        <div class="lp-glow-a"></div>
        <div class="lp-glow-b"></div>
        <div class="lp-grid"></div>

        <div class="lp-container">

            {{-- Brand --}}
            <div class="lp-brand lp-anim-1">
                <div class="lp-logo"></div>
                <div class="lp-brand-text">
                    <h1>Startup Admin</h1>
                    <span>Control Panel v2.0</span>
                </div>
            </div>

            {{-- Card --}}
            <div class="lp-card lp-anim-2">
                <div class="lp-card-inner">

                    {{-- Status tag --}}
                    <div class="lp-tag">
                        <div class="tag-dot"></div>
                        <span>Secure Login</span>
                    </div>

                    <div class="lp-heading">
                        <h2>Welcome back</h2>
                        <p>Enter your credentials to continue</p>
                    </div>

                    @if (Session::has('status'))
                        <div class="lp-alert">
                            &#10003; {{ Session::get('status') }}
                            <button onclick="this.parentElement.remove()">&times;</button>
                        </div>
                    @endif

                    <form wire:submit.prevent="login">

                        {{-- Email --}}
                        <div class="lp-field">
                            <div class="lp-label">
                                <span class="dot"></span>
                                <span class="text">Email</span>
                            </div>
                            <div class="lp-input-wrap">
                                <input
                                    wire:model.live="email"
                                    type="email"
                                    placeholder="admin@company.com"
                                    autocomplete="email"
                                >
                            </div>
                            @error('email')
                                <p class="lp-error">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Password --}}
                        <div class="lp-field">
                            <div class="lp-label">
                                <span class="dot"></span>
                                <span class="text">Password</span>
                            </div>
                            <div class="lp-input-wrap">
                                <input
                                    wire:model.live="password"
                                    type="password"
                                    placeholder="Enter your password"
                                    autocomplete="current-password"
                                >
                            </div>
                            @error('password')
                                <p class="lp-error">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Meta --}}
                        <div class="lp-meta">
                            <label class="lp-check">
                                <input type="checkbox" wire:model="remember_me">
                                <span>Remember me</span>
                            </label>
                            <a href="{{ route('password.forgot') }}" class="lp-forgot">Forgot password?</a>
                        </div>

                        {{-- Submit --}}
                        <button type="submit" class="lp-btn">
                            <span wire:loading.remove wire:target="login">Sign In</span>
                            <span wire:loading wire:target="login" style="display:inline-flex;align-items:center;justify-content:center;">
                                <span class="lp-spinner"></span>
                                Authenticating...
                            </span>
                        </button>

                    </form>
                </div>
            </div>

            {{-- Footer --}}
            <div class="lp-footer lp-anim-3">
                <span>&copy; {{ date('Y') }} &mdash; Startup Admin Panel</span>
            </div>

        </div>
    </div>
</div>