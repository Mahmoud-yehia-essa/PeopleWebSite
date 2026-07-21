@extends('admin.master_admin')
@section('admin')

<style>
/* ===== Dashboard Premium Styles ===== */
@import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap');

:root {
    --primary: #6366f1;
    --primary-light: #818cf8;
    --secondary: #0ea5e9;
    --accent: #10b981;
    --warning: #f59e0b;
    --danger: #ef4444;
    --pink: #ec4899;
    --purple: #8b5cf6;
}

.dashboard-wrapper {
    min-height: 100vh;
    padding: 28px 20px;
    direction: rtl;
    font-family: 'Cairo', sans-serif;
    position: relative;
}

/* ===== Animated Background Orbs ===== */
.bg-orbs {
    position: fixed;
    inset: 0;
    pointer-events: none;
    z-index: 0;
    overflow: hidden;
}

.bg-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.07;
    animation: floatOrb 12s ease-in-out infinite;
}

.bg-orb-1 {
    width: 500px; height: 500px;
    background: radial-gradient(circle, #6366f1, transparent);
    top: -150px; right: -100px;
    animation-delay: 0s;
}

.bg-orb-2 {
    width: 400px; height: 400px;
    background: radial-gradient(circle, #0ea5e9, transparent);
    bottom: 0px; left: -100px;
    animation-delay: -4s;
}

.bg-orb-3 {
    width: 300px; height: 300px;
    background: radial-gradient(circle, #10b981, transparent);
    top: 50%; left: 40%;
    animation-delay: -8s;
}

@keyframes floatOrb {
    0%, 100% { transform: translate(0, 0) scale(1); }
    33% { transform: translate(30px, -30px) scale(1.05); }
    66% { transform: translate(-20px, 20px) scale(0.95); }
}

/* ===== Dashboard Header ===== */
.dash-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 36px;
    position: relative;
    z-index: 1;
    flex-wrap: wrap;
    gap: 16px;
}

.dash-header-title h1 {
    font-size: 26px;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 4px 0;
    letter-spacing: -0.5px;
}

.dash-header-title h1 span {
    background: linear-gradient(135deg, #6366f1, #0ea5e9);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.dash-header-title p {
    color: #64748b;
    font-size: 13px;
    margin: 0;
}

.dash-date-badge {
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(99, 102, 241, 0.1);
    border: 1px solid rgba(99, 102, 241, 0.2);
    border-radius: 50px;
    padding: 8px 16px;
    color: #818cf8;
    font-size: 13px;
    font-weight: 500;
}

.dash-date-badge i { font-size: 16px; }

/* ===== Section Label ===== */
.section-label {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 18px;
    position: relative;
    z-index: 1;
}

.section-label-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6366f1, #0ea5e9);
    box-shadow: 0 0 10px rgba(99, 102, 241, 0.6);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.3); opacity: 0.7; }
}

.section-label span {
    font-size: 13px;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 1.5px;
}

/* ===== Stat Cards ===== */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
    margin-bottom: 20px;
    position: relative;
    z-index: 1;
}

@media (max-width: 1200px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 640px) { .stats-grid { grid-template-columns: 1fr; } }

.stat-card {
    position: relative;
    border-radius: 20px;
    padding: 22px;
    cursor: pointer;
    text-decoration: none !important;
    overflow: hidden;
    transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease;
    display: block;
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255,255,255,0.06);
    animation: slideUpIn 0.5s ease both;
}

.stat-card:nth-child(1) { animation-delay: 0.05s; }
.stat-card:nth-child(2) { animation-delay: 0.10s; }
.stat-card:nth-child(3) { animation-delay: 0.15s; }
.stat-card:nth-child(4) { animation-delay: 0.20s; }

@keyframes slideUpIn {
    from { opacity: 0; transform: translateY(30px); }
    to   { opacity: 1; transform: translateY(0); }
}

.stat-card:hover {
    transform: translateY(-8px) scale(1.02);
    text-decoration: none !important;
}

