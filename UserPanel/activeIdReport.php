<?php include '../includes/header.php'; ?>

<div id="activeIdReportSection" class="content-section active-view">
                <div class="profile-header-bar">
                    <div class="profile-header-title">Active By Fund Report</div>
                    <div class="profile-breadcrumb"><a href="#" onclick="switchView('dashboardSection', document.querySelector('.nav-item')); return false;">Home</a> &raquo; Active By Fund Report</div>
                </div>
                <div class="table-container">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Member Id</th>
                                <th>Package</th>
                                <th>Date</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="5" class="empty-row-msg">No records found</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- VIEW: Upgrade ID -->

<?php include '../includes/footer.php'; ?>