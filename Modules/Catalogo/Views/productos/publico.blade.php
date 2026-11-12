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

        /* =====================================
           DETALLE DE PRODUCTO (MODAL)
        ===================================== */

        .product-card {
            cursor: pointer;
        }

        .product-card:focus-visible {
            outline: 2px solid var(--accent);
            outline-offset: 3px;
        }

        .product-photos {
            position: absolute;
            right: 12px;
            bottom: 12px;
            padding: 5px 10px;
            border: 1px solid var(--border);
            border-radius: 999px;
            background: color-mix(in srgb, var(--bg) 82%, transparent);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            color: var(--text);
            font-size: .68rem;
            font-weight: 700;
        }

        .product-more {
            color: var(--accent);
            font-weight: 700;
            transition: transform .25s ease;
        }

        .product-card:hover .product-more {
            transform: translateX(3px);
        }

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
            -webkit-backdrop-filter: blur(6px);
            animation: pm-fade .25s ease both;
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
            border-radius: 22px;
            background: var(--surface);
            box-shadow: 0 30px 80px rgba(0, 0, 0, .45);
            outline: none;
            animation: pm-rise .3s ease both;
        }

        .pm-close {
            position: absolute;
            top: 14px;
            right: 14px;
            z-index: 3;
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: color-mix(in srgb, var(--bg) 80%, transparent);
            color: var(--text);
            cursor: pointer;
            transition: border-color .25s ease, color .25s ease;
        }

        .pm-close:hover {
            border-color: var(--accent);
            color: var(--accent);
        }

        .pm-gallery {
            padding: 22px;
            background: var(--surface-soft);
        }

        .pm-main {
            position: relative;
            aspect-ratio: 4 / 3;
            overflow: hidden;
            border: 1px solid var(--border);
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
            font-size: 3rem;
        }

        .pm-nav {
            position: absolute;
            top: 50%;
            z-index: 2;
            width: 38px;
            height: 38px;
            display: none;
            place-items: center;
            transform: translateY(-50%);
            border: 1px solid var(--border);
            border-radius: 50%;
            background: color-mix(in srgb, var(--bg) 82%, transparent);
            color: var(--text);
            font-size: 1.3rem;
            line-height: 1;
            cursor: pointer;
            transition: border-color .25s ease, color .25s ease;
        }

        .pm-nav:hover {
            border-color: var(--accent);
            color: var(--accent);
        }

        .pm-prev {
            left: 10px;
        }

        .pm-next {
            right: 10px;
        }

        .pm-thumbs {
            display: flex;
            gap: 10px;
            margin-top: 14px;
            overflow-x: auto;
            padding-bottom: 4px;
        }

        .pm-thumb {
            flex: 0 0 68px;
            width: 68px;
            height: 68px;
            padding: 0;
            overflow: hidden;
            border: 2px solid transparent;
            border-radius: 12px;
            background: var(--bg);
            cursor: pointer;
            opacity: .65;
            transition: opacity .25s ease, border-color .25s ease;
        }

        .pm-thumb img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
        }

        .pm-thumb:hover,
        .pm-thumb.active {
            opacity: 1;
        }

        .pm-thumb.active {
            border-color: var(--accent);
        }

        .pm-info {
            padding: 34px 30px 30px;
        }

        .pm-title {
            margin: 4px 0 14px;
            font-size: 1.55rem;
            font-weight: 800;
            line-height: 1.25;
            letter-spacing: -.02em;
        }

        .pm-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 18px;
        }

        .pm-tag {
            padding: 5px 11px;
            border: 1px solid rgba(56, 189, 248, .25);
            border-radius: 999px;
            background: rgba(14, 165, 233, .08);
            color: var(--accent);
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .pm-desc {
            margin: 0 0 22px;
            color: var(--muted);
            font-size: .9rem;
            line-height: 1.8;
            white-space: pre-line;
        }

        .pm-specs {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin: 0 0 22px;
        }

        .pm-spec {
            padding: 12px 14px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: var(--surface-soft);
        }

        .pm-spec dt {
            color: var(--muted);
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .pm-spec dd {
            margin: 4px 0 0;
            font-size: .88rem;
            font-weight: 700;
        }

        .pm-subtitle {
            margin: 0 0 10px;
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--accent);
        }

        .pm-variants {
            display: grid;
            gap: 8px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .pm-variant {
            padding: 10px 14px;
            border: 1px solid var(--border);
            border-radius: 12px;
            font-size: .82rem;
        }

        .pm-variant strong {
            display: block;
            font-weight: 700;
        }

        .pm-variant span {
            display: block;
            margin-top: 2px;
            color: var(--muted);
            font-size: .76rem;
            line-height: 1.6;
        }

        @keyframes pm-fade {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes pm-rise {
            from { opacity: 0; transform: translateY(18px) scale(.98); }
            to { opacity: 1; transform: none; }
        }

        @media (max-width: 820px) {
            .pm {
                align-items: flex-end;
                padding: 0;
            }

            .pm-dialog {
                grid-template-columns: 1fr;
                max-height: 92vh;
                border-radius: 22px 22px 0 0;
            }

            .pm-info {
                padding: 24px 20px 28px;
            }

            .pm-gallery {
                padding: 16px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .pm-backdrop,
            .pm-dialog {
                animation: none;
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
                    @php
                    $principal = $producto->imagenes->firstWhere('es_principal', true) ?? $producto->imagenes->first();
                    $textoBusqueda = mb_strtolower(implode(' ', array_filter([
                        $producto->nombre,
                        $producto->marca?->nombre,
                        $producto->modelo,
                        $producto->categoria,
                        $producto->descripcion,
                    ])));
                    @endphp

                    <article class="product-card reveal"
                        data-product-id="{{ $producto->id_producto }}"
                        data-search="{{ $textoBusqueda }}"
                        role="button"
                        tabindex="0"
                        aria-haspopup="dialog"
                        aria-label="Ver detalle de {{ $producto->nombre }}">

                        <div class="product-image">

                            @if($principal)
                            <img
                                src="{{ $principal->url }}"
                                alt="{{ $principal->texto_alternativo ?: $producto->nombre }}"
                                loading="lazy"
                                decoding="async"
                                onerror="this.style.display='none'">
                            @else
                            <div class="product-image-placeholder" aria-hidden="true">
                                ⚙
                            </div>
                            @endif

                            @if($producto->imagenes->count() > 1)
                            <span class="product-photos" aria-hidden="true">{{ $producto->imagenes->count() }} fotos</span>
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

                                <span class="product-more">Ver detalle →</span>

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



    <!-- DETALLE DE PRODUCTO (MODAL) -->
    <div id="product-modal" class="pm" aria-hidden="true">
        <div class="pm-backdrop" data-pm-close></div>

        <div class="pm-dialog" role="dialog" aria-modal="true" aria-labelledby="pm-title" tabindex="-1">

            <button type="button" class="pm-close" data-pm-close aria-label="Cerrar detalle">✕</button>

            <div class="pm-gallery">
                <div class="pm-main">
                    <img id="pm-image" alt="" hidden>
                    <div id="pm-placeholder" class="pm-placeholder" aria-hidden="true">⚙</div>

                    <button type="button" id="pm-prev" class="pm-nav pm-prev" aria-label="Imagen anterior">‹</button>
                    <button type="button" id="pm-next" class="pm-nav pm-next" aria-label="Imagen siguiente">›</button>
                </div>

                <div id="pm-thumbs" class="pm-thumbs" aria-label="Miniaturas"></div>
            </div>

            <div class="pm-info">
                <span id="pm-brand" class="product-brand"></span>
                <h3 id="pm-title" class="pm-title"></h3>

                <div id="pm-tags" class="pm-tags"></div>

                <p id="pm-desc" class="pm-desc"></p>

                <dl id="pm-specs" class="pm-specs"></dl>

                <div id="pm-variants-wrap" hidden>
                    <h4 class="pm-subtitle">Variantes disponibles</h4>
                    <ul id="pm-variants" class="pm-variants"></ul>
                </div>
            </div>

        </div>
    </div>

    <script id="product-data" type="application/json">{!! json_encode($detalle ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) !!}</script>

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
                        const content = (card.dataset.search || card.textContent)
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
               DETALLE DE PRODUCTO (MODAL)
            ===================================== */

            const dataEl = document.getElementById('product-data');
            const modal = document.getElementById('product-modal');

            if (dataEl && modal) {
                const productos = JSON.parse(dataEl.textContent || '{}');
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

                function mostrarImagen(i) {
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
                }

                function llenar(p) {
                    document.getElementById('pm-brand').textContent = p.marca || '';
                    document.getElementById('pm-title').textContent = p.nombre;
                    document.getElementById('pm-desc').textContent =
                        p.descripcion || 'Consulta las características de este producto.';

                    const tags = document.getElementById('pm-tags');
                    tags.replaceChildren();
                    [p.categoria, p.tipo].filter(Boolean).forEach(t => tags.appendChild(el('span', 'pm-tag', t)));

                    const specs = document.getElementById('pm-specs');
                    specs.replaceChildren();
                    const filas = [
                        ['Marca', p.marca],
                        ['Origen', p.pais],
                        ['Modelo', p.modelo],
                        ['Garantía', p.garantia ? `${p.garantia} meses` : 'Según producto'],
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
                    wrap.hidden = !p.variantes.length;

                    p.variantes.forEach(v => {
                        const li = el('li', 'pm-variant');
                        li.appendChild(el('strong', null, v.color || v.sku));
                        const detalle = [v.color ? v.sku : null, v.caracteristicas].filter(Boolean).join(' · ');
                        if (detalle) li.appendChild(el('span', null, detalle));
                        lista.appendChild(li);
                    });

                    galeria = p.imagenes;
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
                }

                function abrir(id) {
                    const p = productos[id];
                    if (!p) return;

                    ultimoFoco = document.activeElement;
                    llenar(p);
                    dialog.scrollTop = 0;
                    modal.classList.add('open');
                    modal.setAttribute('aria-hidden', 'false');
                    document.body.style.overflow = 'hidden';
                    dialog.focus();
                }

                function cerrar() {
                    modal.classList.remove('open');
                    modal.setAttribute('aria-hidden', 'true');
                    document.body.style.overflow = '';
                    ultimoFoco?.focus?.();
                }

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

                    // Mantiene el foco dentro del diálogo mientras está abierto
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