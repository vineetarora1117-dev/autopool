<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php $env = parse_ini_file(__DIR__ . '/../.env'); echo ($env['SITE_NAME'] ?? 'SAPG') . ' - User Panel'; ?></title>
    <!-- SweetAlert2 CSS & JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Icons के लिए Font Awesome CSS लिंक -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            overflow: hidden;
            background-color: #030b14;
            color: #fff;
            display: flex;
            flex-direction: column;
            height: 100vh;
            height: 100dvh;
            width: 100vw;
        }

        /* 3D Canvas Background */
        #canvas-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: 1;
        }

        /* --- 1. Top Navbar --- */
        .top-navbar {
            height: 55px;
            width: 100%;
            background: #bfa100;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            z-index: 10;
            position: relative;
        }

        .top-left {
            display: flex;
            align-items: center;
        }

        .toggle-menu-btn {
            background: none;
            border: none;
            color: #fff;
            font-size: 20px;
            cursor: pointer;
        }

        .top-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .lang-select {
            background: #fff;
            color: #000;
            border: 1px solid #ccc;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 13px;
            outline: none;
            cursor: pointer;
        }

        .fullscreen-btn {
            background: none;
            border: none;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
        }

        .user-profile-wrapper {
            position: relative;
        }

        .user-profile-menu {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 4px;
            transition: background 0.2s;
        }

        .user-profile-menu:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .user-avatar {
            width: 30px;
            height: 30px;
            background: #005f73;
            border: 2px solid #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 14px;
        }

        .user-name {
            font-size: 14px;
            font-weight: 600;
            color: #ffb703;
        }

        .profile-dropdown {
            position: absolute;
            top: 45px;
            right: 0;
            width: 200px;
            background: #ffffff;
            border-radius: 6px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            display: none;
            flex-direction: column;
            padding: 8px 0;
            z-index: 100;
            pointer-events: auto;
        }

        .profile-dropdown::before {
            content: '';
            position: absolute;
            top: -6px;
            right: 15px;
            width: 12px;
            height: 12px;
            background: #ffffff;
            transform: rotate(45deg);
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 16px;
            color: #555555;
            text-decoration: none;
            font-size: 14px;
            transition: background 0.2s, color 0.2s;
            cursor: pointer;
            position: relative;
            background: #fff;
        }

        .dropdown-item i {
            font-size: 15px;
            width: 18px;
            color: #777777;
        }

        .dropdown-item:hover {
            background: #f5f6f8;
            color: #000;
        }

        .profile-dropdown.show {
            display: flex;
        }

        /* --- Main Layout --- */
        .content-layout {
            display: flex;
            flex: 1;
            width: 100%;
            height: calc(100vh - 55px);
            height: calc(100dvh - 55px);
            position: relative;
            z-index: 2;
            pointer-events: none;
        }

        /* --- 2. Sidebar Menu --- */
        sidebar {
            width: 260px;
            height: 100%;
            background: rgba(6, 17, 33, 0.75);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-right: 1px solid rgba(255, 183, 3, 0.15);
            padding: 25px 15px;
            display: flex;
            flex-direction: column;
            pointer-events: auto;
            flex-shrink: 0;
            overflow-y: auto;
            transition: margin-left 0.3s ease;
        }

        sidebar.sidebar-hidden {
            margin-left: -260px;
        }

        .sidebar-backdrop {
            display: none;
        }

        @media (max-width: 900px) {
            sidebar {
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                height: 100vh;
                width: 260px;
                z-index: 200;
                margin-left: -280px;
                background: #060f1a;
                box-shadow: 4px 0 25px rgba(0,0,0,0.5);
                padding-top: 20px;
            }
            sidebar.sidebar-hidden {
                margin-left: -280px;
            }
            sidebar.sidebar-open {
                margin-left: 0;
            }
            .sidebar-backdrop.show {
                display: block;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.55);
                z-index: 150;
                animation: fadeIn 0.2s ease;
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .brand-title {
            color: #ffb703;
            font-weight: bold;
            font-size: 22px;
            margin-bottom: 25px;
            padding-left: 10px;
        }

        .menu-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #a0aec0;
            margin-bottom: 12px;
            padding-left: 10px;
        }

        .nav-list {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 12px;
            border-radius: 6px;
            color: #e2e8f0;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .nav-item-content {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
        }

        .nav-item-content i {
            font-size: 16px;
            width: 20px;
            text-align: center;
            color: #a0aec0;
        }

        .arrow-icon {
            font-size: 11px;
            color: #a0aec0;
            transition: transform 0.2s;
        }

        .nav-item.active {
            background: rgba(255, 183, 3, 0.15);
            color: #ffb703;
            border-left: 3px solid #ffb703;
        }

        .nav-item.active .nav-item-content i {
            color: #ffb703;
        }

        .nav-item:hover:not(.active) {
            background: rgba(255, 255, 255, 0.05);
        }

        /* --- Sub-menu Dropdown --- */
        .submenu-container {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
            padding-left: 20px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .submenu-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            color: #e2e8f0;
            font-size: 13.5px;
            text-decoration: none;
            cursor: pointer;
            border-radius: 4px;
            transition: background 0.2s;
        }

        .submenu-item i {
            font-size: 10px;
            color: #a0aec0;
        }

        .submenu-item:hover, .submenu-item.active-sub {
            background: rgba(255, 255, 255, 0.05);
            color: #ffb703;
        }

        .nav-item.open .arrow-icon {
            transform: rotate(90deg);
            color: #ffb703;
        }

        /* --- 3. Main Dashboard Area --- */
        main {
            flex: 1;
            padding: 25px 30px;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            pointer-events: auto;
        }

        @media (max-width: 768px) {
            main {
                padding: 16px 14px;
                padding-bottom: calc(env(safe-area-inset-bottom, 0px) + 60px);
            }
        }

        /* --- Gold Header Bars --- */
        .profile-header-bar {
            background: #bfa100;
            padding: 14px 20px;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }

        .profile-header-title {
            color: #000;
            font-size: 18px;
            font-weight: bold;
        }

        .profile-breadcrumb {
            font-size: 13px;
            color: #000;
            font-weight: 500;
        }

        .profile-breadcrumb a {
            color: #000;
            text-decoration: none;
        }

        /* Section View Control */
        .content-section {
            display: none;
        }

        .content-section.active-view {
            display: block;
        }

        /* ==========================================================
           🆕 NEW DASHBOARD DESIGN (FROM IMAGE) STYLES
           ========================================================== */
        .db-welcome-title {
            font-size: 24px;
            font-weight: bold;
            color: #fff;
            margin-bottom: 4px;
        }
        
        .db-member-id {
            font-size: 13px;
            color: #ffb703;
            margin-bottom: 20px;
        }

        /* Referral Link Section */
        .db-referral-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 30px;
            max-width: 600px;
        }

        .db-referral-input {
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 183, 3, 0.4);
            color: #a0aec0;
            padding: 10px 15px;
            border-radius: 4px;
            font-size: 13.5px;
            flex: 1;
            outline: none;
        }

        .db-referral-btn {
            background: linear-gradient(135deg, #ffcf00 0%, #bfa100 100%);
            border: 1px solid #ffb703;
            color: #000;
            padding: 10px 18px;
            font-size: 13.5px;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
            white-space: nowrap;
        }

        /* Market Ticker Block (announcement marquee + price row) */
        .market-ticker-wrap {
            margin-bottom: 20px;
        }

        .ticker-announce {
            background: rgba(191, 161, 0, 0.12);
            border: 1px solid #bfa100;
            border-radius: 6px;
            padding: 8px 0;
            overflow: hidden;
            white-space: nowrap;
            margin-bottom: 10px;
        }

        .ticker-announce-track {
            display: inline-block;
            padding-left: 100%;
            color: #ffb703;
            font-weight: 600;
            font-size: 13px;
            animation: ticker-scroll 22s linear infinite;
        }

        @keyframes ticker-scroll {
            0%   { transform: translateX(0); }
            100% { transform: translateX(-100%); }
        }

        .ticker-price-row {
            display: flex;
            background: rgba(6, 17, 33, 0.6);
            border: 1px solid rgba(255, 183, 3, 0.2);
            border-radius: 6px;
            overflow: hidden;
            padding: 10px 0;
        }

        .ticker-price-track {
            display: flex;
            flex-shrink: 0;
            animation: ticker-price-scroll 25s linear infinite;
        }

        .ticker-price-row:hover .ticker-price-track {
            animation-play-state: paused;
        }

        @keyframes ticker-price-scroll {
            0%   { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }

        .ticker-price-item {
            flex: 0 0 auto;
            min-width: 130px;
            padding: 0 18px;
            border-right: 1px solid rgba(255,255,255,0.08);
            white-space: nowrap;
        }

        .ticker-price-item:last-child {
            border-right: none;
        }

        .ticker-price-name {
            font-size: 13px;
            font-weight: bold;
            color: #fff;
            margin-bottom: 4px;
        }

        .ticker-price-value {
            font-size: 13px;
            color: #cfcfcf;
        }

        .ticker-price-value .up {
            color: #2ecc71;
            margin-left: 6px;
        }

        .ticker-price-value .down {
            color: #ff4d4d;
            margin-left: 6px;
        }

        @media (max-width: 480px) {
            .ticker-price-item {
                min-width: 105px;
                padding: 0 12px;
            }
        }

        /* Quick Action Bar (Deposit, Withdrawal, Activation, Buy Package) */
        .db-actions-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
            margin-bottom: 25px;
        }

        .db-action-btn {
            background: rgba(6, 17, 33, 0.55);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            color: #ffb703;
            text-align: center;
            padding: 18px;
            font-weight: bold;
            font-size: 17px;
            border-radius: 12px;
            border: 1px solid #ffb703;
            cursor: pointer;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .db-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(0,0,0,0.6);
        }

        /* Main Grid for Stats Cards */
        .db-stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }

        .db-stats-grid-4 {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }

        @media (max-width: 992px) {
            .db-stats-grid, .db-stats-grid-4 {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 480px) {
            .db-stats-grid, .db-stats-grid-4 {
                gap: 12px;
            }
            .db-gold-card {
                padding: 14px 16px;
                min-height: 90px;
            }
            .db-card-value {
                font-size: 21px;
            }
            .db-card-watermark {
                max-width: 38px;
                font-size: 9px;
                top: 12px;
                right: 12px;
            }
        }

        /* Gold Border Glassmorphism Card Design */
        .db-gold-card {
            background: rgba(6, 17, 33, 0.55);
            backdrop-filter: blur(10px);
            border: 1px solid #ffb703;
            border-radius: 12px;
            padding: 20px 22px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 110px;
            position: relative;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
        }

        .db-card-label {
            font-size: 14px;
            color: #ffffff;
            font-weight: 500;
        }

        .db-card-value {
            font-size: 26px;
            font-weight: bold;
            color: #ffb703;
            margin-top: 10px;
        }

        .db-card-watermark {
            position: absolute;
            top: 15px;
            right: 15px;
            opacity: 0.15;
            max-width: 50px;
            font-size: 11px;
            font-weight: bold;
            color: #ffb703;
            text-align: right;
            line-height: 1.1;
        }

        /* Double Inner Containers (Total Team & Total Business Outer Boxes) */
        .db-outer-group-box {
            background: rgba(6, 17, 33, 0.4);
            border: 1px dashed rgba(255, 183, 3, 0.4);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            position: relative;
        }

        .db-group-title {
            position: absolute;
            top: -12px;
            left: 20px;
            background: #030b14;
            padding: 2px 10px;
            font-size: 13px;
            font-weight: bold;
            color: #ffb703;
            border-radius: 4px;
        }

        .db-group-inner-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 5px;
        }

        @media (max-width: 768px) {
            .db-group-inner-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }
        }

        .db-sub-card {
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 183, 3, 0.3);
            border-radius: 8px;
            padding: 20px;
            text-align: center;
        }

        @media (max-width: 768px) {
            .db-sub-card {
                padding: 14px 10px;
            }
            .db-sub-label {
                font-size: 12.5px;
                margin-bottom: 6px;
            }
            .db-sub-value {
                font-size: 17px;
                margin-bottom: 6px;
            }
            .db-sub-logo {
                font-size: 9px;
            }
            .db-outer-group-box {
                padding: 16px 14px;
            }
        }

        .db-sub-label {
            font-size: 14px;
            font-weight: bold;
            color: #fff;
            margin-bottom: 8px;
        }

        .db-sub-value {
            font-size: 20px;
            font-weight: bold;
            color: #ffb703;
            margin-bottom: 8px;
        }

        .db-sub-logo {
            font-size: 11px;
            color: #00bfa5;
            font-weight: bold;
            letter-spacing: 1px;
        }

        /* ==========================================================
           🔚 END NEW STYLES
           ========================================================== */

        /* --- Footer --- */
        .db-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.12);
            text-align: center;
            font-size: 13px;
            color: #9ca3af;
        }

        .db-footer a {
            color: #a78bfa;
            text-decoration: none;
        }

        .db-footer a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .db-footer {
                font-size: 11.5px;
                margin-top: 25px;
                padding: 15px 10px 0;
                line-height: 1.6;
            }
        }

        /* --- Profile Card Design --- */
        .profile-view-card {
            background: rgba(6, 17, 33, 0.6);
            backdrop-filter: blur(15px);
            border: 1px solid #ffb703;
            border-radius: 8px;
            padding: 30px;
            display: flex;
            flex-direction: column;
            gap: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
        }

        .profile-row {
            display: flex;
            align-items: center;
            font-size: 15px;
        }

        .profile-label {
            width: 200px;
            color: #e2e8f0;
            font-weight: 500;
        }

        .profile-value {
            color: #ffb703;
            font-weight: bold;
        }

        /* --- Form Container Structure --- */
        .form-container {
            background: rgba(6, 17, 33, 0.6);
            backdrop-filter: blur(15px);
            border: 1px solid #ffb703;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
        }

        .form-grid-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        @media (max-width: 768px) {
            .form-grid-layout {
                grid-template-columns: 1fr;
            }
        }

        .form-group {
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group label {
            font-size: 13px;
            color: #fff;
            font-weight: 600;
        }

        .input-with-icon {
            display: flex;
            width: 100%;
            background: #fff;
            border-radius: 4px;
            overflow: hidden;
        }

        .input-icon-box {
            background: #f1f1f1;
            color: #666;
            width: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-right: 1px solid #ccc;
        }

        .input-with-icon input {
            flex: 1;
            border: none;
            padding: 12px 15px;
            font-size: 14px;
            color: #000;
            outline: none;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: none;
            border-radius: 4px;
            background: #fff;
            color: #000;
            font-size: 14px;
            outline: none;
        }

        .form-control-disabled {
            background: #e9ecef !important;
            color: #495057 !important;
            cursor: not-allowed;
        }

        .textarea-control {
            width: 100%;
            height: 120px;
            padding: 12px 15px;
            border: none;
            border-radius: 4px;
            background: #fff;
            color: #000;
            font-size: 14px;
            outline: none;
            resize: vertical;
        }

        .info-alert {
            background: rgba(0, 150, 255, 0.1);
            border: 1px solid rgba(0, 150, 255, 0.3);
            border-radius: 4px;
            padding: 12px 15px;
            font-size: 13.5px;
            color: #aadaff;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 22px;
        }

        /* --- Withdrawal Request Dashboard Style --- */
        .withdrawal-info-box {
            background: rgba(6, 17, 33, 0.6);
            backdrop-filter: blur(15px);
            border: 1px solid #ffb703;
            border-radius: 8px;
            padding: 25px 30px;
            max-width: 600px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
        }

        .withdrawal-timing-tag {
            color: #ffb703;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 20px;
            text-align: center;
        }

        .withdrawal-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            font-size: 14px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .withdrawal-row:last-of-type {
            border-bottom: none;
        }

        .withdrawal-lbl {
            color: #fff;
            font-weight: bold;
        }

        .withdrawal-val {
            color: #ffb703;
            font-weight: bold;
        }

        .disabled-action-text {
            color: #555;
            font-size: 13px;
            margin-top: 15px;
            font-style: italic;
        }

        /* --- Data Table Styling --- */
        .table-container {
            background: rgba(6, 17, 33, 0.6);
            backdrop-filter: blur(15px);
            border: 1px solid #ffb703;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
            overflow-x: auto;
        }

        .custom-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 14px;
        }

        .custom-table thead tr {
            background: #ffffff;
            color: #000000;
            font-weight: 600;
        }

        .custom-table th, .custom-table td {
            padding: 12px 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            white-space: nowrap;
        }

        .custom-table tbody tr {
            transition: background 0.2s;
        }

        .custom-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.03);
        }

        .btn-table-action {
            background: #00bfa5;
            color: #fff;
            border: none;
            padding: 6px 14px;
            font-size: 12.5px;
            font-weight: 500;
            border-radius: 4px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background 0.2s;
        }

        .btn-table-action:hover {
            background: #009688;
        }

        .empty-row-msg {
            text-align: center;
            color: #a0aec0;
            padding: 30px !important;
            font-style: italic;
        }

        /* --- Action Buttons --- */
        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 25px;
        }

        .btn-submit-gold {
            background: linear-gradient(135deg, #ffcf00 0%, #bfa100 100%);
            border: 1px solid #ffb703;
            color: #000;
            padding: 10px 24px;
            font-size: 14px;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .btn-reset-pink {
            background: #d63384;
            border: none;
            color: #fff;
            padding: 10px 24px;
            font-size: 14px;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-reset-pink:hover {
            background: #b8266e;
        }

        .btn-submit-gold:hover {
            transform: scale(1.02);
        }

        .btn-gold {
            background: linear-gradient(135deg, #ffcf00 0%, #bfa100 100%);
            border: 1px solid #ffb703;
            color: #000;
            padding: 9px 22px;
            font-size: 14px;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: transform 0.2s;
        }

        .btn-reset {
            background: transparent;
            border: 1px solid #ff4d4d;
            color: #ff4d4d;
            padding: 9px 22px;
            font-size: 14px;
            font-weight: 500;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background 0.2s, color 0.2s;
        }

        .btn-reset:hover {
            background: #ff4d4d;
            color: #fff;
        }

        /* ---------- Tap / ripple interaction system (applies to ALL buttons) ---------- */
        button, .db-action-btn, .btn-table-action, .nav-item, .submenu-item, .dropdown-item, .db-referral-btn {
            position: relative;
            overflow: hidden;
        }
        .ripple-effect {
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,0.55);
            transform: scale(0);
            animation: rippleAnim .55s ease-out forwards;
            pointer-events: none;
            mix-blend-mode: overlay;
        }
        @keyframes rippleAnim {
            to { transform: scale(2.6); opacity: 0; }
        }
        .tap-glow {
            box-shadow: 0 0 0 4px rgba(255,183,3,0.35), 0 0 22px rgba(255,183,3,0.55) !important;
            transition: box-shadow .15s ease;
        }

        /* Choice Modal Styling */
        .choice-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            animation: fadeInModal 0.25s ease-out;
        }

        .choice-modal-content {
            background: rgba(6, 17, 33, 0.9);
            border: 2px solid #ffb703;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 15px 35px rgba(0,0,0,0.6);
            transform: scale(0.9);
            animation: scaleInModal 0.25s ease-out forwards;
        }

        @keyframes fadeInModal {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes scaleInModal {
            to { transform: scale(1); }
        }

        .choice-modal-title {
            color: #ffb703;
            font-size: 22px;
            margin-bottom: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .choice-modal-desc {
            color: #e2e8f0;
            font-size: 14px;
            margin-bottom: 25px;
            line-height: 1.5;
        }

        .choice-modal-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .choice-btn {
            flex: 1;
            padding: 12px 20px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .self-btn {
            background: linear-gradient(135deg, #ffcf00 0%, #bfa100 100%);
            border: 1px solid #ffb703;
            color: #000;
        }

        .others-btn {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
        }

        .choice-btn:hover {
            transform: translateY(-2px);
        }
        .self-btn:hover {
            box-shadow: 0 0 15px rgba(255,183,3,0.4);
        }
        .others-btn:hover {
            background: rgba(255, 255, 255, 0.15);
        }
    </style>
    <!-- THREE.JS Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
</head>
<body>

    <!-- Beautiful Popup Modal for Buy Package Choice -->
    <div id="buyChoiceModal" class="choice-modal-overlay" style="display: none;">
        <div class="choice-modal-content">
            <h3 class="choice-modal-title">Buy Package For</h3>
            <p class="choice-modal-desc">Would you like to activate this package for yourself or another member?</p>
            <div class="choice-modal-buttons">
                <button class="choice-btn self-btn" onclick="handleChoice('self')"><i class="fa-solid fa-user"></i> Self</button>
                <button class="choice-btn others-btn" onclick="handleChoice('others')"><i class="fa-solid fa-users"></i> Others</button>
            </div>
        </div>
    </div>

    <!-- Canvas For Particle Effect Background -->
    <div id="canvas-container"></div>

    <!-- 1. Top Navbar -->
    <div class="top-navbar">
        <div class="top-left">
            <button class="toggle-menu-btn" id="toggleMenuBtn"><i class="fa-solid fa-bars"></i></button>
        </div>
        <div class="top-right">
            <select class="lang-select" style="display:none;">
                <option>Select Language</option>
            </select>
            <button class="fullscreen-btn" id="fullscreenBtn" onclick="toggleFullscreen()"><i class="fa-solid fa-expand"></i></button>
            
            <div class="user-profile-wrapper">
                <div class="user-profile-menu" id="profileMenuBtn">
                    <div class="user-avatar"><i class="fa-solid fa-user"></i></div>
                    <span class="user-name">RJ129688 <i class="fa-solid fa-chevron-down" style="font-size: 10px; color:#ffb703; margin-left: 2px;"></i></span>
                </div>

                <div class="profile-dropdown" id="profileDropdownMenu">
                    <a class="dropdown-item" onclick="switchView('profileViewSection', this)"><i class="fa-regular fa-user"></i> Profile</a>
                    <a class="dropdown-item" onclick="switchView('changePasswordSection', this)"><i class="fa-solid fa-gear"></i> Change Password</a>
                    <a class="dropdown-item" onclick="switchView('ticketSubmitSection', this)"><i class="fa-regular fa-envelope"></i> Help Ticket</a>
                    <a class="dropdown-item" style="border-top: 1px solid #eee;" onclick="window.location.href='login.html'"><i class="fa-solid fa-arrow-right-from-bracket"></i> Log out</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Layout Matrix -->
    <div class="content-layout">

        <!-- Mobile Sidebar Backdrop -->
        <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

        <!-- 2. Sidebar Menu Accordion Matrix -->
        <sidebar>
<?php include '../includes/sidebar.php'; ?>
</sidebar>

        <!-- 3. Main Operational Board Content Views -->
        <main>
