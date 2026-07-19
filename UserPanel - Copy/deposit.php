<?php include '../includes/header.php'; ?>

<div id="depositSection" class="content-section active-view">
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

<?php include '../includes/footer.php'; ?>