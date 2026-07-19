<?php include '../includes/header.php'; ?>

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

<?php include '../includes/footer.php'; ?>