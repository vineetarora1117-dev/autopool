<?php include '../includes/header.php'; ?>

<div id="profileViewSection" class="content-section active-view">
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

<?php include '../includes/footer.php'; ?>