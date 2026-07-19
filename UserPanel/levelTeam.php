<?php include '../includes/header.php'; ?>

<div id="levelTeamSection" class="content-section active-view">
                <div class="profile-header-bar">
                    <div class="profile-header-title">Level Team</div>
                    <div class="profile-breadcrumb"><a href="#" onclick="switchView('dashboardSection', document.querySelector('.nav-item')); return false;">Home</a> &raquo; Level Team</div>
                </div>
                <div class="table-container">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Level</th>
                                <th>Total Users</th>
                                <th>Total Paid Users</th>
                                <th>Team Business</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <script>
                                for(let i=1; i<=10; i++) {
                                    document.write(`
                                        <tr>
                                            <td>\${i}</td>
                                            <td>Level-\${i}</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>$0</td>
                                            <td><button class="btn-table-action"><i class="fa-solid fa-pencil" style="font-size:11px;"></i> View Team</button></td>
                                        </tr>
                                    `);
                                }
                            </script>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- VIEW: Activate ID -->

<?php include '../includes/footer.php'; ?>