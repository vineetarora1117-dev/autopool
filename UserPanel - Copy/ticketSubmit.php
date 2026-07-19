<?php include '../includes/header.php'; ?>

<div id="ticketSubmitSection" class="content-section active-view">
                <div class="profile-header-bar">
                    <div class="profile-header-title">Ticket Submit</div>
                    <div class="profile-breadcrumb"><a href="#" onclick="switchView('dashboardSection', document.querySelector('.nav-item')); return false;">Home</a> &raquo; Ticket Submit</div>
                </div>
                <div class="form-container" style="max-width: 700px;">
                    <div class="form-group">
                        <label>User ID</label>
                        <input type="text" class="form-control form-control-disabled" value="RJ129688" readonly>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="textarea-control" placeholder="Enter Description"></textarea>
                    </div>
                    <div class="form-actions">
                        <button class="btn-submit-gold" style="padding: 10px 20px;">Create Ticket</button>
                    </div>
                </div>
            </div>

            <!-- VIEW: Ticket Report -->

<?php include '../includes/footer.php'; ?>