<?php include '../includes/header.php'; ?>

<div id="activateIdSection" class="content-section active-view">
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

<?php include '../includes/footer.php'; ?>