<!DOCTYPE html>
<html lang="es" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Ewiflor Importaciones | Maquinaria gastronómica</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            color-scheme: dark;
            --bg: #080e1b;
            --surface: #101a2c;
            --surface-soft: #142139;
            --text: #f1f5f9;
            --muted: #94a3b8;
            --accent: #38bdf8;
            --accent-strong: #0284c7;
            --border: rgba(148, 163, 184, .15);
        }

        html.light {
            color-scheme: light;
            --bg: #f4f7fb;
            --surface: #ffffff;
            --surface-soft: #eaf0f8;
            --text: #0f172a;
            --muted: #64748b;
            --accent: #0284c7;
            --accent-strong: #0369a1;
            --border: rgba(15, 23, 42, .12);
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
            scroll-padding-top: 100px;
        }

        body {
            margin: 0;
            background: var(--bg);
            color: var(--text);
            font-family: Inter, ui-sans-serif, system-ui, sans-serif;
            transition: background .3s ease, color .3s ease;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        button,
        input {
            font: inherit;
        }

        .container-main {
            width: min(1180px, calc(100% - 40px));
            margin-inline: auto;
        }

        .section {
            position: relative;
            isolation: isolate;
            overflow: hidden;
            padding: 100px 0;
        }

        .section-content {
            position: relative;
            z-index: 2;
        }

        .section-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 13px;
            border: 1px solid rgba(56, 189, 248, .25);
            border-radius: 999px;
            background: rgba(14, 165, 233, .08);
            color: var(--accent);
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
        }

        .section-title {
            margin: 18px 0;
            font-size: clamp(2rem, 4vw, 3.1rem);
            font-weight: 800;
            line-height: 1.12;
            letter-spacing: -.04em;
        }

        .section-description {
            max-width: 620px;
            color: var(--muted);
            line-height: 1.8;
        }

        .gradient-text {
            color: transparent;
            background: linear-gradient(90deg, #38bdf8, #60a5fa, #818cf8);
            background-clip: text;
            -webkit-background-clip: text;
        }

        /* NAVBAR */

        .navbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            width: 100%;
            border-bottom: 1px solid var(--border);
            background: color-mix(in srgb, var(--bg) 88%, transparent);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        .navbar-inner {
            min-height: 76px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
        }

        .brand-icon {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border-radius: 13px;
            background: linear-gradient(135deg, #0284c7, #2563eb);
            color: white;
            font-size: 1.15rem;
            font-weight: 900;
            box-shadow: 0 8px 25px rgba(2, 132, 199, .25);
        }

        .brand-name {
            font-weight: 800;
            font-size: 1.05rem;
            letter-spacing: -.03em;
        }

        .brand-subtitle {
            display: block;
            color: var(--muted);
            font-size: .65rem;
            letter-spacing: .13em;
            text-transform: uppercase;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 30px;
        }

        .nav-link {
            position: relative;
            padding: 10px 0;
            color: var(--muted);
            font-size: .88rem;
            font-weight: 600;
            transition: color .25s ease;
        }

        .nav-link::after {
            position: absolute;
            right: 0;
            bottom: 3px;
            left: 0;
            height: 2px;
            content: "";
            background: var(--accent);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform .25s ease;
        }

        .nav-link:hover {
            color: var(--text);
        }

        .nav-link:hover::after {
            transform: scaleX(1);
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .theme-button,
        .mobile-menu-button {
            width: 40px;
            height: 40px;
            display: grid;
            place-items: center;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: var(--surface);
            color: var(--text);
            cursor: pointer;
            transition: .25s ease;
        }

        .theme-button:hover,
        .mobile-menu-button:hover {
            border-color: var(--accent);
            color: var(--accent);
        }

        .employee-link {
            padding: 11px 17px;
            border-radius: 10px;
            background: linear-gradient(135deg, #0284c7, #2563eb);
            color: white;
            font-size: .85rem;
            font-weight: 700;
            transition: transform .25s ease, box-shadow .25s ease;
        }

        .employee-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(2, 132, 199, .25);
        }

        .mobile-menu-button {
            display: none;
        }

        .mobile-menu {
            display: none;
            padding: 12px 0 20px;
            border-top: 1px solid var(--border);
        }

        .mobile-menu.open {
            display: grid;
            gap: 8px;
        }

        .mobile-menu a {
            padding: 12px;
            border-radius: 9px;
            color: var(--muted);
        }

        .mobile-menu a:hover {
            background: var(--surface);
            color: var(--text);
        }

        /* HERO */

        .hero {
            min-height: 650px;
            display: flex;
            align-items: center;
            padding: 90px 0;
            background:
                radial-gradient(ellipse at 85% 30%, rgba(14, 116, 216, .15), transparent 42%),
                radial-gradient(ellipse at 5% 85%, rgba(56, 189, 248, .08), transparent 35%);
        }

        .hero-grid {
            display: grid;
            grid-template-columns: 1.05fr .95fr;
            align-items: center;
            gap: 55px;
        }

        .hero h1 {
            max-width: 680px;
            margin: 25px 0;
            font-size: clamp(2.7rem, 5.5vw, 5rem);
            font-weight: 900;
            line-height: 1.02;
            letter-spacing: -.055em;
        }

        .hero-description {
            max-width: 540px;
            color: var(--muted);
            font-size: 1.05rem;
            line-height: 1.85;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 32px;
        }

        .button-primary,
        .button-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 48px;
            padding: 0 22px;
            border-radius: 11px;
            font-size: .9rem;
            font-weight: 700;
            transition: .25s ease;
        }

        .button-primary {
            background: linear-gradient(135deg, #0284c7, #2563eb);
            color: white;
            box-shadow: 0 10px 30px rgba(2, 132, 199, .2);
        }

        .button-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(2, 132, 199, .3);
        }

        .button-secondary {
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--text);
        }

        .button-secondary:hover {
            border-color: var(--accent);
            color: var(--accent);
        }

        .hero-visual {
            position: relative;
            min-width: 0;
            isolation: isolate;
        }

        .hero-image-frame {
            position: relative;
            z-index: 1;
            overflow: hidden;
            aspect-ratio: 5 / 4;
            border: 1px solid var(--border);
            border-radius: 25px;
            background: var(--surface);
            box-shadow: 0 30px 80px rgba(0, 0, 0, .25);
        }

        .hero-image-frame img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
        }

        .hero-glow {
            position: absolute;
            z-index: -1;
            top: 12%;
            right: -8%;
            width: 75%;
            aspect-ratio: 1;
            border-radius: 50%;
            background: rgba(14, 165, 233, .16);
            filter: blur(65px);
            pointer-events: none;
        }

        .hero-caption {
            position: absolute;
            right: 18px;
            bottom: 18px;
            left: 18px;
            z-index: 2;
            padding: 17px 20px;
            border: 1px solid rgba(255, 255, 255, .15);
            border-radius: 14px;
            background: rgba(8, 15, 30, .78);
            color: white;
            backdrop-filter: blur(12px);
        }

        .hero-caption small {
            display: block;
            margin-bottom: 5px;
            color: #7dd3fc;
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        /* CATEGORÍAS */

        .categories-section {
            background: var(--surface);
        }

        .section-heading {
            margin-bottom: 42px;
            text-align: center;
        }

        .section-heading .section-description {
            margin-inline: auto;
        }

        .category-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 22px;
        }

        .category-card {
            position: relative;
            overflow: hidden;
            min-height: 260px;
            border: 1px solid var(--border);
            border-radius: 18px;
            background: var(--surface-soft);
            transition: transform .3s ease, border-color .3s ease;
        }

        .category-card:hover {
            transform: translateY(-6px);
            border-color: rgba(56, 189, 248, .5);
        }

        .category-card img {
            width: 100%;
            height: 100%;
            min-height: 260px;
            display: block;
            object-fit: cover;
            opacity: .7;
            transition: transform .5s ease;
        }

        .category-card:hover img {
            transform: scale(1.06);
        }

        .category-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            justify-content: end;
            padding: 25px;
            background: linear-gradient(transparent 15%, rgba(3, 9, 20, .92));
            color: white;
        }

        .category-overlay h3 {
            margin: 0 0 7px;
            font-size: 1.35rem;
            font-weight: 800;
        }

        .category-overlay p {
            margin: 0;
            color: #cbd5e1;
            font-size: .85rem;
        }

        /* CATÁLOGO */

        .catalog-section {
            background: var(--bg);
        }

        .catalog-header {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 30px;
            margin-bottom: 38px;
        }

        .search-wrapper {
            width: min(100%, 390px);
        }

        .search-box {
            position: relative;
        }

        .search-box svg {
            position: absolute;
            top: 50%;
            left: 16px;
            width: 19px;
            height: 19px;
            color: var(--muted);
            transform: translateY(-50%);
            pointer-events: none;
        }

        .search-input {
            width: 100%;
            min-height: 50px;
            padding: 0 16px 0 46px;
            border: 1px solid var(--border);
            border-radius: 12px;
            outline: none;
            background: var(--surface);
            color: var(--text);
            transition: border-color .25s ease, box-shadow .25s ease;
        }

        .search-input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(56, 189, 248, .12);
        }

        .search-input::placeholder {
            color: var(--muted);
        }

        .search-feedback {
            min-height: 20px;
            margin: 8px 2px 0;
            color: var(--muted);
            font-size: .75rem;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 24px;
        }

        .product-card {
            min-width: 0;
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 18px;
            background: var(--surface);
            transition: transform .3s ease, border-color .3s ease, box-shadow .3s ease;
        }

        .product-card:hover {
            transform: translateY(-6px);
            border-color: rgba(56, 189, 248, .45);
            box-shadow: 0 18px 40px rgba(0, 0, 0, .16);
        }

        .product-image {
            position: relative;
            overflow: hidden;
            aspect-ratio: 4 / 3;
            background: var(--surface-soft);
        }

        .product-image img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
            transition: transform .45s ease;
        }

        .product-card:hover .product-image img {
            transform: scale(1.05);
        }

        .product-image-placeholder {
            position: absolute;
            inset: 0;
            display: grid;
            place-items: center;
            color: var(--muted);
            font-size: 2.5rem;
        }

        .product-content {
            padding: 22px;
        }

        .product-brand {
            display: inline-block;
            margin-bottom: 10px;
            color: var(--accent);
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .1em;
            text-transform: uppercase;
        }

        .product-title {
            margin: 0 0 10px;
            font-size: 1.1rem;
            font-weight: 800;
            line-height: 1.4;
        }

        .product-description {
            display: -webkit-box;
            overflow: hidden;
            min-height: 48px;
            margin: 0;
            color: var(--muted);
            font-size: .85rem;
            line-height: 1.7;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
        }

        .product-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid var(--border);
            color: var(--muted);
            font-size: .75rem;
        }

        .warranty {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--text);
        }

        .no-results {
            display: none;
            padding: 50px 20px;
            border: 1px dashed var(--border);
            border-radius: 18px;
            text-align: center;
            color: var(--muted);
        }

        .empty-catalog {
            padding: 55px 20px;
            border: 1px dashed var(--border);
            border-radius: 18px;
            color: var(--muted);
            text-align: center;
        }

        /* VENTAJAS */

        .advantages-section {
            background: var(--surface);
        }

        .advantages-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 22px;
            margin-top: 42px;
        }

        .advantage-card {
            padding: 28px;
            border: 1px solid var(--border);
            border-radius: 17px;
            background: var(--bg);
            transition: transform .3s ease, border-color .3s ease;
        }

        .advantage-card:hover {
            transform: translateY(-5px);
            border-color: rgba(56, 189, 248, .4);
        }

        .advantage-icon {
            width: 48px;
            height: 48px;
            display: grid;
            place-items: center;
            margin-bottom: 22px;
            border: 1px solid rgba(56, 189, 248, .2);
            border-radius: 14px;
            background: rgba(14, 165, 233, .1);
            color: var(--accent);
            font-size: 1.3rem;
        }

        .advantage-card h3 {
            margin: 0 0 12px;
            font-size: 1.05rem;
            font-weight: 800;
        }

        .advantage-card p {
            margin: 0;
            color: var(--muted);
            font-size: .87rem;
            line-height: 1.8;
        }

        /* NOSOTROS */

        .about-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            align-items: center;
            gap: 55px;
        }

        .about-image {
            overflow: hidden;
            aspect-ratio: 5 / 4;
            border: 1px solid var(--border);
            border-radius: 22px;
            background: var(--surface);
        }

        .about-image img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
        }

        .about-copy p {
            color: var(--muted);
            line-height: 1.9;
        }

        /* CTA */

        .cta-section {
            padding: 85px 0;
            background:
                radial-gradient(ellipse at 50% 0%, rgba(14, 165, 233, .2), transparent 65%),
                var(--surface);
        }

        .cta-box {
            padding: 55px 30px;
            border: 1px solid rgba(56, 189, 248, .25);
            border-radius: 24px;
            background: linear-gradient(135deg, rgba(2, 132, 199, .12), rgba(37, 99, 235, .06));
            text-align: center;
        }

        .cta-box h2 {
            margin: 0 0 15px;
            font-size: clamp(1.9rem, 4vw, 2.8rem);
            font-weight: 900;
            letter-spacing: -.04em;
        }

        .cta-box p {
            max-width: 600px;
            margin: 0 auto 28px;
            color: var(--muted);
            line-height: 1.8;
        }

        /* FOOTER */

        .footer {
            padding: 65px 0 25px;
            border-top: 1px solid var(--border);
            background: var(--bg);
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr 1fr;
            gap: 50px;
            padding-bottom: 45px;
        }

        .footer-description {
            max-width: 350px;
            margin-top: 18px;
            color: var(--muted);
            font-size: .85rem;
            line-height: 1.8;
        }

        .footer-column h4 {
            margin: 0 0 20px;
            font-size: .9rem;
            font-weight: 800;
        }

        .footer-column a {
            display: block;
            width: fit-content;
            margin: 12px 0;
            color: var(--muted);
            font-size: .85rem;
            transition: color .2s ease;
        }

        .footer-column a:hover {
            color: var(--accent);
        }

        .footer-bottom {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            padding-top: 22px;
            border-top: 1px solid var(--border);
            color: var(--muted);
            font-size: .75rem;
        }

        /* ANIMACIONES */

        .reveal {
            opacity: 0;
            transform: translateY(22px);
            transition: opacity .7s ease, transform .7s ease;
        }

        .reveal.visible {
            opacity: 1;
            transform: translateY(0);
        }

        @media (prefers-reduced-motion: reduce) {

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

        /* RESPONSIVE */

        @media (max-width: 900px) {

            .hero-grid,
            .about-grid {
                grid-template-columns: 1fr;
            }

            .hero-grid {
                gap: 45px;
            }

            .hero-visual {
                max-width: 650px;
                width: 100%;
                margin-inline: auto;
            }

            .category-grid,
            .product-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .advantages-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .catalog-header {
                align-items: start;
                flex-direction: column;
            }

            .search-wrapper {
                width: 100%;
            }
        }

        @media (max-width: 700px) {
            .container-main {
                width: min(100% - 30px, 1180px);
            }

            .navbar-inner {
                min-height: 68px;
            }

            .nav-links,
            .desktop-employee-link {
                display: none;
            }

            .mobile-menu-button {
                display: grid;
            }

            .hero {
                min-height: auto;
                padding: 75px 0;
            }

            .hero h1 {
                font-size: clamp(2.5rem, 11vw, 4rem);
            }

            .section {
                padding: 75px 0;
            }

            .category-grid,
            .product-grid,
            .advantages-grid {
                grid-template-columns: 1fr;
            }

            .category-card,
            .category-card img {
                min-height: 230px;
            }

            .footer-grid {
                grid-template-columns: 1fr 1fr;
                gap: 35px;
            }

            .footer-brand-column {
                grid-column: 1 / -1;
            }

            .footer-bottom {
                flex-direction: column;
            }
        }

        @media (max-width: 420px) {
            .brand-name {
                font-size: .92rem;
            }

            .brand-subtitle {
                font-size: .57rem;
            }

            .hero-actions {
                flex-direction: column;
            }

            .hero-actions a {
                width: 100%;
            }

            .footer-grid {
                grid-template-columns: 1fr;
            }

            .footer-brand-column {
                grid-column: auto;
            }
        }
    </style>
</head>

<body>

    <!-- NAVBAR -->
    <header class="navbar">
        <div class="container-main navbar-inner">

            <a href="#inicio" class="brand">
                <div class="brand-icon">E</div>
                <div>
                    <span class="brand-name">EWIFLOR</span>
                    <span class="brand-subtitle">Importaciones</span>
                </div>
            </a>

            <nav class="nav-links" aria-label="Navegación principal">
                <a href="#inicio" class="nav-link">Inicio</a>
                <a href="#categorias" class="nav-link">Categorías</a>
                <a href="#catalogo" class="nav-link">Catálogo</a>
                <a href="#nosotros" class="nav-link">Nosotros</a>
            </nav>

            <div class="nav-actions">
                <button
                    type="button"
                    id="theme-toggle"
                    class="theme-button"
                    aria-label="Cambiar tema"
                    title="Cambiar tema">
                    <span id="theme-icon">☀</span>
                </button>

                <a href="{{ route('login') }}" class="employee-link desktop-employee-link">
                    Acceso empleados
                </a>

                <button
                    type="button"
                    id="mobile-menu-toggle"
                    class="mobile-menu-button"
                    aria-label="Abrir menú"
                    aria-expanded="false">
                    ☰
                </button>
            </div>
        </div>

        <div id="mobile-menu" class="mobile-menu container-main">
            <a href="#inicio">Inicio</a>
            <a href="#categorias">Categorías</a>
            <a href="#catalogo">Catálogo</a>
            <a href="#nosotros">Nosotros</a>
            <a href="{{ route('login') }}">Acceso empleados</a>
        </div>
    </header>


    <main>

        <!-- HERO -->
        <section id="inicio" class="section hero">
            <div class="container-main section-content hero-grid">

                <div class="reveal">
                    <span class="section-label">
                        <span>✦</span>
                        Equipamiento gastronómico
                    </span>

                    <h1>
                        Equipamos tus ideas.
                        <span class="gradient-text">Impulsamos tu negocio.</span>
                    </h1>

                    <p class="hero-description">
                        Descubre soluciones y equipamiento para el sector gastronómico.
                        Explora nuestro catálogo de maquinaria y encuentra productos
                        pensados para acompañar el crecimiento de tu negocio.
                    </p>

                    <div class="hero-actions">
                        <a href="#catalogo" class="button-primary">
                            Explorar catálogo <span>→</span>
                        </a>

                        <a href="#nosotros" class="button-secondary">
                            Conócenos
                        </a>
                    </div>
                </div>

                <div class="hero-visual reveal">
                    <div class="hero-glow"></div>

                    <div class="hero-image-frame">
                        <img
                            src="/images/catalog/hero-maquinaria.jpg"
                            alt="Referencia de maquinaria gastronómica"
                            fetchpriority="high"
                            decoding="async"
                            onerror="this.style.display='none'">

                        <div class="hero-caption">
                            <small>Equipamiento profesional</small>
                            <strong>Soluciones para el sector gastronómico</strong>
                        </div>
                    </div>
                </div>

            </div>
        </section>


        <!-- CATEGORÍAS DEMOSTRATIVAS -->
        <section id="categorias" class="section categories-section">
            <div class="container-main section-content">

                <div class="section-heading reveal">
                    <span class="section-label">Explora nuestras referencias</span>

                    <h2 class="section-title">
                        Equipamiento para <span class="gradient-text">cada necesidad</span>
                    </h2>

                    <p class="section-description">
                        Una muestra visual de las distintas áreas de equipamiento
                        gastronómico que forman parte de nuestro catálogo.
                    </p>
                </div>

                <div class="category-grid">

                    <article class="category-card reveal">
                        <img
                            src="/images/catalog/categoria-hornos.jpg"
                            alt="Referencia de hornos industriales"
                            loading="lazy"
                            decoding="async"
                            onerror="this.style.display='none'">
                        <div class="category-overlay">
                            <h3>Hornos y cocción</h3>
                            <p>Equipamiento para preparación y cocción.</p>
                        </div>
                    </article>

                    <article class="category-card reveal">
                        <img
                            src="/images/catalog/categoria-refrigeracion.jpg"
                            alt="Referencia de equipos de refrigeración"
                            loading="lazy"
                            decoding="async"
                            onerror="this.style.display='none'">
                        <div class="category-overlay">
                            <h3>Refrigeración</h3>
                            <p>Soluciones para conservación de alimentos.</p>
                        </div>
                    </article>

                    <article class="category-card reveal">
                        <img
                            src="/images/catalog/categoria-panaderia.jpg"
                            alt="Referencia de maquinaria para panadería"
                            loading="lazy"
                            decoding="async"
                            onerror="this.style.display='none'">
                        <div class="category-overlay">
                            <h3>Panadería y pastelería</h3>
                            <p>Maquinaria para procesos de producción.</p>
                        </div>
                    </article>

                </div>
            </div>
        </section>


        <!-- CATÁLOGO CONECTADO AL BACKEND -->
        <section id="catalogo" class="section catalog-section">
            <div class="container-main section-content">

                <div class="catalog-header">
                    <div class="reveal">
                        <span class="section-label">Nuestros productos</span>

                        <h2 class="section-title">
                            Catálogo <span class="gradient-text">disponible</span>
                        </h2>

                        <p class="section-description">
                            Explora nuestra selección de maquinaria y equipamiento.
                        </p>
                    </div>

                    <div class="search-wrapper reveal">
                        <div class="search-box">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <circle cx="11" cy="11" r="7"></circle>
                                <path d="m20 20-4-4"></path>
                            </svg>

                            <input
                                type="search"
                                id="product-search"
                                class="search-input"
                                placeholder="Buscar producto o marca..."
                                autocomplete="off"
                                aria-label="Buscar productos">
                        </div>

                        <p id="search-results" class="search-feedback" aria-live="polite">
                            Explora los productos disponibles.
                        </p>
                    </div>
                </div>

                @php
                $hayProductos = isset($productos) && $productos->isNotEmpty();
                @endphp

                @if($hayProductos)

                <div id="product-grid" class="product-grid">

                    @foreach($productos as $producto)

                    <article class="product-card reveal">

                        <div class="product-image">

                            @if($producto->imagenPrincipal)
                            <img
                                src="{{ $producto->imagenPrincipal }}"
                                alt="{{ $producto->nombre }}"
                                loading="lazy"
                                decoding="async"
                                onerror="this.style.display='none'">
                            @else
                            <div class="product-image-placeholder" aria-hidden="true">
                                ⚙
                            </div>
                            @endif

                        </div>

                        <div class="product-content">

                            @if($producto->marca)
                            <span class="product-brand">
                                {{ $producto->marca->nombre }}
                            </span>
                            @endif

                            <h3 class="product-title">
                                {{ $producto->nombre }}
                            </h3>

                            <p class="product-description">
                                {{ $producto->descripcion ?: 'Consulta las características de este producto.' }}
                            </p>

                            <div class="product-meta">

                                <span class="warranty">
                                    <span>✓</span>
                                    @if($producto->garantia_meses)
                                    {{ $producto->garantia_meses }} meses de garantía
                                    @else
                                    Garantía según producto
                                    @endif
                                </span>

                                <span>EWIFLOR</span>

                            </div>

                        </div>
                    </article>

                    @endforeach

                </div>

                <div id="no-search-results" class="no-results">
                    <div style="font-size: 2rem; margin-bottom: 12px;">⌕</div>
                    <h3 style="font-weight: 800; color: var(--text); margin-bottom: 8px;">
                        No encontramos resultados
                    </h3>
                    <p>Prueba con otro nombre, marca o término de búsqueda.</p>
                </div>

                @else

                <div class="empty-catalog">
                    <div style="font-size: 2.5rem; margin-bottom: 15px;">⚙</div>

                    <h3 style="font-size: 1.2rem; font-weight: 800; color: var(--text);">
                        Próximamente nuestro catálogo
                    </h3>

                    <p style="margin-top: 10px;">
                        Estamos preparando la información de nuestros productos.
                    </p>
                </div>

                @endif

            </div>
        </section>


        <!-- VENTAJAS DEMOSTRATIVAS -->
        <section class="section advantages-section">
            <div class="container-main section-content">

                <div class="section-heading reveal">
                    <span class="section-label">Nuestra propuesta</span>

                    <h2 class="section-title">
                        Más que maquinaria, <span class="gradient-text">soluciones</span>
                    </h2>

                    <p class="section-description">
                        Una presentación de los aspectos que buscamos destacar
                        en nuestra propuesta comercial.
                    </p>
                </div>

                <div class="advantages-grid">

                    <article class="advantage-card reveal">
                        <div class="advantage-icon">⚙</div>
                        <h3>Equipamiento especializado</h3>
                        <p>
                            Referencias de maquinaria orientadas a diferentes
                            necesidades del sector gastronómico.
                        </p>
                    </article>

                    <article class="advantage-card reveal">
                        <div class="advantage-icon">◇</div>
                        <h3>Variedad de productos</h3>
                        <p>
                            Una selección de equipos para distintos procesos
                            de preparación, producción y conservación.
                        </p>
                    </article>

                    <article class="advantage-card reveal">
                        <div class="advantage-icon">✦</div>
                        <h3>Atención al cliente</h3>
                        <p>
                            Un espacio pensado para facilitar la exploración
                            de productos y el acercamiento comercial.
                        </p>
                    </article>

                </div>
            </div>
        </section>


        <!-- NOSOTROS: CONTENIDO DEMOSTRATIVO -->
        <section id="nosotros" class="section">
            <div class="container-main section-content about-grid">

                <div class="about-image reveal">
                    <img
                        src="/images/catalog/nosotros.jpg"
                        alt="Referencia de equipamiento gastronómico"
                        loading="lazy"
                        decoding="async"
                        onerror="this.style.display='none'">
                </div>

                <div class="about-copy reveal">
                    <span class="section-label">Sobre Ewiflor</span>

                    <h2 class="section-title">
                        Tecnología y equipamiento para el sector gastronómico
                    </h2>

                    <p>
                        Ewiflor Importaciones es una propuesta comercial enfocada
                        en la importación y comercialización de maquinaria y
                        equipamiento para el sector gastronómico.
                    </p>

                    <p>
                        Esta sección está destinada a presentar información
                        institucional de la empresa. El contenido podrá ampliarse
                        posteriormente con su historia, trayectoria y demás
                        información corporativa verificada.
                    </p>

                    <a href="#catalogo" class="button-primary" style="margin-top: 15px;">
                        Ver productos <span>→</span>
                    </a>
                </div>

            </div>
        </section>


        <!-- CTA DEMOSTRATIVO -->
        <section class="cta-section">
            <div class="container-main section-content">

                <div class="cta-box reveal">
                    <span class="section-label">Descubre nuestro catálogo</span>

                    <h2>
                        Encuentra el equipo que <span class="gradient-text">buscas</span>
                    </h2>

                    <p>
                        Explora las referencias disponibles y conoce las
                        diferentes alternativas de equipamiento gastronómico.
                    </p>

                    <a href="#catalogo" class="button-primary">
                        Explorar productos <span>→</span>
                    </a>
                </div>

            </div>
        </section>

    </main>


    <!-- FOOTER -->
    <footer class="footer">
        <div class="container-main">

            <div class="footer-grid">

                <div class="footer-brand-column">
                    <a href="#inicio" class="brand">
                        <div class="brand-icon">E</div>
                        <div>
                            <span class="brand-name">EWIFLOR</span>
                            <span class="brand-subtitle">Importaciones</span>
                        </div>
                    </a>

                    <p class="footer-description">
                        Maquinaria y equipamiento para el sector gastronómico.
                        Conoce nuestras referencias y explora nuestro catálogo.
                    </p>
                </div>

                <div class="footer-column">
                    <h4>Navegación</h4>
                    <a href="#inicio">Inicio</a>
                    <a href="#categorias">Categorías</a>
                    <a href="#catalogo">Catálogo</a>
                    <a href="#nosotros">Nosotros</a>
                </div>

                <div class="footer-column">
                    <h4>Acceso</h4>
                    <a href="{{ route('login') }}">Acceso empleados</a>
                    <a href="#catalogo">Productos</a>
                </div>

            </div>

            <div class="footer-bottom">
                <span>
                    © {{ date('Y') }} Ewiflor Importaciones. Todos los derechos reservados.
                </span>

                <span>
                    Sitio web de presentación.
                </span>
            </div>

        </div>
    </footer>


    <!-- JAVASCRIPT -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {

            /* =====================================
               TEMA OSCURO / CLARO
            ===================================== */

            const html = document.documentElement;
            const themeButton = document.getElementById('theme-toggle');
            const themeIcon = document.getElementById('theme-icon');

            function updateThemeIcons() {
                const isLight = html.classList.contains('light');
                themeIcon.textContent = isLight ? '☾' : '☀';
            }

            const savedTheme = localStorage.getItem('theme');

            if (savedTheme === 'light') {
                html.classList.add('light');
            } else {
                html.classList.remove('light');
            }

            updateThemeIcons();

            themeButton?.addEventListener('click', () => {
                html.classList.toggle('light');

                const theme = html.classList.contains('light') ? 'light' : 'dark';

                localStorage.setItem('theme', theme);
                updateThemeIcons();
            });


            /* =====================================
               MENÚ MÓVIL
            ===================================== */

            const mobileButton = document.getElementById('mobile-menu-toggle');
            const mobileMenu = document.getElementById('mobile-menu');

            mobileButton?.addEventListener('click', () => {
                const isOpen = mobileMenu.classList.toggle('open');

                mobileButton.setAttribute('aria-expanded', String(isOpen));
                mobileButton.textContent = isOpen ? '✕' : '☰';
            });

            mobileMenu?.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', () => {
                    mobileMenu.classList.remove('open');
                    mobileButton?.setAttribute('aria-expanded', 'false');

                    if (mobileButton) {
                        mobileButton.textContent = '☰';
                    }
                });
            });


            /* =====================================
               BÚSQUEDA LOCAL DE PRODUCTOS
               No realiza consultas al backend.
            ===================================== */

            const searchInput = document.getElementById('product-search');
            const productCards = document.querySelectorAll('.product-card');
            const resultsText = document.getElementById('search-results');
            const noResults = document.getElementById('no-search-results');

            if (searchInput && productCards.length > 0) {

                resultsText.textContent =
                    `${productCards.length} producto(s) disponibles`;

                function filterProducts() {
                    const query = searchInput.value
                        .trim()
                        .toLocaleLowerCase('es');

                    let visibleProducts = 0;

                    productCards.forEach(card => {
                        const content = card.textContent
                            .toLocaleLowerCase('es');

                        const matches = content.includes(query);

                        card.style.display = matches ? '' : 'none';

                        if (matches) {
                            visibleProducts++;
                        }
                    });

                    resultsText.textContent = query ?
                        `${visibleProducts} producto(s) encontrado(s)` :
                        `${productCards.length} producto(s) disponibles`;

                    if (noResults) {
                        noResults.style.display =
                            visibleProducts === 0 ? 'block' : 'none';
                    }
                }

                searchInput.addEventListener('input', filterProducts);
            }


            /* =====================================
               ANIMACIONES DE APARICIÓN
            ===================================== */

            const revealElements = document.querySelectorAll('.reveal');

            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('visible');
                            observer.unobserve(entry.target);
                        }
                    });
                }, {
                    threshold: 0.12,
                    rootMargin: '0px 0px -35px 0px'
                });

                revealElements.forEach(element => observer.observe(element));
            } else {
                revealElements.forEach(element => {
                    element.classList.add('visible');
                });
            }

        });
    </script>

</body>

</html>