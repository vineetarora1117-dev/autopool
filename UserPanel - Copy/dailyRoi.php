<?php include '../includes/header.php'; ?>

<div id="dailyRoiSection" class="content-section active-view">
                <div class="profile-header-bar">
                    <div class="profile-header-title">Daily Roi Income</div>
                    <div class="profile-breadcrumb"><a href="#" onclick="switchView('dashboardSection', document.querySelector('.nav-item')); return false;">Home</a> &raquo; Weekly Roi Income Report</div>
                </div>
                <div class="table-container">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Member ID</th>
                                <th>Package</th>
                                <th>Income</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="5" class="empty-row-msg">No records found</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- VIEW: Ticket Submit -->

<?php include '../includes/footer.php'; ?>