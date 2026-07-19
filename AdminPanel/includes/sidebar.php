<sidebar>
    <ul class="nav-list">
        <li class="nav-item"><a href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
        <li class="nav-item"><a href="fundRequests.php"><i class="fas fa-money-bill-wave"></i> Fund Requests</a></li>
        <li class="nav-item"><a href="withdrawalRequests.php"><i class="fas fa-hand-holding-usd"></i> Withdrawal Requests</a></li>
        
        <li class="nav-item" onclick="this.classList.toggle('open')">
            <a href="#"><i class="fas fa-users"></i> User Management <i class="fas fa-chevron-down" style="margin-left:auto; font-size:12px;"></i></a>
            <ul class="submenu">
                <li><a href="allMembers.php">All Members</a></li>
                <li><a href="activeMembers.php">Active Members</a></li>
                <li><a href="inactiveMembers.php">Inactive Members</a></li>
                <li><a href="blockedUsers.php">Blocked Users</a></li>
            </ul>
        </li>
        
        <li class="nav-item"><a href="transactionLogs.php"><i class="fas fa-list-alt"></i> Transaction Logs</a></li>
        <li class="nav-item"><a href="networkExplorer.php"><i class="fas fa-network-wired"></i> Network Explorer</a></li>
        <li class="nav-item"><a href="supportTickets.php"><i class="fas fa-ticket-alt"></i> Support Tickets</a></li>
        <li class="nav-item"><a href="announcements.php"><i class="fas fa-bullhorn"></i> Announcements</a></li>
        <li class="nav-item"><a href="settings.php"><i class="fas fa-cogs"></i> Settings</a></li>
        <li class="nav-item"><a href="#" onclick="logoutAdmin()"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</sidebar>
<script>
function logoutAdmin() {
    Swal.fire({
        title: 'Logout?',
        text: 'Are you sure you want to logout?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, logout',
        confirmButtonColor: '#ff4d4d'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('api/auth.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=logout'
            }).then(() => {
                window.location.href = 'login.php';
            });
        }
    });
}
</script>
