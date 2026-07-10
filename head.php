:root {
    --clinix-primary: #006B3F;
    --clinix-primary-dark: #014421;
    --clinix-gold: #F4C430;
    --clinix-red: #CD5C5C;
    --clinix-bg: #F8FAFC;
    --clinix-radius: 14px;
}

body {
    background: var(--clinix-bg);
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    color: #1f2d27;
}

/* ---------- Navbar / Sidebar ---------- */
.clinix-navbar {
    background: var(--clinix-primary);
    box-shadow: 0 2px 10px rgba(0,0,0,.08);
}
.clinix-navbar .navbar-brand {
    font-weight: 700;
    letter-spacing: .5px;
    color: #fff !important;
}
.clinix-navbar .nav-link {
    color: rgba(255,255,255,.85) !important;
}
.clinix-navbar .nav-link:hover,
.clinix-navbar .nav-link.active {
    color: #fff !important;
    font-weight: 600;
}

.clinix-sidebar {
    background: var(--clinix-primary-dark);
    min-height: 100vh;
    padding-top: 1.5rem;
}
.clinix-sidebar .brand {
    color: #fff;
    font-weight: 700;
    font-size: 1.25rem;
    padding: 0 1.25rem 1.5rem;
    display: flex;
    align-items: center;
    gap: .5rem;
}
.clinix-sidebar .nav-link {
    color: rgba(255,255,255,.75);
    padding: .65rem 1.25rem;
    border-left: 4px solid transparent;
    font-weight: 500;
}
.clinix-sidebar .nav-link i { width: 20px; margin-right: 8px; }
.clinix-sidebar .nav-link:hover {
    background: rgba(255,255,255,.06);
    color: #fff;
}
.clinix-sidebar .nav-link.active {
    background: rgba(244,196,48,.12);
    border-left-color: var(--clinix-gold);
    color: #fff;
}

/* ---------- Cards / Buttons ---------- */
.card {
    border: none;
    border-radius: var(--clinix-radius);
    box-shadow: 0 4px 18px rgba(0,50,30,.06);
}
.card-header {
    background: transparent;
    border-bottom: 1px solid rgba(0,0,0,.05);
    font-weight: 600;
}

.btn-clinix {
    background: var(--clinix-primary);
    border-color: var(--clinix-primary);
    color: #fff;
    font-weight: 600;
}
.btn-clinix:hover {
    background: var(--clinix-primary-dark);
    border-color: var(--clinix-primary-dark);
    color: #fff;
}
.btn-gold {
    background: var(--clinix-gold);
    border-color: var(--clinix-gold);
    color: #3a2c00;
    font-weight: 700;
}
.btn-gold:hover {
    background: #e0b520;
    border-color: #e0b520;
    color: #3a2c00;
}
.btn-outline-clinix {
    border: 1.5px solid var(--clinix-primary);
    color: var(--clinix-primary);
    font-weight: 600;
    background: transparent;
}
.btn-outline-clinix:hover {
    background: var(--clinix-primary);
    color: #fff;
}

/* ---------- Auth pages ---------- */
.auth-wrapper {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--clinix-bg);
    padding: 2rem 1rem;
}
.auth-card {
    max-width: 460px;
    width: 100%;
    border-radius: 18px;
    overflow: hidden;
}
.auth-card .auth-header {
    background: linear-gradient(135deg, var(--clinix-primary), var(--clinix-primary-dark));
    color: #fff;
    padding: 2rem 2rem 1.25rem;
    text-align: center;
}
.auth-card .auth-header .logo-badge {
    width: 56px; height: 56px;
    background: var(--clinix-gold);
    border-radius: 50%;
    display: flex; align-items:center; justify-content:center;
    margin: 0 auto .75rem;
    font-size: 1.5rem;
    color: var(--clinix-primary-dark);
    font-weight: 800;
}
.auth-card .auth-body { padding: 2rem; background:#fff; }

/* ---------- Status badges ---------- */
.badge-pending   { background: #fff3cd; color: #856404; }
.badge-confirmed { background: rgba(0,107,63,.12); color: var(--clinix-primary); }
.badge-cancelled { background: rgba(205,92,92,.12); color: var(--clinix-red); }
.badge-completed { background: #e2e3e5; color: #383d41; }

/* ---------- Stat cards on dashboards ---------- */
.stat-card {
    border-radius: var(--clinix-radius);
    padding: 1.25rem 1.5rem;
    color: #fff;
    background: var(--clinix-primary);
}
.stat-card.gold { background: linear-gradient(135deg,#F4C430,#e0b520); color:#3a2c00; }
.stat-card.dark { background: var(--clinix-primary-dark); }
.stat-card.red  { background: var(--clinix-red); }
.stat-card .stat-num { font-size: 2rem; font-weight: 700; }
.stat-card .stat-icon { font-size: 1.8rem; opacity:.85; }

/* Calendar-ish slot buttons for booking */
.slot-btn {
    border: 1.5px solid var(--clinix-primary);
    color: var(--clinix-primary);
    background: #fff;
    border-radius: 10px;
    padding: .5rem .25rem;
    font-weight: 600;
    font-size: .9rem;
}
.slot-btn.taken {
    border-color: #ccc;
    color: #aaa;
    background: #f1f1f1;
    pointer-events: none;
}
.slot-btn:hover:not(.taken) {
    background: var(--clinix-primary);
    color: #fff;
}
.slot-btn.selected {
    background: var(--clinix-gold);
    border-color: var(--clinix-gold);
    color: #3a2c00;
}

.section-title {
    color: var(--clinix-primary-dark);
    font-weight: 700;
}

footer.clinix-footer {
    background: var(--clinix-primary-dark);
    color: rgba(255,255,255,.75);
    padding: 1rem 0;
    font-size: .875rem;
}
