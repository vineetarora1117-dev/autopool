<?php require_once 'includes/header.php'; ?>
<style>
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

<div class="breadcrumb">
    <a href="index.php">Dashboard</a> / Network Explorer
</div>

<div class="card">
    <h3 class="card-title">Network Explorer</h3>
    <form id="explorerForm" style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
        <input type="text" id="targetUserId" placeholder="Enter User ID" required style="padding: 8px; background: #061121; color: #fff; border: 1px solid #ffb703; flex: 1; min-width: 150px;">
        <select id="packageSelect" style="padding: 8px; background: #061121; color: #fff; border: 1px solid #ffb703; min-width: 200px;">
            <optgroup label="Main Packages">
                <option value="main_11">Starter Pool ($11)</option>
                <option value="main_30">Bronze Pool ($30)</option>
                <option value="main_60">Silver Pool ($60)</option>
                <option value="main_120">Gold Pool ($120)</option>
                <option value="main_240">Platinum Pool ($240)</option>
                <option value="main_480">Diamond Pool ($480)</option>
            </optgroup>
            <optgroup label="Booster Packages">
                <option value="booster_10">10 Booster ($10)</option>
                <option value="booster_20">20 Booster ($20)</option>
                <option value="booster_40">40 Booster ($40)</option>
                <option value="booster_80">80 Booster ($80)</option>
                <option value="booster_160">160 Booster ($160)</option>
                <option value="booster_320">320 Booster ($320)</option>
            </optgroup>
        </select>
        <button type="submit" class="btn btn-gold">Explore</button>
    </form>
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
        <div style="font-size: 16px; color: #fff;">
            Viewing Matrix Downlines of: <strong id="currentViewingUser" style="color: #ffb703;">-</strong>
        </div>
        <div id="navigationButtons" style="display: flex; gap: 10px;">
            <!-- Navigation buttons will be injected here -->
        </div>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Position Slot</th>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Sponsor ID</th>
                    <th>Joining Date</th>
                    <th>Status</th>
                    <th>Additional Info</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="networkTableBody">
                <tr>
                    <td colspan="8" style="text-align: center; color: #a0aec0; padding: 30px;">Enter a User ID and select a package to explore the network</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('explorerForm');
    const tbody = document.getElementById('networkTableBody');
    const navContainer = document.getElementById('navigationButtons');
    const currentViewingUserSpan = document.getElementById('currentViewingUser');
    
    let searchRootUser = '';

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const userId = document.getElementById('targetUserId').value.trim();
        searchRootUser = userId;
        loadNetwork(userId);
    });

    function loadNetwork(targetUser) {
        const packageSelect = document.getElementById('packageSelect');
        const packageType = packageSelect.value;
        const matrixType = packageType.startsWith('booster_') ? 'booster' : 'main';

        tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding:30px; color:#a0aec0;"><i class="fas fa-spinner fa-spin"></i> Loading tree downlines...</td></tr>`;
        navContainer.innerHTML = '';
        currentViewingUserSpan.innerText = targetUser;

        const params = new URLSearchParams({
            action: 'get_network',
            user_id: targetUser,
            matrix_type: matrixType,
            package_type: packageType
        });

        fetch(`api/network.php?${params.toString()}`)
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding:30px; color:#ff4d4d; font-weight:bold;">Error: ${data.message}</td></tr>`;
                    return;
                }

                currentViewingUserSpan.innerText = data.target_id;

                // Setup Navigation Buttons
                if (data.target_id !== searchRootUser) {
                    // Back to Root button
                    const btnRoot = document.createElement('button');
                    btnRoot.className = 'btn-nav-tree';
                    btnRoot.innerHTML = '<i class="fas fa-home"></i> Back to Root';
                    btnRoot.onclick = () => loadNetwork(searchRootUser);
                    navContainer.appendChild(btnRoot);

                    // Back to Parent button
                    if (data.parent_id) {
                        const btnParent = document.createElement('button');
                        btnParent.className = 'btn-nav-tree';
                        btnParent.innerHTML = '<i class="fas fa-arrow-up"></i> Up One Level';
                        btnParent.onclick = () => loadNetwork(data.parent_id);
                        navContainer.appendChild(btnParent);
                    }
                }

                // Populate Table
                tbody.innerHTML = '';
                if (!data.children || data.children.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding:30px; color:#a0aec0;">No matrix downlines found under this member.</td></tr>`;
                    return;
                }

                data.children.forEach(child => {
                    const row = document.createElement('tr');
                    
                    const slotText = child.position_slot == 1 ? 'Left Slot' : 'Right Slot';
                    const slotClass = child.position_slot == 1 ? 'badge-left' : 'badge-right';

                    const formattedDate = new Date(child.created_at).toLocaleDateString('en-US', {
                        day: '2-digit', month: 'short', year: 'numeric'
                    });

                    const extraInfo = matrixType === 'booster' ? `Board: ${child.board_id ?? '-'}` : `Level: ${child.matrix_level ?? '-'}`;

                    row.innerHTML = `
                        <td><span class="badge-slot ${slotClass}">${slotText}</span></td>
                        <td style="font-weight: bold; color: #ffb703;">${child.user_id}</td>
                        <td>${child.name}</td>
                        <td style="color: #a0aec0;">${child.sponsor_id ?? '-'}</td>
                        <td>${formattedDate}</td>
                        <td>
                            <span style="color: ${child.status === 'Active' ? '#2ecc71' : (child.status === 'Blocked' ? '#ff4d4d' : 'orange')}">
                                ${child.status}
                            </span>
                        </td>
                        <td>${extraInfo}</td>
                        <td><button class="btn-explore" data-userid="${child.user_id}"><i class="fas fa-sitemap"></i> Explore</button></td>
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
                tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding:30px; color:#ff4d4d;">Failed to load network data.</td></tr>`;
            });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
