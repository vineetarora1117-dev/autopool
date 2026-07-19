<?php include '../includes/header.php'; ?>

<div id="upgradeIdSection" class="content-section active-view">
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

<?php include '../includes/footer.php'; ?>