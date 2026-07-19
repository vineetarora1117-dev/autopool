<?php include '../includes/header.php'; ?>

<div id="walletAddressSection" class="content-section active-view">
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

<?php include '../includes/footer.php'; ?>