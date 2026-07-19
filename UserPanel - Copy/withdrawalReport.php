<?php include '../includes/header.php'; ?>

<div id="withdrawalReportSection" class="content-section active-view">
                <div class="profile-header-bar">
                    <div class="profile-header-title">Payout Report</div>
                    <div class="profile-breadcrumb"><a href="#" onclick="switchView('dashboardSection', document.querySelector('.nav-item')); return false;">Home</a> &raquo; Payout Report</div>
                </div>
                <div class="table-container">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>User ID</th>
                                <th>Name</th>
                                <th>Amount</th>
                                <th>Service Charge</th>
                                <th>Net Amount</th>
                                <th>Payout Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="8" class="empty-row-msg">No records found</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- VIEW: Daily ROI Income -->

<?php include '../includes/footer.php'; ?>