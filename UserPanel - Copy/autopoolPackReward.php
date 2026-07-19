<?php include '../includes/header.php'; ?>

<div id="autopoolPack<?php echo htmlspecialchars($_GET['pack'] ?? '1'); ?>Reward" class="content-section active-view">
                <div class="profile-header-bar">
                    <div class="profile-header-title">Autopool Pack <?php echo htmlspecialchars($_GET['pack'] ?? '1'); ?> - Reward Income</div>
                    <div class="profile-breadcrumb"><a href="#" onclick="switchView('dashboardSection', document.querySelector('.nav-item')); return false;">Home</a> &raquo; Autopool Pack <?php echo htmlspecialchars($_GET['pack'] ?? '1'); ?> &raquo; Reward Income</div>
                </div>
                <div class="table-container">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>User ID</th>
                                <th>Reward Name</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="6" class="empty-row-msg">No records found</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endfor; ?>

            <?php for ($i = 1; $i <= 6; $i++): ?>
            <!-- Infinity Pack <?php echo htmlspecialchars($_GET['pack'] ?? '1'); ?> Sections -->

<?php include '../includes/footer.php'; ?>