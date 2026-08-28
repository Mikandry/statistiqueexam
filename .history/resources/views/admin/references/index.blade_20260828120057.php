<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin · Référentiels</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('soe-favicon.svg') }}" />
    @include('partials.head-assets')

    <style>
        /* ── reset / base ── */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --bg-primary: #080c18;
            --bg-secondary: #0d1428;
            --bg-card: rgba(16, 24, 56, 0.65);
            --border-glow: rgba(0, 255, 255, 0.18);
            --border-subtle: rgba(255, 255, 255, 0.06);
            --text-primary: #f0f4ff;
            --text-secondary: #94a9cf;
            --text-muted: #5a6f96;
            --accent-cyan: #00f0ff;
            --accent-purple: #a78bfa;
            --accent-pink: #f472b6;
            --accent-teal: #34d399;
            --shadow-glow: 0 8px 40px rgba(0, 200, 255, 0.08);
            --radius-card: 28px;
            --radius-sm: 16px;
            --font: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: var(--font);
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }

        /* ── animated aurora background ── */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            z-index: 0;
            background:
                radial-gradient(ellipse 80% 50% at 10% 20%, rgba(0, 200, 255, 0.10) 0%, transparent 60%),
                radial-gradient(ellipse 70% 50% at 90% 80%, rgba(167, 139, 250, 0.10) 0%, transparent 55%),
                radial-gradient(ellipse 60% 40% at 50% 50%, rgba(244, 114, 182, 0.06) 0%, transparent 50%),
                var(--bg-primary);
            pointer-events: none;
        }

        body::after {
            content: '';
            position: fixed;
            inset: 0;
            z-index: 0;
            background-image:
                radial-gradient(2px 2px at 20% 30%, rgba(255, 255, 255, 0.04), transparent),
                radial-gradient(2px 2px at 40% 70%, rgba(255, 255, 255, 0.04), transparent),
                radial-gradient(2px 2px at 60% 20%, rgba(255, 255, 255, 0.04), transparent),
                radial-gradient(2px 2px at 80% 50%, rgba(255, 255, 255, 0.04), transparent),
                radial-gradient(2px 2px at 10% 80%, rgba(255, 255, 255, 0.04), transparent),
                radial-gradient(2px 2px at 70% 90%, rgba(255, 255, 255, 0.04), transparent);
            background-size: 200px 200px;
            pointer-events: none;
            opacity: 0.5;
        }

        /* floating orbs */
        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(100px);
            pointer-events: none;
            z-index: 0;
            animation: orbFloat 18s ease-in-out infinite alternate;
        }
        .orb--1 {
            width: 500px;
            height: 500px;
            top: -10%;
            left: -10%;
            background: rgba(0, 200, 255, 0.07);
            animation-duration: 22s;
        }
        .orb--2 {
            width: 400px;
            height: 400px;
            bottom: -8%;
            right: -5%;
            background: rgba(167, 139, 250, 0.07);
            animation-duration: 26s;
            animation-delay: -6s;
        }
        .orb--3 {
            width: 300px;
            height: 300px;
            top: 40%;
            left: 50%;
            background: rgba(244, 114, 182, 0.05);
            animation-duration: 20s;
            animation-delay: -10s;
        }

        @keyframes orbFloat {
            0% {
                transform: translate(0, 0) scale(1);
            }
            33% {
                transform: translate(40px, -30px) scale(1.08);
            }
            66% {
                transform: translate(-20px, 40px) scale(0.95);
            }
            100% {
                transform: translate(30px, -20px) scale(1.02);
            }
        }

        /* ── scrollbar ── */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(0, 240, 255, 0.25);
            border-radius: 12px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 240, 255, 0.40);
        }

        /* ── layout overrides ── */
        .mx-auto {
            position: relative;
            z-index: 1;
        }

        /* ── main panel ── */
        .ref-panel {
            border: 1px solid rgba(255, 255, 255, 0.06);
            background: rgba(12, 18, 44, 0.70);
            backdrop-filter: blur(28px) saturate(1.2);
            -webkit-backdrop-filter: blur(28px) saturate(1.2);
            box-shadow: 0 32px 80px rgba(0, 0, 0, 0.60), inset 0 1px 0 rgba(255, 255, 255, 0.04);
            border-radius: 32px;
            transition: box-shadow 0.4s ease;
        }
        .ref-panel:hover {
            box-shadow: 0 40px 100px rgba(0, 0, 0, 0.70), inset 0 1px 0 rgba(255, 255, 255, 0.06);
        }

        /* ── header ── */
        .ref-panel .border-b {
            border-bottom: 1px solid rgba(255, 255, 255, 0.05) !important;
            background: radial-gradient(ellipse 70% 100% at 50% 0%, rgba(0, 200, 255, 0.06), transparent 70%),
                rgba(12, 18, 44, 0.40) !important;
            border-radius: 32px 32px 0 0;
        }

        .ref-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: var(--text-muted);
            background: rgba(255, 255, 255, 0.03);
            display: inline-block;
            padding: 0.1rem 0.6rem;
            border-radius: 40px;
            border: 1px solid rgba(255, 255, 255, 0.04);
        }

        .ref-panel h1 {
            background: linear-gradient(135deg, #fff 40%, rgba(0, 240, 255, 0.7));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .ref-panel .text-slate-500 {
            color: var(--text-secondary) !important;
        }

        /* ── header buttons ── */
        .ref-panel .border-slate-200 {
            border-color: rgba(255, 255, 255, 0.06) !important;
        }
        .ref-panel .bg-white {
            background: rgba(255, 255, 255, 0.04) !important;
            color: var(--text-primary) !important;
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.06);
            transition: all 0.25s ease;
        }
        .ref-panel .bg-white:hover {
            background: rgba(255, 255, 255, 0.09) !important;
            border-color: rgba(0, 240, 255, 0.20) !important;
            box-shadow: 0 0 30px rgba(0, 200, 255, 0.05);
        }
        .ref-panel .text-slate-700 {
            color: var(--text-secondary) !important;
        }

        /* ── stats ── */
        .ref-stat {
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(8px);
            transition: all 0.30s ease;
            position: relative;
            overflow: hidden;
        }
        .ref-stat::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 24px;
            padding: 1px;
            background: linear-gradient(135deg, rgba(0, 240, 255, 0.15), transparent 50%, rgba(167, 139, 250, 0.10));
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            pointer-events: none;
        }
        .ref-stat:hover {
            border-color: rgba(0, 240, 255, 0.12);
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.30);
        }
        .ref-stat .text-slate-900 {
            color: #fff !important;
            font-weight: 800;
        }
        .ref-stat .ref-label {
            background: rgba(255, 255, 255, 0.04);
            border-color: rgba(255, 255, 255, 0.04);
        }

        /* ── alerts ── */
        .border-emerald-200\/80 {
            border-color: rgba(52, 211, 153, 0.20) !important;
            background: rgba(52, 211, 153, 0.06) !important;
            color: #6ee7b7 !important;
            backdrop-filter: blur(8px);
        }
        .border-rose-200\/80 {
            border-color: rgba(244, 114, 182, 0.20) !important;
            background: rgba(244, 114, 182, 0.06) !important;
            color: #f9a8d4 !important;
            backdrop-filter: blur(8px);
        }
        .text-emerald-700 {
            color: #6ee7b7 !important;
        }
        .text-rose-700 {
            color: #f9a8d4 !important;
        }

        /* ── collapsibles ── */
        .ref-collapsible {
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(8px);
            transition: all 0.30s ease;
            overflow: hidden;
        }
        .ref-collapsible:hover {
            border-color: rgba(255, 255, 255, 0.08);
        }
        .ref-collapsible summary {
            list-style: none;
            cursor: pointer;
            transition: background 0.20s ease;
            border-radius: 24px;
        }
        .ref-collapsible summary:hover {
            background: rgba(255, 255, 255, 0.02);
        }
        .ref-collapsible summary::-webkit-details-marker {
            display: none;
        }
        .ref-collapsible-toggle {
            transition: transform 0.30s cubic-bezier(0.34, 1.56, 0.64, 1);
            color: var(--text-muted);
        }
        .ref-collapsible[open] .ref-collapsible-toggle {
            transform: rotate(180deg);
            color: var(--accent-cyan);
        }
        .ref-collapsible-body {
            display: grid;
            grid-template-rows: 0fr;
            transition: grid-template-rows 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .ref-collapsible[open] .ref-collapsible-body {
            grid-template-rows: 1fr;
        }
        .ref-collapsible-inner {
            overflow: hidden;
        }

        .ref-collapsible .ref-label {
            background: rgba(255, 255, 255, 0.03);
            border-color: rgba(255, 255, 255, 0.04);
        }
        .ref-collapsible h2 {
            color: #fff;
            font-weight: 800;
            letter-spacing: -0.01em;
        }
        .ref-collapsible .text-slate-500 {
            color: var(--text-secondary) !important;
        }

        /* ── cards inside collapsibles ── */
        .ref-card {
            border: 1px solid rgba(255, 255, 255, 0.05);
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(8px);
            border-radius: 24px;
            transition: all 0.30s ease;
        }
        .ref-card:hover {
            border-color: rgba(0, 240, 255, 0.10);
            box-shadow: 0 8px 40px rgba(0, 0, 0, 0.20);
        }
        .ref-grid-card {
            position: relative;
            overflow: hidden;
        }
        .ref-grid-card::before {
            content: '';
            position: absolute;
            inset: 0 auto auto 0;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, var(--accent-cyan), var(--accent-purple));
            opacity: 0.50;
        }
        .ref-grid-card .ref-label {
            background: rgba(255, 255, 255, 0.04);
            border-color: rgba(255, 255, 255, 0.04);
        }
        .ref-grid-card h2 {
            color: #fff;
            font-weight: 800;
        }

        /* ── inputs / selects ── */
        .ref-input,
        .ref-select {
            width: 100%;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.06);
            background: rgba(255, 255, 255, 0.04);
            padding: 0.75rem 0.95rem;
            font-size: 0.92rem;
            color: var(--text-primary);
            transition: all 0.25s ease;
            backdrop-filter: blur(4px);
            font-family: var(--font);
        }
        .ref-input::placeholder {
            color: var(--text-muted);
            opacity: 0.6;
        }
        .ref-input:focus,
        .ref-select:focus {
            outline: none;
            border-color: var(--accent-cyan);
            box-shadow: 0 0 0 4px rgba(0, 240, 255, 0.08), 0 0 40px rgba(0, 200, 255, 0.04);
            background: rgba(255, 255, 255, 0.06);
        }
        .ref-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%235a6f96' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            padding-right: 2.5rem;
        }
        .ref-select option {
            background: #0d1428;
            color: var(--text-primary);
        }

        /* ── buttons ── */
        .ref-btn-primary {
            border-radius: 16px;
            background: linear-gradient(135deg, rgba(0, 240, 255, 0.15), rgba(167, 139, 250, 0.10));
            border: 1px solid rgba(0, 240, 255, 0.15);
            color: #fff;
            font-weight: 700;
            transition: all 0.25s ease;
            box-shadow: 0 4px 24px rgba(0, 200, 255, 0.04);
            backdrop-filter: blur(4px);
            position: relative;
            overflow: hidden;
        }
        .ref-btn-primary::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 16px;
            background: linear-gradient(135deg, rgba(0, 240, 255, 0.08), transparent);
            opacity: 0;
            transition: opacity 0.30s ease;
        }
        .ref-btn-primary:hover {
            transform: translateY(-2px);
            border-color: rgba(0, 240, 255, 0.30);
            box-shadow: 0 8px 40px rgba(0, 200, 255, 0.10), inset 0 1px 0 rgba(255, 255, 255, 0.05);
        }
        .ref-btn-primary:hover::before {
            opacity: 1;
        }
        .ref-btn-primary:active {
            transform: translateY(0px);
        }

        .ref-btn-secondary {
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.06);
            background: rgba(255, 255, 255, 0.03);
            color: var(--text-secondary);
            font-weight: 700;
            transition: all 0.25s ease;
            backdrop-filter: blur(4px);
        }
        .ref-btn-secondary:hover {
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(255, 255, 255, 0.10);
            color: #fff;
            transform: translateY(-1px);
        }

        .ref-btn-danger {
            border-radius: 16px;
            border: 1px solid rgba(244, 114, 182, 0.12);
            background: rgba(244, 114, 182, 0.05);
            color: #f9a8d4;
            font-weight: 700;
            transition: all 0.25s ease;
            backdrop-filter: blur(4px);
        }
        .ref-btn-danger:hover {
            background: rgba(244, 114, 182, 0.10);
            border-color: rgba(244, 114, 182, 0.25);
            color: #fbcfe8;
            transform: translateY(-1px);
            box-shadow: 0 4px 24px rgba(244, 114, 182, 0.06);
        }

        /* ── tables ── */
        .ref-table-wrap {
            overflow: hidden;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.04);
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(8px);
        }
        .ref-table thead tr {
            background: rgba(255, 255, 255, 0.02);
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
        }
        .ref-table th {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: var(--text-muted);
            padding: 0.9rem 0.95rem;
        }
        .ref-table td {
            padding: 0.7rem 0.95rem;
            vertical-align: middle;
            border-bottom: 1px solid rgba(255, 255, 255, 0.03);
            color: var(--text-secondary);
        }
        .ref-table tbody tr {
            transition: background 0.20s ease;
        }
        .ref-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.03);
        }
        .ref-table .ref-input,
        .ref-table .ref-select {
            background: rgba(255, 255, 255, 0.03);
            border-color: rgba(255, 255, 255, 0.04);
            padding: 0.5rem 0.75rem;
            font-size: 0.82rem;
            border-radius: 12px;
        }
        .ref-table .ref-input:focus,
        .ref-table .ref-select:focus {
            background: rgba(255, 255, 255, 0.06);
            border-color: var(--accent-cyan);
        }
        .ref-table .ref-btn-primary,
        .ref-table .ref-btn-secondary,
        .ref-table .ref-btn-danger {
            padding: 0.4rem 0.9rem;
            font-size: 0.75rem;
            border-radius: 12px;
        }

        /* ── pagination ── */
        .ref-table-wrap+.mt-3 {
            color: var(--text-muted);
            font-size: 0.85rem;
        }
        .ref-table-wrap+.mt-3 a {
            color: var(--text-secondary);
            transition: color 0.20s ease;
        }
        .ref-table-wrap+.mt-3 a:hover {
            color: var(--accent-cyan);
        }

        /* ── chips ── */
        .ref-chip {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 0.2rem 0.65rem;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            background: rgba(0, 240, 255, 0.06);
            border: 1px solid rgba(0, 240, 255, 0.08);
            color: var(--accent-cyan);
        }

        /* ── filter form ── */
        #heritageFilterForm {
            border: 1px solid rgba(255, 255, 255, 0.04);
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(8px);
            padding: 1.25rem 1.5rem;
        }
        #heritageFilterForm label {
            color: var(--text-secondary);
            font-weight: 600;
            font-size: 0.75rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        /* ── responsive tweaks ── */
        @media (max-width: 640px) {
            .ref-panel {
                border-radius: 20px;
            }
            .ref-panel .border-b {
                border-radius: 20px 20px 0 0;
            }
            .ref-stat {
                border-radius: 18px;
                padding: 0.75rem !important;
            }
            .ref-stat .text-2xl {
                font-size: 1.25rem !important;
            }
            .ref-collapsible {
                border-radius: 18px;
            }
            .ref-card {
                border-radius: 18px;
            }
            .ref-table-wrap {
                border-radius: 14px;
            }
            #heritageFilterForm {
                border-radius: 18px;
                padding: 1rem;
            }
        }

        /* ── utility overrides for dark theme ── */
        .bg-slate-50,
        .bg-slate-50\/90,
        .bg-slate-50\/80 {
            background: rgba(255, 255, 255, 0.02) !important;
        }
        .border-slate-200,
        .border-slate-200\/80 {
            border-color: rgba(255, 255, 255, 0.05) !important;
        }
        .text-slate-900 {
            color: #fff !important;
        }
        .text-slate-700 {
            color: var(--text-secondary) !important;
        }
        .text-slate-500 {
            color: var(--text-muted) !important;
        }
        .text-slate-400 {
            color: var(--text-muted) !important;
        }
        .bg-teal-50 {
            background: rgba(52, 211, 153, 0.06) !important;
            border-color: rgba(52, 211, 153, 0.10) !important;
            color: #6ee7b7 !important;
        }
        .text-teal-800 {
            color: #6ee7b7 !important;
        }
        .border-teal-200 {
            border-color: rgba(52, 211, 153, 0.10) !important;
        }
        .bg-gradient-to-r {
            background: rgba(255, 255, 255, 0.02) !important;
        }
        .from-emerald-50 {
            --tw-gradient-from: transparent !important;
        }
        .to-white {
            --tw-gradient-to: transparent !important;
        }
        .from-rose-50 {
            --tw-gradient-from: transparent !important;
        }
        .shadow-sm {
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.15) !important;
        }

        /* fix for the "type" badge in forms */
        .inline-flex.items-center.rounded-2xl {
            background: rgba(0, 240, 255, 0.04) !important;
            border-color: rgba(0, 240, 255, 0.08) !important;
            color: var(--accent-cyan) !important;
            font-weight: 700;
        }

        /* sidebar compatibility */
        .bg-gradient-to-br {
            background: var(--bg-primary) !important;
        }
        .from-slate-50 {
            --tw-gradient-from: var(--bg-primary) !important;
        }
        .to-slate-100 {
            --tw-gradient-to: var(--bg-secondary) !important;
        }

        /* small glow for headings */
        .ref-panel .text-3xl {
            text-shadow: 0 0 60px rgba(0, 200, 255, 0.04);
        }

        /* drop point / axis tables inside dispatching */
        .ref-table-wrap h3 {
            color: var(--text-secondary);
            font-weight: 600;
            letter-spacing: 0.06em;
        }

        /* subtle animation on stat numbers */
        .ref-stat .text-2xl {
            transition: all 0.30s ease;
        }
        .ref-stat:hover .text-2xl {
            color: var(--accent-cyan) !important;
        }

        /* focus ring for better a11y */
        a:focus-visible,
        button:focus-visible,
        input:focus-visible,
        select:focus-visible {
            outline: 2px solid var(--accent-cyan);
            outline-offset: 2px;
        }

        /* ── extra polish for the "Création" cards ── */
        .ref-card .ref-label {
            background: rgba(255, 255, 255, 0.03);
            border-color: rgba(255, 255, 255, 0.04);
        }

        /* make the "Modifier" / "Supprimer" buttons in tables more cohesive */
        .ref-table .flex.gap-2 {
            gap: 0.35rem !important;
        }

        /* ── smooth scroll offset for anchor links ── */
        #zone-dispatching-referentiels,
        #zone-filtres-referentiels {
            scroll-margin-top: 100px;
        }

        /* ── small decorative line under section titles ── */
        .ref-collapsible h2 {
            position: relative;
            display: inline-block;
        }
        .ref-collapsible h2::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 100%;
            height: 1px;
            background: linear-gradient(90deg, var(--accent-cyan), transparent 80%);
            opacity: 0.20;
        }

        /* ── final touch: subtle border glow on the main panel ── */
        .ref-panel {
            position: relative;
        }
        .ref-panel::after {
            content: '';
            position: absolute;
            inset: -1px;
            border-radius: 32px;
            padding: 1px;
            background: linear-gradient(135deg, rgba(0, 240, 255, 0.06), transparent 40%, rgba(167, 139, 250, 0.04));
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            pointer-events: none;
            z-index: 2;
        }
    </style>
