<!DOCTYPE html>
<html lang="es" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Ewiflor Importaciones: maquinaria y equipamiento gastronómico profesional.">
    <title>@yield('title', 'Ewiflor Importaciones | Maquinaria gastronómica')</title>
    <meta name="theme-color" content="#FFFFFF">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,400,0,0" rel="stylesheet">

    {{-- Aplica el tema ANTES de pintar para evitar flash --}}
    <script>
        (() => {
            const saved = localStorage.getItem('theme');
            const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.dataset.theme = saved || (systemDark ? 'dark' : 'light');
        })();
    </script>

    {{-- Estilos del proyecto. Conviven con app.css (Tailwind v4); no se pisan. --}}
    <style>
        :root {
            color-scheme: light;
            --green: #2A9832;
            --lime: #82B723;
            --blue: #16598F;
            --blue-dark: #16398A;
            --bg: #FFFFFF;
            --surface: #F3F6F8;
            --surface-2: #E9EEF2;
            --text: #252525;
            --muted: #64748B;
            --border: #E2E8F0;
            --nav-bg: rgba(255, 255, 255, .94);
            --shadow: 0 12px 38px rgba(22, 57, 138, .10);
            --radius: 22px;
        }

        [data-theme="dark"] {
            color-scheme: dark;
            --bg: #171D26;
            --surface: #222C38;
            --surface-2: #2B3745;
            --text: #FFFFFF;
            --muted: #CBD5E1;
            --border: #3C4856;
            --nav-bg: rgba(34, 44, 56, .94);
            --shadow: 0 12px 38px rgba(0, 0, 0, .28);
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
            scroll-padding-top: 110px;
        }

        body {
            margin: 0;
            background: var(--bg);
            color: var(--text);
            font-family: 'Inter', sans-serif;
            transition: background .25s, color .25s;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        button,
        input,
        textarea {
            font: inherit;
        }

        button {
            cursor: pointer;
        }

        img {
            max-width: 100%;
        }

        .material-symbols-rounded {
            font-family: 'Material Symbols Rounded';
            font-weight: normal;
            font-style: normal;
            font-size: 22px;
            line-height: 1;
            letter-spacing: normal;
            text-transform: none;
            display: inline-block;
            white-space: nowrap;
            direction: ltr;
            font-feature-settings: 'liga';
            -webkit-font-feature-settings: 'liga';
            -webkit-font-smoothing: antialiased;
        }

        .container-main {
            width: min(1240px, calc(100% - 48px));
            margin-inline: auto;
        }

        .section {
            position: relative;
            padding: 104px 0;
            background: var(--bg);
        }

        .section-content {
            position: relative;
            z-index: 2;
        }

        .section-label {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 8px 13px;
            border: 1px solid color-mix(in srgb, var(--green) 25%, var(--border));
            border-radius: 999px;
            background: color-mix(in srgb, var(--green) 8%, var(--surface));
            color: var(--green);
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .section-title {
            margin: 18px 0;
            font-size: clamp(2rem, 4vw, 3.15rem);
            font-weight: 800;
            line-height: 1.12;
            letter-spacing: -.045em;
        }

        .section-description {
            max-width: 650px;
            color: var(--muted);
            line-height: 1.8;
            font-size: .96rem;
        }

        .gradient-text {
            color: var(--green);
        }

        /* Navbar */
        .navbar {
            position: fixed;
            z-index: 1000;
            top: 18px;
            left: 0;
            width: 100%;
            border: 0;
            background: transparent;
            pointer-events: none;
            transition: top .25s;
        }

        .navbar-inner {
            min-height: 0;
            width: min(1440px, calc(100% - 48px));
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            position: relative;
        }

        .nav-island,
        .login-island {
            pointer-events: auto;
            display: flex;
            align-items: center;
            border: 1px solid var(--border);
            border-radius: 999px;
            background: var(--nav-bg);
            box-shadow: var(--shadow);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            transition: background .25s, border-color .25s, box-shadow .25s;
        }

        .nav-island {
            padding: 7px 12px 7px 17px;
            gap: 24px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }

        .brand-icon {
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            border-radius: 13px;
            background: var(--green);
            color: #fff;
            font-size: 1.05rem;
            font-weight: 800;
        }

        .brand-name {
            display: block;
            color: var(--text);
            font-size: .98rem;
            font-weight: 800;
            letter-spacing: -.035em;
        }

        .brand-subtitle {
            display: block;
            margin-top: 2px;
            color: var(--muted);
            font-size: .58rem;
            letter-spacing: .13em;
            text-transform: uppercase;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .nav-link {
            display: block;
            padding: 11px 13px;
            border-radius: 999px;
            color: var(--muted);
            font-size: .79rem;
            font-weight: 600;
            transition: background .2s, color .2s;
        }

        .nav-link:hover,
        .nav-link.active {
            background: var(--surface);
            color: var(--green);
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 0;
        }

        .login-island {
            position: absolute;
            right: 0;
            padding: 5px;
            gap: 5px;
        }

        .theme-button,
        .mobile-menu-button {
            width: 40px;
            height: 40px;
            display: grid;
            place-items: center;
            border: 0;
            border-radius: 50%;
            background: transparent;
            color: var(--text);
            transition: background .2s;
        }

        .theme-button:hover,
        .mobile-menu-button:hover {
            background: var(--surface);
        }

        .employee-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            padding: 0 17px;
            border-radius: 999px;
            background: var(--blue);
            color: #fff;
            font-size: .78rem;
            font-weight: 700;
            transition: background .2s;
        }

        .employee-link:hover {
            background: var(--blue-dark);
        }

        .mobile-menu-button {
            display: none;
        }

        .mobile-menu {
            display: none;
        }

        .navbar.scrolled .nav-island,
        .navbar.scrolled .login-island {
            background: color-mix(in srgb, var(--nav-bg) 76%, transparent);
            border-color: color-mix(in srgb, var(--border) 72%, transparent);
            box-shadow: 0 7px 25px rgba(0, 0, 0, .10);
        }

        .navbar.scrolled {
            top: 10px;
        }

        /* Hero */
        .hero {
            position: relative;
            min-height: 100svh;
            display: flex;
            align-items: center;
            overflow: hidden;
            padding: 130px 0 90px;
            background: #17212a;
            color: #fff;
        }

        .hero-bg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

        .hero-overlay {
            position: absolute;
            inset: 0;
            background: rgba(15, 25, 32, .64);
        }

        .hero .container-main {
            z-index: 2;
        }

        .hero-copy {
            max-width: 850px;
        }

        .hero .section-label {
            background: rgba(255, 255, 255, .10);
            border-color: rgba(255, 255, 255, .28);
            color: #fff;
        }

        .hero .section-label .material-symbols-rounded {
            color: var(--lime);
        }

        .hero h1 {
            max-width: 850px;
            margin: 25px 0;
            font-size: clamp(2.8rem, 6.7vw, 5.8rem);
            line-height: 1.04;
            font-weight: 800;
            letter-spacing: -.065em;
        }

        .hero h1 .gradient-text {
            color: var(--lime);
        }

        .hero-description {
            max-width: 600px;
            color: rgba(255, 255, 255, .84);
            font-size: 1.05rem;
            line-height: 1.85;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 32px;
        }

        .button-primary,
        .button-secondary {
            min-height: 50px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 0 22px;
            border-radius: 999px;
            font-size: .85rem;
            font-weight: 700;
            transition: transform .2s, background .2s, border-color .2s;
        }

        .button-primary {
            border: 1px solid var(--green);
            background: var(--green);
            color: #fff;
        }

        .button-primary:hover {
            transform: translateY(-2px);
            background: #23822B;
        }

        .button-secondary {
            border: 1px solid rgba(255, 255, 255, .55);
            background: rgba(255, 255, 255, .08);
            color: #fff;
        }

        .button-secondary:hover {
            background: rgba(255, 255, 255, .16);
        }

        .hero-bottom {
            position: absolute;
            z-index: 2;
            bottom: 27px;
            left: 0;
            width: 100%;
            color: rgba(255, 255, 255, .76);
            font-size: .72rem;
        }

        .hero-bottom-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .scroll-indicator {
            display: flex;
            align-items: center;
            gap: 9px;
        }

        /* Secciones y tarjetas */
        .categories-section,
        .advantages-section,
        .contacto-section {
            background: var(--surface);
        }

        .section-heading {
            margin-bottom: 42px;
            text-align: center;
        }

        .section-heading .section-description {
            margin-inline: auto;
        }

        .category-grid,
        .advantages-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 22px;
        }

        .category-card {
            position: relative;
            min-height: 270px;
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            background: var(--surface-2);
            transition: transform .25s, border-color .25s, box-shadow .25s;
        }

        .category-card:hover {
            transform: translateY(-5px);
            border-color: var(--green);
            box-shadow: var(--shadow);
        }

        .category-card img {
            width: 100%;
            height: 100%;
            min-height: 270px;
            display: block;
            object-fit: cover;
            transition: transform .45s;
        }

        .category-card:hover img {
            transform: scale(1.04);
        }

        .category-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            justify-content: end;
            padding: 25px;
            background: rgba(15, 25, 32, .48);
            color: #fff;
        }

        .category-overlay h3 {
            margin: 0 0 7px;
            font-size: 1.3rem;
            font-weight: 700;
        }

        .category-overlay p {
            margin: 0;
            color: #E2E8F0;
            font-size: .82rem;
        }

        /* Catálogo */
        .catalog-section {
            background: var(--bg);
        }

        .catalog-header {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 28px;
            margin-bottom: 36px;
        }

        .search-wrapper {
            width: min(100%, 390px);
        }

        .search-box {
            position: relative;
        }

        .search-box .material-symbols-rounded {
            position: absolute;
            top: 50%;
            left: 15px;
            color: var(--muted);
            transform: translateY(-50%);
            pointer-events: none;
        }

        .search-input {
            width: 100%;
            min-height: 50px;
            padding: 0 16px 0 47px;
            border: 1px solid var(--border);
            border-radius: 14px;
            outline: none;
            background: var(--surface);
            color: var(--text);
            transition: border-color .2s, box-shadow .2s;
        }

        .search-input:focus {
            border-color: var(--green);
            box-shadow: 0 0 0 3px rgba(42, 152, 50, .12);
        }

        .search-input::placeholder {
            color: var(--muted);
        }

        .search-feedback {
            min-height: 18px;
            margin: 8px 3px 0;
            color: var(--muted);
            font-size: .73rem;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 22px;
        }

        .product-card {
            min-width: 0;
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            background: var(--surface);
            cursor: pointer;
            transition: transform .25s, border-color .25s, box-shadow .25s;
        }

        .product-card:hover {
            transform: translateY(-5px);
            border-color: color-mix(in srgb, var(--green) 55%, var(--border));
            box-shadow: var(--shadow);
        }

        .product-card:focus-visible {
            outline: 3px solid var(--green);
            outline-offset: 3px;
        }

        .product-image {
            position: relative;
            overflow: hidden;
            aspect-ratio: 4/3;
            background: var(--surface-2);
        }

        .product-image img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
            transition: transform .4s;
        }

        .product-card:hover .product-image img {
            transform: scale(1.04);
        }

        .product-image-placeholder {
            position: absolute;
            inset: 0;
            display: grid;
            place-items: center;
            color: var(--muted);
        }

        .product-content {
            padding: 22px;
        }

        .product-brand {
            display: inline-block;
            margin-bottom: 9px;
            color: var(--blue);
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        [data-theme="dark"] .product-brand {
            color: #8ABBE0;
        }

        .product-title {
            margin: 0 0 10px;
            color: var(--text);
            font-size: 1.05rem;
            font-weight: 700;
            line-height: 1.4;
        }

        .product-description {
            display: -webkit-box;
            overflow: hidden;
            min-height: 46px;
            margin: 0;
            color: var(--muted);
            font-size: .82rem;
            line-height: 1.7;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
        }

        .product-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-top: 18px;
            padding-top: 15px;
            border-top: 1px solid var(--border);
            color: var(--muted);
            font-size: .72rem;
        }

        .warranty {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--muted);
        }

        .warranty .material-symbols-rounded {
            color: var(--green);
            font-size: 18px;
        }

        .product-more {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            color: var(--green);
            font-weight: 700;
            white-space: nowrap;
        }

        .promo-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            padding: 7px 11px;
            border-radius: 999px;
            background: var(--green);
            color: #fff;
            font-size: .68rem;
            font-weight: 700;
            box-shadow: 0 4px 14px rgba(0, 0, 0, .16);
        }

        .product-photos {
            position: absolute;
            right: 12px;
            bottom: 12px;
            padding: 6px 10px;
            border: 1px solid rgba(255, 255, 255, .25);
            border-radius: 999px;
            background: rgba(20, 30, 38, .72);
            color: #fff;
            font-size: .68rem;
            font-weight: 600;
            backdrop-filter: blur(8px);
        }

        .combo-chip {
            display: inline-flex;
            align-items: center;
            margin-top: 8px;
            padding: 5px 10px;
            border: 1px solid color-mix(in srgb, var(--blue) 30%, var(--border));
            border-radius: 999px;
            background: color-mix(in srgb, var(--blue) 9%, var(--surface));
            color: var(--blue);
            font-size: .67rem;
            font-weight: 700;
            letter-spacing: .06em;
        }

        .combo-componentes {
            display: block;
            margin-top: 6px;
            color: var(--muted);
            font-size: .73rem;
            line-height: 1.5;
        }

        .no-results,
        .empty-catalog {
            padding: 48px 20px;
            border: 1px dashed var(--border);
            border-radius: var(--radius);
            color: var(--muted);
            text-align: center;
        }

        .no-results .material-symbols-rounded,
        .empty-catalog .material-symbols-rounded {
            display: block;
            margin-bottom: 12px;
            color: var(--green);
            font-size: 38px;
        }

        /* Contacto / ventajas / nosotros / CTA */
        .advantages-grid {
            margin-top: 35px;
        }

        .advantage-card {
            padding: 28px;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            background: var(--bg);
            transition: transform .25s, border-color .25s;
        }

        .advantage-card:hover {
            transform: translateY(-4px);
            border-color: var(--green);
        }

        .advantage-icon {
            width: 48px;
            height: 48px;
            display: grid;
            place-items: center;
            margin-bottom: 20px;
            border-radius: 15px;
            background: color-mix(in srgb, var(--green) 10%, var(--surface));
            color: var(--green);
        }

        .advantage-icon .material-symbols-rounded {
            font-size: 25px;
        }

        .advantage-card h3 {
            margin: 0 0 10px;
            font-size: 1rem;
        }

        .advantage-card p,
        .about-copy p {
            color: var(--muted);
            font-size: .86rem;
            line-height: 1.8;
        }

        .contacto-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 42px;
            margin-top: 38px;
        }

        .contacto-info h3,
        .contacto-form h3 {
            margin: 0 0 14px;
            font-size: 1.2rem;
        }

        .contacto-info p {
            color: var(--muted);
            line-height: 1.8;
            font-size: .88rem;
        }

        .contacto-dato {
            display: flex;
            align-items: center;
            gap: 13px;
            margin: 18px 0;
            color: var(--muted);
            font-size: .85rem;
        }

        .contacto-dato>.material-symbols-rounded {
            width: 40px;
            height: 40px;
            display: grid;
            place-items: center;
            border-radius: 13px;
            background: color-mix(in srgb, var(--green) 10%, var(--surface));
            color: var(--green);
        }

        .contacto-dato strong {
            display: block;
            margin-bottom: 3px;
            color: var(--text);
        }

        .contacto-form {
            padding: 30px;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            background: var(--bg);
            box-shadow: var(--shadow);
        }

        .contacto-form .form-group {
            margin-bottom: 17px;
        }

        .contacto-form label {
            display: block;
            margin-bottom: 7px;
            color: var(--muted);
            font-size: .73rem;
            font-weight: 700;
            text-transform: none;
            letter-spacing: 0;
        }

        .contacto-form input,
        .contacto-form textarea {
            width: 100%;
            min-height: 48px;
            padding: 12px 14px;
            border: 1px solid var(--border);
            border-radius: 12px;
            outline: none;
            background: var(--surface);
            color: var(--text);
        }

        .contacto-form textarea {
            min-height: 105px;
            resize: vertical;
        }

        .contacto-form input:focus,
        .contacto-form textarea:focus {
            border-color: var(--green);
            box-shadow: 0 0 0 3px rgba(42, 152, 50, .12);
        }

        .contacto-form .form-hint {
            margin-top: 7px;
            color: var(--muted);
            font-size: .7rem;
        }

        .contacto-form button[type="submit"] {
            width: 100%;
            min-height: 49px;
            border: 0;
            border-radius: 999px;
            background: var(--green);
            color: #fff;
            font-weight: 700;
        }

        .about-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            align-items: center;
            gap: 55px;
        }

        .about-image {
            overflow: hidden;
            aspect-ratio: 5/4;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            background: var(--surface);
        }

        .about-image img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
        }

        .cta-section {
            padding: 80px 0;
            background: var(--surface);
        }

        .cta-box {
            padding: 55px 30px;
            border-radius: 28px;
            background: var(--blue-dark);
            color: #fff;
            text-align: center;
        }

        .cta-box .section-label {
            border-color: rgba(255, 255, 255, .24);
            background: rgba(255, 255, 255, .09);
            color: #fff;
        }

        .cta-box h2 {
            margin: 18px 0 14px;
            font-size: clamp(1.9rem, 4vw, 2.8rem);
            letter-spacing: -.04em;
        }

        .cta-box .gradient-text {
            color: var(--lime);
        }

        .cta-box p {
            max-width: 620px;
            margin: 0 auto 26px;
            color: rgba(255, 255, 255, .78);
            line-height: 1.8;
        }

        .footer {
            padding: 65px 0 25px;
            background: #16398A;
            color: #fff;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr 1fr;
            gap: 50px;
            padding-bottom: 42px;
        }

        .footer .brand-name {
            color: #fff;
        }

        .footer .brand-subtitle {
            color: rgba(255, 255, 255, .68);
        }

        .footer-description {
            max-width: 350px;
            margin-top: 18px;
            color: rgba(255, 255, 255, .72);
            font-size: .84rem;
            line-height: 1.8;
        }

        .footer-column h4 {
            margin: 0 0 18px;
            font-size: .88rem;
        }

        .footer-column a {
            display: block;
            width: fit-content;
            margin: 12px 0;
            color: rgba(255, 255, 255, .72);
            font-size: .82rem;
        }

        .footer-column a:hover {
            color: #fff;
        }

        .footer-bottom {
            display: flex;
            justify-content: space-between;
            gap: 18px;
            padding-top: 22px;
            border-top: 1px solid rgba(255, 255, 255, .2);
            color: rgba(255, 255, 255, .68);
            font-size: .72rem;
        }

        .reveal {
            opacity: 0;
            transform: translateY(18px);
            transition: opacity .55s ease, transform .55s ease;
        }

        .reveal.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Modal de producto */
        .pm {
            position: fixed;
            inset: 0;
            z-index: 2000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .pm.open {
            display: flex;
        }

        .pm-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(2, 6, 23, .72);
            backdrop-filter: blur(6px);
        }

        .pm-dialog {
            position: relative;
            z-index: 1;
            width: min(980px, 100%);
            max-height: calc(100vh - 40px);
            overflow-y: auto;
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(0, 1fr);
            border: 1px solid var(--border);
            border-radius: 24px;
            background: var(--surface);
            box-shadow: 0 30px 80px rgba(0, 0, 0, .4);
            outline: none;
        }

        .pm-close {
            position: absolute;
            top: 13px;
            right: 13px;
            z-index: 3;
            width: 40px;
            height: 40px;
            display: grid;
            place-items: center;
            border: 1px solid var(--border);
            border-radius: 50%;
            background: var(--bg);
            color: var(--text);
        }

        .pm-gallery {
            padding: 22px;
            background: var(--surface-2);
        }

        .pm-main {
            position: relative;
            aspect-ratio: 4/3;
            overflow: hidden;
            border-radius: 16px;
            background: var(--bg);
        }

        .pm-main img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .pm-placeholder {
            position: absolute;
            inset: 0;
            display: none;
            place-items: center;
            color: var(--muted);
        }

        .pm-placeholder .material-symbols-rounded {
            font-size: 48px;
        }

        .pm-nav {
            position: absolute;
            top: 50%;
            z-index: 2;
            width: 40px;
            height: 40px;
            display: none;
            place-items: center;
            transform: translateY(-50%);
            border: 1px solid var(--border);
            border-radius: 50%;
            background: var(--bg);
            color: var(--text);
        }

        .pm-prev {
            left: 10px;
        }

        .pm-next {
            right: 10px;
        }

        .pm-thumbs {
            display: flex;
            gap: 9px;
            margin-top: 13px;
            overflow-x: auto;
        }

        .pm-thumb {
            flex: 0 0 66px;
            width: 66px;
            height: 66px;
            padding: 0;
            overflow: hidden;
            border: 2px solid transparent;
            border-radius: 11px;
            background: var(--bg);
            opacity: .65;
        }

        .pm-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .pm-thumb.active {
            border-color: var(--green);
            opacity: 1;
        }

        .pm-info {
            padding: 34px 30px 30px;
        }

        .pm-title {
            margin: 5px 0 14px;
            font-size: 1.5rem;
            font-weight: 800;
            line-height: 1.25;
        }

        .pm-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 18px;
        }

        .pm-tag {
            padding: 6px 10px;
            border-radius: 999px;
            background: color-mix(in srgb, var(--green) 9%, var(--surface));
            color: var(--green);
            font-size: .68rem;
            font-weight: 700;
        }

        .pm-desc {
            margin: 0 0 20px;
            color: var(--muted);
            font-size: .88rem;
            line-height: 1.8;
            white-space: pre-line;
        }

        .pm-specs {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            margin: 0 0 20px;
        }

        .pm-spec {
            padding: 12px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: var(--bg);
        }

        .pm-spec dt,
        .pm-subtitle {
            color: var(--muted);
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .07em;
        }

        .pm-spec dd {
            margin: 5px 0 0;
            font-size: .82rem;
            font-weight: 700;
        }

        .pm-subtitle {
            margin: 0 0 10px;
            color: var(--green);
        }

        .pm-variants {
            display: grid;
            gap: 8px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .pm-variant {
            padding: 11px 13px;
            border: 1px solid var(--border);
            border-radius: 12px;
            font-size: .8rem;
        }

        .pm-variant strong,
        .pm-variant span {
            display: block;
        }

        .pm-variant span {
            margin-top: 3px;
            color: var(--muted);
            font-size: .74rem;
            line-height: 1.5;
        }

        .pm-variant-precio {
            display: flex;
            gap: 10px;
            margin-top: 5px;
        }

        .pm-variant-precio del {
            color: var(--muted);
        }

        .pm-variant-precio ins {
            color: var(--green);
            font-weight: 800;
            text-decoration: none;
        }

        [hidden] {
            display: none !important;
        }

        @media (max-width:1050px) {
            .navbar-inner {
                justify-content: flex-start;
            }

            .nav-island {
                gap: 12px;
            }

            .nav-links {
                gap: 0;
            }

            .nav-link {
                padding: 10px 9px;
                font-size: .73rem;
            }

            .login-island {
                right: 0;
            }

            .category-grid,
            .product-grid,
            .advantages-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width:760px) {
            .container-main {
                width: min(100% - 32px, 1240px);
            }

            .navbar {
                top: 10px;
            }

            .navbar-inner {
                width: calc(100% - 24px);
                justify-content: space-between;
            }

            .nav-island {
                width: auto;
                max-width: calc(100% - 125px);
                justify-content: space-between;
                padding: 6px 8px 6px 12px;
            }

            .nav-island .brand {
                gap: 8px;
            }

            .nav-island .nav-links {
                display: none;
            }

            .mobile-menu-button {
                display: grid;
            }

            .login-island {
                position: static;
                padding: 4px;
            }

            .theme-button {
                width: 36px;
                height: 36px;
            }

            .employee-link {
                min-height: 36px;
                padding: 0 12px;
                font-size: .7rem;
            }

            .mobile-menu {
                position: absolute;
                top: 64px;
                left: 16px;
                width: calc(100% - 32px);
                padding: 12px;
                border: 1px solid var(--border);
                border-radius: 22px;
                background: var(--nav-bg);
                box-shadow: var(--shadow);
                backdrop-filter: blur(18px);
            }

            .mobile-menu.open {
                display: grid;
                gap: 4px;
            }

            .mobile-menu a {
                padding: 12px 14px;
                border-radius: 12px;
                color: var(--muted);
                font-size: .86rem;
                font-weight: 600;
            }

            .mobile-menu a:hover {
                background: var(--surface);
                color: var(--green);
            }

            .hero {
                min-height: 100svh;
                padding: 130px 0 95px;
            }

            .hero h1 {
                font-size: clamp(2.65rem, 11vw, 4.6rem);
                letter-spacing: -.055em;
            }

            .hero-description {
                font-size: .93rem;
            }

            .hero-bottom {
                bottom: 18px;
            }

            .hero-bottom-inner>span:first-child {
                max-width: 45%;
            }

            .section {
                padding: 76px 0;
            }

            .category-grid,
            .product-grid,
            .advantages-grid,
            .contacto-grid,
            .about-grid {
                grid-template-columns: 1fr;
            }

            .catalog-header {
                align-items: stretch;
                flex-direction: column;
            }

            .search-wrapper {
                width: 100%;
            }

            .category-card,
            .category-card img {
                min-height: 235px;
            }

            .footer-grid {
                grid-template-columns: 1fr 1fr;
                gap: 30px;
            }

            .footer-brand-column {
                grid-column: 1/-1;
            }

            .footer-bottom {
                flex-direction: column;
            }

            .pm {
                align-items: flex-end;
                padding: 0;
            }

            .pm-dialog {
                grid-template-columns: 1fr;
                max-height: 92svh;
                border-radius: 22px 22px 0 0;
            }

            .pm-gallery {
                padding: 15px;
            }

            .pm-info {
                padding: 23px 19px 28px;
            }
        }

        @media (max-width:430px) {
            .brand-subtitle {
                font-size: .53rem;
            }

            .brand-name {
                font-size: .88rem;
            }

            .brand-icon {
                width: 34px;
                height: 34px;
            }

            .employee-link {
                padding: 0 10px;
                font-size: .65rem;
            }

            .hero-actions {
                flex-direction: column;
            }

            .hero-actions a {
                width: 100%;
            }

            .hero-bottom-inner>span:first-child {
                display: none;
            }

            .hero-bottom-inner {
                justify-content: flex-end;
            }

            .footer-grid {
                grid-template-columns: 1fr;
            }

            .footer-brand-column {
                grid-column: auto;
            }
        }

        @media (prefers-reduced-motion:reduce) {

            *,
            *::before,
            *::after {
                scroll-behavior: auto !important;
                animation-duration: .01ms !important;
                transition-duration: .01ms !important;
            }

            .reveal {
                opacity: 1;
                transform: none;
            }
        }
    </style>

    @stack('head')
</head>

<body>
    @include('publico::partials.navbar')

    <main>
        @yield('content')
    </main>

    @include('publico::partials.footer')
    @include('publico::partials.product-modal')

    {{-- Datos del modal como JSON válido. Antes: `{ !!json_encode(...) !! }` → texto literal roto. --}}
    <script id="product-data" type="application/json">
        {
            !!json_encode($detalle ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) !!
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const html = document.documentElement;
            const themeButton = document.getElementById('theme-toggle');
            const themeIcon = document.getElementById('theme-icon');
            const navbar = document.getElementById('site-navbar');
            const mobileButton = document.getElementById('mobile-menu-toggle');
            const mobileMenu = document.getElementById('mobile-menu');

            /* ===================== TEMA ===================== */
            function syncThemeUI(theme) {
                html.dataset.theme = theme;

                if (themeIcon) {
                    themeIcon.textContent = theme === 'dark' ? 'light_mode' : 'dark_mode';
                }

                themeButton?.setAttribute(
                    'aria-label',
                    theme === 'dark' ? 'Activar modo claro' : 'Activar modo oscuro'
                );

                document
                    .querySelector('meta[name="theme-color"]')
                    ?.setAttribute('content', theme === 'dark' ? '#171D26' : '#FFFFFF');
            }

            function setTheme(theme, persist = true) {
                syncThemeUI(theme);
                if (persist) localStorage.setItem('theme', theme);
            }

            // Sincroniza UI con el tema que el <head> ya aplicó (sin sobrescribir localStorage)
            syncThemeUI(html.dataset.theme);

            themeButton?.addEventListener('click', () => {
                const next = html.dataset.theme === 'dark' ? 'light' : 'dark';
                setTheme(next);
            });

            /* ===================== NAVBAR ===================== */
            function updateNavbar() {
                navbar?.classList.toggle('scrolled', window.scrollY > 35);
            }
            updateNavbar();
            window.addEventListener('scroll', updateNavbar, {
                passive: true
            });

            /* ===================== MENÚ MÓVIL ===================== */
            mobileButton?.addEventListener('click', () => {
                const open = mobileMenu?.classList.toggle('open');
                mobileButton.setAttribute('aria-expanded', String(open));
                mobileButton.setAttribute('aria-label', open ? 'Cerrar menú' : 'Abrir menú');
                const icon = mobileButton.querySelector('.material-symbols-rounded');
                if (icon) icon.textContent = open ? 'close' : 'menu';
            });

            mobileMenu?.querySelectorAll('a').forEach(link => link.addEventListener('click', () => {
                mobileMenu.classList.remove('open');
                mobileButton?.setAttribute('aria-expanded', 'false');
                const icon = mobileButton?.querySelector('.material-symbols-rounded');
                if (icon) icon.textContent = 'menu';
            }));

            /* ===================== BÚSQUEDA ===================== */
            const searchInput = document.getElementById('product-search');
            const productCards = document.querySelectorAll('.product-card[data-product-id]');
            const resultsText = document.getElementById('search-results');
            const noResults = document.getElementById('no-search-results');

            if (searchInput) {
                const total = productCards.length;
                if (resultsText) resultsText.textContent = `${total} producto(s) disponibles`;

                const filterProducts = () => {
                    const query = searchInput.value.trim().toLocaleLowerCase('es');
                    let visible = 0;
                    productCards.forEach(card => {
                        const content = (card.dataset.search || card.textContent).toLocaleLowerCase('es');
                        const matches = content.includes(query);
                        card.hidden = !matches;
                        if (matches) visible++;
                    });
                    if (resultsText) resultsText.textContent = query ?
                        `${visible} producto(s) encontrado(s)` :
                        `${total} producto(s) disponibles`;
                    if (noResults) noResults.style.display = visible === 0 ? 'block' : 'none';
                };
                searchInput.addEventListener('input', filterProducts);
            }

            /* ===================== NAV ACTIVO ===================== */
            const sectionObserver = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    if (!entry.isIntersecting) return;
                    document.querySelectorAll('.nav-link').forEach(link => {
                        link.classList.toggle('active', link.getAttribute('href') === `#${entry.target.id}`);
                    });
                });
            }, {
                rootMargin: '-35% 0px -55% 0px'
            });
            document.querySelectorAll('main section[id]').forEach(section => sectionObserver.observe(section));

            /* ===================== MODAL PRODUCTO ===================== */
            const dataEl = document.getElementById('product-data');
            const modal = document.getElementById('product-modal');

            if (dataEl && modal) {
                // Parse defensivo: si el JSON está roto, NO rompe el resto de la página.
                let productos = {};
                try {
                    productos = JSON.parse(dataEl.textContent?.trim() || '{}');
                } catch (e) {
                    console.warn('[product-data] JSON inválido, el modal no abrirá:', e);
                }

                const dialog = modal.querySelector('.pm-dialog');
                const mainImg = document.getElementById('pm-image');
                const placeholder = document.getElementById('pm-placeholder');
                const thumbs = document.getElementById('pm-thumbs');
                const btnPrev = document.getElementById('pm-prev');
                const btnNext = document.getElementById('pm-next');

                let galeria = [];
                let indice = 0;
                let ultimoFoco = null;

                const el = (tag, className, text) => {
                    const node = document.createElement(tag);
                    if (className) node.className = className;
                    if (text !== undefined && text !== null) node.textContent = text;
                    return node;
                };

                const mostrarImagen = (i) => {
                    if (!galeria.length) {
                        mainImg.hidden = true;
                        placeholder.style.display = 'grid';
                        return;
                    }
                    indice = (i + galeria.length) % galeria.length;
                    mainImg.hidden = false;
                    mainImg.src = galeria[indice].url;
                    mainImg.alt = galeria[indice].alt;
                    placeholder.style.display = 'none';

                    thumbs.querySelectorAll('.pm-thumb').forEach((t, n) => {
                        t.classList.toggle('active', n === indice);
                        t.setAttribute('aria-current', n === indice ? 'true' : 'false');
                    });
                };

                const llenar = (p) => {
                    document.getElementById('pm-brand').textContent = p.marca || '';
                    document.getElementById('pm-title').textContent = p.nombre;
                    document.getElementById('pm-desc').textContent =
                        p.descripcion || 'Consulta las características de este producto.';

                    const tags = document.getElementById('pm-tags');
                    tags.replaceChildren();
                    [p.categoria, p.tipo, p.es_combo ? 'COMBO' : null]
                    .filter(Boolean)
                        .forEach(t => tags.appendChild(el('span', 'pm-tag', t)));

                    const specs = document.getElementById('pm-specs');
                    specs.replaceChildren();
                    const filas = [
                        ['Marca', p.marca],
                        ['Origen', p.pais],
                        ['Modelo', p.modelo],
                        ['Garantía', p.garantia ? `${p.garantia} meses` : 'Según producto'],
                        ['Incluye', p.es_combo ? p.componentes : null],
                    ].filter(([, v]) => v);

                    filas.forEach(([k, v]) => {
                        const box = el('div', 'pm-spec');
                        box.appendChild(el('dt', null, k));
                        box.appendChild(el('dd', null, v));
                        specs.appendChild(box);
                    });

                    const wrap = document.getElementById('pm-variants-wrap');
                    const lista = document.getElementById('pm-variants');
                    lista.replaceChildren();
                    wrap.hidden = !(p.variantes?.length);

                    (p.variantes || []).forEach(v => {
                        const li = el('li', 'pm-variant');
                        li.appendChild(el('strong', null, v.color || v.sku));
                        const detalle = [v.color ? v.sku : null, v.caracteristicas].filter(Boolean).join(' · ');
                        if (detalle) li.appendChild(el('span', null, detalle));

                        if (v.promo && v.precio) {
                            const ahora = v.promo.tipo === 'USD' ?
                                Math.max(0, v.precio - v.promo.valor) :
                                Math.max(0, v.precio * (1 - (v.promo.pct ?? 0) / 100));
                            const precio = el('span', 'pm-variant-precio');
                            precio.appendChild(el('del', null, `USD ${Number(v.precio).toFixed(2)}`));
                            precio.appendChild(el('ins', null, `USD ${Number(ahora).toFixed(2)}`));
                            li.appendChild(precio);
                        }

                        lista.appendChild(li);
                    });

                    galeria = p.imagenes || [];
                    thumbs.replaceChildren();
                    const varias = galeria.length > 1;
                    btnPrev.style.display = btnNext.style.display = varias ? 'grid' : 'none';
                    thumbs.style.display = varias ? 'flex' : 'none';

                    if (varias) {
                        galeria.forEach((img, n) => {
                            const b = el('button', 'pm-thumb');
                            b.type = 'button';
                            b.setAttribute('aria-label', `Ver imagen ${n + 1}`);
                            const im = el('img');
                            im.src = img.url;
                            im.alt = '';
                            im.loading = 'lazy';
                            b.appendChild(im);
                            b.addEventListener('click', () => mostrarImagen(n));
                            thumbs.appendChild(b);
                        });
                    }

                    mostrarImagen(0);
                };

                const abrir = (id) => {
                    const p = productos[id];
                    if (!p) return;
                    ultimoFoco = document.activeElement;
                    llenar(p);
                    dialog.scrollTop = 0;
                    modal.classList.add('open');
                    modal.setAttribute('aria-hidden', 'false');
                    document.body.style.overflow = 'hidden';
                    dialog.focus();
                };

                const cerrar = () => {
                    modal.classList.remove('open');
                    modal.setAttribute('aria-hidden', 'true');
                    document.body.style.overflow = '';
                    ultimoFoco?.focus?.();
                };

                document.querySelectorAll('.product-card[data-product-id]').forEach(card => {
                    card.addEventListener('click', () => abrir(card.dataset.productId));
                    card.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            abrir(card.dataset.productId);
                        }
                    });
                });

                modal.querySelectorAll('[data-pm-close]').forEach(b => b.addEventListener('click', cerrar));
                btnPrev.addEventListener('click', () => mostrarImagen(indice - 1));
                btnNext.addEventListener('click', () => mostrarImagen(indice + 1));

                document.addEventListener('keydown', (e) => {
                    if (!modal.classList.contains('open')) return;
                    if (e.key === 'Escape') cerrar();
                    if (e.key === 'ArrowLeft' && galeria.length > 1) mostrarImagen(indice - 1);
                    if (e.key === 'ArrowRight' && galeria.length > 1) mostrarImagen(indice + 1);

                    if (e.key === 'Tab') {
                        const foco = Array.from(dialog.querySelectorAll('button, [href], [tabindex]:not([tabindex="-1"])'))
                            .filter(n => n.offsetParent !== null);
                        if (!foco.length) return;
                        const primero = foco[0];
                        const ultimo = foco[foco.length - 1];

                        if (e.shiftKey && (document.activeElement === primero || document.activeElement === dialog)) {
                            e.preventDefault();
                            ultimo.focus();
                        } else if (!e.shiftKey && document.activeElement === ultimo) {
                            e.preventDefault();
                            primero.focus();
                        }
                    }
                });
            }

            /* ===================== REVEAL ===================== */
            const revealElements = document.querySelectorAll('.reveal');
            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries, obs) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('visible');
                            obs.unobserve(entry.target);
                        }
                    });
                }, {
                    threshold: 0.12,
                    rootMargin: '0px 0px -35px 0px'
                });
                revealElements.forEach(el => observer.observe(el));
            } else {
                revealElements.forEach(el => el.classList.add('visible'));
            }
        });
    </script>

    @stack('scripts')
</body>

</html>