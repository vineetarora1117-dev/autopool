<?php include '../includes/header.php'; ?>

<div id="editProfileSection" class="content-section active-view">
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

<?php include '../includes/footer.php'; ?>