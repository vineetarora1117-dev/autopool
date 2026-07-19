<?php include '../includes/header.php'; ?>

<div id="infinityPack<?php echo htmlspecialchars($_GET['pack'] ?? '1'); ?>IncomeReport" class="content-section active-view">
                <div class="profile-header-bar">
                    <div class="profile-header-title">Infinity Pack <?php echo htmlspecialchars($_GET['pack'] ?? '1'); ?> - Income Report</div>
                    <div class="profile-breadcrumb"><a href="#" onclick="switchView('dashboardSection', document.querySelector('.nav-item')); return false;">Home</a> &raquo; Infinity Pack <?php echo htmlspecialchars($_GET['pack'] ?? '1'); ?> &raquo; Income Report</div>
                </div>
                <div class="table-container">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>User ID</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="5" class="empty-row-msg">No records found</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endfor; ?>

            <!-- Footer -->
            <div class="db-footer">
                &copy; 2026 RJ Rathore Trading. All rights reserved. &bull; Contact: <a href="mailto:support@rjrathoretrading.online">support@rjrathoretrading.online</a>
            </div>

<?php include '../includes/footer.php'; ?>