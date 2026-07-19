<?php include '../includes/header.php'; ?>

<div id="upgradeIdReportSection" class="content-section active-view">
                <div class="profile-header-bar">
                    <div class="profile-header-title">Upgrade Report</div>
                    <div class="profile-breadcrumb"><a href="#" onclick="switchView('dashboardSection', document.querySelector('.nav-item')); return false;">Home</a> &raquo; Upgrade ID Report</div>
                </div>
                <div class="table-container">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Member Id</th>
                                <th>Amount</th>
                                <th>Referral Id</th>
                                <th>Date</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="6" class="empty-row-msg">No records found</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- VIEW: Self Upgrade Report -->

<?php include '../includes/footer.php'; ?>