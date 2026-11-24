<!DOCTYPE html>
<html lang="es" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Ewiflor Importaciones: maquinaria y equipamiento gastronómico profesional.">
    <meta name="page-type" content="place">
    <meta name="site-kind" content="other">
    <meta name="page-kind" content="place">
    <title>@yield('title', 'Ewiflor Importaciones | Maquinaria gastronómica')</title>

    <!-- Meta tags Open Graph por defecto -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('og_title', 'Ewiflor Importaciones | Maquinaria gastronómica')">
    <meta property="og:description" content="@yield('og_description', 'Maquinaria y equipamiento para el sector gastronómico. Explora nuestro catálogo y encuentra soluciones para tu negocio.')">
    <meta property="og:url" content="@yield('og_url', request()->url())">
    <meta property="og:site_name" content="Ewiflor Importaciones">
    <meta property="og:image" content="@yield('og_image', asset('images/ewiflor-logo.webp'))">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="theme-color" content="#FFFFFF">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script type="module">
        import { motion } from 'https://esm.sh/motion';
        window.Motion = motion;
    </script>

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
            scroll-padding-top: 80px;
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
            top: 0;
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
            width: 44px;
            height: 44px;
            display: grid;
            place-items: center;
            border-radius: 13px;
            background: none;
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
            height: 100svh;
            min-height: 600px;
            display: flex;
            align-items: center;
            overflow: hidden;
            padding: 0;
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

        /* Páginas internas: la cabecera ya está en el flujo normal. */
        .section-page {
            min-height: 72vh;
            padding-top: 56px;
        }

        @media (max-width: 760px) {
            .section-page {
                padding-top: 40px;
            }
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

        /* ============================================================
           NUEVOS COMPONENTES — WhatsApp, paginación, wishlist
        ============================================================ */

        .button-whatsapp {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 50px;
            padding: 0 22px;
            border-radius: 999px;
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--text);
            font-size: 0.85rem;
            font-weight: 700;
            transition: transform 0.2s, background 0.2s, border-color 0.2s;
            text-decoration: none;
        }

        .button-whatsapp:hover {
            transform: translateY(-2px);
            background: color-mix(in srgb, var(--green) 12%, var(--surface));
            border-color: var(--green);
            color: var(--green);
        }

        .button-whatsapp:active {
            transform: scale(0.97);
        }

        /* Paginación */
        .catalog-pagination {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin: 38px 0;
            padding: 0 20px;
            flex-wrap: wrap;
        }

        .pagination-info {
            font-size: 0.82rem;
            color: var(--muted);
            font-weight: 600;
        }

        .pagination-links {
            display: flex;
            gap: 10px;
        }

        .pagination-link {
            min-height: 44px;
            padding: 0 18px;
            border-radius: 999px;
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--text);
            font-size: 0.82rem;
            font-weight: 700;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .pagination-link:hover {
            background: var(--green);
            border-color: var(--green);
            color: #fff;
        }

        /* Wishlist button en tarjetas */
        .product-wishlist {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: var(--bg);
            color: var(--muted);
            transition: all 0.2s;
            cursor: pointer;
            line-height: 1;
        }

        .product-wishlist:hover {
            border-color: var(--green);
            color: var(--green);
            background: color-mix(in srgb, var(--green) 8%, var(--surface));
        }

        .product-wishlist:active {
            transform: scale(0.92);
        }

        /* Botón WhatsApp pequeño en tarjetas de catálogo */
        .product-whatsapp {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: var(--bg);
            color: var(--muted);
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
            line-height: 1;
        }

        .product-whatsapp:hover {
            border-color: #25D366;
            color: #25D366;
            background: color-mix(in srgb, #25D366 8%, var(--surface));
        }

        .product-whatsapp:active {
            transform: scale(0.92);
        }

        .product-whatsapp .material-symbols-rounded {
            font-size: 18px;
        }

        /* Wishlist modal */
        .wishlist-modal {
            position: fixed;
            inset: 0;
            z-index: 2100;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .wishlist-modal.open {
            display: flex;
        }

        .wishlist-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(2, 6, 23, 0.6);
            backdrop-filter: blur(6px);
        }

        .wishlist-dialog {
            position: relative;
            z-index: 1;
            width: min(520px, 92%);
            max-height: 85vh;
            overflow-y: auto;
            border: 1px solid var(--border);
            border-radius: 24px;
            background: var(--surface);
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.4);
            animation: wishlistSlide 0.35s ease-out;
        }

        @keyframes wishlistSlide {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .wishlist-close {
            position: absolute;
            top: 14px;
            right: 14px;
            width: 40px;
            height: 40px;
            display: grid;
            place-items: center;
            border: 1px solid var(--border);
            border-radius: 50%;
            background: var(--bg);
            color: var(--text);
            cursor: pointer;
            transition: all 0.2s;
        }

        .wishlist-close:hover {
            background: var(--surface-2);
            border-color: var(--green);
        }

        .wishlist-dialog h3 {
            margin: 0 0 8px;
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--text);
        }

        .wishlist-hint {
            margin: 0 0 22px;
            color: var(--muted);
            font-size: 0.85rem;
        }

        .wishlist-items {
            display: flex;
            flex-direction: column;
            gap: 14px;
            padding: 0 30px;
        }

        .wishlist-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: var(--bg);
        }

        .wishlist-item-img {
            width: 72px;
            height: 72px;
            object-fit: cover;
            border-radius: 10px;
            background: var(--surface-2);
            flex-shrink: 0;
        }

        .wishlist-item-info {
            flex: 1;
            min-width: 0;
        }

        .wishlist-item-name {
            display: block;
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 4px;
        }

        .wishlist-item-marca {
            display: block;
            font-size: 0.78rem;
            color: var(--muted);
        }

        .wishlist-remove {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: transparent;
            color: var(--muted);
            cursor: pointer;
            transition: all 0.2s;
            flex-shrink: 0;
        }

        .wishlist-remove:hover {
            border-color: #dc2626;
            color: #dc2626;
        }

        .wishlist-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            padding: 22px 30px;
            border-top: 1px solid var(--border);
            background: var(--surface-2);
        }

        .wishlist-clear {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 16px;
            border: 1px solid var(--border);
            border-radius: 999px;
            background: transparent;
            color: var(--muted);
            font-size: 0.8rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
        }

        .wishlist-clear:hover {
            border-color: #dc2626;
            color: #dc2626;
        }

        .wishlist-empty {
            padding: 40px;
            text-align: center;
            color: var(--muted);
            font-size: 0.9rem;
        }

        @media (max-width: 760px) {
            .catalog-pagination {
                flex-direction: column;
                align-items: stretch;
                gap: 16px;
            }
            .pagination-links {
                justify-content: center;
            }
            .wishlist-actions {
                flex-direction: column;
            }
            .wishlist-clear,
            .wishlist-actions .button-whatsapp {
                width: 100%;
                justify-content: center;
            }
        }

        /* ============================================================
           HERO CARRUSEL
        ============================================================ */
        .hero-carousel {
            position: relative;
            width: 100%;
            height: 100svh;
            overflow: hidden;
            min-height: 600px;
        }

        .hero-track {
            display: flex;
            width: 100%;
            height: 100%;
            transition: transform 0.8s cubic-bezier(0.77, 0, 0.175, 1);
        }

        .hero-slide {
            flex: 0 0 100%;
            width: 100%;
            height: 100%;
            position: relative;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hero-slide .hero-overlay {
            position: absolute;
            inset: 0;
            background: rgba(15, 25, 32, 0.55);
        }

        .hero-slide .container-main {
            position: relative;
            z-index: 2;
            width: min(1000px, calc(100% - 48px));
            margin-inline: auto;
            padding: 20px 0;
        }

        /* Botones de navegación del carrusel */
        .hero-carousel-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 52px;
            height: 52px;
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.12);
            color: #fff;
            cursor: pointer;
            display: grid;
            place-items: center;
            font-size: 1.2rem;
            z-index: 3;
            transition: all 0.2s;
            backdrop-filter: blur(6px);
        }

        .hero-carousel-btn:hover {
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.7);
            transform: translateY(-50%) scale(1.05);
        }

        .hero-carousel-btn:active {
            transform: translateY(-50%) scale(0.95);
        }

        .hero-prev { left: 24px; }
        .hero-next { right: 24px; }

        /* Dots del carrusel */
        .hero-carousel-dots {
            position: absolute;
            bottom: 32px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            z-index: 3;
        }

        .hero-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.5);
            background: transparent;
            cursor: pointer;
            transition: all 0.3s;
        }

        .hero-dot:hover {
            border-color: var(--green);
        }

        .hero-dot.active {
            background: var(--green);
            border-color: var(--green);
            transform: scale(1.3);
        }

        /* Ajustes del hero-copy dentro del carrusel */
        .hero-carousel .hero-copy {
            animation: none !important;
            opacity: 1;
            transform: none;
        }

        .hero-carousel .hero-copy .section-label,
        .hero-carousel .hero-copy h1,
        .hero-carousel .hero-copy .hero-description,
        .hero-carousel .hero-copy .hero-actions {
            opacity: 0;
            transform: translateY(20px);
            animation: heroCopyEnter 0.7s ease-out forwards;
        }

        .hero-carousel .hero-copy h1 { animation-delay: 0.1s; }
        .hero-carousel .hero-copy .hero-description { animation-delay: 0.25s; }
        .hero-carousel .hero-copy .hero-actions { animation-delay: 0.4s; }

        @keyframes heroCopyEnter {
            to { opacity: 1; transform: translateY(0); }
        }

        /* Typewriter para letras verdes — escrita letra por letra */
        .typewriter {
            display: inline-block;
            white-space: nowrap;
            /* border-right manejado por JS para no interferir con el ancho */
        }

        /* Cursor parpadeante solo al final */
        .typewriter::after {
            content: '|';
            display: inline-block;
            width: 2px;
            margin-left: 2px;
            color: var(--green);
            animation: blink 0.75s step-end infinite;
        }

        @keyframes blink {
            50% { opacity: 0; }
        }

        /* Barra de búsqueda grande (justo debajo del hero) */
        .hero-search-section {
            background: var(--surface);
            padding: 20px 0 60px;
        }

        .search-big-wrapper {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .search-big-icon {
            color: var(--muted);
            font-size: 1.4rem;
            pointer-events: none;
        }

        .search-big-input {
            flex: 1;
            min-height: 64px;
            padding: 0 20px 0 56px;
            border: 1px solid var(--border);
            border-radius: 14px;
            outline: none;
            background: var(--bg);
            color: var(--text);
            font-size: 1.05rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .search-big-input:focus {
            border-color: var(--green);
            box-shadow: 0 0 0 3px rgba(42, 152, 50, 0.12);
        }

        .search-big-input::placeholder {
            color: var(--muted);
        }

        .search-big-btn {
            min-height: 64px;
            padding: 0 28px;
            border-radius: 14px;
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--text);
            font-size: 0.95rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s;
            text-decoration: none;
            white-space: nowrap;
        }

        .search-big-btn:hover {
            background: var(--green);
            border-color: var(--green);
            color: #fff;
        }

        .search-big-hint {
            text-align: center;
            margin-top: 14px;
            color: var(--muted);
            font-size: 0.9rem;
        }

        /* Botón WhatsApp flotante inferior derecho */
        .whatsapp-float {
            position: fixed;
            bottom: 32px;
            right: 32px;
            z-index: 1000;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            min-height: 56px;
            padding: 0 20px 0 14px;
            border-radius: 999px;
            border: 2px solid #25D366;
            background: #25D366;
            color: #fff;
            font-size: 0.95rem;
            font-weight: 800;
            text-decoration: none;
            box-shadow: 0 10px 30px rgba(37, 211, 102, 0.35);
            animation: whatsappFloat 2.5s ease-in-out infinite;
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
        }

        .whatsapp-float img {
            width: 24px;
            height: 24px;
            display: block;
        }

        .whatsapp-float:hover {
            transform: scale(1.08) translateY(-2px);
            box-shadow: 0 14px 35px rgba(37, 211, 102, 0.5);
            animation: none;
        }

        .whatsapp-float:active {
            transform: scale(0.95);
        }

        @keyframes whatsappFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }

        @media (max-width: 760px) {
            .hero-carousel-btn { width: 44px; height: 44px; font-size: 1rem; }
            .hero-prev { left: 12px; }
            .hero-next { right: 12px; }
            .hero-carousel-dots { bottom: 20px; }
            .search-big-wrapper {
                flex-direction: column;
                gap: 10px;
                align-items: stretch;
            }
            .search-big-input {
                padding-left: 20px;
                padding-right: 20px;
            }
            .search-big-icon {
                position: absolute;
                left: 16px;
                top: 50%;
                transform: translateY(-50%);
            }
            .search-big-btn {
                width: 100%;
                justify-content: center;
            }
            .whatsapp-float {
                bottom: 20px;
                right: 20px;
                padding: 0 16px;
                font-size: 0.85rem;
                min-height: 48px;
            }
        }

        /* ============================================================
           ÚLTIMOS PRODUCTOS VISTOS — Carrusel dinámico
        ============================================================ */
        .vista-reciente-section {
            background: var(--bg);
            padding: 56px 0 40px;
        }

        .recientes-carousel-track {
            display: flex;
            gap: 16px;
            overflow-x: auto;
            scrollbar-width: thin;
            padding-bottom: 20px;
            scroll-behavior: smooth;
        }

        .recientes-carousel-track::-webkit-scrollbar {
            height: 6px;
        }

        .recientes-carousel-track::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 3px;
        }

        .reciente-card {
            flex: 0 0 auto;
            width: 190px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            transition: transform 0.2s;
        }

        .reciente-card:hover {
            transform: translateY(-4px);
        }

        .reciente-card img {
            width: 100%;
            height: 130px;
            object-fit: cover;
            border-radius: 12px;
            background: var(--surface-2);
            display: block;
        }

        .recientes-nav {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 16px;
        }

        .recientes-btn {
            width: 40px;
            height: 40px;
            border: 1px solid var(--border);
            border-radius: 50%;
            background: var(--bg);
            color: var(--text);
            cursor: pointer;
            display: grid;
            place-items: center;
            font-size: 1.2rem;
            transition: all 0.2s;
        }

        .recientes-btn:hover {
            background: var(--green);
            border-color: var(--green);
            color: #fff;
        }

        .recientes-hint {
            text-align: center;
            margin-top: 16px;
            color: var(--muted);
            font-size: 0.85rem;
        }
        /* ============================================================
           PORTADA REDISEÑADA (place)
           Hero · franja de respaldo · destacados · marcas · servicio · cierre
           La cabecera vive en su propio partial (topbar + mainnav).
        ============================================================ */

        :root {
            --header-offset: 140px;
        }

        @media (max-width: 860px) {
            :root {
                --header-offset: 152px;
            }
        }

        /* Entrada suave del hero (una sola vez, al cargar) */
        @keyframes ewRise {
            from {
                opacity: 0;
                transform: translateY(22px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        [data-enter] .enter {
            opacity: 0;
            animation: ewRise .75s cubic-bezier(.22, .61, .36, 1) forwards;
            animation-delay: calc(var(--i, 0) * .12s + .12s);
        }

        /* ---------- Hero ---------- */
        .hero-home {
            position: relative;
            display: flex;
            align-items: center;
            min-height: clamp(560px, calc(100svh - var(--header-offset)), 860px);
            padding: 88px 0 96px;
            overflow: hidden;
            background: #17212a;
            color: #fff;
        }

        .hero-home-media {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

        .hero-home-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(100deg,
                    rgba(12, 20, 28, .88) 0%,
                    rgba(12, 20, 28, .64) 55%,
                    rgba(12, 20, 28, .34) 100%);
        }

        .hero-home-inner {
            position: relative;
            z-index: 2;
            max-width: 880px;
        }

        .hero-home .section-label {
            background: rgba(255, 255, 255, .10);
            border-color: rgba(255, 255, 255, .28);
            color: #fff;
        }

        .hero-home .section-label .material-symbols-rounded {
            color: var(--lime);
        }

        .hero-home h1 {
            margin: 22px 0 20px;
            font-size: clamp(2.6rem, 5.6vw, 5rem);
            line-height: 1.05;
            font-weight: 800;
            letter-spacing: -.055em;
        }

        .hero-home h1 .gradient-text {
            color: var(--lime);
        }

        .hero-home-text {
            max-width: 600px;
            color: rgba(255, 255, 255, .86);
            font-size: 1.04rem;
            line-height: 1.8;
        }

        .hero-home-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 30px;
        }

        /* Centrado con márgenes: la animación de entrada también usa
           transform, así que aquí no conviene translateX(-50%). */
        .hero-home-foot {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 20px;
            z-index: 2;
            display: flex;
            justify-content: space-between;
            gap: 16px;
            width: min(1240px, calc(100% - 48px));
            margin-inline: auto;
            color: rgba(255, 255, 255, .72);
            font-size: .74rem;
        }

        .hero-home-scroll {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        /* ---------- Franja de respaldo ---------- */
        .values-band {
            background: var(--blue-dark);
            color: #fff;
        }

        .values-band-inner {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .value-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 30px 22px;
            border-left: 1px solid rgba(255, 255, 255, .14);
        }

        .value-item:first-child {
            border-left: 0;
            padding-left: 0;
        }

        .value-item .material-symbols-rounded {
            flex-shrink: 0;
            color: var(--lime);
            font-size: 30px;
        }

        .value-item strong {
            display: block;
            font-size: .98rem;
            font-weight: 800;
            letter-spacing: -.01em;
        }

        .value-item div span {
            display: block;
            margin-top: 3px;
            color: rgba(255, 255, 255, .72);
            font-size: .8rem;
            line-height: 1.4;
        }

        /* ---------- Destacados ---------- */
        .featured-section {
            background: var(--bg);
        }

        /* ---------- Marcas ---------- */
        .brands-section {
            background: var(--surface);
        }

        .brand-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 16px;
        }

        .brand-tile {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 120px;
            padding: 20px;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            background: var(--bg);
            transition: transform .25s, border-color .25s, box-shadow .25s;
        }

        .brand-tile:hover {
            transform: translateY(-2px);
            border-color: var(--green);
            box-shadow: var(--shadow);
        }

        .brand-tile img {
            max-width: 100%;
            max-height: 56px;
            object-fit: contain;
            filter: grayscale(1);
            opacity: .75;
            transition: filter .3s, opacity .3s;
        }

        .brand-tile:hover img {
            filter: grayscale(0);
            opacity: 1;
        }

        .brand-tile-name {
            color: var(--muted);
            font-size: .8rem;
            font-weight: 800;
            letter-spacing: .06em;
            text-align: center;
            text-transform: uppercase;
        }

        /* ---------- Servicio integral ---------- */
        .service-section {
            background: var(--bg);
        }

        .service-layout {
            display: grid;
            grid-template-columns: minmax(0, .95fr) minmax(0, 1.05fr);
            gap: 56px;
            align-items: start;
        }

        .service-copy .button-primary {
            margin-top: 26px;
        }

        .service-steps {
            display: grid;
            gap: 14px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .service-step {
            display: flex;
            gap: 18px;
            padding: 24px;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            background: var(--surface);
            transition: transform .25s, border-color .25s;
        }

        .service-step:hover {
            transform: translateY(-2px);
            border-color: color-mix(in srgb, var(--green) 45%, var(--border));
        }

        .service-step-num {
            flex-shrink: 0;
            color: var(--green);
            font-size: 1.5rem;
            font-weight: 800;
            line-height: 1;
            letter-spacing: -.04em;
        }

        .service-step h3 {
            margin: 0 0 6px;
            font-size: 1.02rem;
        }

        .service-step p {
            margin: 0;
            color: var(--muted);
            font-size: .86rem;
            line-height: 1.7;
        }

        /* ---------- Cierre ---------- */
        .cta-final {
            padding: 84px 0;
            background: var(--surface);
        }

        .cta-final-box {
            max-width: 980px;
            margin: 0 auto;
            padding: 54px 28px;
            border-radius: 28px;
            background: var(--blue-dark);
            color: #fff;
            text-align: center;
        }

        .cta-final-box .section-label {
            background: rgba(255, 255, 255, .10);
            border-color: rgba(255, 255, 255, .24);
            color: #fff;
        }

        .cta-final-box h2 {
            margin: 18px 0 12px;
            font-size: clamp(1.9rem, 4vw, 2.8rem);
            letter-spacing: -.04em;
        }

        .cta-final-box p {
            max-width: 620px;
            margin: 0 auto 26px;
            color: rgba(255, 255, 255, .8);
            line-height: 1.8;
        }

        .cta-final-btn {
            background: #25D366;
            border-color: #25D366;
            color: #fff;
        }

        .cta-final-btn:hover {
            background: #1FB758;
            border-color: #1FB758;
            color: #fff;
        }

        /* ---------- Footer con sucursales y mapa ---------- */
        .footer-grid {
            grid-template-columns: 1.25fr .95fr 1.1fr;
            align-items: start;
        }

        .footer .brand-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .footer-branch {
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, .12);
        }

        .footer-branch:first-of-type {
            padding-top: 0;
        }

        .footer-branch:last-of-type {
            border-bottom: 0;
        }

        .footer-branch strong {
            display: block;
            color: #fff;
            font-size: .9rem;
        }

        .footer-branch span {
            display: block;
            margin-top: 3px;
            color: rgba(255, 255, 255, .72);
            font-size: .78rem;
            line-height: 1.5;
        }

        .footer-branch .footer-branch-dept {
            margin-top: 1px;
            color: rgba(255, 255, 255, .55);
            font-size: .7rem;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .footer-branch a {
            display: inline-block;
            margin-top: 7px;
            color: var(--lime);
            font-size: .76rem;
            font-weight: 700;
        }

        .footer-branch a:hover {
            text-decoration: underline;
        }

        .footer-map-column h4 {
            margin: 0 0 18px;
            font-size: .88rem;
        }

        .footer-map {
            width: 100%;
            height: 210px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, .16);
            border-radius: 16px;
            background: rgba(255, 255, 255, .06);
        }

        .footer-map .leaflet-container {
            font-family: 'Inter', sans-serif;
        }

        .footer-whatsapp {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 42px;
            margin-top: 20px;
            padding: 0 16px;
            border-radius: 999px;
            background: #25D366;
            color: #fff;
            font-size: .8rem;
            font-weight: 700;
            transition: transform .2s, background .2s;
        }

        .footer-whatsapp:hover {
            transform: translateY(-2px);
            background: #1FB758;
        }

        .footer-empty {
            color: rgba(255, 255, 255, .72);
            font-size: .82rem;
        }

        .footer-bottom a {
            color: rgba(255, 255, 255, .85);
            text-decoration: underline;
        }

        /* ---------- Responsive de la portada ---------- */
        @media (max-width: 1024px) {
            .brand-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 900px) {
            .service-layout {
                grid-template-columns: 1fr;
                gap: 32px;
            }

            .values-band-inner {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .value-item:nth-child(odd) {
                border-left: 0;
                padding-left: 0;
            }

            .value-item:nth-child(-n+2) {
                border-bottom: 1px solid rgba(255, 255, 255, .14);
            }
        }

        @media (max-width: 760px) {
            .hero-home {
                min-height: 560px;
                padding: 64px 0 84px;
            }

            .hero-home-foot {
                width: calc(100% - 32px);
                font-size: .68rem;
            }

            .hero-home-foot > span:first-child {
                display: none;
            }

            .hero-home-foot {
                justify-content: flex-end;
            }

            .hero-home-actions {
                flex-direction: column;
            }

            .hero-home-actions a {
                width: 100%;
            }

            .footer-map-column {
                grid-column: 1 / -1;
            }

            .footer-map {
                height: 180px;
            }

            .brand-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 520px) {
            .values-band-inner {
                grid-template-columns: 1fr;
            }

            .value-item {
                border-left: 0;
                padding: 18px 0;
                border-bottom: 1px solid rgba(255, 255, 255, .14);
            }

            .value-item:last-child {
                border-bottom: 0;
            }

            .brand-grid {
                grid-template-columns: 1fr;
            }

            .footer-grid {
                grid-template-columns: 1fr;
            }

            .footer-brand-column {
                grid-column: auto;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            [data-enter] .enter {
                opacity: 1;
                animation: none;
            }
        }
    </style>

    @stack('head')
</head>

<body>
    @php
    $esInicio = request()->routeIs('catalogo.publico');
    $enCatalogo = request()->routeIs('publico.catalogo');
    $enServicio = request()->routeIs('publico.servicio.tecnico');
    $enNosotros = request()->routeIs('publico.nosotros');
    // En el inicio los anclajes son locales (#seccion); en otras páginas vuelven al inicio.
    $base = $esInicio ? '' : route('catalogo.publico');
    @endphp
    @include('publico::partials.navbar')

    <main>
        @yield('content')
    </main>

    @include('publico::partials.footer')
    @include('publico::partials.product-modal')

    {{-- Datos del modal como JSON válido. Los flags HEX evitan que contenido
         editable desde el admin cierre este bloque <script> (XSS stored). --}}
    <script id="product-data" type="application/json">
        {!! json_encode($detalle ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}
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

            /* La búsqueda y el filtro por categoría viven en partials/products-grid. */

            /* ===================== NAV ACTIVO ===================== */
            const sectionObserver = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    if (!entry.isIntersecting) return;
                    document.querySelectorAll('.nav-link:not([data-page])').forEach(link => {
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

                        // Precios ocultos por política comercial

                        lista.appendChild(li);
                    });

                    // Configurar botón WhatsApp del modal con contexto del producto
                    const wsBtn = document.getElementById('pm-ws-btn');
                    if (wsBtn && p.nombre) {
                        const marca = p.marca || '';
                        const modelo = p.modelo || '';
                        const msg = `Hola Ewiflor, me interesa el producto: ${p.nombre} (Marca: ${marca}, Modelo: ${modelo}). ¿Podrían darme más información?`;
                        wsBtn.href = `https://wa.me/{{ env('WA_CONTACT', '59170000000') }}?text=${encodeURIComponent(msg)}`;
                    }

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