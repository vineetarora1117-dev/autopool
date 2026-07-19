<?php include '../includes/header.php'; ?>

<div id="directMemberSection" class="content-section active-view">
                <div class="profile-header-bar">
                    <div class="profile-header-title">Direct Member</div>
                    <div class="profile-breadcrumb"><a href="#" onclick="switchView('dashboardSection', document.querySelector('.nav-item')); return false;">Home</a> &raquo; Direct Member</div>
                </div>
                <div class="table-container">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>User ID</th>
                                <th>Name</th>
                                <th>Sponsor Id</th>
                                <th>Mobile No</th>
                                <th>Package</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="8" class="empty-row-msg">No records found</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- VIEW: Level Team -->

<?php include '../includes/footer.php'; ?>