<?php include '../includes/header.php'; ?>

<div id="ticketReportSection" class="content-section active-view">
                <div class="profile-header-bar">
                    <div class="profile-header-title">Ticket Report</div>
                    <div class="profile-breadcrumb"><a href="#" onclick="switchView('dashboardSection', document.querySelector('.nav-item')); return false;">Home</a> &raquo; Ticket Report</div>
                </div>
                <div class="table-container">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Sno</th>
                                <th>User Id</th>
                                <th>Message</th>
                                <th>Reply</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="5" class="empty-row-msg">No records found</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php for ($i = 1; $i <= 8; $i++): ?>
            <!-- Autopool Pack <?php echo $i; ?> Sections -->

<?php include '../includes/footer.php'; ?>