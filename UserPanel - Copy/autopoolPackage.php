<?php include '../includes/header.php'; ?>

<div id="autopoolPackageSection" class="content-section active-view">
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

<?php include '../includes/footer.php'; ?>