/* Card Gradients - Vivid Solid Gradients */
.card-indigo   { background: linear-gradient(135deg, #4f46e5 0%, #6366f1 50%, #818cf8 100%); box-shadow: 0 8px 32px rgba(99,102,241,0.35); }
.card-indigo:hover { box-shadow: 0 20px 60px rgba(99,102,241,0.5); }

.card-sky      { background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 50%, #38bdf8 100%); box-shadow: 0 8px 32px rgba(14,165,233,0.35); }
.card-sky:hover { box-shadow: 0 20px 60px rgba(14,165,233,0.5); }

.card-emerald  { background: linear-gradient(135deg, #059669 0%, #10b981 50%, #34d399 100%); box-shadow: 0 8px 32px rgba(16,185,129,0.35); }
.card-emerald:hover { box-shadow: 0 20px 60px rgba(16,185,129,0.5); }

.card-violet   { background: linear-gradient(135deg, #7c3aed 0%, #8b5cf6 50%, #a78bfa 100%); box-shadow: 0 8px 32px rgba(139,92,246,0.35); }
.card-violet:hover { box-shadow: 0 20px 60px rgba(139,92,246,0.5); }

.card-amber    { background: linear-gradient(135deg, #d97706 0%, #f59e0b 50%, #fbbf24 100%); box-shadow: 0 8px 32px rgba(245,158,11,0.35); }
.card-amber:hover { box-shadow: 0 20px 60px rgba(245,158,11,0.5); }

.card-rose     { background: linear-gradient(135deg, #be123c 0%, #f43f5e 50%, #fb7185 100%); box-shadow: 0 8px 32px rgba(244,63,94,0.35); }
.card-rose:hover { box-shadow: 0 20px 60px rgba(244,63,94,0.5); }

.card-teal     { background: linear-gradient(135deg, #0d9488 0%, #14b8a6 50%, #2dd4bf 100%); box-shadow: 0 8px 32px rgba(20,184,166,0.35); }
.card-teal:hover { box-shadow: 0 20px 60px rgba(20,184,166,0.5); }

.card-orange   { background: linear-gradient(135deg, #c2410c 0%, #f97316 50%, #fb923c 100%); box-shadow: 0 8px 32px rgba(249,115,22,0.35); }
.card-orange:hover { box-shadow: 0 20px 60px rgba(249,115,22,0.5); }

/* Card Inner Glow */
.stat-card::before {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 20px;
    background: linear-gradient(135deg, rgba(255,255,255,0.18) 0%, transparent 60%);
    pointer-events: none;
}

/* Card Shine Effect */
.stat-card::after {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 100%;
    height: 100%;
    background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
    transform: rotate(-30deg);
    pointer-events: none;
    transition: opacity 0.3s;
    opacity: 0;
}

.stat-card:hover::after { opacity: 1; }

/* Icon Container */
.stat-icon-wrap {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 18px;
    position: relative;
    flex-shrink: 0;
}

.stat-icon-bg-indigo  { background: rgba(255,255,255,0.2); }
.stat-icon-bg-sky     { background: rgba(255,255,255,0.2); }
.stat-icon-bg-emerald { background: rgba(255,255,255,0.2); }
.stat-icon-bg-violet  { background: rgba(255,255,255,0.2); }
.stat-icon-bg-amber   { background: rgba(255,255,255,0.2); }
.stat-icon-bg-rose    { background: rgba(255,255,255,0.2); }
.stat-icon-bg-teal    { background: rgba(255,255,255,0.2); }
.stat-icon-bg-orange  { background: rgba(255,255,255,0.2); }

.stat-icon-wrap i {
    font-size: 26px;
    line-height: 1;
}

.ic-indigo,
.ic-sky,
.ic-emerald,
.ic-violet,
.ic-amber,
.ic-rose,
.ic-teal,
.ic-orange  { color: rgba(255,255,255,0.95) !important; }

.stat-card-content {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
}

.stat-card-info {
    flex: 1;
}

/* Force all text in stat cards to be white */
.stat-card,
.stat-card *,
.stat-card:hover,
.stat-card:hover * {
    color: #ffffff !important;
    text-decoration: none !important;
}

.stat-number {
    font-size: 38px;
    font-weight: 900;
    line-height: 1;
    margin-bottom: 6px;
    color: #ffffff !important;
    font-feature-settings: "tnum";
    letter-spacing: -1px;
    text-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.stat-label {
    font-size: 18px;
    color: rgba(255,255,255,0.9) !important;
    font-weight: 600;
    letter-spacing: 0.3px;
}

/* Progress Bar */
.stat-progress {
    margin-top: 18px;
    height: 3px;
    border-radius: 2px;
    background: rgba(255,255,255,0.08);
    overflow: hidden;
}

.stat-progress-bar {
    height: 100%;
    border-radius: 2px;
    animation: growBar 1.5s ease both;
    animation-delay: 0.5s;
}

@keyframes growBar {
    from { width: 0 !important; }
}

.bar-indigo  { background: linear-gradient(90deg, #6366f1, #818cf8); box-shadow: 0 0 8px rgba(99,102,241,0.6); }
.bar-sky     { background: linear-gradient(90deg, #0ea5e9, #38bdf8); box-shadow: 0 0 8px rgba(14,165,233,0.6); }
.bar-emerald { background: linear-gradient(90deg, #10b981, #34d399); box-shadow: 0 0 8px rgba(16,185,129,0.6); }
.bar-violet  { background: linear-gradient(90deg, #8b5cf6, #a78bfa); box-shadow: 0 0 8px rgba(139,92,246,0.6); }
.bar-amber   { background: linear-gradient(90deg, #f59e0b, #fcd34d); box-shadow: 0 0 8px rgba(245,158,11,0.6); }
.bar-rose    { background: linear-gradient(90deg, #f43f5e, #fb7185); box-shadow: 0 0 8px rgba(244,63,94,0.6); }
.bar-teal    { background: linear-gradient(90deg, #14b8a6, #2dd4bf); box-shadow: 0 0 8px rgba(20,184,166,0.6); }
.bar-orange  { background: linear-gradient(90deg, #f97316, #fb923c); box-shadow: 0 0 8px rgba(249,115,22,0.6); }

/* Stat Card Arrow */
.stat-arrow {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: rgba(255,255,255,0.06);
    display: flex;
    align-items: center;
    justify-content: center;
    color: rgba(255,255,255,0.4);
    font-size: 14px;
    transition: all 0.3s;
    flex-shrink: 0;
}

.stat-card:hover .stat-arrow {
    background: rgba(255,255,255,0.12);
    color: rgba(255,255,255,0.8);
    transform: translateX(-3px);
}

/* ===== Special Section Row ===== */
.special-row {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 18px;
    margin-bottom: 20px;
    position: relative;
    z-index: 1;
}

@media (max-width: 992px) { .special-row { grid-template-columns: 1fr; } }

/* ===== Top Affiliate Card ===== */
.affiliate-card {
    background: linear-gradient(145deg, #1e0a2e 0%, #0f0520 60%, #1a0530 100%);
    border: 1px solid rgba(168, 85, 247, 0.2);
    border-radius: 24px;
    padding: 28px;
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
    text-decoration: none !important;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    animation: slideUpIn 0.5s ease 0.4s both;
    gap: 24px;
}

.affiliate-card > div:first-child {
    flex: 1;
}

@media (max-width: 768px) {
    .affiliate-card {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
    }
}

.affiliate-card::before {
    content: '';
    position: absolute;
    top: -60px; right: -60px;
    width: 200px; height: 200px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(168,85,247,0.3), transparent 70%);
    pointer-events: none;
}

.affiliate-card::after {
    content: '';
    position: absolute;
    bottom: -40px; left: -40px;
    width: 160px; height: 160px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(236,72,153,0.2), transparent 70%);
    pointer-events: none;
}

.affiliate-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 25px 60px rgba(168,85,247,0.2);
    text-decoration: none !important;
}

.aff-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(168,85,247,0.15);
    border: 1px solid rgba(168,85,247,0.3);
    border-radius: 50px;
    padding: 5px 12px;
    font-size: 11px;
    color: #c084fc;
    font-weight: 600;
    margin-bottom: 14px;
    width: fit-content;
    letter-spacing: 0.5px;
}

.aff-title {
    font-size: 15px;
    font-weight: 700;
    color: #e2e8f0;
    margin: 0 0 6px 0;
    position: relative;
    z-index: 1;
}

.aff-subtitle {
    font-size: 12px;
    color: rgba(255,255,255,0.4);
    margin: 0 0 16px 0;
    line-height: 1.6;
    position: relative;
    z-index: 1;
}

.aff-name {
    font-size: 24px;
    font-weight: 900;
    color: #fff;
    margin: 0;
    position: relative;
    z-index: 1;
    text-shadow: 0 0 20px rgba(168,85,247,0.5);
}

.aff-footer {
    display: flex;
    align-items: center;
    gap: 32px;
    position: relative;
    z-index: 1;
}

@media (min-width: 769px) {
    .aff-footer {
        margin-top: 0;
        padding-top: 0;
        border-top: none;
    }
}

@media (max-width: 768px) {
    .aff-footer {
        width: 100%;
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px solid rgba(255,255,255,0.06);
        justify-content: space-between;
    }
}

.aff-count-label {
    font-size: 11px;
    color: rgba(255,255,255,0.4);
    margin-bottom: 3px;
}

.aff-count-value {
    font-size: 22px;
    font-weight: 800;
    color: #c084fc;
}

.aff-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(168,85,247,0.15);
    border: 1px solid rgba(168,85,247,0.3);
    border-radius: 50px;
    padding: 8px 16px;
    font-size: 12px;
    color: #c084fc;
    font-weight: 600;
    transition: all 0.3s;
    white-space: nowrap;
}

.affiliate-card:hover .aff-btn {
    background: rgba(168,85,247,0.25);
    color: #e9d5ff;
}

/* ===== Chart Card ===== */
.chart-card {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 24px;
    padding: 24px;
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    animation: slideUpIn 0.5s ease 0.5s both;
}

.chart-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
}

.chart-card-title {
    font-size: 15px;
    font-weight: 700;
    color: #e2e8f0;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.chart-card-title i {
    color: #818cf8;
    font-size: 18px;
}

.chart-badge {
    background: rgba(99,102,241,0.1);
    border: 1px solid rgba(99,102,241,0.2);
    border-radius: 50px;
    padding: 4px 12px;
    font-size: 11px;
    color: #818cf8;
    font-weight: 600;
}

/* ===== Users Table Section ===== */
.table-section {
    position: relative;
    z-index: 1;
    animation: slideUpIn 0.5s ease 0.6s both;
}

.table-section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 12px;
}

.table-section-title {
    display: flex;
    align-items: center;
    gap: 12px;
}

.table-section-title h4 {
    font-size: 22px;
    font-weight: 800;
    color: #1e293b;
    margin: 0;
}

.table-section-title .title-icon-wrap {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    background: linear-gradient(135deg, #6366f1, #818cf8);
    box-shadow: 0 4px 15px rgba(99,102,241,0.4);
    display: flex;
    align-items: center;
    justify-content: center;
}

.table-section-title .title-icon-wrap i {
    color: #ffffff;
    font-size: 22px;
}

.view-all-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, #6366f1, #818cf8);
    border: none;
    border-radius: 50px;
    padding: 10px 22px;
    font-size: 14px;
    color: #ffffff !important;
    font-weight: 700;
    transition: all 0.3s;
    text-decoration: none;
    box-shadow: 0 4px 15px rgba(99,102,241,0.35);
}

.view-all-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(99,102,241,0.5);
    text-decoration: none;
    color: #ffffff !important;
}

/* Premium Table */
.premium-table-card {
    background: #1e2235;
    border: 1px solid rgba(99,102,241,0.2);
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
}

.premium-table-card .card-body {
    padding: 0;
}

.premium-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-family: 'Cairo', sans-serif;
}

.premium-table thead th {
    padding: 18px 22px;
    font-size: 14px;
    font-weight: 800;
    color: #94a3b8;
    background: #151829;
    border-bottom: 2px solid rgba(99,102,241,0.25);
    white-space: nowrap;
    letter-spacing: 0.5px;
}

.premium-table tbody tr {
    transition: background 0.2s ease;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}

.premium-table tbody tr:hover {
    background: rgba(99,102,241,0.12);
}

.premium-table tbody tr:hover .name-cell {
    color: #a5b4fc !important;
}

.premium-table tbody td {
    padding: 16px 22px;
    font-size: 15.5px;
    color: #e2e8f0;
    vertical-align: middle;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}

.premium-table tbody tr:last-child td {
    border-bottom: none;
}

.user-avatar-wrap {
    position: relative;
    width: 44px;
    height: 44px;
    display: inline-flex;
}

.user-avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    object-fit: cover;
    border: 2.5px solid #6366f1;
    box-shadow: 0 0 12px rgba(99,102,241,0.4);
    display: block;
}

.row-num-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    border-radius: 10px;
    background: rgba(99,102,241,0.15);
    border: 1px solid rgba(99,102,241,0.3);
    font-size: 14px;
    font-weight: 700;
    color: #818cf8;
}

.date-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(16,185,129,0.12);
    border: 1px solid rgba(16,185,129,0.3);
    border-radius: 8px;
    padding: 5px 14px;
    font-size: 14px;
    color: #34d399;
    font-weight: 600;
}

.name-cell {
    font-weight: 700;
    color: #1e293b;
    font-size: 16px;
}

.email-cell {
    font-size: 14px;
    color: #94a3b8;
    font-weight: 500;
}

/* Table footer header */
.premium-table tfoot th {
    padding: 16px 22px;
    font-size: 13px;
    font-weight: 700;
    color: #64748b;
    background: #151829;
    border-top: 2px solid rgba(99,102,241,0.2);
}

/* Empty state */
.empty-state {
    text-align: center;
    padding: 50px 20px;
    color: #475569;
}

.empty-state i {
    font-size: 48px;
    opacity: 0.3;
    display: block;
    margin-bottom: 12px;
}

/* ===== Divider ===== */
.premium-divider {
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(99,102,241,0.3), transparent);
    margin: 32px 0;
    position: relative;
    z-index: 1;
}

/* ===== Counter animation ===== */
.counter-num {
    display: inline-block;
}

/* ===== Top Active Users Section ===== */
.top-users-section {
    position: relative;
    z-index: 1;
    background: rgba(255, 255, 255, 0.45);
    border: 1px solid rgba(99, 102, 241, 0.12);
    border-radius: 28px;
    padding: 28px;
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    box-shadow: 0 10px 30px rgba(99, 102, 241, 0.03);
    animation: slideUpIn 0.5s ease 0.35s both;
}

.top-users-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 26px;
    flex-wrap: wrap;
    gap: 16px;
}

.top-users-title-wrap {
    display: flex;
    align-items: center;
    gap: 16px;
}

.top-users-icon-wrap {
    width: 54px;
    height: 54px;
    border-radius: 16px;
    background: linear-gradient(135deg, #f59e0b, #fbbf24);
    box-shadow: 0 6px 20px rgba(245,158,11,0.45);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.top-users-icon-wrap i {
    font-size: 28px;
    color: #fff;
}

.top-users-main-title {
    font-size: 19px;
    font-weight: 800;
    color: #1e293b !important;
    margin: 0 0 4px 0;
}

.top-users-subtitle {
    font-size: 13px;
    color: #64748b;
    margin: 0;
}

.top-users-total-badge {
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(245,158,11,0.08);
    border: 1px solid rgba(245,158,11,0.2);
    border-radius: 50px;
    padding: 10px 20px;
    color: #d97706;
    font-size: 13px;
    font-weight: 600;
}

.top-users-total-badge strong {
    font-size: 17px;
    font-weight: 900;
    color: #b45309;
}

/* Grid: 5 cols on wide, 2 on mobile */
.top-users-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 14px;
}

@media (max-width: 1400px) { .top-users-grid { grid-template-columns: repeat(4, 1fr); } }
@media (max-width: 1100px) { .top-users-grid { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 768px)  { .top-users-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 480px)  { .top-users-grid { grid-template-columns: 1fr; } }

/* Individual User Card */
.top-user-card {
    position: relative;
    background: #ffffff;
    border: 1px solid rgba(99, 102, 241, 0.08);
    border-radius: 20px;
    padding: 20px 16px 16px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease, border-color 0.3s ease;
    animation: slideUpIn 0.5s ease both;
    overflow: hidden;
    cursor: default;
    box-shadow: 0 8px 24px rgba(99, 102, 241, 0.04);
}

.top-user-card::before {
    content: '';
    position: absolute;
    inset: 0;
    background: var(--card-gradient);
    opacity: 0;
    border-radius: 20px;
    transition: opacity 0.35s ease;
    z-index: 0;
}

.top-user-card:hover {
    transform: translateY(-8px) scale(1.03);
    box-shadow: 0 20px 50px var(--card-glow);
    border-color: rgba(255,255,255,0.18);
}

.top-user-card:hover::before { opacity: 1; }
.top-user-card:hover .tuc-rank-badge,
.top-user-card:hover .tuc-info,
.top-user-card:hover .tuc-progress-wrap { position: relative; z-index: 1; }

/* Rank Badge */
.tuc-rank-badge {
    position: absolute;
    top: 12px;
    left: 12px;
    display: flex;
    align-items: center;
    gap: 4px;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 3px 9px;
    font-size: 13px;
    font-weight: 800;
    color: #64748b;
    z-index: 2;
    transition: all 0.3s;
}

.tuc-rank-top {
    background: rgba(255,215,0,0.15);
    border-color: rgba(255,215,0,0.3);
    color: #b45309;
}

.top-user-card:hover .tuc-rank-badge {
    background: rgba(255,255,255,0.25) !important;
    border-color: rgba(255,255,255,0.3) !important;
    color: #fff !important;
}

.top-user-card:hover .tuc-rank-badge i {
    color: #fff !important;
}

/* Avatar */
.tuc-avatar-wrap {
    position: relative;
    width: 70px;
    height: 70px;
    margin-top: 8px;
    z-index: 1;
}

.tuc-avatar {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid rgba(99, 102, 241, 0.15);
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    display: block;
    transition: border-color 0.3s;
}

.tuc-avatar-fallback {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background: var(--card-gradient, linear-gradient(135deg, #4f46e5, #6366f1));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    font-weight: 900;
    color: #fff;
    border: 3px solid rgba(255,255,255,0.2);
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    text-shadow: 0 1px 4px rgba(0,0,0,0.2);
}

.tuc-medal-ring {
    position: absolute;
    inset: -4px;
    border-radius: 50%;
    border: 2.5px solid;
    pointer-events: none;
    animation: medalPulse 2.5s ease-in-out infinite;
}

@keyframes medalPulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.06); opacity: 0.7; }
}

.top-user-card:hover .tuc-avatar { border-color: rgba(255,255,255,0.5); }

/* Info */
.tuc-info {
    text-align: center;
    z-index: 2;
    position: relative;
    width: 100%;
}

.tuc-name {
    font-size: 14px;
    font-weight: 700;
    color: #1e293b !important;
    margin-bottom: 4px;
    line-height: 1.4;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    transition: color 0.3s;
    position: relative;
    z-index: 2;
}

.top-user-card:hover .tuc-name { color: #fff !important; }

.tuc-posts-label {
    font-size: 10px;
    color: #64748b !important;
    font-weight: 600;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    margin-bottom: 2px;
    transition: color 0.3s;
    position: relative;
    z-index: 2;
}

.top-user-card:hover .tuc-posts-label { color: rgba(255,255,255,0.7) !important; }

.tuc-count {
    display: flex;
    align-items: baseline;
    justify-content: center;
    gap: 4px;
}

.tuc-count-num {
    font-size: 22px;
    font-weight: 900;
    color: #4f46e5 !important;
    line-height: 1;
    font-feature-settings: "tnum";
    transition: color 0.3s;
    position: relative;
    z-index: 2;
}

.top-user-card:hover .tuc-count-num { color: #fff !important; }

.tuc-count-unit {
    font-size: 11px;
    color: #64748b !important;
    font-weight: 600;
    transition: color 0.3s;
    position: relative;
    z-index: 2;
}

.top-user-card:hover .tuc-count-unit { color: rgba(255,255,255,0.7) !important; }

/* Progress Bar */
.tuc-progress-wrap {
    width: 100%;
    height: 4px;
    background: rgba(0, 0, 0, 0.05);
    border-radius: 4px;
    overflow: hidden;
    z-index: 1;
    transition: background 0.3s;
}

.top-user-card:hover .tuc-progress-wrap {
    background: rgba(255, 255, 255, 0.2);
}

.tuc-progress-bar {
    height: 100%;
    background: var(--card-gradient, linear-gradient(90deg, #6366f1, #818cf8));
    border-radius: 4px;
    animation: growBar 1.5s ease both;
    animation-delay: inherit;
    box-shadow: 0 0 8px rgba(99,102,241,0.2);
    min-width: 4px;
    transition: background 0.3s, box-shadow 0.3s;
}

.top-user-card:hover .tuc-progress-bar {
    background: #ffffff !important;
    box-shadow: 0 0 8px rgba(255, 255, 255, 0.8) !important;
}

/* Empty State */
.top-users-empty {
    grid-column: 1 / -1;
    text-align: center;
    padding: 50px 20px;
    color: #475569;
}

/* ===== New Premium Statistics Panel ===== */
.stats-distribution-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

@media (max-width: 992px) {
    .stats-distribution-grid {
        grid-template-columns: 1fr;
    }
}

.dist-bar-item {
    background: rgba(255, 255, 255, 0.65);
    border: 1px solid rgba(99, 102, 241, 0.08);
    border-radius: 16px;
    padding: 16px 20px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    transition: all 0.3s ease;
}

.dist-bar-item:hover {
    background: #ffffff;
    border-color: rgba(99, 102, 241, 0.2);
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(99, 102, 241, 0.05);
}

.dist-bar-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.dist-bar-icon-title {
    display: flex;
    align-items: center;
    gap: 12px;
}

.dist-bar-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}

.dist-bar-title {
    font-size: 14.5px;
    font-weight: 700;
    color: #1e293b;
}

.dist-bar-value-percentage {
    display: flex;
    align-items: center;
    gap: 8px;
}

.dist-bar-value {
    font-size: 16px;
    font-weight: 800;
    color: #0f172a;
}

.dist-bar-percentage {
    font-size: 12.5px;
    font-weight: 700;
    color: #4f46e5;
    background: rgba(99, 102, 241, 0.08);
    padding: 2px 8px;
    border-radius: 6px;
}

.dist-bar-progress-container {
    height: 8px;
    border-radius: 4px;
    background: rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

.dist-bar-progress-fill {
    height: 100%;
    border-radius: 4px;
    width: 0;
    transition: width 1.5s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Colors for the 8 elements */
.bg-indigo-light { background: rgba(99, 102, 241, 0.1); color: #6366f1; }
.bg-sky-light    { background: rgba(14, 165, 233, 0.1); color: #0ea5e9; }
.bg-emerald-light{ background: rgba(16, 185, 129, 0.1); color: #10b981; }
.bg-violet-light { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; }
.bg-amber-light  { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
.bg-rose-light   { background: rgba(244, 63, 94, 0.1); color: #f43f5e; }
.bg-teal-light   { background: rgba(20, 184, 166, 0.1); color: #14b8a6; }
.bg-orange-light { background: rgba(249, 115, 22, 0.1); color: #f97316; }

/* Bar Fills */
.fill-indigo   { background: linear-gradient(90deg, #818cf8, #6366f1); }
.fill-sky      { background: linear-gradient(90deg, #38bdf8, #0ea5e9); }
.fill-emerald  { background: linear-gradient(90deg, #34d399, #10b981); }
.fill-violet   { background: linear-gradient(90deg, #a78bfa, #8b5cf6); }
.fill-amber    { background: linear-gradient(90deg, #fbbf24, #f59e0b); }
.fill-rose     { background: linear-gradient(90deg, #fb7185, #f43f5e); }
.fill-teal     { background: linear-gradient(90deg, #2dd4bf, #14b8a6); }
.fill-orange   { background: linear-gradient(90deg, #fb923c, #f97316); }
</style>

<!-- Background Orbs -->
<div class="bg-orbs">
    <div class="bg-orb bg-orb-1"></div>
    <div class="bg-orb bg-orb-2"></div>
    <div class="bg-orb bg-orb-3"></div>
</div>

<div class="dashboard-wrapper">

    <!-- ===== Dashboard Header ===== -->
    <div class="dash-header">
        <div class="dash-header-title">
            <h1>لوحة <span>التحكم</span></h1>
            <p>مرحباً بك في لوحة إدارة حكماء العالم • نظرة شاملة على الإحصائيات</p>
        </div>
        <div class="dash-date-badge">
            <i class='bx bx-calendar'></i>
            <span id="current-date">{{ now()->translatedFormat('l، d F Y') }}</span>
        </div>
    </div>

    <!-- ===== Section Label ===== -->
    <div class="section-label mb-3">
        <div class="section-label-dot"></div>
        <span>الإحصائيات العامة — الصف الأول</span>
    </div>

    <!-- ===== Stats Row 1 ===== -->
    <div class="stats-grid mb-4">

        <!-- Card: Posts -->
        <a href="{{ route('all.posts') }}" class="stat-card card-indigo">
            <div class="stat-card-content">
                <div class="stat-card-info">
                    <div class="stat-icon-wrap stat-icon-bg-indigo">
                        <i class='bx bx-detail ic-indigo'></i>
                    </div>
                    <div class="stat-number counter-num" data-target="{{ $postsCount }}">0</div>
                    <div class="stat-label">عدد المواضيع</div>
                </div>
                <div class="stat-arrow">
                    <i class='bx bx-chevron-left'></i>
                </div>
            </div>
            <div class="stat-progress">
                <div class="stat-progress-bar bar-indigo" style="width: 85%"></div>
            </div>
        </a>

        <!-- Card: Users -->
        <a href="{{ route('all.users') }}" class="stat-card card-sky">
            <div class="stat-card-content">
                <div class="stat-card-info">
                    <div class="stat-icon-wrap stat-icon-bg-sky">
                        <i class='bx bx-user ic-sky'></i>
                    </div>
                    <div class="stat-number counter-num" data-target="{{ $usersCount }}">0</div>
                    <div class="stat-label">عدد المستخدمين</div>
                </div>
                <div class="stat-arrow">
                    <i class='bx bx-chevron-left'></i>
                </div>
            </div>
            <div class="stat-progress">
                <div class="stat-progress-bar bar-sky" style="width: 92%"></div>
            </div>
        </a>

        <!-- Card: Group Sites -->
        <a href="{{ route('all.group_sites') }}" class="stat-card card-emerald">
            <div class="stat-card-content">
                <div class="stat-card-info">
                    <div class="stat-icon-wrap stat-icon-bg-emerald">
                        <i class='bx bx-globe ic-emerald'></i>
                    </div>
                    <div class="stat-number counter-num" data-target="{{ $groupSitesCount }}">0</div>
                    <div class="stat-label">المجموعات العامة والخاصة</div>
                </div>
                <div class="stat-arrow">
                    <i class='bx bx-chevron-left'></i>
                </div>
            </div>
            <div class="stat-progress">
                <div class="stat-progress-bar bar-emerald" style="width: 70%"></div>
            </div>
        </a>

        <!-- Card: Languages -->
        <a href="{{ route('all.languages') }}" class="stat-card card-violet">
            <div class="stat-card-content">
                <div class="stat-card-info">
                    <div class="stat-icon-wrap stat-icon-bg-violet">
                        <i class='bx bx-font ic-violet'></i>
                    </div>
                    <div class="stat-number counter-num" data-target="{{ $languagesCount }}">0</div>
                    <div class="stat-label">عدد اللغات المتاحة</div>
                </div>
                <div class="stat-arrow">
                    <i class='bx bx-chevron-left'></i>
                </div>
            </div>
            <div class="stat-progress">
                <div class="stat-progress-bar bar-violet" style="width: 60%"></div>
            </div>
        </a>

    </div>

    <!-- ===== Section Label ===== -->
    <div class="section-label mb-3">
        <div class="section-label-dot"></div>
        <span>الإحصائيات العامة — الصف الثاني</span>
    </div>

    <!-- ===== Stats Row 2 ===== -->
    <div class="stats-grid mb-4">

        <!-- Card: Stories -->
        <a href="{{ route('all.stories') }}" class="stat-card card-amber">
            <div class="stat-card-content">
                <div class="stat-card-info">
                    <div class="stat-icon-wrap stat-icon-bg-amber">
                        <i class='bx bx-images ic-amber'></i>
                    </div>
                    <div class="stat-number counter-num" data-target="{{ $storiesCount }}">0</div>
                    <div class="stat-label">عدد القصص (Stories)</div>
                </div>
                <div class="stat-arrow">
                    <i class='bx bx-chevron-left'></i>
                </div>
            </div>
            <div class="stat-progress">
                <div class="stat-progress-bar bar-amber" style="width: 78%"></div>
            </div>
        </a>

        <!-- Card: Rankings -->
        <a href="{{ route('all.rankings') }}" class="stat-card card-rose">
            <div class="stat-card-content">
                <div class="stat-card-info">
                    <div class="stat-icon-wrap stat-icon-bg-rose">
                        <i class='bx bx-award ic-rose'></i>
                    </div>
                    <div class="stat-number counter-num" data-target="{{ $rankingsCount }}">0</div>
                    <div class="stat-label">عدد الرتب والمستويات</div>
                </div>
                <div class="stat-arrow">
                    <i class='bx bx-chevron-left'></i>
                </div>
            </div>
            <div class="stat-progress">
                <div class="stat-progress-bar bar-rose" style="width: 65%"></div>
            </div>
        </a>

        <!-- Card: Wise Committee -->
        <a href="{{ route('admin.wise_committees.index') }}" class="stat-card card-teal">
            <div class="stat-card-content">
                <div class="stat-card-info">
                    <div class="stat-icon-wrap stat-icon-bg-teal">
                        <i class='bx bxs-user-detail ic-teal'></i>
                    </div>
                    <div class="stat-number counter-num" data-target="{{ $wiseCommitteeCount }}">0</div>
                    <div class="stat-label">أعضاء لجنة الحكماء</div>
                </div>
                <div class="stat-arrow">
                    <i class='bx bx-chevron-left'></i>
                </div>
            </div>
            <div class="stat-progress">
                <div class="stat-progress-bar bar-teal" style="width: 55%"></div>
            </div>
        </a>

        <!-- Card: Support Tickets -->
        <a href="{{ route('admin.support_tickets.index') }}" class="stat-card card-orange">
            <div class="stat-card-content">
                <div class="stat-card-info">
                    <div class="stat-icon-wrap stat-icon-bg-orange">
                        <i class='bx bx-message-square-detail ic-orange'></i>
                    </div>
                    <div class="stat-number counter-num" data-target="{{ $supportTicketsCount }}">0</div>
                    <div class="stat-label">رسائل التواصل مع المستخدمين</div>
                </div>
                <div class="stat-arrow">
                    <i class='bx bx-chevron-left'></i>
                </div>
            </div>
            <div class="stat-progress">
                <div class="stat-progress-bar bar-orange" style="width: 40%"></div>
            </div>
        </a>

    </div>

    <!-- ===== Top 10 Most Active Users Section ===== -->
    <div class="section-label mb-3 mt-2">
        <div class="section-label-dot"></div>
        <span>أبرز المساهمين — أكثر المستخدمين مشاركة</span>
    </div>

    <div class="top-users-section mb-4">
        <div class="top-users-header">
            <div class="top-users-title-wrap">
                <div class="top-users-icon-wrap">
                    <i class='bx bxs-crown'></i>
                </div>
                <div>
                    <h3 class="top-users-main-title">أبرز 10 مستخدمين في منصة حكماء العالم</h3>
                    <p class="top-users-subtitle">مرتبون حسب إجمالي عدد المشاركات والمواضيع المنشورة</p>
                </div>
            </div>
            <div class="top-users-total-badge">
                <i class='bx bx-bar-chart-alt-2'></i>
                <span>إجمالي المساهمين</span>
                <strong>{{ number_format($topActiveUsers->sum('posts_count')) }}</strong>
            </div>
        </div>

        <div class="top-users-grid">
            @foreach($topActiveUsers as $index => $activeUser)
            @php
                $rank = $index + 1;
                $maxCount = $topActiveUsers->first()->posts_count;
                $percentage = $maxCount > 0 ? round(($activeUser->posts_count / $maxCount) * 100) : 0;
                $medalColors = ['#FFD700', '#C0C0C0', '#CD7F32'];
                $cardGradients = [
                    'linear-gradient(135deg, #4f46e5 0%, #6366f1 100%)',
                    'linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%)',
                    'linear-gradient(135deg, #059669 0%, #10b981 100%)',
                    'linear-gradient(135deg, #7c3aed 0%, #8b5cf6 100%)',
                    'linear-gradient(135deg, #d97706 0%, #f59e0b 100%)',
                    'linear-gradient(135deg, #be123c 0%, #f43f5e 100%)',
                    'linear-gradient(135deg, #0d9488 0%, #14b8a6 100%)',
                    'linear-gradient(135deg, #c2410c 0%, #f97316 100%)',
                    'linear-gradient(135deg, #4338ca 0%, #6366f1 100%)',
                    'linear-gradient(135deg, #0e7490 0%, #06b6d4 100%)',
                ];
                $glowColors = [
                    'rgba(99,102,241,0.5)','rgba(14,165,233,0.5)','rgba(16,185,129,0.5)',
                    'rgba(139,92,246,0.5)','rgba(245,158,11,0.5)','rgba(244,63,94,0.5)',
                    'rgba(20,184,166,0.5)','rgba(249,115,22,0.5)','rgba(99,102,241,0.4)',
                    'rgba(6,182,212,0.5)'
                ];
                $gradient = $cardGradients[$index] ?? $cardGradients[0];
                $glow = $glowColors[$index] ?? $glowColors[0];
            @endphp
            <div class="top-user-card" style="--card-gradient: {{ $gradient }}; --card-glow: {{ $glow }}; animation-delay: {{ $index * 0.06 }}s;">
                <!-- Rank Badge -->
                <div class="tuc-rank-badge {{ $rank <= 3 ? 'tuc-rank-top' : '' }}">
                    @if($rank <= 3)
                        <i class='bx bxs-crown' style="color: {{ $medalColors[$rank-1] }}; font-size: 13px;"></i>
                    @endif
                    <span>{{ $rank }}</span>
                </div>

                <!-- Avatar -->
                <div class="tuc-avatar-wrap">
                    @if($activeUser->profile_picture)
                        <img src="{{ (!empty($activeUser->profile_picture) && $activeUser->profile_picture != 'non') ? 'http://localhost:8888/new_wiselook/uploads/'.basename($activeUser->profile_picture) : url('upload/no_image.jpg') }}" alt="{{ $activeUser->first_name }}" class="tuc-avatar" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="tuc-avatar-fallback" style="display:none;">
                            {{ mb_substr($activeUser->first_name, 0, 1) }}
                        </div>
                    @else
                        <div class="tuc-avatar-fallback">
                            {{ mb_substr($activeUser->first_name, 0, 1) }}
                        </div>
                    @endif
                    @if($rank <= 3)
                    <div class="tuc-medal-ring" style="border-color: {{ $medalColors[$rank-1] }}; box-shadow: 0 0 12px {{ $medalColors[$rank-1] }}80;"></div>
                    @endif
                </div>

                <!-- Info -->
                <div class="tuc-info">
                    <div class="tuc-name">{{ $activeUser->first_name }} {{ $activeUser->last_name }}</div>
                    <div class="tuc-posts-label">إجمالي المشاركات</div>
                    <div class="tuc-count">
                        <span class="tuc-count-num">{{ number_format($activeUser->posts_count) }}</span>
                        <span class="tuc-count-unit">موضوع</span>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="tuc-progress-wrap">
                    <div class="tuc-progress-bar" style="width: {{ $percentage }}%;"></div>
                </div>
            </div>
            @endforeach

            @if($topActiveUsers->isEmpty())
            <div class="top-users-empty">
                <i class='bx bx-user-x'></i>
                <p>لا توجد بيانات كافية لعرض المستخدمين الأكثر نشاطاً</p>
            </div>
            @endif
        </div>
    </div>

    <!-- ===== Top Affiliate Card (Full Row) ===== -->
    <div class="mb-4">
        <!-- Top Affiliate Card -->
        <a href="{{ route('all.affiliate_trackings') }}" class="affiliate-card">
            <div>
                <div class="aff-badge">
                    <i class='bx bxs-star'></i>
                    أفضل مسوّق
                </div>
                <p class="aff-title">المسوق المتميز لهذا الشهر</p>
                <p class="aff-subtitle">العضو الأكثر جلب للمستخدمين من خلال Affiliate</p>
                <h2 class="aff-name">{{ $topAffiliateUser }}</h2>
            </div>
            <div class="aff-footer">
                <div>
                    <div class="aff-count-label">المستخدمين المسجلين عبره</div>
                    <div class="aff-count-value">{{ $topAffiliateCount }} <small style="font-size:13px; color:rgba(192,132,252,0.6);">عضو</small></div>
                </div>
                <span class="aff-btn">
                    <i class='bx bx-list-ul'></i>
                    سجل الإحالات
                </span>
            </div>
        </a>
    </div>

    <!-- ===== Premium Statistics Panel (Full Row) ===== -->
    @php
        $totalStatsSum = $postsCount + $usersCount + $groupSitesCount + $languagesCount + $storiesCount + $rankingsCount + $wiseCommitteeCount + $supportTicketsCount;
        $totalStatsSum = max(1, $totalStatsSum); // avoid division by zero
        
        $postsPercent = round(($postsCount / $totalStatsSum) * 100, 1);
        $usersPercent = round(($usersCount / $totalStatsSum) * 100, 1);
        $groupsPercent = round(($groupSitesCount / $totalStatsSum) * 100, 1);
        $languagesPercent = round(($languagesCount / $totalStatsSum) * 100, 1);
        $storiesPercent = round(($storiesCount / $totalStatsSum) * 100, 1);
        $rankingsPercent = round(($rankingsCount / $totalStatsSum) * 100, 1);
        $wiseCommitteePercent = round(($wiseCommitteeCount / $totalStatsSum) * 100, 1);
        $supportTicketsPercent = round(($supportTicketsCount / $totalStatsSum) * 100, 1);
    @endphp

    <div class="mb-4" style="background: rgba(255, 255, 255, 0.45); border: 1px solid rgba(99, 102, 241, 0.12); border-radius: 28px; backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); box-shadow: 0 10px 30px rgba(99, 102, 241, 0.03);">
        <div class="p-4">
            <h5 class="mb-2" style="font-size: 18px; font-weight: 800; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                <i class='bx bx-analyse text-primary' style="font-size: 24px;"></i>
                توزيع إحصائيات حكماء العالم الشاملة
            </h5>
            <p style="color: #64748b; font-size: 13.5px; margin-bottom: 24px;">يوضح هذا القسم نسبة توزع ومساهمة كل عنصر من عناصر المنصة مقارنة بالإجمالي الكلي للنشاط (إجمالي العناصر: {{ number_format($totalStatsSum) }}).</p>
            
            <div class="stats-distribution-grid">
                
                <!-- Bar 1: Posts -->
                <div class="dist-bar-item">
                    <div class="dist-bar-header">
                        <div class="dist-bar-icon-title">
                            <div class="dist-bar-icon bg-indigo-light">
                                <i class='bx bx-detail'></i>
                            </div>
                            <span class="dist-bar-title">المواضيع والمنشورات</span>
                        </div>
                        <div class="dist-bar-value-percentage">
                            <span class="dist-bar-value">{{ number_format($postsCount) }}</span>
                            <span class="dist-bar-percentage">{{ $postsPercent }}%</span>
                        </div>
                    </div>
                    <div class="dist-bar-progress-container">
                        <div class="dist-bar-progress-fill fill-indigo" style="width: {{ $postsPercent }}%;"></div>
                    </div>
                </div>
                
                <!-- Bar 2: Users -->
                <div class="dist-bar-item">
                    <div class="dist-bar-header">
                        <div class="dist-bar-icon-title">
                            <div class="dist-bar-icon bg-sky-light">
                                <i class='bx bx-user'></i>
                            </div>
                            <span class="dist-bar-title">إجمالي المستخدمين</span>
                        </div>
                        <div class="dist-bar-value-percentage">
                            <span class="dist-bar-value">{{ number_format($usersCount) }}</span>
                            <span class="dist-bar-percentage">{{ $usersPercent }}%</span>
                        </div>
                    </div>
                    <div class="dist-bar-progress-container">
                        <div class="dist-bar-progress-fill fill-sky" style="width: {{ $usersPercent }}%;"></div>
                    </div>
                </div>
                
                <!-- Bar 3: Groups -->
                <div class="dist-bar-item">
                    <div class="dist-bar-header">
                        <div class="dist-bar-icon-title">
                            <div class="dist-bar-icon bg-emerald-light">
                                <i class='bx bx-globe'></i>
                            </div>
                            <span class="dist-bar-title">المجموعات العامة والخاصة</span>
                        </div>
                        <div class="dist-bar-value-percentage">
                            <span class="dist-bar-value">{{ number_format($groupSitesCount) }}</span>
                            <span class="dist-bar-percentage">{{ $groupsPercent }}%</span>
                        </div>
                    </div>
                    <div class="dist-bar-progress-container">
                        <div class="dist-bar-progress-fill fill-emerald" style="width: {{ $groupsPercent }}%;"></div>
                    </div>
                </div>
                
                <!-- Bar 4: Languages -->
                <div class="dist-bar-item">
                    <div class="dist-bar-header">
                        <div class="dist-bar-icon-title">
                            <div class="dist-bar-icon bg-violet-light">
                                <i class='bx bx-font'></i>
                            </div>
                            <span class="dist-bar-title">اللغات المتاحة</span>
                        </div>
                        <div class="dist-bar-value-percentage">
                            <span class="dist-bar-value">{{ number_format($languagesCount) }}</span>
                            <span class="dist-bar-percentage">{{ $languagesPercent }}%</span>
                        </div>
                    </div>
                    <div class="dist-bar-progress-container">
                        <div class="dist-bar-progress-fill fill-violet" style="width: {{ $languagesPercent }}%;"></div>
                    </div>
                </div>
                
                <!-- Bar 5: Stories -->
                <div class="dist-bar-item">
                    <div class="dist-bar-header">
                        <div class="dist-bar-icon-title">
                            <div class="dist-bar-icon bg-amber-light">
                                <i class='bx bx-images'></i>
                            </div>
                            <span class="dist-bar-title">القصص المضافة (Stories)</span>
                        </div>
                        <div class="dist-bar-value-percentage">
                            <span class="dist-bar-value">{{ number_format($storiesCount) }}</span>
                            <span class="dist-bar-percentage">{{ $storiesPercent }}%</span>
                        </div>
                    </div>
                    <div class="dist-bar-progress-container">
                        <div class="dist-bar-progress-fill fill-amber" style="width: {{ $storiesPercent }}%;"></div>
                    </div>
                </div>
                
                <!-- Bar 6: Rankings -->
                <div class="dist-bar-item">
                    <div class="dist-bar-header">
                        <div class="dist-bar-icon-title">
                            <div class="dist-bar-icon bg-rose-light">
                                <i class='bx bx-award'></i>
                            </div>
                            <span class="dist-bar-title">الرتب والمستويات</span>
                        </div>
                        <div class="dist-bar-value-percentage">
                            <span class="dist-bar-value">{{ number_format($rankingsCount) }}</span>
                            <span class="dist-bar-percentage">{{ $rankingsPercent }}%</span>
                        </div>
                    </div>
                    <div class="dist-bar-progress-container">
                        <div class="dist-bar-progress-fill fill-rose" style="width: {{ $rankingsPercent }}%;"></div>
                    </div>
                </div>
                
                <!-- Bar 7: Wise Committee -->
                <div class="dist-bar-item">
                    <div class="dist-bar-header">
                        <div class="dist-bar-icon-title">
                            <div class="dist-bar-icon bg-teal-light">
                                <i class='bx bxs-user-detail'></i>
                            </div>
                            <span class="dist-bar-title">لجنة الحكماء</span>
                        </div>
                        <div class="dist-bar-value-percentage">
                            <span class="dist-bar-value">{{ number_format($wiseCommitteeCount) }}</span>
                            <span class="dist-bar-percentage">{{ $wiseCommitteePercent }}%</span>
                        </div>
                    </div>
                    <div class="dist-bar-progress-container">
                        <div class="dist-bar-progress-fill fill-teal" style="width: {{ $wiseCommitteePercent }}%;"></div>
                    </div>
                </div>
                
                <!-- Bar 8: Support Tickets -->
                <div class="dist-bar-item">
                    <div class="dist-bar-header">
                        <div class="dist-bar-icon-title">
                            <div class="dist-bar-icon bg-orange-light">
                                <i class='bx bx-message-square-detail'></i>
                            </div>
                            <span class="dist-bar-title">رسائل تواصل المستخدمين</span>
                        </div>
                        <div class="dist-bar-value-percentage">
                            <span class="dist-bar-value">{{ number_format($supportTicketsCount) }}</span>
                            <span class="dist-bar-percentage">{{ $supportTicketsPercent }}%</span>
                        </div>
                    </div>
                    <div class="dist-bar-progress-container">
                        <div class="dist-bar-progress-fill fill-orange" style="width: {{ $supportTicketsPercent }}%;"></div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>

    <!-- ===== Divider ===== -->
    <div class="premium-divider"></div>

    <!-- ===== Recent Users Table ===== -->
    <div class="table-section">
        <div class="table-section-header">
            <div class="table-section-title">
                <div class="title-icon-wrap">
                    <i class='bx bx-user-plus'></i>
                </div>
                <h4>أحدث الأعضاء المسجلين</h4>
            </div>
            <a href="{{ route('all.users') }}" class="view-all-btn">
                <i class='bx bx-link-external'></i>
                عرض الكل
            </a>
        </div>

        <div class="premium-table-card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example" class="premium-table">
                        <thead>
                            <tr>
                                <th style="width: 60px; text-align: center;">#</th>
                                <th>الاسم الأول</th>
                                <th>اسم العائلة</th>
                                <th>البريد الإلكتروني</th>
                                <th>تاريخ التسجيل</th>
                                <th style="text-align: center; width: 80px;">الصورة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $key => $item)
                            <tr>
                                <td style="text-align: center;">
                                    <span class="row-num-badge">{{ $key+1 }}</span>
                                </td>
                                <td class="name-cell">{{ $item->first_name }}</td>
                                <td class="name-cell">{{ $item->last_name }}</td>
                                <td class="email-cell">{{ $item->email }}</td>
                                <td>
                                    <span class="date-badge">
                                        <i class='bx bx-time-five' style="font-size: 12px;"></i>
                                        {{ $item->created_at ? $item->created_at->diffForHumans() : 'لم يتم التحديد' }}
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <div class="user-avatar-wrap">
                                        <img class="user-avatar"
                                             src="{{ (!empty($item->photo) && $item->photo != 'non') ? 'http://localhost:8888/new_wiselook/uploads/'.$item->photo : url('upload/no_image.jpg') }}"
                                            loading="lazy"
                                            alt="{{ $item->first_name }}">
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <i class='bx bx-user-x'></i>
                                        <p>لا يوجد مستخدمون مسجلون حالياً</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <th style="text-align: center;">#</th>
                                <th>الاسم الأول</th>
                                <th>اسم العائلة</th>
                                <th>البريد الإلكتروني</th>
                                <th>تاريخ التسجيل</th>
                                <th style="text-align: center;">الصورة</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div><!-- end dashboard-wrapper -->



<!-- Counter Animation Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {

    /* ===== Animated Counters ===== */
    const counters = document.querySelectorAll('.counter-num');

    const animateCounter = (el) => {
        const target = parseInt(el.getAttribute('data-target')) || 0;
        const duration = 1400;
        const start = performance.now();

        const update = (now) => {
            const elapsed = now - start;
            const progress = Math.min(elapsed / duration, 1);
            // Ease out cubic
            const eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = Math.floor(eased * target).toLocaleString('ar');
            if (progress < 1) requestAnimationFrame(update);
            else el.textContent = target.toLocaleString('ar');
        };

        requestAnimationFrame(update);
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.3 });

    counters.forEach(c => observer.observe(c));

    /* ===== Live Date ===== */
    const dateEl = document.getElementById('current-date');
    if (dateEl) {
        const now = new Date();
        const opts = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        try {
            dateEl.textContent = now.toLocaleDateString('ar-SA', opts);
        } catch(e) {}
    }

});
</script>

@endsection
