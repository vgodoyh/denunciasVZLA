@props([
    'code'     => '000',
    'title'    => 'Algo salió mal',
    'message'  => 'Ocurrió un problema inesperado.',
    'icon'     => 'fa-solid fa-triangle-exclamation',
    'variant'  => 'error', // error | warn | offline
    'ctaText'  => 'Volver al inicio',
    'ctaUrl'   => null,
    'ctaAction'=> null, // 'reload' overrides href with onclick
])

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $code }} · {{ $title }} · Denuncias Vzla</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root{
            --bg: #0e1015;
            --bg-radial: #171a21;
            --card: #16191f;
            --border: rgba(255,255,255,.07);
            --text: #f2f3f5;
            --muted: #8b92a0;
            --red: #e63946;
            --red-dim: #5c1f26;
            --amber: #f2a154;
            --amber-dim: #5c3d1f;
            --ok-accent: var(--red);
        }
        *{ box-sizing:border-box; }
        html,body{
            margin:0; padding:0; height:100%;
            background:
                radial-gradient(ellipse 900px 600px at 50% -10%, var(--bg-radial), var(--bg) 60%);
            color: var(--text);
            font-family:'Inter', system-ui, sans-serif;
        }
        body.error-page{
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            padding: 32px 20px;
            position:relative;
            overflow:hidden;
        }
        /* faint scanlines, broadcast/static texture */
        body.error-page::before{
            content:"";
            position:fixed; inset:0;
            background: repeating-linear-gradient(
                to bottom,
                rgba(255,255,255,.015) 0px,
                rgba(255,255,255,.015) 1px,
                transparent 1px,
                transparent 3px
            );
            pointer-events:none;
        }

        .error-card{
            width:100%;
            max-width: 460px;
            text-align:center;
            position:relative;
            z-index:1;
        }

        /* --- Signature: broadcast / signal rings --- */
        .signal{
            position:relative;
            width:132px; height:132px;
            margin: 0 auto 28px;
            display:flex; align-items:center; justify-content:center;
        }
        .signal .ring{
            position:absolute;
            border-radius:50%;
            border:1.5px solid var(--red);
            opacity:0;
            animation: pulse 2.6s cubic-bezier(.2,.6,.35,1) infinite;
        }
        .signal .ring:nth-child(1){ width:60px;  height:60px;  animation-delay:0s; }
        .signal .ring:nth-child(2){ width:96px;  height:96px;  animation-delay:.5s; }
        .signal .ring:nth-child(3){ width:132px; height:132px; animation-delay:1s; }
        @keyframes pulse{
            0%   { transform:scale(.7); opacity:0; }
            15%  { opacity:.55; }
            80%  { opacity:0; }
            100% { transform:scale(1); opacity:0; }
        }
        .signal .core{
            position:relative;
            width:56px; height:56px;
            border-radius:50%;
            background: linear-gradient(160deg, var(--red), var(--red-dim));
            display:flex; align-items:center; justify-content:center;
            box-shadow: 0 0 0 1px rgba(255,255,255,.08), 0 8px 24px -8px rgba(230,57,70,.55);
            font-size:20px;
            color:#fff;
        }

        /* Variant: warn (429/402) — amber tone, faster pulse (overload) */
        .signal.warn .ring{ border-color: var(--amber); animation-duration:1.4s; }
        .signal.warn .core{ background: linear-gradient(160deg, var(--amber), var(--amber-dim)); box-shadow:0 0 0 1px rgba(255,255,255,.08), 0 8px 24px -8px rgba(242,161,84,.55); }

        /* Variant: offline (500/503) — signal lost, flicker + static ring */
        .signal.offline .ring{ animation-duration:2.6s; }
        .signal.offline .ring:nth-child(3){ border-style:dashed; }
        .signal.offline .core{ animation: flicker 3.2s steps(12) infinite; }
        @keyframes flicker{
            0%,100%{ opacity:1; }
            8%{ opacity:.55; }
            9%{ opacity:1; }
            32%{ opacity:.7; }
            33%{ opacity:1; }
        }

        /* Variant: notfound (404) — ring never resolves, dashed outer */
        .signal.notfound .ring:nth-child(3){ border-style:dashed; animation-duration:3.4s; }

        .code{
            font-family:'JetBrains Mono', monospace;
            font-weight:700;
            font-size: 15px;
            letter-spacing:.14em;
            color: var(--red);
            margin: 0 0 10px;
        }
        .signal.warn ~ .code{ color: var(--amber); }

        h1{
            font-size: 24px;
            font-weight:700;
            line-height:1.3;
            margin: 0 0 10px;
            color: var(--text);
        }
        p.desc{
            font-size:14.5px;
            line-height:1.6;
            color: var(--muted);
            margin: 0 0 30px;
        }

        .cta{
            display:inline-flex;
            align-items:center;
            gap:8px;
            padding: 11px 22px;
            border-radius:8px;
            background: var(--red);
            color:#fff;
            text-decoration:none;
            font-weight:600;
            font-size:14px;
            border:none;
            cursor:pointer;
            transition: filter .15s ease, transform .15s ease;
        }
        .cta:hover{ filter:brightness(1.08); transform:translateY(-1px); }
        .signal.warn ~ * .cta,
        .warn-cta{ background: var(--amber); }

        .footer-brand{
            margin-top:44px;
            display:flex; flex-direction:column; align-items:center; gap:6px;
            opacity:.55;
        }
        .footer-brand span{
            font-size:11px; color: var(--muted); letter-spacing:.04em;
        }

        @media (max-width:420px){
            h1{ font-size:20px; }
            .signal{ width:110px; height:110px; }
        }
    </style>
</head>
<body class="error-page">
    <div class="error-card">
        <div class="signal {{ $variant === 'warn' ? 'warn' : ($variant === 'offline' ? 'offline' : ($variant === 'notfound' ? 'notfound' : '')) }}">
            <div class="ring"></div>
            <div class="ring"></div>
            <div class="ring"></div>
            <div class="core"><i class="{{ $icon }}"></i></div>
        </div>

        <p class="code">SEÑAL {{ $code }}</p>
        <h1>{{ $title }}</h1>
        <p class="desc">{{ $message }}</p>

        @if($ctaAction === 'reload')
            <button class="cta {{ $variant === 'warn' ? 'warn-cta' : '' }}" onclick="window.location.reload()">
                <i class="fa-solid fa-rotate-right"></i> {{ $ctaText }}
            </button>
        @else
            <a class="cta {{ $variant === 'warn' ? 'warn-cta' : '' }}" href="{{ $ctaUrl ?? url('/') }}">
                <i class="fa-solid fa-house"></i> {{ $ctaText }}
            </a>
        @endif

        <div class="footer-brand">
            <span>DENUNCIAS VZLA</span>
        </div>
    </div>
</body>
</html>