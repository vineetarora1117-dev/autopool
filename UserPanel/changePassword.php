<?php include '../includes/header.php'; ?>

<div id="changePasswordSection" class="content-section active-view">
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

<?php include '../includes/footer.php'; ?>