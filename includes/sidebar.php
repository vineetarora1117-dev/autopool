
            <div class="brand-title"><?php $env = parse_ini_file(__DIR__ . '/../.env'); echo $env['SITE_NAME'] ?? 'SAPG'; ?></div>
            <div class="menu-title">Menu</div>
            <div class="nav-list">
                <a class="nav-item" href="index">
                    <div class="nav-item-content"><i class="fa-solid fa-house"></i><span>Dashboard</span></div>
                </a>

                <a class="nav-item" href="myWallet">
                    <div class="nav-item-content"><i class="fa-solid fa-wallet"></i><span>My Wallet</span></div>
                </a>

                <!-- Funds Menu -->
                <div class="nav-item sidebar-toggle" data-target="fundsSubmenu">
                    <div class="nav-item-content"><i class="fa-solid fa-wallet"></i><span>Funds</span></div>
                    <i class="fa-solid fa-chevron-right arrow-icon"></i>
                </div>
                <div class="submenu-container" id="fundsSubmenu">
                    <a class="submenu-item" href="deposit"><i class="fa-regular fa-square"></i> Deposit</a>
                    <a class="submenu-item" href="depositHistory"><i class="fa-regular fa-square"></i> Deposit History</a>
                </div>

                <!-- Profile Menu -->
                <div class="nav-item sidebar-toggle" data-target="profileSubmenu">
                    <div class="nav-item-content"><i class="fa-solid fa-user"></i><span>Profile</span></div>
                    <i class="fa-solid fa-chevron-right arrow-icon"></i>
                </div>
                <div class="submenu-container" id="profileSubmenu">
                    <a class="submenu-item" href="profileView"><i class="fa-regular fa-square"></i> Profile View</a>
                    <a class="submenu-item" href="walletAddress"><i class="fa-regular fa-square"></i> Wallet Address</a>
                    <a class="submenu-item" href="changePassword"><i class="fa-regular fa-square"></i> Change Password</a>
                </div>

                <!-- Team Menu -->
                <div class="nav-item sidebar-toggle" data-target="teamSubmenu">
                    <div class="nav-item-content"><i class="fa-solid fa-sitemap"></i><span>Team</span></div>
                    <i class="fa-solid fa-chevron-right arrow-icon"></i>
                </div>
                <div class="submenu-container" id="teamSubmenu">
                    <a class="submenu-item" href="directMember"><i class="fa-regular fa-square"></i> Direct Team</a>
                    <a class="submenu-item" href="levelTeam"><i class="fa-regular fa-square"></i> Level Team</a>
                </div>
                <!-- Activation Menu (Hidden)
                <div class="nav-item sidebar-toggle" data-target="activationSubmenu">
                    <div class="nav-item-content"><i class="fa-solid fa-cart-shopping"></i><span>Activation</span></div>
                    <i class="fa-solid fa-chevron-right arrow-icon"></i>
                </div>
                <div class="submenu-container" id="activationSubmenu">
                    <a class="submenu-item" href="activateId"><i class="fa-regular fa-square"></i> Active ID</a>
                    <a class="submenu-item" href="activeIdReport"><i class="fa-regular fa-square"></i> Active ID Report</a>
                    <a class="submenu-item" href="upgradeId"><i class="fa-regular fa-square"></i> Upgrade ID</a>
                    <a class="submenu-item" href="upgradeIdReport"><i class="fa-regular fa-square"></i> Upgrade ID Report</a>
                    <a class="submenu-item" href="selfUpgradeReport"><i class="fa-regular fa-square"></i> Self Upgrade ID Report</a>
                </div>
                -->

                <!-- Withdrawal Menu -->
                <div class="nav-item sidebar-toggle" data-target="withdrawalSubmenu">
                    <div class="nav-item-content"><i class="fa-solid fa-money-bill-transfer"></i><span>Withdrawal</span></div>
                    <i class="fa-solid fa-chevron-right arrow-icon"></i>
                </div>
                <div class="submenu-container" id="withdrawalSubmenu">
                    <a class="submenu-item" href="newWithdrawal"><i class="fa-regular fa-square"></i> New Withdrawal</a>
                    <a class="submenu-item" href="withdrawalReport"><i class="fa-regular fa-square"></i> Withdrawal Report</a>
                </div>

                <div class="nav-item sidebar-toggle" data-target="buyPackageSubmenu">
                    <div class="nav-item-content"><i class="fa-solid fa-box-open"></i><span>Buy Package</span></div>
                    <i class="fa-solid fa-chevron-right arrow-icon"></i>
                </div>
                <div class="submenu-container" id="buyPackageSubmenu">
                    <a class="submenu-item" href="#" onclick="openBuyPackageFlow('autopool'); return false;"><i class="fa-regular fa-square"></i> Autopool Package</a>
                    <a class="submenu-item" href="#" onclick="openBuyPackageFlow('infinity'); return false;"><i class="fa-regular fa-square"></i> Infinity Package</a>
                </div>


                <!-- More Menu -->
                <div class="nav-item sidebar-toggle" data-target="moreSubmenu">
                    <div class="nav-item-content"><i class="fa-solid fa-circle-question"></i><span>More</span></div>
                    <i class="fa-solid fa-chevron-right arrow-icon"></i>
                </div>
                <div class="submenu-container" id="moreSubmenu">
                    <a class="submenu-item" href="ticketSubmit"><i class="fa-regular fa-square"></i> Ticket Submit</a>
                    <a class="submenu-item" href="ticketReport"><i class="fa-regular fa-square"></i> View Ticket</a>
                </div>

                <?php for ($i = 1; $i <= 6; $i++): ?>
                <!-- Autopool Pack <?php echo $i; ?> -->
                <div class="nav-item sidebar-toggle" data-target="autopoolPack<?php echo $i; ?>Submenu">
                    <div class="nav-item-content"><i class="fa-solid fa-layer-group"></i><span>Autopool Pack <?php echo $i; ?></span></div>
                    <i class="fa-solid fa-chevron-right arrow-icon"></i>
                </div>
                <div class="submenu-container" id="autopoolPack<?php echo $i; ?>Submenu">
                    <a class="submenu-item" href="autopoolPackMyNetwork?pack=<?php echo $i; ?>"><i class="fa-regular fa-square"></i> My Network</a>
                    <a class="submenu-item" href="autopoolPackIncome?pack=<?php echo $i; ?>"><i class="fa-regular fa-square"></i> Autopool income</a>
                    <a class="submenu-item" href="autopoolPackSponsor?pack=<?php echo $i; ?>"><i class="fa-regular fa-square"></i> Sponsor income</a>
                    <a class="submenu-item" href="autopoolPackLevel?pack=<?php echo $i; ?>"><i class="fa-regular fa-square"></i> Level Income</a>
                    <a class="submenu-item" href="autopoolPackWallet?pack=<?php echo $i; ?>"><i class="fa-regular fa-square"></i> Wallet</a>
                    <?php if ($i == 1): ?>
                    <a class="submenu-item" href="autopoolPackReward?pack=<?php echo $i; ?>"><i class="fa-regular fa-square"></i> Reward Income</a>
                    <?php endif; ?>
                </div>
                <?php endfor; ?>

                <?php for ($i = 1; $i <= 6; $i++): ?>
                <!-- Infinity Pack <?php echo $i; ?> -->
                <div class="nav-item sidebar-toggle" data-target="infinityPack<?php echo $i; ?>Submenu">
                    <div class="nav-item-content"><i class="fa-solid fa-infinity"></i><span>Infinity Pack <?php echo $i; ?></span></div>
                    <i class="fa-solid fa-chevron-right arrow-icon"></i>
                </div>
                <div class="submenu-container" id="infinityPack<?php echo $i; ?>Submenu">
                    <a class="submenu-item" href="infinityPackMatrix?pack=<?php echo $i; ?>"><i class="fa-regular fa-square"></i> Matrix</a>
                    <a class="submenu-item" href="infinityPackIncomeReport?pack=<?php echo $i; ?>"><i class="fa-regular fa-square"></i> Income Report</a>
                </div>
                <?php endfor; ?>

                <div class="nav-item" style="margin-top: 20px;" onclick="window.location.href='login.html'">
                    <div class="nav-item-content"><i class="fa-solid fa-power-off" style="color:#ff4d4d;"></i><span>Logout</span></div>
                </div>
            </div>
        