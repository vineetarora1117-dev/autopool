<?php
require_once '../libs/db.php';
require_once '../libs/auth.php';
require_once '../libs/config.php';

requireLogin();
$user_id = $_SESSION['user_id'];

// Fetch all boosters owned by current user
$stmtMyBoosters = $pdo->prepare("SELECT * FROM user_boosters WHERE user_id = ? ORDER BY id DESC");
$stmtMyBoosters->execute([$user_id]);
$myBoosters = $stmtMyBoosters->fetchAll(PDO::FETCH_ASSOC);

// Determine which booster to view
$selectedId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$rootBooster = null;

if (!empty($myBoosters)) {
    if ($selectedId > 0) {
        foreach ($myBoosters as $mb) {
            if (intval($mb['id']) === $selectedId) {
                $rootBooster = $mb;
                break;
            }
        }
    }
    // Default to first booster if not specified or not found in user list
    if (!$rootBooster) {
        $rootBooster = $myBoosters[0];
        $selectedId = intval($rootBooster['id']);
    }
}

// Fetch tree data if root booster exists
$level1Nodes = [];
$level2Nodes = []; // Keyed by level 1 booster ID

if ($rootBooster) {
    // Level 1: Up to 3 downlines of root booster
    $stmtL1 = $pdo->prepare("SELECT * FROM user_boosters WHERE upline_booster_id = ? ORDER BY id ASC LIMIT 3");
    $stmtL1->execute([$rootBooster['id']]);
    $level1Nodes = $stmtL1->fetchAll(PDO::FETCH_ASSOC);

    // Level 2: Up to 3 downlines for each Level 1 booster
    foreach ($level1Nodes as $l1) {
        $stmtL2 = $pdo->prepare("SELECT * FROM user_boosters WHERE upline_booster_id = ? ORDER BY id ASC LIMIT 3");
        $stmtL2->execute([$l1['id']]);
        $level2Nodes[$l1['id']] = $stmtL2->fetchAll(PDO::FETCH_ASSOC);
    }
}

include '../includes/header.php';
?>

<style>
.tree-container {
    background: rgba(6, 17, 33, 0.85);
    border: 1px solid #ffb703;
    border-radius: 14px;
    padding: 30px 20px;
    margin-top: 20px;
    overflow-x: auto;
}

.tree-wrapper {
    display: flex;
    flex-direction: column;
    align-items: center;
    min-width: 750px;
    padding: 10px 0;
}

/* Tree Nodes Styling */
.tree-node {
    border-radius: 12px;
    padding: 12px 18px;
    text-align: center;
    transition: transform 0.2s, box-shadow 0.2s;
    box-shadow: 0 4px 12px rgba(0,0,0,0.4);
    position: relative;
    z-index: 2;
}
.tree-node:hover {
    transform: translateY(-2px);
}

/* Root Node Style */
.node-root {
    background: linear-gradient(135deg, rgba(255, 183, 3, 0.25), rgba(255, 183, 3, 0.08));
    border: 2px solid #ffb703;
    color: #fff;
    min-width: 220px;
}
.node-root .node-title {
    font-size: 15px;
    font-weight: bold;
    color: #ffb703;
    margin-bottom: 4px;
}
.node-root .node-sub {
    font-size: 12px;
    color: #e2e8f0;
}

/* Level 1 Filled vs Empty Node Styles */
.node-filled {
    background: linear-gradient(135deg, rgba(46, 204, 113, 0.2), rgba(46, 204, 113, 0.06));
    border: 2px solid #2ecc71;
    color: #2ecc71;
    min-width: 190px;
}
.node-filled .node-title {
    font-size: 14px;
    font-weight: bold;
    color: #2ecc71;
    margin-bottom: 4px;
}
.node-filled .node-sub {
    font-size: 11px;
    color: #a0aec0;
}

.node-empty {
    background: rgba(255, 255, 255, 0.03);
    border: 2px dashed #4a5568;
    color: #718096;
    min-width: 190px;
}
.node-empty .node-title {
    font-size: 14px;
    font-weight: bold;
    color: #718096;
    margin-bottom: 4px;
}
.node-empty .node-sub {
    font-size: 11px;
    color: #4a5568;
}

/* Level 2 Sub-node Styles */
.subnode-filled {
    background: rgba(46, 204, 113, 0.15);
    border: 1px solid #2ecc71;
    color: #2ecc71;
    border-radius: 8px;
    padding: 6px 10px;
    font-size: 11px;
    font-weight: bold;
    min-width: 70px;
    text-align: center;
}
.subnode-empty {
    background: rgba(255, 255, 255, 0.02);
    border: 1px dashed #4a5568;
    color: #718096;
    border-radius: 8px;
    padding: 6px 10px;
    font-size: 11px;
    font-weight: bold;
    min-width: 70px;
    text-align: center;
}