</head>
<body>

    <!-- floating orbs -->
    <div class="orb orb--1"></div>
    <div class="orb orb--2"></div>
    <div class="orb orb--3"></div>

    <div class="mx-auto max-w-[1700px] p-4 md:p-6 lg:p-8">
        <div class="flex flex-col gap-5 md:flex-row md:items-start">
            @include('partials.sidebar')

            <main class="min-w-0 flex-1">
                <div class="ref-panel overflow-hidden rounded-[30px] backdrop-blur-sm transition-all duration-200 hover:shadow-xl">
                    <div class="border-b border-slate-200/80 bg-[radial-gradient(circle_at_top_right,_rgba(20,184,166,0.12),_transparent_28%),linear-gradient(120deg,_rgba(255,255,255,0.96),_rgba(248,250,252,0.96))] px-6 py-6 md:px-8 md:py-7">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div class="space-y-1">
                                <p class="ref-label">Administration Centrale</p>
                                <h1 class="text-3xl font-black tracking-tight text-slate-900 md:text-4xl">Référentiels</h1>
                                <p class="max-w-3xl text-sm font-medium leading-relaxed text-slate-500">Ajout, organisation et maintenance des DREN, CISCO, centres de correction, centres d'écrit et paramètres de dispatching.</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <a class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition-all duration-200 hover:border-slate-300 hover:bg-slate-50 hover:shadow" href="{{ route('admin.statistics.index') }}">Statistiques</a>
                                <a class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition-all duration-200 hover:border-slate-300 hover:bg-slate-50 hover:shadow" href="{{ route('admin.users.index') }}">Utilisateurs</a>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 md:p-8">
                        <div class="mb-6 grid grid-cols-2 gap-3 xl:grid-cols-5">
                            <div class="ref-stat p-4">
                                <p class="ref-label">DREN</p>
                                <p class="mt-2 text-2xl font-black text-slate-900">{{ number_format($drens->count(), 0, ',', ' ') }}</p>
                            </div>
                            <div class="ref-stat p-4">
                                <p class="ref-label">CISCO</p>
                                <p class="mt-2 text-2xl font-black text-slate-900">{{ number_format($formCiscos->count(), 0, ',', ' ') }}</p>
                            </div>
                            <div class="ref-stat p-4">
                                <p class="ref-label">Correction</p>
                                <p class="mt-2 text-2xl font-black text-slate-900">{{ number_format($formCentresCorrection->count(), 0, ',', ' ') }}</p>
                            </div>
                            <div class="ref-stat p-4">
                                <p class="ref-label">Centres Écrit</p>
                                <p class="mt-2 text-2xl font-black text-slate-900">{{ number_format($centresEcritPage->total(), 0, ',', ' ') }}</p>
                            </div>
                            <div class="ref-stat p-4">
                                <p class="ref-label">Dispatching</p>
                                <p class="mt-2 text-2xl font-black text-slate-900">{{ number_format(count($dispatchingAxes) + count($dispatchingDropPoints), 0, ',', ' ') }}</p>
                            </div>
                        </div>

                        @if(session('status'))
                            <div class="mb-6 rounded-lg border border-emerald-200/80 bg-gradient-to-r from-emerald-50 to-white px-4 py-3 text-sm font-medium text-emerald-700 shadow-sm">{{ session('status') }}</div>
                        @endif
                        @if($errors->any())
                            <div class="mb-6 rounded-lg border border-rose-200/80 bg-gradient-to-r from-rose-50 to-white px-4 py-3 text-sm font-medium text-rose-700 shadow-sm">
                                @foreach($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <div class="mb-6 space-y-4">
                            <details class="ref-collapsible" open>
                                <summary class="flex items-center justify-between gap-4 px-5 py-4 md:px-6">
                                    <div>
                                        <p class="ref-label">Création</p>
                                        <h2 class="mt-1 text-xl font-black text-slate-900">Ajouter DREN et CISCO</h2>
                                    </div>
                                    <svg class="ref-collapsible-toggle h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </summary>
                                <div class="ref-collapsible-body">
                                    <div class="ref-collapsible-inner px-5 pb-5 md:px-6 md:pb-6">
                                        <div class="grid gap-5 xl:grid-cols-2">
                                            <div class="ref-card ref-grid-card rounded-[24px] p-5 md:p-6">
                                                <div class="mb-4">
                                                    <p class="ref-label">Création</p>
                                                    <h2 class="mt-1 text-xl font-black text-slate-900">Ajouter DREN</h2>
                                                </div>
                                                <form method="POST" action="{{ route('admin.references.drens.store') }}" class="grid grid-cols-1 gap-3 md:grid-cols-4">
                                                    @csrf
                                                    <input class="ref-input md:col-span-3" name="nom" placeholder="Nom DREN" required>
                                                    <button class="ref-btn-primary inline-flex items-center justify-center px-4 py-3 text-sm" type="submit">Ajouter</button>
                                                </form>
                                            </div>

                                            <div class="ref-card ref-grid-card rounded-[24px] p-5 md:p-6">
                                                <div class="mb-4">
                                                    <p class="ref-label">Création</p>
                                                    <h2 class="mt-1 text-xl font-black text-slate-900">Ajouter CISCO</h2>
                                                </div>
                                                <form method="POST" action="{{ route('admin.references.ciscos.store') }}" class="grid grid-cols-1 gap-3 md:grid-cols-4">
                                                    @csrf
                                                    <select class="ref-select" name="dren_id" required>
                                                        <option value="">DREN</option>
                                                        @foreach($drens as $dren)
                                                            <option value="{{ $dren->id }}">{{ $dren->nom }}</option>
                                                        @endforeach
                                                    </select>
                                                    <input class="ref-input md:col-span-2" name="nom" placeholder="Nom CISCO" required>
                                                    <button class="ref-btn-primary inline-flex items-center justify-center px-4 py-3 text-sm" type="submit">Ajouter</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </details>

                            <details class="ref-collapsible">
                                <summary class="flex items-center justify-between gap-4 px-5 py-4 md:px-6">
                                    <div>
                                        <p class="ref-label">Création</p>
                                        <h2 class="mt-1 text-xl font-black text-slate-900">Ajouter Centres</h2>
                                    </div>
                                    <svg class="ref-collapsible-toggle h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </summary>
                                <div class="ref-collapsible-body">
                                    <div class="ref-collapsible-inner px-5 pb-5 md:px-6 md:pb-6">
                                        <div class="grid gap-5 xl:grid-cols-2">
                                            <div class="ref-card ref-grid-card rounded-[24px] p-5 md:p-6">
                                                <div class="mb-4">
                                                    <p class="ref-label">Création</p>
                                                    <h2 class="mt-1 text-xl font-black text-slate-900">Ajouter Centre de Correction</h2>
                                                </div>
                                                <form method="POST" action="{{ route('admin.references.centres-correction.store') }}" class="grid grid-cols-1 gap-3 md:grid-cols-4">
                                                    @csrf
                                                    <select class="ref-select md:col-span-2" name="cisco_id" required>
                                                        <option value="">CISCO</option>
                                                        @foreach($formCiscos as $cisco)
                                                            <option value="{{ $cisco->id }}">{{ $cisco->dren->nom ?? '-' }} / {{ $cisco->nom }}</option>
                                                        @endforeach
                                                    </select>
                                                    <input class="ref-input" name="nom" placeholder="Nom centre correction" required>
                                                    <div class="inline-flex items-center rounded-2xl border border-teal-200 bg-teal-50 px-4 py-3 text-sm font-bold text-teal-800">{{ $centreTypeForForms }}</div>
                                                    <input type="hidden" name="type_examen" value="{{ $centreTypeForForms }}">
                                                    <div class="md:col-span-4">
                                                        <button class="ref-btn-primary inline-flex items-center justify-center px-4 py-3 text-sm" type="submit">Ajouter Centre correction</button>
                                                    </div>
                                                </form>
                                            </div>

                                            <div class="ref-card ref-grid-card rounded-[24px] p-5 md:p-6">
                                                <div class="mb-4">
                                                    <p class="ref-label">Création</p>
                                                    <h2 class="mt-1 text-xl font-black text-slate-900">Ajouter Centre d'Écrit</h2>
                                                </div>
                                                <form method="POST" action="{{ route('admin.references.centres-ecrit.store') }}" class="grid grid-cols-1 gap-3 md:grid-cols-4">
                                                    @csrf
                                                    <select class="ref-select md:col-span-2" name="centre_correction_id" required>
                                                        <option value="">Centre correction</option>
                                                        @foreach($formCentresCorrection as $cc)
                                                            <option value="{{ $cc->id }}">{{ $cc->cisco->dren->nom ?? '-' }} / {{ $cc->cisco->nom ?? '-' }} / {{ $cc->nom }} ({{ $cc->type_examen }})</option>
                                                        @endforeach
                                                    </select>
                                                    <input class="ref-input" name="nom" placeholder="Nom centre écrit" required>
                                                    <div class="inline-flex items-center rounded-2xl border border-teal-200 bg-teal-50 px-4 py-3 text-sm font-bold text-teal-800">{{ $centreTypeForForms }}</div>
                                                    <input type="hidden" name="type_examen" value="{{ $centreTypeForForms }}">
                                                    <div class="md:col-span-4">
                                                        <button class="ref-btn-primary inline-flex items-center justify-center px-4 py-3 text-sm" type="submit">Ajouter Centre écrit</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </details>
                        </div>

                        <details id="zone-dispatching-referentiels" class="ref-collapsible mb-6 scroll-mt-24">
                            <summary class="flex items-center justify-between gap-4 px-5 py-4 md:px-6">
                                <div>
                                    <p class="ref-label">Paramètres Métier</p>
                                    <h2 class="mt-1 text-xl font-black text-slate-900">Axes de Dispatching et Points de Largage</h2>
                                    <p class="mt-1 max-w-2xl text-sm text-slate-500">Gestion rapide des listes utilisées dans la saisie. Les axes par défaut ont été préchargés et restent modifiables à tout moment.</p>
                                </div>
                                <svg class="ref-collapsible-toggle h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </summary>
                            <div class="ref-collapsible-body">
                                <div class="ref-collapsible-inner px-5 pb-5 md:px-6 md:pb-6">
                                    <div class="grid gap-5 md:grid-cols-2">
                                        <div class="rounded-[22px] border border-slate-200 bg-slate-50/80 p-4">
                                            <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Ajouter un axe</h3>
                                            <form method="POST" action="{{ route('admin.references.dispatching-axes.store') }}" class="flex flex-col gap-3 sm:flex-row">
                                                @csrf
                                                <input class="ref-input" name="nom" placeholder="Nom de l'axe" required>
                                                <button class="ref-btn-primary px-4 py-3 text-sm" type="submit">Ajouter axe</button>
                                            </form>
                                        </div>
                                        <div class="rounded-[22px] border border-slate-200 bg-slate-50/80 p-4">
                                            <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Ajouter un point de largage</h3>
                                            <form method="POST" action="{{ route('admin.references.dispatching-drop-points.store') }}" class="flex flex-col gap-3 sm:flex-row">
                                                @csrf
                                                <input class="ref-input" name="nom" placeholder="Nom du point de largage" required>
                                                <button class="ref-btn-primary px-4 py-3 text-sm" type="submit">Ajouter point</button>
                                            </form>
                                        </div>
                                    </div>

                                    <div class="mt-5 grid gap-5 md:grid-cols-2">
                                        <div class="ref-table-wrap overflow-x-auto">
                                            <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-slate-500">Axes configurés</h3>
                                            <table class="ref-table min-w-full border-collapse text-sm">
                                                <thead><tr><th class="text-left">Axe</th><th class="text-left">Action</th></tr></thead>
                                                <tbody>
                                                    @forelse($dispatchingAxes as $index => $axis)
                                                        <tr>
                                                            <td>
                                                                <form method="POST" action="{{ route('admin.references.dispatching-axes.update', $index) }}" class="flex flex-wrap gap-2">
                                                                    @csrf
                                                                    @method('PUT')
                                                                    <input class="ref-input" name="nom" value="{{ $axis }}" required>
                                                                </td>
                                                                <td>
                                                                    <div class="flex flex-wrap gap-2">
                                                                        <button class="ref-btn-primary px-3 py-2 text-sm" type="submit">Modifier</button>
                                                                    </form>
                                                                    <form method="POST" action="{{ route('admin.references.dispatching-axes.destroy', $index) }}" onsubmit="return confirm('Supprimer l\'axe {{ addslashes($axis) }} ?');">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button class="ref-btn-danger px-3 py-2 text-sm" type="submit">Supprimer</button>
                                                                    </form>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr><td class="text-slate-500" colspan="2">Aucun axe configuré.</td></tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="ref-table-wrap overflow-x-auto">
                                            <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-slate-500">Points de largage configurés</h3>
                                            <table class="ref-table min-w-full border-collapse text-sm">
                                                <thead><tr><th class="text-left">Point</th><th class="text-left">Action</th></tr></thead>
                                                <tbody>
                                                    @forelse($dispatchingDropPoints as $index => $point)
                                                        <tr>
                                                            <td>
                                                                <form method="POST" action="{{ route('admin.references.dispatching-drop-points.update', $index) }}" class="flex flex-wrap gap-2">
                                                                    @csrf
                                                                    @method('PUT')
                                                                    <input class="ref-input" name="nom" value="{{ $point }}" required>
                                                                </td>
                                                                <td>
                                                                    <div class="flex flex-wrap gap-2">
                                                                        <button class="ref-btn-primary px-3 py-2 text-sm" type="submit">Modifier</button>
                                                                    </form>
                                                                    <form method="POST" action="{{ route('admin.references.dispatching-drop-points.destroy', $index) }}" onsubmit="return confirm('Supprimer le point de largage {{ addslashes($point) }} ?');">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button class="ref-btn-danger px-3 py-2 text-sm" type="submit">Supprimer</button>
                                                                    </form>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr><td class="text-slate-500" colspan="2">Aucun point de largage configuré.</td></tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </details>

                        <details id="zone-filtres-referentiels" class="ref-collapsible scroll-mt-24" open>
                            <summary class="flex items-center justify-between gap-4 px-5 py-4 md:px-6">
                                <div>
                                    <p class="ref-label">Maintenance</p>
                                    <h2 class="mt-1 text-xl font-black text-slate-900">Modifier Référentiels Existants</h2>
                                </div>
                                <svg class="ref-collapsible-toggle h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </summary>
                            <div class="ref-collapsible-body">
                                <div class="ref-collapsible-inner px-5 pb-5 md:px-6 md:pb-6">

                                    <form method="GET" action="{{ route('admin.references.index') }}#zone-filtres-referentiels" id="heritageFilterForm" class="mb-6 grid grid-cols-1 gap-3 rounded-[24px] border border-slate-200/80 bg-slate-50/90 p-4 md:grid-cols-5">
                                        <div>
                                            <label class="mb-1 block text-sm font-semibold text-slate-700" for="filter_type_examen">Filtre Examen</label>
                                            <select id="filter_type_examen" name="filter_type_examen" class="ref-select">
                                                <option value="ALL" {{ $selectedTypeExamen === 'ALL' ? 'selected' : '' }}>Tous</option>
                                                <option value="BEPC" {{ $selectedTypeExamen === 'BEPC' ? 'selected' : '' }}>BEPC</option>
                                                <option value="CEPE" {{ $selectedTypeExamen === 'CEPE' ? 'selected' : '' }}>CEPE</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-sm font-semibold text-slate-700" for="filter_dren_id">Filtre DREN</label>
                                            <select id="filter_dren_id" name="filter_dren_id" class="ref-select">
                                                <option value="">Toutes</option>
                                                @foreach($drens as $dren)
                                                    <option value="{{ $dren->id }}" {{ (int) $selectedDrenId === (int) $dren->id ? 'selected' : '' }}>{{ $dren->nom }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-sm font-semibold text-slate-700" for="filter_cisco_id">Filtre CISCO</label>
                                            <select id="filter_cisco_id" name="filter_cisco_id" class="ref-select">
                                                <option value="">Tous</option>
                                                @foreach($filterCiscos as $cisco)
                                                    <option value="{{ $cisco->id }}" {{ (int) $selectedCiscoId === (int) $cisco->id ? 'selected' : '' }}>{{ $cisco->dren->nom ?? '-' }} / {{ $cisco->nom }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-sm font-semibold text-slate-700" for="filter_centre_correction_id">Filtre Centre correction</label>
                                            <select id="filter_centre_correction_id" name="filter_centre_correction_id" class="ref-select">
                                                <option value="">Tous</option>
                                                @foreach($filterCentresCorrection as $cc)
                                                    <option value="{{ $cc->id }}" {{ (int) $selectedCentreCorrectionId === (int) $cc->id ? 'selected' : '' }}>{{ $cc->cisco->dren->nom ?? '-' }} / {{ $cc->cisco->nom ?? '-' }} / {{ $cc->nom }} ({{ $cc->type_examen }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="flex items-end gap-2 md:col-span-2">
                                            <button class="ref-btn-primary w-full px-4 py-3 text-sm" type="submit">Filtrer</button>
                                            <a class="ref-btn-secondary w-full px-4 py-3 text-center text-sm" href="{{ route('admin.references.index') }}#zone-filtres-referentiels">Réinitialiser</a>
                                        </div>
                                    </form>

                                    <div class="mb-6 overflow-x-auto">
                                        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">DREN</h3>
                                        <div class="ref-table-wrap">
                                            <table class="ref-table min-w-full border-collapse text-sm">
                                                <thead><tr><th class="text-left">Nom</th><th class="text-left">Action</th></tr></thead>
                                                <tbody>
                                                    @forelse($drensPage as $dren)
                                                        <tr>
                                                            <td>
                                                                <form method="POST" action="{{ route('admin.references.drens.update', $dren) }}" class="flex flex-wrap gap-2">
                                                                    @csrf
                                                                    @method('PUT')
                                                                    <input class="ref-input" name="nom" value="{{ $dren->nom }}" required>
                                                                </td>
                                                                <td>
                                                                    <div class="flex flex-wrap gap-2">
                                                                        <button class="ref-btn-primary px-3 py-2 text-sm" type="submit">Modifier</button>
                                                                    </form>
                                                                    <form method="POST" action="{{ route('admin.references.drens.destroy', $dren) }}" onsubmit="return confirm('Supprimer la DREN {{ addslashes($dren->nom) }} et tout son héritage (CISCO, centres, statistiques) ?');">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button class="ref-btn-danger px-3 py-2 text-sm" type="submit">Supprimer</button>
                                                                    </form>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr><td class="text-slate-500" colspan="2">Aucune DREN trouvée.</td></tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                        @if($drensPage->hasPages())
                                            <div class="mt-3">{{ $drensPage->links() }}</div>
                                        @endif
                                    </div>

                                    <div class="mb-6 overflow-x-auto">
                                        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">CISCO</h3>
                                        <div class="ref-table-wrap">
                                            <table class="ref-table min-w-full border-collapse text-sm">
                                                <thead><tr><th class="text-left">DREN</th><th class="text-left">Nom</th><th class="text-left">Action</th></tr></thead>
                                                <tbody>
                                                    @forelse($ciscosPage as $cisco)
                                                        <tr>
                                                            <td>
                                                                <form method="POST" action="{{ route('admin.references.ciscos.update', $cisco) }}" class="flex flex-wrap gap-2">
                                                                    @csrf
                                                                    @method('PUT')
                                                                    <select class="ref-select" name="dren_id" required>
                                                                        @foreach($drens as $dren)
                                                                            <option value="{{ $dren->id }}" {{ (int)$cisco->dren_id === (int)$dren->id ? 'selected' : '' }}>{{ $dren->nom }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                                <td><input class="ref-input" name="nom" value="{{ $cisco->nom }}" required></td>
                                                                <td>
                                                                    <div class="flex flex-wrap gap-2">
                                                                        <button class="ref-btn-primary px-3 py-2 text-sm" type="submit">Modifier</button>
                                                                    </form>
                                                                    <form method="POST" action="{{ route('admin.references.ciscos.destroy', $cisco) }}" onsubmit="return confirm('Supprimer le CISCO {{ addslashes($cisco->nom) }} et tous ses centres/statistiques ?');">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button class="ref-btn-danger px-3 py-2 text-sm" type="submit">Supprimer</button>
                                                                    </form>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr><td class="text-slate-500" colspan="3">Aucun CISCO trouvé.</td></tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                        @if($ciscosPage->hasPages())
                                            <div class="mt-3">{{ $ciscosPage->links() }}</div>
                                        @endif
                                    </div>

                                    <div class="mb-6 overflow-x-auto">
                                        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Centres de correction</h3>
                                        <div class="ref-table-wrap">
                                            <table class="ref-table min-w-full border-collapse text-sm">
                                                <thead><tr><th class="text-left">CISCO</th><th class="text-left">Nom</th><th class="text-left">Type</th><th class="text-left">Action</th></tr></thead>
                                                <tbody>
                                                    @forelse($centresCorrectionPage as $cc)
                                                        <tr>
                                                            <td>
                                                                <form method="POST" action="{{ route('admin.references.centres-correction.update', $cc) }}">
                                                                    @csrf
                                                                    @method('PUT')
                                                                    <select class="ref-select" name="cisco_id" required>
                                                                        @foreach($allCiscos as $cisco)
                                                                            <option value="{{ $cisco->id }}" {{ (int)$cc->cisco_id === (int)$cisco->id ? 'selected' : '' }}>{{ $cisco->dren->nom ?? '-' }} / {{ $cisco->nom }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                                <td><input class="ref-input" name="nom" value="{{ $cc->nom }}" required></td>
                                                                <td>
                                                                    <select class="ref-select" name="type_examen" required>
                                                                        <option value="BEPC" {{ $cc->type_examen === 'BEPC' ? 'selected' : '' }}>BEPC</option>
                                                                        <option value="CEPE" {{ $cc->type_examen === 'CEPE' ? 'selected' : '' }}>CEPE</option>
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <div class="flex flex-wrap gap-2">
                                                                        <button class="ref-btn-primary px-3 py-2 text-sm" type="submit">Modifier</button>
                                                                    </form>
                                                                    <form method="POST" action="{{ route('admin.references.centres-correction.destroy', $cc) }}" onsubmit="return confirm('Supprimer le centre de correction {{ addslashes($cc->nom) }} ({{ $cc->type_examen }}) et tous ses centres d\'écrit/statistiques ?');">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button class="ref-btn-danger px-3 py-2 text-sm" type="submit">Supprimer</button>
                                                                    </form>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr><td class="text-slate-500" colspan="4">Aucun centre de correction trouvé.</td></tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                        @if($centresCorrectionPage->hasPages())
                                            <div class="mt-3">{{ $centresCorrectionPage->links() }}</div>
                                        @endif
                                    </div>

                                    <div class="overflow-x-auto">
                                        <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-slate-500">Centres d'écrit</h3>
                                        <div class="ref-table-wrap">
                                            <table class="ref-table min-w-full border-collapse text-sm">
                                                <thead><tr><th class="text-left">Centre correction</th><th class="text-left">Nom</th><th class="text-left">Type</th><th class="text-left">Action</th></tr></thead>
                                                <tbody>
                                                    @forelse($centresEcritPage as $ce)
                                                        <tr>
                                                            <td>
                                                                <form method="POST" action="{{ route('admin.references.centres-ecrit.update', $ce) }}">
                                                                    @csrf
                                                                    @method('PUT')
                                                                    <select class="ref-select" name="centre_correction_id" required>
                                                                        @foreach($allCentresCorrection as $cc)
                                                                            <option value="{{ $cc->id }}" {{ (int)$ce->centre_correction_id === (int)$cc->id ? 'selected' : '' }}>{{ $cc->cisco->dren->nom ?? '-' }} / {{ $cc->cisco->nom ?? '-' }} / {{ $cc->nom }} ({{ $cc->type_examen }})</option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                                <td><input class="ref-input" name="nom" value="{{ $ce->nom }}" required></td>
                                                                <td>
                                                                    <select class="ref-select" name="type_examen" required>
                                                                        <option value="BEPC" {{ $ce->type_examen === 'BEPC' ? 'selected' : '' }}>BEPC</option>
                                                                        <option value="CEPE" {{ $ce->type_examen === 'CEPE' ? 'selected' : '' }}>CEPE</option>
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <div class="flex flex-wrap gap-2">
                                                                        <button class="ref-btn-primary px-3 py-2 text-sm" type="submit">Modifier</button>
                                                                    </form>
                                                                    <form method="POST" action="{{ route('admin.references.centres-ecrit.destroy', $ce) }}" onsubmit="return confirm('Supprimer le centre d\'écrit {{ addslashes($ce->nom) }} ({{ $ce->type_examen }}) et ses statistiques ?');">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button class="ref-btn-danger px-3 py-2 text-sm" type="submit">Supprimer</button>
                                                                    </form>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr><td class="text-slate-500" colspan="4">Aucun centre d'écrit trouvé.</td></tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                        @if($centresEcritPage->hasPages())
                                            <div class="mt-3">{{ $centresEcritPage->links() }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </details>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        (function() {
            const form = document.getElementById('heritageFilterForm');
            const examen = document.getElementById('filter_type_examen');
            const dren = document.getElementById('filter_dren_id');
            const cisco = document.getElementById('filter_cisco_id');
            const centre = document.getElementById('filter_centre_correction_id');
            if (!form || !examen || !dren || !cisco || !centre) {
                return;
            }

            examen.addEventListener('change', () => form.submit());
            dren.addEventListener('change', () => form.submit());
            cisco.addEventListener('change', () => form.submit());
            centre.addEventListener('change', () => form.submit());
        })();
    </script>
</body>
</html>