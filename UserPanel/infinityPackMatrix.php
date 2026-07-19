<?php include '../includes/header.php'; ?>

<div id="infinityPack<?php echo htmlspecialchars($_GET['pack'] ?? '1'); ?>Matrix" class="content-section active-view">
                <div class="profile-header-bar">
                    <div class="profile-header-title">Infinity Pack <?php echo htmlspecialchars($_GET['pack'] ?? '1'); ?> - Matrix</div>
                    <div class="profile-breadcrumb"><a href="#" onclick="switchView('dashboardSection', document.querySelector('.nav-item')); return false;">Home</a> &raquo; Infinity Pack <?php echo htmlspecialchars($_GET['pack'] ?? '1'); ?> &raquo; Matrix</div>
                </div>
                <div class="table-container">
                    <div style="text-align: center; padding: 40px; color: #ffb703;">
                        <i class="fa-solid fa-sitemap" style="font-size: 48px; margin-bottom: 15px;"></i>
                        <h3>Infinity Pack <?php echo htmlspecialchars($_GET['pack'] ?? '1'); ?> Matrix View</h3>
                        <p style="color: #a0aec0; margin-top: 10px;">Matrix structure visualization for Infinity Pack <?php echo htmlspecialchars($_GET['pack'] ?? '1'); ?> will be loaded here.</p>
                    </div>
                </div>
            </div>

<?php include '../includes/footer.php'; ?>