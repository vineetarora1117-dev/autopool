<?php 
require_once 'includes/check_package.php';
include '../includes/header.php'; 

$pack = isset($_GET['pack']) ? (int)$_GET['pack'] : 1;
?>
<style>
.network-card {
    background: rgba(6, 17, 33, 0.75);
    border: 1px solid rgba(255, 183, 3, 0.2);
    border-radius: 12px;
    padding: 24px;
    margin-top: 20px;
}
.network-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 10px;
}
.btn-nav-tree {
    background: transparent;
    color: #ffb703;
    border: 1px solid #ffb703;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 13px;
    cursor: pointer;
    font-weight: bold;
    transition: 0.2s;
}
.btn-nav-tree:hover {
    background: rgba(255, 183, 3, 0.1);
}
.btn-explore {
    background: #ffb703;
    color: #000;
    border: none;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
    font-weight: bold;
    transition: 0.2s;
}
.btn-explore:hover {
    background: #bfa100;
}
.badge-slot {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
}
.badge-left {
    background: rgba(46, 204, 113, 0.2);
    color: #2ecc71;
    border: 1px solid #2ecc71;
}
.badge-right {
    background: rgba(52, 152, 219, 0.2);
    color: #3498db;
    border: 1px solid #3498db;
}
</style>

<div id="autopoolPack<?php echo $pack; ?>MyNetwork" class="content-section active-view">
    <div class="profile-header-bar">
        <div class="profile-header-title">Autopool Pack <?php echo $pack; ?> Network Explorer</div>
        <div class="profile-breadcrumb">
            <a href="index.php">Home</a> &raquo; 
            Autopool Pack <?php echo $pack; ?> &raquo; 
            My Network
        </div>
    </div>

    <div class="network-card">
        <div class="network-header">
            <div style="font-size: 16px; color: #fff;">
                Viewing Matrix Downlines of: <strong id="currentViewingUser" style="color: #ffb703;"><?php echo htmlspecialchars($user_id); ?></strong>
            </div>
            <div id="navigationButtons" style="display: flex; gap: 10px;">
                <!-- Navigation buttons will be injected here -->
            </div>
        </div>

        <div class="table-container">
            <table class="custom-table" style="width: 100%; text-align: left;">
                <thead>
                    <tr>
                        <th>Position Slot</th>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Sponsor ID</th>
                        <th>Joining Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="networkTableBody">
                    <tr><td colspan="7" style="text-align:center; padding:30px; color:#a0aec0;"><i class="fa-solid fa-spinner fa-spin"></i> Loading tree downlines...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$envConfig = parse_ini_file(__DIR__ . '/../.env');
$siteUrl = rtrim($envConfig['SITE_URL'] ?? 'http://localhost/autopool', '/');
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const rootUser = "<?php echo htmlspecialchars($user_id); ?>";
    const pack = <?php echo $pack; ?>;
    const apiUrl = "<?php echo $siteUrl; ?>/UserPanel/api/packages.php";

    function loadNetwork(targetUser) {
        const tbody = document.getElementById('networkTableBody');
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:30px; color:#a0aec0;"><i class="fa-solid fa-spinner fa-spin"></i> Loading tree downlines...</td></tr>`;

        fetch(`${apiUrl}?action=get_network&pack=${pack}&user_id=${targetUser}`)
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:30px; color:#e74c3c; font-weight:bold;">Error: ${data.message}</td></tr>`;
                    return;
                }

                document.getElementById('currentViewingUser').innerText = data.target_id;

                // Configure Back / Up Navigation Buttons
                const navContainer = document.getElementById('navigationButtons');
                navContainer.innerHTML = '';
                
                if (data.target_id !== rootUser) {
                    // Back to Root button
                    const btnRoot = document.createElement('button');
                    btnRoot.className = 'btn-nav-tree';
                    btnRoot.innerHTML = '<i class="fa-solid fa-house-user"></i> Back to My Tree';
                    btnRoot.onclick = () => loadNetwork(rootUser);
                    navContainer.appendChild(btnRoot);

                    // Back to Parent button
                    if (data.parent_id) {
                        const btnParent = document.createElement('button');
                        btnParent.className = 'btn-nav-tree';
                        btnParent.innerHTML = '<i class="fa-solid fa-arrow-up-long"></i> Up One Level';
                        btnParent.onclick = () => loadNetwork(data.parent_id);
                        navContainer.appendChild(btnParent);
                    }
                }

                // Populate Table
                tbody.innerHTML = '';
                if (data.children.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:30px; color:#a0aec0;">No matrix downlines found under this member.</td></tr>`;
                    return;
                }

                data.children.forEach(child => {
                    const row = document.createElement('tr');
                    
                    const slotText = child.position_slot == 1 ? 'Left Slot' : 'Right Slot';
                    const slotClass = child.position_slot == 1 ? 'badge-left' : 'badge-right';

                    const formattedDate = new Date(child.created_at).toLocaleDateString('en-GB', {
                        day: '2-digit', month: 'short', year: 'numeric'
                    });

                    row.innerHTML = `
                        <td><span class="badge-slot ${slotClass}">${slotText}</span></td>
                        <td style="font-weight: bold; color: #ffb703;">${child.user_id}</td>
                        <td>${child.name}</td>
                        <td style="color: #a0aec0;">${child.sponsor_id}</td>
                        <td>${formattedDate}</td>
                        <td><span class="badge ${child.status === 'Active' ? 'badge-active' : 'badge-inactive'}">${child.status}</span></td>
                        <td><button class="btn-explore" data-userid="${child.user_id}"><i class="fa-solid fa-sitemap"></i> Explore</button></td>
                    `;
                    tbody.appendChild(row);
                });

                // Attach explore action click events
                tbody.querySelectorAll('.btn-explore').forEach(btn => {
                    btn.addEventListener('click', function() {
                        loadNetwork(this.getAttribute('data-userid'));
                    });
                });
            })
            .catch(err => {
                tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:30px; color:#e74c3c;">Failed to load network data.</td></tr>`;
            });
    }

    // Load initial root user matrix on startup
    loadNetwork(rootUser);
});
</script>
<?php include '../includes/footer.php'; ?>