<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RJ Rathore Trading - User Panel</title>
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
            <div class="brand-title">RJ Rathore Trading</div>
            <div class="menu-title">Menu</div>
            <div class="nav-list">
                <!-- Dashboard Button -->
                <div class="nav-item active" onclick="switchView('dashboardSection', this)">
                    <div class="nav-item-content"><i class="fa-solid fa-house"></i><span>Dashboard</span></div>
                </div>

                <div class="nav-item" onclick="switchView('depositSection', this)">
                    <div class="nav-item-content"><i class="fa-solid fa-wallet"></i><span>Deposit</span></div>
                </div>

                <!-- Profile Menu -->
                <div class="nav-item sidebar-toggle" data-target="profileSubmenu">
                    <div class="nav-item-content"><i class="fa-solid fa-user"></i><span>Profile</span></div>
                    <i class="fa-solid fa-chevron-right arrow-icon"></i>
                </div>
                <div class="submenu-container" id="profileSubmenu">
                    <a class="submenu-item" onclick="switchView('profileViewSection', this)"><i class="fa-regular fa-square"></i> Profile View</a>
                    <a class="submenu-item" onclick="switchView('walletAddressSection', this)"><i class="fa-regular fa-square"></i> Wallet Address</a>
                    <a class="submenu-item" onclick="switchView('changePasswordSection', this)"><i class="fa-regular fa-square"></i> Change Password</a>
                </div>

                <!-- Team Menu
                <div class="nav-item sidebar-toggle" data-target="teamSubmenu">
                    <div class="nav-item-content"><i class="fa-solid fa-sitemap"></i><span>Team</span></div>
                    <i class="fa-solid fa-chevron-right arrow-icon"></i>
                </div>
                <div class="submenu-container" id="teamSubmenu">
                    <a class="submenu-item" onclick="switchView('directMemberSection', this)"><i class="fa-regular fa-square"></i> Direct Team</a>
                    <a class="submenu-item" onclick="switchView('levelTeamSection', this)"><i class="fa-regular fa-square"></i> Level Team</a>
                </div>
                -->

                <!-- Activation Menu -->
                <div class="nav-item sidebar-toggle" data-target="activationSubmenu">
                    <div class="nav-item-content"><i class="fa-solid fa-cart-shopping"></i><span>Activation</span></div>
                    <i class="fa-solid fa-chevron-right arrow-icon"></i>
                </div>
                <div class="submenu-container" id="activationSubmenu">
                    <a class="submenu-item" onclick="switchView('activateIdSection', this)"><i class="fa-regular fa-square"></i> Active ID</a>
                    <a class="submenu-item" onclick="switchView('activeIdReportSection', this)"><i class="fa-regular fa-square"></i> Active ID Report</a>
                    <a class="submenu-item" onclick="switchView('upgradeIdSection', this)"><i class="fa-regular fa-square"></i> Upgrade ID</a>
                    <a class="submenu-item" onclick="switchView('upgradeIdReportSection', this)"><i class="fa-regular fa-square"></i> Upgrade ID Report</a>
                    <a class="submenu-item" onclick="switchView('selfUpgradeReportSection', this)"><i class="fa-regular fa-square"></i> Self Upgrade ID Report</a>
                </div>

                <!-- Withdrawal Menu -->
                <div class="nav-item sidebar-toggle" data-target="withdrawalSubmenu">
                    <div class="nav-item-content"><i class="fa-solid fa-money-bill-transfer"></i><span>Withdrawal</span></div>
                    <i class="fa-solid fa-chevron-right arrow-icon"></i>
                </div>
                <div class="submenu-container" id="withdrawalSubmenu">
                    <a class="submenu-item" onclick="switchView('newWithdrawalSection', this)"><i class="fa-regular fa-square"></i> New Withdrawal</a>
                    <a class="submenu-item" onclick="switchView('withdrawalReportSection', this)"><i class="fa-regular fa-square"></i> Withdrawal Report</a>
                </div>

                <div class="nav-item sidebar-toggle" data-target="buyPackageSubmenu">
                    <div class="nav-item-content"><i class="fa-solid fa-box-open"></i><span>Buy Package</span></div>
                    <i class="fa-solid fa-chevron-right arrow-icon"></i>
                </div>
                <div class="submenu-container" id="buyPackageSubmenu">
                    <a class="submenu-item" onclick="switchView('autopoolPackageSection', this)"><i class="fa-regular fa-square"></i> Autopool Package</a>
                    <a class="submenu-item" onclick="switchView('infinityPackageSection', this)"><i class="fa-regular fa-square"></i> Infinity Package</a>
                </div>

                <!-- My Income Menu
                <div class="nav-item sidebar-toggle" data-target="incomeSubmenu">
                    <div class="nav-item-content"><i class="fa-solid fa-gift"></i><span>My Income</span></div>
                    <i class="fa-solid fa-chevron-right arrow-icon"></i>
                </div>
                <div class="submenu-container" id="incomeSubmenu">
                    <a class="submenu-item" onclick="switchView('dailyRoiSection', this)"><i class="fa-regular fa-square"></i> Daily ROI Income</a>
                </div>
                -->

                <!-- More Menu -->
                <div class="nav-item sidebar-toggle" data-target="moreSubmenu">
                    <div class="nav-item-content"><i class="fa-solid fa-circle-question"></i><span>More</span></div>
                    <i class="fa-solid fa-chevron-right arrow-icon"></i>
                </div>
                <div class="submenu-container" id="moreSubmenu">
                    <a class="submenu-item" onclick="switchView('ticketSubmitSection', this)"><i class="fa-regular fa-square"></i> Ticket Submit</a>
                    <a class="submenu-item" onclick="switchView('ticketReportSection', this)"><i class="fa-regular fa-square"></i> View Ticket</a>
                </div>

                <?php for ($i = 1; $i <= 8; $i++): ?>
                <!-- Autopool Pack <?php echo $i; ?> -->
                <div class="nav-item sidebar-toggle" data-target="autopoolPack<?php echo $i; ?>Submenu">
                    <div class="nav-item-content"><i class="fa-solid fa-layer-group"></i><span>Autopool Pack <?php echo $i; ?></span></div>
                    <i class="fa-solid fa-chevron-right arrow-icon"></i>
                </div>
                <div class="submenu-container" id="autopoolPack<?php echo $i; ?>Submenu">
                    <a class="submenu-item" onclick="switchView('autopoolPack<?php echo $i; ?>MyNetwork', this)"><i class="fa-regular fa-square"></i> My Network</a>
                    <a class="submenu-item" onclick="switchView('autopoolPack<?php echo $i; ?>Income', this)"><i class="fa-regular fa-square"></i> Autopool income</a>
                    <a class="submenu-item" onclick="switchView('autopoolPack<?php echo $i; ?>Sponsor', this)"><i class="fa-regular fa-square"></i> Sponsor income</a>
                    <a class="submenu-item" onclick="switchView('autopoolPack<?php echo $i; ?>Level', this)"><i class="fa-regular fa-square"></i> Level Income</a>
                    <a class="submenu-item" onclick="switchView('autopoolPack<?php echo $i; ?>Reward', this)"><i class="fa-regular fa-square"></i> Reward Income</a>
                </div>
                <?php endfor; ?>

                <?php for ($i = 1; $i <= 6; $i++): ?>
                <!-- Infinity Pack <?php echo $i; ?> -->
                <div class="nav-item sidebar-toggle" data-target="infinityPack<?php echo $i; ?>Submenu">
                    <div class="nav-item-content"><i class="fa-solid fa-infinity"></i><span>Infinity Pack <?php echo $i; ?></span></div>
                    <i class="fa-solid fa-chevron-right arrow-icon"></i>
                </div>
                <div class="submenu-container" id="infinityPack<?php echo $i; ?>Submenu">
                    <a class="submenu-item" onclick="switchView('infinityPack<?php echo $i; ?>Matrix', this)"><i class="fa-regular fa-square"></i> Matrix</a>
                    <a class="submenu-item" onclick="switchView('infinityPack<?php echo $i; ?>IncomeReport', this)"><i class="fa-regular fa-square"></i> Income Report</a>
                </div>
                <?php endfor; ?>

                <div class="nav-item" style="margin-top: 20px;" onclick="window.location.href='login.html'">
                    <div class="nav-item-content"><i class="fa-solid fa-power-off" style="color:#ff4d4d;"></i><span>Logout</span></div>
                </div>
            </div>
        </sidebar>

        <!-- 3. Main Operational Board Content Views -->
        <main>
            
            <!-- 🆕 VIEW: Main Dashboard Overview Section (Fully custom matched with your image) -->
            <div id="dashboardSection" class="content-section active-view">
                <div class="profile-header-bar">
                    <div class="profile-header-title">Index</div>
                    <div class="profile-breadcrumb"><a href="#" onclick="switchView('dashboardSection', document.querySelector('.nav-item')); return false;">Home</a> &raquo; Index</div>
                </div>
                
                <!-- Market Ticker: Announcement Marquee + Live Price Row -->
                <div class="market-ticker-wrap">
                    <div class="ticker-announce">
                        <span class="ticker-announce-track">100 DAYS &gt; 6%, 400 DAYS &gt; 7%, 600 DAYS &gt; 8%, 800 DAYS &gt; 9%, 1000 DAYS &gt; 10% ★</span>
                    </div>
                    <div class="ticker-price-row">
                        <div class="ticker-price-track">
                            <div class="ticker-price-item">
                                <div class="ticker-price-name">US 100 Cash CFD</div>
                                <div class="ticker-price-value">29,257.2 <span class="up">+0.86%</span></div>
                            </div>
                            <div class="ticker-price-item">
                                <div class="ticker-price-name">EUR to USD</div>
                                <div class="ticker-price-value">1.146 <span class="down">-0.07%</span></div>
                            </div>
                            <div class="ticker-price-item">
                                <div class="ticker-price-name">Bitcoin</div>
                                <div class="ticker-price-value">64,176 <span class="down">-1.78%</span></div>
                            </div>
                            <div class="ticker-price-item">
                                <div class="ticker-price-name">Ethereum</div>
                                <div class="ticker-price-value">1,884.4 <span class="down">-1.72%</span></div>
                            </div>
                            <div class="ticker-price-item">
                                <div class="ticker-price-name">S&amp;P 500</div>
                                <div class="ticker-price-value">7,354.3 <span class="down">-0.24%</span></div>
                            </div>
                            <!-- Duplicate set for seamless infinite scroll -->
                            <div class="ticker-price-item" aria-hidden="true">
                                <div class="ticker-price-name">US 100 Cash CFD</div>
                                <div class="ticker-price-value">29,257.2 <span class="up">+0.86%</span></div>
                            </div>
                            <div class="ticker-price-item" aria-hidden="true">
                                <div class="ticker-price-name">EUR to USD</div>
                                <div class="ticker-price-value">1.146 <span class="down">-0.07%</span></div>
                            </div>
                            <div class="ticker-price-item" aria-hidden="true">
                                <div class="ticker-price-name">Bitcoin</div>
                                <div class="ticker-price-value">64,176 <span class="down">-1.78%</span></div>
                            </div>
                            <div class="ticker-price-item" aria-hidden="true">
                                <div class="ticker-price-name">Ethereum</div>
                                <div class="ticker-price-value">1,884.4 <span class="down">-1.72%</span></div>
                            </div>
                            <div class="ticker-price-item" aria-hidden="true">
                                <div class="ticker-price-name">S&amp;P 500</div>
                                <div class="ticker-price-value">7,354.3 <span class="down">-0.24%</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Welcome Greetings Header & Member ID Info -->
                <div class="db-welcome-title">Welcome, cervrgf</div>
                <div class="db-member-id">Member Id: RJI29688</div>

                <!-- Referral Link Interface Layer -->
                <div class="db-referral-wrapper">
                    <input type="text" class="db-referral-input" value="https://rjrathoretrading.online/reffer.php?id=RJI29688" readonly id="refLinkInput">
                    <button class="db-referral-btn" onclick="navigator.clipboard.writeText(document.getElementById('refLinkInput').value); alert('Referral Link Copied!');">Referral Link</button>
                </div>

                <!-- Quick Action Button Bars Matrix -->
                <div class="db-actions-row">
                    <div class="db-action-btn" onclick="switchView('depositSection')">Deposit</div>
                    <div class="db-action-btn" onclick="switchView('newWithdrawalSection')">Withdrawal</div>
                    <div class="db-action-btn" onclick="switchView('activateIdSection')">Activation</div>
                    <div class="db-action-btn" onclick="switchView('buyPackageSection')">Buy Package</div>
                </div>

                <!-- Primary Row Statistics Cards Block -->
                <div class="db-stats-grid">
                    <!-- Account Status Card -->
                    <div class="db-gold-card">
                        <div class="db-card-label">Account Status</div>
                        <div class="db-card-value">Active</div>
                    </div>
                    <!-- My Package Card -->
                    <div class="db-gold-card">
                        <div class="db-card-label">My Package</div>
                        <div class="db-card-value">$0</div>
                    </div>
                    <!-- Direct Team Card -->
                    <div class="db-gold-card">
                        <div class="db-card-label">Direct Team</div>
                        <div class="db-card-value">0</div>
                    </div>
                    <!-- Total Active Team Card -->
                    <div class="db-gold-card">
                        <div class="db-card-label">Total Active Team</div>
                        <div class="db-card-value">0</div>
                    </div>
                    <!-- Total Inactive Team Card -->
                    <div class="db-gold-card">
                        <div class="db-card-label">Total Inactive Team</div>
                        <div class="db-card-value">0</div>
                    </div>
                </div>

                <!-- Secondary Income Matrix Cluster Cards Block -->
                <div class="db-stats-grid-4">
                    <!-- Direct Referral Income -->
                    <div class="db-gold-card">
                        <div class="db-card-label">Direct Referral Income</div>
                        <div class="db-card-value">$0</div>
                    </div>
                    <!-- Team Level Income -->
                    <div class="db-gold-card">
                        <div class="db-card-label">Team Level Income</div>
                        <div class="db-card-value">$0</div>
                    </div>
                    <!-- Global Autopool Income -->
                    <div class="db-gold-card">
                        <div class="db-card-label">Global Autopool Income</div>
                        <div class="db-card-value">$0</div>
                    </div>
                    <!-- Global Royalty Pool Income -->
                    <div class="db-gold-card">
                        <div class="db-card-label">Global Royalty Pool Income</div>
                        <div class="db-card-value">$0</div>
                    </div>
                    <!-- Team Performance Income -->
                    <div class="db-gold-card">
                        <div class="db-card-label">Team Performance Income</div>
                        <div class="db-card-value">$0</div>
                    </div>
                    <!-- Booster Income -->
                    <div class="db-gold-card">
                        <div class="db-card-label">Booster Income</div>
                        <div class="db-card-value">$0</div>
                    </div>
                    <!-- Total Income -->
                    <div class="db-gold-card">
                        <div class="db-card-label">Total Income</div>
                        <div class="db-card-value">$0</div>
                    </div>
                    <!-- Total Withdrawal Income -->
                    <div class="db-gold-card">
                        <div class="db-card-label">Total Withdrawal Income</div>
                        <div class="db-card-value">$0</div>
                    </div>
                    <!-- Net Income -->
                    <div class="db-gold-card">
                        <div class="db-card-label">Net Income</div>
                        <div class="db-card-value">$0</div>
                    </div>
                    <!-- Wallet Address (Auto Generated ID) -->
                    <div class="db-gold-card">
                        <div class="db-card-label">Wallet Address (Auto Generated ID)</div>
                        <div class="db-card-value" style="font-size: 16px;">RJ129688</div>
                    </div>
                </div>

                <!-- Lower Layout Group Matrix: Total Team Section -->
                <div class="db-outer-group-box" style="display: none;">
                    <div class="db-group-title">Total Team</div>
                    <div class="db-group-inner-grid">
                        <div class="db-sub-card">
                            <div class="db-sub-label">Direct Team</div>
                            <div class="db-sub-value">0</div>
                            <div class="db-sub-logo">RJ Rathore TRADING</div>
                        </div>
                        <div class="db-sub-card">
                            <div class="db-sub-label">Level Team</div>
                            <div class="db-sub-value">0</div>
                            <div class="db-sub-logo">RJ Rathore TRADING</div>
                        </div>
                        <div class="db-sub-card">
                            <div class="db-sub-label">Active Team</div>
                            <div class="db-sub-value">0</div>
                            <div class="db-sub-logo">RJ Rathore TRADING</div>
                        </div>
                        <div class="db-sub-card">
                            <div class="db-sub-label">Inactive Team</div>
                            <div class="db-sub-value">0</div>
                            <div class="db-sub-logo">RJ Rathore TRADING</div>
                        </div>
                    </div>
                </div>

                <!-- Lower Layout Group Matrix: Total Business Section -->
                <div class="db-outer-group-box" style="display: none;">
                    <div class="db-group-title">Total Business</div>
                    <div class="db-group-inner-grid">
                        <div class="db-sub-card">
                            <div class="db-sub-label">Team Business</div>
                            <div class="db-sub-value">$0</div>
                            <div class="db-sub-logo">RJ Rathore TRADING</div>
                        </div>
                        <div class="db-sub-card">
                            <div class="db-sub-label">Direct Business</div>
                            <div class="db-sub-value">$0</div>
                            <div class="db-sub-logo">RJ Rathore TRADING</div>
                        </div>
                        <div class="db-sub-card">
                            <div class="db-sub-label">Self Business</div>
                            <div class="db-sub-value">$0</div>
                            <div class="db-sub-logo">RJ Rathore TRADING</div>
                        </div>
                        <div class="db-sub-card">
                            <div class="db-sub-label">Today Business</div>
                            <div class="db-sub-value">$0</div>
                            <div class="db-sub-logo">RJ Rathore TRADING</div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- VIEW: Profile View -->
            <div id="profileViewSection" class="content-section">
                <div class="profile-header-bar">
                    <div class="profile-header-title">Profile View</div>
                    <div class="profile-breadcrumb"><a href="#" onclick="switchView('dashboardSection', document.querySelector('.nav-item')); return false;">Home</a> &raquo; Profile View</div>
                </div>
                <div class="profile-view-card">
                    <div class="profile-row"><div class="profile-label">Sponsor ID</div><div class="profile-value">RJI23456</div></div>
                    <div class="profile-row"><div class="profile-label">My User ID</div><div class="profile-value">RJ129688</div></div>
                    <div class="profile-row"><div class="profile-label">Name</div><div class="profile-value">cervrgf</div></div>
                    <div class="profile-row"><div class="profile-label">Email</div><div class="profile-value">cdvef@gmail.com</div></div>
                    <div class="profile-row"><div class="profile-label">Mobile</div><div class="profile-value">3456754343</div></div>
                    <button class="btn-gold" style="margin-top: 10px;" onclick="switchView('editProfileSection')"><i class="fa-solid fa-pen"></i> Edit</button>
                </div>
            </div>

            <!-- VIEW: Edit Profile -->
            <div id="editProfileSection" class="content-section">
                <div class="profile-header-bar">
                    <div class="profile-header-title">Edit Profile</div>
                    <div class="profile-breadcrumb"><a href="#" onclick="switchView('dashboardSection', document.querySelector('.nav-item')); return false;">Home</a> &raquo; Edit Profile</div>
                </div>
                <div class="form-container">
                    <div class="form-grid-layout">
                        <div class="form-group">
                            <label>Sponsor ID</label>
                            <input type="text" class="form-control form-control-disabled" value="RJI23456" readonly>
                        </div>
                        <div class="form-group">
                            <label>User ID</label>
                            <input type="text" class="form-control form-control-disabled" value="RJ129688" readonly>
                        </div>
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" class="form-control" value="cervrgf">
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" class="form-control" value="cdvef@gmail.com">
                        </div>
                        <div class="form-group">
                            <label>Mobile</label>
                            <input type="text" class="form-control" value="3456754343">
                        </div>
                        <div class="form-group">
                            <label>User Since</label>
                            <input type="text" class="form-control form-control-disabled" value="" readonly>
                        </div>
                    </div>
                    <div class="form-actions" style="justify-content: flex-end;">
                        <button class="btn-submit-gold"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
                        <button class="btn-reset-pink"><i class="fa-solid fa-arrow-rotate-left"></i> Reset</button>
                    </div>
                </div>
            </div>

            <!-- VIEW: Update Wallet Address -->
            <div id="walletAddressSection" class="content-section">
                <div class="profile-header-bar">
                    <div class="profile-header-title">Update Wallet Address</div>
                    <div class="profile-breadcrumb"><a href="#" onclick="switchView('dashboardSection', document.querySelector('.nav-item')); return false;">Home</a> &raquo; Wallet Update</div>
                </div>
                <div class="form-container">
                    <div class="info-alert">
                        <i class="fa-solid fa-circle-info"></i>
                        <span>Please enter a valid <strong>USDT BEP20</strong> wallet address carefully.</span>
                    </div>
                    <div class="form-group">
                        <label>USDT BEP20 Wallet Address</label>
                        <div class="input-with-icon">
                            <div class="input-icon-box"><i class="fa-solid fa-wallet"></i></div>
                            <input type="text" placeholder="Enter Wallet Address">
                        </div>
                    </div>
                    <div class="form-actions">
                        <button class="btn-gold"><i class="fa-solid fa-floppy-disk"></i> Save Wallet</button>
                        <button class="btn-reset"><i class="fa-solid fa-arrow-rotate-left"></i> Reset</button>
                    </div>
                </div>
            </div>

            <!-- VIEW: Change Password -->
            <div id="changePasswordSection" class="content-section">
                <div class="profile-header-bar">
                    <div class="profile-header-title">Change Password</div>
                    <div class="profile-breadcrumb"><a href="#" onclick="switchView('dashboardSection', document.querySelector('.nav-item')); return false;">Home</a> &raquo; Change Password</div>
                </div>
                <div class="form-container">
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" class="form-control" placeholder="Current Password">
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" class="form-control" placeholder="New Password">
                    </div>
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" class="form-control" placeholder="Confirm Password">
                    </div>
                    <div class="form-actions">
                        <button class="btn-gold"><i class="fa-solid fa-lock"></i> Change Password</button>
                        <button class="btn-reset"><i class="fa-solid fa-arrow-rotate-left"></i> Reset</button>
                    </div>
                </div>
            </div>

            <!-- VIEW: Direct Member -->
            <div id="directMemberSection" class="content-section">
                <div class="profile-header-bar">
                    <div class="profile-header-title">Direct Member</div>
                    <div class="profile-breadcrumb"><a href="#" onclick="switchView('dashboardSection', document.querySelector('.nav-item')); return false;">Home</a> &raquo; Direct Member</div>
                </div>
                <div class="table-container">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>User ID</th>
                                <th>Name</th>
                                <th>Sponsor Id</th>
                                <th>Mobile No</th>
                                <th>Package</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="8" class="empty-row-msg">No records found</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- VIEW: Level Team -->
            <div id="levelTeamSection" class="content-section">
                <div class="profile-header-bar">
                    <div class="profile-header-title">Level Team</div>
                    <div class="profile-breadcrumb"><a href="#" onclick="switchView('dashboardSection', document.querySelector('.nav-item')); return false;">Home</a> &raquo; Level Team</div>
                </div>
                <div class="table-container">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Level</th>
                                <th>Total Users</th>
                                <th>Total Paid Users</th>
                                <th>Team Business</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <script>
                                for(let i=1; i<=10; i++) {
                                    document.write(`
                                        <tr>
                                            <td>\${i}</td>
                                            <td>Level-\${i}</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>$0</td>
                                            <td><button class="btn-table-action"><i class="fa-solid fa-pencil" style="font-size:11px;"></i> View Team</button></td>
                                        </tr>
                                    `);
                                }
                            </script>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- VIEW: Activate ID -->
            <div id="activateIdSection" class="content-section">
                <div class="profile-header-bar">
                    <div class="profile-header-title">Activate ID</div>
                    <div class="profile-breadcrumb"><a href="#" onclick="switchView('dashboardSection', document.querySelector('.nav-item')); return false;">Home</a> &raquo; Activate ID Here</div>
                </div>
                <div class="db-gold-card" style="max-width: 260px; margin-bottom: 20px;">
                    <div class="db-card-label">Package</div>
                    <div class="db-card-value">$0</div>
                </div>
                <div class="form-container">
                    <div class="form-grid-layout">
                        <div class="form-group">
                            <label>User Id</label>
                            <input type="text" class="form-control form-control-disabled" value="RJ129688" readonly>
                        </div>
                        <div class="form-group">
                            <label>Fund</label>
                            <input type="text" class="form-control form-control-disabled" value="0.00" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Member ID</label>
                        <input type="text" class="form-control" placeholder="Enter Member ID">
                    </div>
                    <div class="form-group">
                        <label>Package Amount</label>
                        <input type="text" class="form-control" placeholder="Min $50">
                    </div>
                    <div class="form-actions">
                        <button class="btn-submit-gold">Submit</button>
                        <button class="btn-reset-pink">Reset</button>
                    </div>
                </div>
            </div>

            <!-- VIEW: Deposit Funds -->
            <div id="depositSection" class="content-section">
                <div class="profile-header-bar">
                    <div class="profile-header-title">Deposit Funds</div>
                    <div class="profile-breadcrumb"><a href="#" onclick="switchView('dashboardSection', document.querySelector('.nav-item')); return false;">Home</a> &raquo; Deposit Funds</div>
                </div>
                <div class="form-container">
                    <div class="form-group">
                        <label><i class="fa-solid fa-coins"></i> Fund Amount</label>
                        <input type="text" class="form-control" placeholder="Enter amount to deposit">
                    </div>
                    <div class="form-group">
                        <label><i class="fa-solid fa-receipt"></i> Transaction Hash</label>
                        <input type="text" class="form-control" placeholder="Enter your transaction Hash">
                    </div>
                    <div class="form-actions">
                        <button class="btn-submit-gold"><i class="fa-solid fa-paper-plane"></i> Submit Deposit Request</button>
                        <button class="btn-reset-pink"><i class="fa-solid fa-arrow-rotate-left"></i> Reset</button>
                    </div>
                </div>
            </div>

            <!-- VIEW: Buy Autopool Package -->
            <div id="autopoolPackageSection" class="content-section">
                <div class="profile-header-bar">
                    <div class="profile-header-title">Buy Autopool Package</div>
                    <div class="profile-breadcrumb"><a href="#" onclick="switchView('dashboardSection', document.querySelector('.nav-item')); return false;">Home</a> &raquo; Buy Autopool Package</div>
                </div>
                <div class="form-container">
                    <div class="form-grid-layout">
                        <div class="form-group">
                            <label>My ID</label>
                            <input type="text" class="form-control form-control-disabled" value="RJ129688" readonly>
                        </div>
                        <div class="form-group">
                            <label>Wallet Balance</label>
                            <input type="text" class="form-control form-control-disabled" value="$150" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label><i class="fa-solid fa-id-card"></i> Member ID to Activate</label>
                        <input type="text" id="autopoolMemberId" class="form-control" placeholder="Enter the member ID you want to activate">
                    </div>
                    <div class="form-group">
                        <label><i class="fa-solid fa-coins"></i> Select Fund Amount</label>
                        <select class="form-control">
                            <option value="">Select Amount</option>
                            <option value="11">$11</option>
                            <option value="30">$30</option>
                            <option value="60">$60</option>
                            <option value="120">$120</option>
                            <option value="240">$240</option>
                            <option value="480">$480</option>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button class="btn-submit-gold"><i class="fa-solid fa-circle-check"></i> Buy Package</button>
                        <button class="btn-reset-pink"><i class="fa-solid fa-arrow-rotate-left"></i> Reset Form</button>
                    </div>
                </div>
            </div>

            <!-- VIEW: Buy Infinity Package -->
            <div id="infinityPackageSection" class="content-section">
                <div class="profile-header-bar">
                    <div class="profile-header-title">Buy Infinity Package</div>
                    <div class="profile-breadcrumb"><a href="#" onclick="switchView('dashboardSection', document.querySelector('.nav-item')); return false;">Home</a> &raquo; Buy Infinity Package</div>
                </div>
                <div class="form-container">
                    <div class="form-grid-layout">
                        <div class="form-group">
                            <label>My ID</label>
                            <input type="text" class="form-control form-control-disabled" value="RJ129688" readonly>
                        </div>
                        <div class="form-group">
                            <label>Wallet Balance</label>
                            <input type="text" class="form-control form-control-disabled" value="$150" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label><i class="fa-solid fa-id-card"></i> Member ID to Activate</label>
                        <input type="text" id="infinityMemberId" class="form-control" placeholder="Enter the member ID you want to activate">
                    </div>
                    <div class="form-group">
                        <label><i class="fa-solid fa-coins"></i> Select Fund Amount</label>
                        <select class="form-control">
                            <option value="">Select Amount</option>
                            <option value="10">$10</option>
                            <option value="20">$20</option>
                            <option value="40">$40</option>
                            <option value="80">$80</option>
                            <option value="160">$160</option>
                            <option value="320">$320</option>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button class="btn-submit-gold"><i class="fa-solid fa-circle-check"></i> Buy Package</button>
                        <button class="btn-reset-pink"><i class="fa-solid fa-arrow-rotate-left"></i> Reset Form</button>
                    </div>
                </div>
            </div>
            <div id="activeIdReportSection" class="content-section">
                <div class="profile-header-bar">
                    <div class="profile-header-title">Active By Fund Report</div>
                    <div class="profile-breadcrumb"><a href="#" onclick="switchView('dashboardSection', document.querySelector('.nav-item')); return false;">Home</a> &raquo; Active By Fund Report</div>
                </div>
                <div class="table-container">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Member Id</th>
                                <th>Package</th>
                                <th>Date</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="5" class="empty-row-msg">No records found</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- VIEW: Upgrade ID -->
            <div id="upgradeIdSection" class="content-section">
                <div class="profile-header-bar">
                    <div class="profile-header-title">Upgrade ID</div>
                    <div class="profile-breadcrumb"><a href="#" onclick="switchView('dashboardSection', document.querySelector('.nav-item')); return false;">Home</a> &raquo; Upgrade ID Here</div>
                </div>
                <div class="form-container">
                    <div class="form-group">
                        <label>Member ID</label>
                        <input type="text" class="form-control" placeholder="Enter Member ID to Upgrade">
                    </div>
                    <div class="form-group">
                        <label>Upgrade Package Amount</label>
                        <input type="text" class="form-control" placeholder="Enter Amount">
                    </div>
                    <div class="form-actions">
                        <button class="btn-submit-gold">Upgrade</button>
                        <button class="btn-reset-pink">Reset</button>
                    </div>
                </div>
            </div>

            <!-- VIEW: Upgrade Report -->
            <div id="upgradeIdReportSection" class="content-section">
                <div class="profile-header-bar">
                    <div class="profile-header-title">Upgrade Report</div>
                    <div class="profile-breadcrumb"><a href="#" onclick="switchView('dashboardSection', document.querySelector('.nav-item')); return false;">Home</a> &raquo; Upgrade ID Report</div>
                </div>
                <div class="table-container">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Member Id</th>
                                <th>Amount</th>
                                <th>Referral Id</th>
                                <th>Date</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="6" class="empty-row-msg">No records found</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- VIEW: Self Upgrade Report -->
            <div id="selfUpgradeReportSection" class="content-section">
                <div class="profile-header-bar">
                    <div class="profile-header-title">Self Upgrade Report</div>
                    <div class="profile-breadcrumb"><a href="#" onclick="switchView('dashboardSection', document.querySelector('.nav-item')); return false;">Home</a> &raquo; Upgrade ID Report</div>
                </div>
                <div class="table-container">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Member Id</th>
                                <th>Amount</th>
                                <th>Referral Id</th>
                                <th>Date</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="6" class="empty-row-msg">No records found</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- VIEW: New Withdrawal -->
            <div id="newWithdrawalSection" class="content-section">
                <div class="profile-header-bar">
                    <div class="profile-header-title">Withdrawal Request</div>
                    <div class="profile-breadcrumb"><a href="#" onclick="switchView('dashboardSection', document.querySelector('.nav-item')); return false;">Home</a> &raquo; Withdrawal Request</div>
                </div>
                <div class="withdrawal-info-box">
                    <div class="withdrawal-timing-tag">Withdrawal Only Timing 06:00 AM TO 07:00 PM</div>
                    <div class="withdrawal-row"><span class="withdrawal-lbl">Total Wallet</span><span class="withdrawal-val">$0</span></div>
                    <div class="withdrawal-row"><span class="withdrawal-lbl">Total Withdrawal Income</span><span class="withdrawal-val">$0</span></div>
                    <div class="withdrawal-row"><span class="withdrawal-lbl">Net Wallet Income</span><span class="withdrawal-val">$0</span></div>
                    <div class="disabled-action-text">Activate Your ID</div>
                </div>
            </div>

            <!-- VIEW: Payout Report -->
            <div id="withdrawalReportSection" class="content-section">
                <div class="profile-header-bar">
                    <div class="profile-header-title">Payout Report</div>
                    <div class="profile-breadcrumb"><a href="#" onclick="switchView('dashboardSection', document.querySelector('.nav-item')); return false;">Home</a> &raquo; Payout Report</div>
                </div>
                <div class="table-container">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>User ID</th>
                                <th>Name</th>
                                <th>Amount</th>
                                <th>Service Charge</th>
                                <th>Net Amount</th>
                                <th>Payout Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="8" class="empty-row-msg">No records found</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- VIEW: Daily ROI Income -->
            <div id="dailyRoiSection" class="content-section">
                <div class="profile-header-bar">
                    <div class="profile-header-title">Daily Roi Income</div>
                    <div class="profile-breadcrumb"><a href="#" onclick="switchView('dashboardSection', document.querySelector('.nav-item')); return false;">Home</a> &raquo; Weekly Roi Income Report</div>
                </div>
                <div class="table-container">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Member ID</th>
                                <th>Package</th>
                                <th>Income</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="5" class="empty-row-msg">No records found</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- VIEW: Ticket Submit -->
            <div id="ticketSubmitSection" class="content-section">
                <div class="profile-header-bar">
                    <div class="profile-header-title">Ticket Submit</div>
                    <div class="profile-breadcrumb"><a href="#" onclick="switchView('dashboardSection', document.querySelector('.nav-item')); return false;">Home</a> &raquo; Ticket Submit</div>
                </div>
                <div class="form-container" style="max-width: 700px;">
                    <div class="form-group">
                        <label>User ID</label>
                        <input type="text" class="form-control form-control-disabled" value="RJ129688" readonly>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="textarea-control" placeholder="Enter Description"></textarea>
                    </div>
                    <div class="form-actions">
                        <button class="btn-submit-gold" style="padding: 10px 20px;">Create Ticket</button>
                    </div>
                </div>
            </div>

            <!-- VIEW: Ticket Report -->
            <div id="ticketReportSection" class="content-section">
                <div class="profile-header-bar">
                    <div class="profile-header-title">Ticket Report</div>
                    <div class="profile-breadcrumb"><a href="#" onclick="switchView('dashboardSection', document.querySelector('.nav-item')); return false;">Home</a> &raquo; Ticket Report</div>
                </div>
                <div class="table-container">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Sno</th>
                                <th>User Id</th>
                                <th>Message</th>
                                <th>Reply</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="5" class="empty-row-msg">No records found</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php for ($i = 1; $i <= 8; $i++): ?>
            <!-- Autopool Pack <?php echo $i; ?> Sections -->
            <div id="autopoolPack<?php echo $i; ?>MyNetwork" class="content-section">
                <div class="profile-header-bar">
                    <div class="profile-header-title">Autopool Pack <?php echo $i; ?> - My Network</div>
                    <div class="profile-breadcrumb"><a href="#" onclick="switchView('dashboardSection', document.querySelector('.nav-item')); return false;">Home</a> &raquo; Autopool Pack <?php echo $i; ?> &raquo; My Network</div>
                </div>
                <div class="table-container">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>User ID</th>
                                <th>Name</th>
                                <th>Sponsor ID</th>
                                <th>Joining Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="6" class="empty-row-msg">No records found</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="autopoolPack<?php echo $i; ?>Income" class="content-section">
                <div class="profile-header-bar">
                    <div class="profile-header-title">Autopool Pack <?php echo $i; ?> - Autopool Income</div>
                    <div class="profile-breadcrumb"><a href="#" onclick="switchView('dashboardSection', document.querySelector('.nav-item')); return false;">Home</a> &raquo; Autopool Pack <?php echo $i; ?> &raquo; Autopool Income</div>
                </div>
                <div class="table-container">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>User ID</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="5" class="empty-row-msg">No records found</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="autopoolPack<?php echo $i; ?>Sponsor" class="content-section">
                <div class="profile-header-bar">
                    <div class="profile-header-title">Autopool Pack <?php echo $i; ?> - Sponsor Income</div>
                    <div class="profile-breadcrumb"><a href="#" onclick="switchView('dashboardSection', document.querySelector('.nav-item')); return false;">Home</a> &raquo; Autopool Pack <?php echo $i; ?> &raquo; Sponsor Income</div>
                </div>
                <div class="table-container">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>User ID</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="5" class="empty-row-msg">No records found</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="autopoolPack<?php echo $i; ?>Level" class="content-section">
                <div class="profile-header-bar">
                    <div class="profile-header-title">Autopool Pack <?php echo $i; ?> - Level Income</div>
                    <div class="profile-breadcrumb"><a href="#" onclick="switchView('dashboardSection', document.querySelector('.nav-item')); return false;">Home</a> &raquo; Autopool Pack <?php echo $i; ?> &raquo; Level Income</div>
                </div>
                <div class="table-container">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>User ID</th>
                                <th>Amount</th>
                                <th>Level</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="6" class="empty-row-msg">No records found</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="autopoolPack<?php echo $i; ?>Reward" class="content-section">
                <div class="profile-header-bar">
                    <div class="profile-header-title">Autopool Pack <?php echo $i; ?> - Reward Income</div>
                    <div class="profile-breadcrumb"><a href="#" onclick="switchView('dashboardSection', document.querySelector('.nav-item')); return false;">Home</a> &raquo; Autopool Pack <?php echo $i; ?> &raquo; Reward Income</div>
                </div>
                <div class="table-container">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>User ID</th>
                                <th>Reward Name</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="6" class="empty-row-msg">No records found</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endfor; ?>

            <?php for ($i = 1; $i <= 6; $i++): ?>
            <!-- Infinity Pack <?php echo $i; ?> Sections -->
            <div id="infinityPack<?php echo $i; ?>Matrix" class="content-section">
                <div class="profile-header-bar">
                    <div class="profile-header-title">Infinity Pack <?php echo $i; ?> - Matrix</div>
                    <div class="profile-breadcrumb"><a href="#" onclick="switchView('dashboardSection', document.querySelector('.nav-item')); return false;">Home</a> &raquo; Infinity Pack <?php echo $i; ?> &raquo; Matrix</div>
                </div>
                <div class="table-container">
                    <div style="text-align: center; padding: 40px; color: #ffb703;">
                        <i class="fa-solid fa-sitemap" style="font-size: 48px; margin-bottom: 15px;"></i>
                        <h3>Infinity Pack <?php echo $i; ?> Matrix View</h3>
                        <p style="color: #a0aec0; margin-top: 10px;">Matrix structure visualization for Infinity Pack <?php echo $i; ?> will be loaded here.</p>
                    </div>
                </div>
            </div>

            <div id="infinityPack<?php echo $i; ?>IncomeReport" class="content-section">
                <div class="profile-header-bar">
                    <div class="profile-header-title">Infinity Pack <?php echo $i; ?> - Income Report</div>
                    <div class="profile-breadcrumb"><a href="#" onclick="switchView('dashboardSection', document.querySelector('.nav-item')); return false;">Home</a> &raquo; Infinity Pack <?php echo $i; ?> &raquo; Income Report</div>
                </div>
                <div class="table-container">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>User ID</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="5" class="empty-row-msg">No records found</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endfor; ?>

            <!-- Footer -->
            <div class="db-footer">
                &copy; 2026 RJ Rathore Trading. All rights reserved. &bull; Contact: <a href="mailto:support@rjrathoretrading.online">support@rjrathoretrading.online</a>
            </div>

        </main>
    </div>

    <!-- UI Interaction Scripts Architecture -->
    <script>
        const toggleMenuBtn = document.getElementById('toggleMenuBtn');
        const sidebarEl = document.querySelector('sidebar');
        const sidebarBackdrop = document.getElementById('sidebarBackdrop');

        function openSidebarMobile() {
            sidebarEl.classList.add('sidebar-open');
            sidebarBackdrop.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebarMobile() {
            sidebarEl.classList.remove('sidebar-open');
            sidebarBackdrop.classList.remove('show');
            document.body.style.overflow = '';
        }

        if (toggleMenuBtn && sidebarEl) {
            toggleMenuBtn.addEventListener('click', () => {
                if (window.innerWidth <= 900) {
                    if (sidebarEl.classList.contains('sidebar-open')) {
                        closeSidebarMobile();
                    } else {
                        openSidebarMobile();
                    }
                } else {
                    sidebarEl.classList.toggle('sidebar-hidden');
                }
            });
        }

        if (sidebarBackdrop) {
            sidebarBackdrop.addEventListener('click', closeSidebarMobile);
        }

        // Auto-close sidebar on mobile when a menu link is tapped
        if (sidebarEl) {
            sidebarEl.addEventListener('click', (e) => {
                if (window.innerWidth <= 900 && (e.target.closest('.nav-item') || e.target.closest('.submenu-item'))) {
                    closeSidebarMobile();
                }
            });
        }

        function switchView(sectionId, clickedElement) {
            // 1. सभी कंटेंट सेक्शन्स से active-view क्लास हटाएं
            const sections = document.querySelectorAll('.content-section');
            sections.forEach(sec => sec.classList.remove('active-view'));

            // 2. सिलेक्टेड सेक्शन को स्क्रीन पर दिखाएं
            const target = document.getElementById(sectionId);
            if(target) target.classList.add('active-view');

            // 3. सभी मेनू आइटम से पुरानी 'active' क्लास हटाएं
            const mainNavItems = document.querySelectorAll('.nav-item');
            mainNavItems.forEach(item => item.classList.remove('active'));

            // 4. सभी सब-मेनू आइटम से पुरानी 'active-sub' क्लास हटाएं
            const subItems = document.querySelectorAll('.submenu-item');
            subItems.forEach(item => item.classList.remove('active-sub'));
            
            // 5. वर्तमान में क्लिक किए गए एलिमेंट को एक्टिव क्लास असाइन करें
            if (clickedElement) {
                if (clickedElement.classList.contains('submenu-item')) {
                    clickedElement.classList.add('active-sub');
                    clickedElement.closest('.submenu-container')?.previousElementSibling?.classList.add('active');
                } else if (clickedElement.classList.contains('nav-item')) {
                    clickedElement.classList.add('active');
                }
            } else {
                // अगर डायरेक्ट आईडी से कॉल हुआ हो (जैसे क्विक बटन्स से)
                if(sectionId === 'dashboardSection') {
                    document.querySelector('.nav-item').classList.add('active');
                }
            }

            // 6. हर सेक्शन को उसका अपना URL दें (एक ही फाइल में, अलग-अलग पेज जैसा अनुभव)
            if (target && window.location.hash !== '#' + sectionId) {
                history.pushState(null, '', '#' + sectionId);
            }
            window.scrollTo(0, 0);

            // Hook for Choice Modal Popups on Buy Packages
            if (sectionId === 'autopoolPackageSection' || sectionId === 'infinityPackageSection') {
                showChoiceModal(sectionId);
            }
        }

        let activeChoiceSection = '';
        function showChoiceModal(sectionId) {
            activeChoiceSection = sectionId;
            document.getElementById('buyChoiceModal').style.display = 'flex';
        }

        function handleChoice(choice) {
            document.getElementById('buyChoiceModal').style.display = 'none';
            const targetInputId = activeChoiceSection === 'autopoolPackageSection' ? 'autopoolMemberId' : 'infinityMemberId';
            const inputEl = document.getElementById(targetInputId);
            
            if (inputEl) {
                if (choice === 'self') {
                    inputEl.value = 'RJ129688';
                    inputEl.readOnly = true;
                    inputEl.classList.add('form-control-disabled');
                } else {
                    inputEl.value = '';
                    inputEl.readOnly = false;
                    inputEl.classList.remove('form-control-disabled');
                }
            }
        }

        // पेज लोड होने पर या ब्राउज़र के Back/Forward बटन दबाने पर सही सेक्शन खोलें
        function openSectionFromHash() {
            const id = window.location.hash.replace('#', '');
            if (id && document.getElementById(id)) {
                let navMatch = null;
                document.querySelectorAll('.nav-item, .submenu-item').forEach(el => {
                    const onclickAttr = el.getAttribute('onclick') || '';
                    if (onclickAttr.includes("'" + id + "'")) navMatch = el;
                });
                switchView(id, navMatch);
                if (navMatch && navMatch.classList.contains('submenu-item')) {
                    navMatch.closest('.submenu-container')?.classList.add('show');
                    navMatch.closest('.submenu-container')?.previousElementSibling?.classList.add('open');
                }
            }
        }
        window.addEventListener('popstate', openSectionFromHash);
        window.addEventListener('DOMContentLoaded', () => {
            if (window.location.hash) openSectionFromHash();
        });

        // साइडबार एकॉर्डियन मेनू ड्रॉपडाउन 
        const toggles = document.querySelectorAll('.sidebar-toggle');
        toggles.forEach(toggle => {
            toggle.addEventListener('click', () => {
                const targetId = toggle.getAttribute('data-target');
                const submenu = document.getElementById(targetId);
                toggle.classList.toggle('open');
                
                if (submenu.style.maxHeight && submenu.style.maxHeight !== '0px') {
                    submenu.style.maxHeight = '0px';
                } else {
                    submenu.style.maxHeight = submenu.scrollHeight + 'px';
                }
            });
        });

        // Fullscreen toggle (with vendor-prefixed fallbacks for older browsers)
        function toggleFullscreen() {
            const el = document.documentElement;
            const isFullscreen = document.fullscreenElement || document.webkitFullscreenElement || document.msFullscreenElement;

            if (!isFullscreen) {
                if (el.requestFullscreen) el.requestFullscreen();
                else if (el.webkitRequestFullscreen) el.webkitRequestFullscreen();
                else if (el.msRequestFullscreen) el.msRequestFullscreen();
            } else {
                if (document.exitFullscreen) document.exitFullscreen();
                else if (document.webkitExitFullscreen) document.webkitExitFullscreen();
                else if (document.msExitFullscreen) document.msExitFullscreen();
            }
        }

        function updateFullscreenIcon() {
            const icon = document.querySelector('#fullscreenBtn i');
            if (!icon) return;
            const isFullscreen = document.fullscreenElement || document.webkitFullscreenElement || document.msFullscreenElement;
            icon.classList.toggle('fa-expand', !isFullscreen);
            icon.classList.toggle('fa-compress', !!isFullscreen);
        }

        document.addEventListener('fullscreenchange', updateFullscreenIcon);
        document.addEventListener('webkitfullscreenchange', updateFullscreenIcon);
        document.addEventListener('MSFullscreenChange', updateFullscreenIcon);

        // प्रोफाइल ड्रॉपडाउन हेडर ऐक्शन
        const profileBtn = document.getElementById('profileMenuBtn');
        const dropdownMenu = document.getElementById('profileDropdownMenu');

        profileBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdownMenu.classList.toggle('show');
        });

        document.addEventListener('click', () => {
            dropdownMenu.classList.remove('show');
        });

        // Global tap/ripple + glow feedback for every button-like element
        (function () {
            const tappableSelector = 'button, .db-action-btn, .btn-table-action, .nav-item, .submenu-item, .dropdown-item, .db-referral-btn';
            document.addEventListener('click', function (e) {
                const el = e.target.closest(tappableSelector);
                if (!el) return;

                const rect = el.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const ripple = document.createElement('span');
                ripple.className = 'ripple-effect';
                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = (e.clientX - rect.left - size / 2) + 'px';
                ripple.style.top = (e.clientY - rect.top - size / 2) + 'px';
                el.appendChild(ripple);
                setTimeout(() => ripple.remove(), 600);

                el.classList.add('tap-glow');
                setTimeout(() => el.classList.remove('tap-glow'), 280);
            });
        })();

        // --- 3D Cyber Fluid Engine (Three.js Background) — Enhanced ---
        let scene, camera, renderer, ambientLight;
        let clock = new THREE.Clock();
        const layers = []; // multiple particle layers with independent drift/twinkle
        let coreMesh, ringMesh, linesMesh;
        let mouseX = 0, mouseY = 0;

        // Custom shader material so particles twinkle (pulse) and have soft round glow,
        // instead of flat static dots.
        function makeTwinkleMaterial(color, baseSize) {
            return new THREE.ShaderMaterial({
                uniforms: {
                    uTime:  { value: 0 },
                    uColor: { value: new THREE.Color(color) },
                    uSize:  { value: baseSize }
                },
                vertexShader: `
                    attribute float aPhase;
                    attribute float aSpeed;
                    attribute float aScale;
                    uniform float uTime;
                    uniform float uSize;
                    varying float vTwinkle;
                    void main() {
                        vTwinkle = 0.55 + 0.45 * sin(uTime * aSpeed + aPhase);
                        vec4 mvPosition = modelViewMatrix * vec4(position, 1.0);
                        gl_PointSize = uSize * aScale * vTwinkle * (300.0 / -mvPosition.z);
                        gl_Position = projectionMatrix * mvPosition;
                    }
                `,
                fragmentShader: `
                    uniform vec3 uColor;
                    varying float vTwinkle;
                    void main() {
                        vec2 c = gl_PointCoord - vec2(0.5);
                        float d = length(c);
                        float glow = smoothstep(0.5, 0.0, d);
                        if (glow < 0.02) discard;
                        gl_FragColor = vec4(uColor, glow * vTwinkle);
                    }
                `,
                transparent: true,
                depthWrite: false,
                blending: THREE.AdditiveBlending
            });
        }

        function makeParticleLayer(count, spread, color, size, driftSpeed) {
            const geometry = new THREE.BufferGeometry();
            const positions = new Float32Array(count * 3);
            const phases = new Float32Array(count);
            const speeds = new Float32Array(count);
            const scales = new Float32Array(count);

            for (let i = 0; i < count; i++) {
                positions[i * 3]     = (Math.random() * 2 - 1) * spread.x;
                positions[i * 3 + 1] = (Math.random() * 2 - 1) * spread.y;
                positions[i * 3 + 2] = (Math.random() * 2 - 1) * spread.z;
                phases[i] = Math.random() * Math.PI * 2;
                speeds[i] = 0.6 + Math.random() * 1.8;
                scales[i] = 0.5 + Math.random() * 1.3;
            }

            geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
            geometry.setAttribute('aPhase', new THREE.BufferAttribute(phases, 1));
            geometry.setAttribute('aSpeed', new THREE.BufferAttribute(speeds, 1));
            geometry.setAttribute('aScale', new THREE.BufferAttribute(scales, 1));

            const material = makeTwinkleMaterial(color, size);
            const points = new THREE.Points(geometry, material);
            points.userData.driftSpeed = driftSpeed;
            points.userData.spread = spread;
            scene.add(points);
            layers.push(points);
            return points;
        }

        // Thin constellation lines connecting a subset of nearby foreground particles —
        // gives the background a "network / trading data" feel instead of plain rain.
        function makeConstellation(sourcePoints, maxLinks, maxDist) {
            const posAttr = sourcePoints.geometry.attributes.position;
            const linePositions = [];
            const total = posAttr.count;
            let links = 0;
            for (let i = 0; i < total && links < maxLinks; i += 3) {
                const ax = posAttr.getX(i), ay = posAttr.getY(i), az = posAttr.getZ(i);
                for (let j = i + 3; j < total && links < maxLinks; j += 7) {
                    const bx = posAttr.getX(j), by = posAttr.getY(j), bz = posAttr.getZ(j);
                    const dist = Math.hypot(ax - bx, ay - by, az - bz);
                    if (dist < maxDist) {
                        linePositions.push(ax, ay, az, bx, by, bz);
                        links++;
                    }
                }
            }
            const geometry = new THREE.BufferGeometry();
            geometry.setAttribute('position', new THREE.Float32BufferAttribute(linePositions, 3));
            const material = new THREE.LineBasicMaterial({
                color: 0xffb703,
                transparent: true,
                opacity: 0.12,
                blending: THREE.AdditiveBlending
            });
            const lines = new THREE.LineSegments(geometry, material);
            scene.add(lines);
            return lines;
        }

        function init() {
            const container = document.getElementById('canvas-container');
            scene = new THREE.Scene();
            scene.fog = new THREE.FogExp2(0x030b14, 0.0022);

            camera = new THREE.PerspectiveCamera(60, window.innerWidth / window.innerHeight, 1, 1000);
            camera.position.z = 65;
            camera.position.y = 12;
            camera.rotation.x = -0.12;

            ambientLight = new THREE.AmbientLight(0x222222);
            scene.add(ambientLight);

            let blueLight = new THREE.PointLight(0x00f2fe, 4, 400);
            blueLight.position.set(-120, 100, -60);
            scene.add(blueLight);

            let goldLight = new THREE.PointLight(0xffb703, 3.5, 400);
            goldLight.position.set(120, -100, -60);
            scene.add(goldLight);
            window.cyberLights = { blueLight, goldLight };

            renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
            renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
            renderer.setSize(window.innerWidth, window.innerHeight);
            container.appendChild(renderer.domElement);

            // Layer 1: distant gold haze (slow, large spread, small points)
            makeParticleLayer(700, { x: 420, y: 320, z: 260 }, 0xffb703, 26, 0.35);
            // Layer 2: mid cyan drifting field (adds depth + color contrast)
            makeParticleLayer(500, { x: 380, y: 280, z: 220 }, 0x00e5ff, 22, 0.55);
            // Layer 3: bright foreground gold sparks (faster, crisper)
            const foreground = makeParticleLayer(420, { x: 300, y: 220, z: 160 }, 0xffe57f, 30, 0.9);

            // Faint constellation network among the foreground sparks
            linesMesh = makeConstellation(foreground, 90, 55);

            // Slow-rotating wireframe icosahedron "core" — gives a focal 3D anchor
            const coreGeo = new THREE.IcosahedronGeometry(16, 1);
            const coreMat = new THREE.MeshBasicMaterial({
                color: 0xffb703,
                wireframe: true,
                transparent: true,
                opacity: 0.18
            });
            coreMesh = new THREE.Mesh(coreGeo, coreMat);
            coreMesh.position.set(0, -5, -140);
            scene.add(coreMesh);

            // Thin orbiting ring around the core for extra depth
            const ringGeo = new THREE.TorusGeometry(24, 0.25, 8, 100);
            const ringMat = new THREE.MeshBasicMaterial({
                color: 0x00e5ff,
                transparent: true,
                opacity: 0.25
            });
            ringMesh = new THREE.Mesh(ringGeo, ringMat);
            ringMesh.position.copy(coreMesh.position);
            ringMesh.rotation.x = Math.PI / 2.3;
            scene.add(ringMesh);

            window.addEventListener('resize', onWindowResize, false);
            window.addEventListener('mousemove', onMouseMove, false);
            animate();
        }

        function onMouseMove(e) {
            mouseX = (e.clientX / window.innerWidth - 0.5);
            mouseY = (e.clientY / window.innerHeight - 0.5);
        }

        function onWindowResize() {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        }

        function animate() {
            requestAnimationFrame(animate);
            const t = clock.getElapsedTime();

            // Drift + gently wrap each particle layer, with subtle per-layer sine sway
            layers.forEach((points) => {
                const positions = points.geometry.attributes.position.array;
                const spread = points.userData.spread;
                const speed = points.userData.driftSpeed;
                for (let i = 0; i < positions.length; i += 3) {
                    positions[i + 1] -= speed * 0.35;
                    positions[i] += Math.sin(t * 0.3 + positions[i + 2] * 0.01) * 0.03;
                    if (positions[i + 1] < -spread.y) {
                        positions[i + 1] = spread.y;
                    }
                }
                points.geometry.attributes.position.needsUpdate = true;
                points.rotation.y += 0.0006 * speed;
                points.material.uniforms.uTime.value = t;
            });

            // Slowly tumble the wireframe core + counter-rotate its ring
            if (coreMesh) {
                coreMesh.rotation.y += 0.0025;
                coreMesh.rotation.x += 0.0012;
                coreMesh.material.opacity = 0.14 + 0.06 * Math.sin(t * 0.5);
            }
            if (ringMesh) {
                ringMesh.rotation.z += 0.0018;
                ringMesh.material.opacity = 0.18 + 0.08 * Math.sin(t * 0.7 + 1);
            }
            if (linesMesh) {
                linesMesh.material.opacity = 0.08 + 0.05 * Math.sin(t * 0.4);
            }

            // Pulse the two point lights for a subtle breathing glow
            if (window.cyberLights) {
                window.cyberLights.blueLight.intensity = 3.2 + Math.sin(t * 0.6) * 1.2;
                window.cyberLights.goldLight.intensity = 2.8 + Math.cos(t * 0.5) * 1.1;
            }

            // Gentle parallax camera drift following the mouse — adds a "living" 3D feel
            camera.position.x += (mouseX * 18 - camera.position.x) * 0.02;
            camera.position.y += (12 - mouseY * 12 - camera.position.y) * 0.02;
            camera.lookAt(0, -5, -140);

            renderer.render(scene, camera);
        }

        init();
    </script>
</body>
</html>