/* Connector Lines */
.tree-line-v {
    width: 2px;
    height: 25px;
    background: #ffb703;
}
.tree-line-v-green {
    width: 2px;
    height: 20px;
    background: #2ecc71;
}
.tree-line-v-grey {
    width: 2px;
    height: 20px;
    background: #4a5568;
}

.tree-level1-row {
    display: flex;
    justify-content: space-around;
    width: 100%;
    position: relative;
}

.tree-branch-col {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
}

.tree-level2-row {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-top: 5px;
}

.legend-bar {
    display: flex;
    justify-content: center;
    gap: 25px;
    margin-bottom: 25px;
    flex-wrap: wrap;
    background: rgba(0, 0, 0, 0.3);
    padding: 12px 20px;
    border-radius: 8px;
    border: 1px solid rgba(255, 183, 3, 0.2);
}
.legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 500;
}
.legend-box {
    width: 16px;
    height: 16px;
    border-radius: 4px;
}
</style>

<div class="content-section active-view" style="padding: 20px; max-width: 1100px; margin: 0 auto;">
    <div class="profile-header-bar">
        <div class="profile-header-title">
            <i class="fa-solid fa-sitemap"></i> Global 1x3 Booster Matrix Tree
        </div>
        <div class="profile-breadcrumb">
            <a href="index.php">Home</a> &raquo; 
            <a href="boosterIncome.php">Booster</a> &raquo; 
            Global Booster Tree
        </div>
    </div>

    <!-- Controls & Legend Row -->
    <div style="background: rgba(6, 17, 33, 0.75); border: 1px solid rgba(255, 183, 3, 0.3); border-radius: 12px; padding: 20px; margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <!-- Select Booster Dropdown -->
            <div style="display: flex; align-items: center; gap: 10px;">
                <label for="boosterSelect" style="color: #ffb703; font-weight: bold; font-size: 14px;">Select Booster:</label>
                <?php if (empty($myBoosters)): ?>
                    <span style="color: #a0aec0; font-size: 14px;">No Boosters Purchased</span>
                <?php else: ?>
                    <select id="boosterSelect" onchange="window.location.href='boosterTree.php?id='+this.value" style="background: #061121; color: #ffb703; border: 1px solid #ffb703; padding: 8px 14px; border-radius: 6px; font-weight: bold; font-size: 14px; cursor: pointer; outline: none;">
                        <?php foreach ($myBoosters as $mb): ?>
                            <option value="<?php echo $mb['id']; ?>" <?php echo (intval($mb['id']) === $selectedId) ? 'selected' : ''; ?>>
                                Booster #<?php echo $mb['id']; ?> (<?php echo ucfirst($mb['status']); ?> - <?php echo $mb['downline_count']; ?>/3 Downlines)
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>

            <!-- Return to Booster Income Button -->
            <div>
                <a href="boosterIncome.php" style="background: rgba(255, 183, 3, 0.15); color: #ffb703; border: 1px solid #ffb703; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 13px; display: inline-flex; align-items: center; gap: 8px; transition: 0.2s;">
                    <i class="fa-solid fa-arrow-left"></i> Back to Booster Payout Log
                </a>
            </div>
        </div>

        <!-- Legend -->
        <div class="legend-bar" style="margin-top: 20px; margin-bottom: 0;">
            <div class="legend-item">
                <div class="legend-box" style="background: rgba(255, 183, 3, 0.3); border: 2px solid #ffb703;"></div>
                <span style="color: #ffb703;">Your Booster (#ID)</span>
            </div>
            <div class="legend-item">
                <div class="legend-box" style="background: rgba(46, 204, 113, 0.3); border: 2px solid #2ecc71;"></div>
                <span style="color: #2ecc71;">Filled Spot (Occupied)</span>
            </div>
            <div class="legend-item">
                <div class="legend-box" style="background: rgba(255, 255, 255, 0.05); border: 2px dashed #4a5568;"></div>
                <span style="color: #a0aec0;">Empty Spot (Waiting)</span>
            </div>
        </div>
    </div>

    <!-- Tree Visual Section -->
    <div class="tree-container">
        <?php if (!$rootBooster): ?>
            <div style="text-align: center; padding: 50px 20px; color: #a0aec0;">
                <i class="fa-solid fa-sitemap fa-3x" style="color: #ffb703; margin-bottom: 15px;"></i>
                <h3 style="color: #fff; margin-bottom: 10px;">No Boosters Available</h3>
                <p style="margin-bottom: 20px;">You haven't purchased any boosters yet to view in the global matrix tree.</p>
                <a href="buyBooster.php" style="background: #ffb703; color: #000; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 14px; display: inline-block;">
                    Buy Booster Now
                </a>
            </div>
        <?php else: ?>
            <div class="tree-wrapper">
                <!-- ROOT NODE (Current User's Selected Booster) -->
                <div class="tree-node node-root">
                    <div class="node-title">
                        <i class="fa-solid fa-crown" style="color: #ffb703;"></i> Booster #<?php echo $rootBooster['id']; ?>
                    </div>
                    <div class="node-sub" style="font-weight: bold; color: #fff;">
                        User: <span style="color: #ffb703;"><?php echo htmlspecialchars($user_id); ?></span>
                    </div>
                    <div class="node-sub" style="margin-top: 4px;">
                        Downlines: <strong><?php echo $rootBooster['downline_count']; ?> / 3</strong>
                    </div>
                    <div style="margin-top: 6px;">
                        <?php if ($rootBooster['status'] === 'completed'): ?>
                            <span style="background: rgba(46, 204, 113, 0.25); color: #2ecc71; padding: 2px 8px; border-radius: 10px; font-size: 10px; border: 1px solid #2ecc71; font-weight: bold;">Completed</span>
                        <?php else: ?>
                            <span style="background: rgba(241, 196, 15, 0.25); color: #f1c40f; padding: 2px 8px; border-radius: 10px; font-size: 10px; border: 1px solid #f1c40f; font-weight: bold;">Active</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Connector Line down from Root -->
                <div class="tree-line-v"></div>

                <!-- Horizontal Connector Line connecting 3 Level 1 Branches -->
                <div style="width: 66%; height: 2px; background: #ffb703; margin-bottom: 0;"></div>

                <!-- LEVEL 1 ROW (3 Downline Spots) -->
                <div class="tree-level1-row">
                    <?php for ($i = 0; $i < 3; $i++): ?>
                        <?php 
                        $l1Child = isset($level1Nodes[$i]) ? $level1Nodes[$i] : null;
                        $isL1Filled = ($l1Child !== null);
                        ?>
                        <div class="tree-branch-col">
                            <!-- Line connecting horizontal bar to node -->
                            <div class="<?php echo $isL1Filled ? 'tree-line-v-green' : 'tree-line-v-grey'; ?>"></div>

                            <!-- Level 1 Node Card -->
                            <?php if ($isL1Filled): ?>
                                <div class="tree-node node-filled">
                                    <div class="node-title">
                                        <i class="fa-solid fa-user-check"></i> Spot #<?php echo ($i + 1); ?>: Filled
                                    </div>
                                    <div class="node-sub">
                                        <?php if ($l1Child['user_id'] === $user_id): ?>
                                            <span style="color: #2ecc71; font-weight: bold;">My Re-entry (#<?php echo $l1Child['id']; ?>)</span>
                                        <?php else: ?>
                                            <span style="color: #2ecc71; font-weight: bold;">Occupied</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="node-sub" style="margin-top: 3px;">
                                        Downlines: <?php echo $l1Child['downline_count']; ?>/3
                                    </div>
                                </div>

                                <!-- Line down to Level 2 sub-nodes -->
                                <div class="tree-line-v-green"></div>

                                <!-- Level 2 Sub-nodes (3 child spots of this Level 1 node) -->
                                <div class="tree-level2-row">
                                    <?php 
                                    $l2Children = isset($level2Nodes[$l1Child['id']]) ? $level2Nodes[$l1Child['id']] : [];
                                    for ($j = 0; $j < 3; $j++): 
                                        $isL2Filled = isset($l2Children[$j]);
                                    ?>
                                        <?php if ($isL2Filled): ?>
                                            <div class="subnode-filled" title="Filled Downline Spot">
                                                <i class="fa-solid fa-check-circle"></i> Filled
                                            </div>
                                        <?php else: ?>
                                            <div class="subnode-empty" title="Empty Spot">
                                                Empty
                                            </div>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>

                            <?php else: ?>
                                <div class="tree-node node-empty">
                                    <div class="node-title">
                                        <i class="fa-solid fa-circle-plus"></i> Spot #<?php echo ($i + 1); ?>: Empty
                                    </div>
                                    <div class="node-sub">
                                        Waiting for Downline
                                    </div>
                                </div>

                                <!-- Line down to Level 2 empty sub-nodes -->
                                <div class="tree-line-v-grey"></div>

                                <!-- Level 2 Empty Sub-nodes -->
                                <div class="tree-level2-row">
                                    <div class="subnode-empty">Empty</div>
                                    <div class="subnode-empty">Empty</div>
                                    <div class="subnode-empty">Empty</div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
