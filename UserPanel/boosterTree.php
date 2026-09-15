<?php
require_once '../libs/db.php';
require_once '../libs/auth.php';
require_once '../libs/config.php';

requireLogin();
$user_id = $_SESSION['user_id'];

// Fetch all boosters owned by current user
$stmtMyBoosters = $pdo->prepare("SELECT * FROM user_boosters WHERE user_id = ? ORDER BY id ASC");
$stmtMyBoosters->execute([$user_id]);
$myBoosters = $stmtMyBoosters->fetchAll(PDO::FETCH_ASSOC);

// Fetch global root booster (Booster #1) or requested booster ID
$requestedId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($requestedId > 0) {
    $stmtFocus = $pdo->prepare("SELECT * FROM user_boosters WHERE id = ?");
    $stmtFocus->execute([$requestedId]);
    $focusBooster = $stmtFocus->fetch(PDO::FETCH_ASSOC);
}

if (empty($focusBooster)) {
    // If user has boosters, default to user's first active booster; otherwise global root #1
    $myActive = array_filter($myBoosters, function($b) { return $b['status'] === 'active'; });
    if (!empty($myActive)) {
        $focusBooster = reset($myActive);
    } else if (!empty($myBoosters)) {
        $focusBooster = $myBoosters[0];
    } else {
        $stmtRoot = $pdo->query("SELECT * FROM user_boosters ORDER BY id ASC LIMIT 1");
        $focusBooster = $stmtRoot->fetch(PDO::FETCH_ASSOC);
    }
}

// -------------------------------------------------------------
// Calculate Global Queue Position & Activation Requirements
// -------------------------------------------------------------
$queueMetrics = [];
foreach ($myBoosters as $mb) {
    if ($mb['status'] === 'active') {
        $mbId = intval($mb['id']);
        $currentDownlines = intval($mb['downline_count']);

        // Count open spots in all active boosters created before this booster
        $stmtAhead = $pdo->prepare("SELECT SUM(3 - downline_count) FROM user_boosters WHERE status = 'active' AND id < ?");
        $stmtAhead->execute([$mbId]);
        $spotsAhead = intval($stmtAhead->fetchColumn() ?? 0);

        // Global joins needed for 1st, 2nd, 3rd downline activation
        $neededFor1st = ($currentDownlines < 1) ? $spotsAhead + 1 : 0;
        $neededFor2nd = ($currentDownlines < 2) ? ($currentDownlines < 1 ? $spotsAhead + 2 : 1) : 0;
        $neededFor3rd = ($currentDownlines < 3) ? ($currentDownlines < 1 ? $spotsAhead + 3 : ($currentDownlines < 2 ? 2 : 1)) : 0;

        $queueMetrics[] = [
            'booster' => $mb,
            'spots_ahead' => $spotsAhead,
            'needed_1st' => $neededFor1st,
            'needed_2nd' => $neededFor2nd,
            'needed_completion' => $neededFor3rd
        ];
    }
}

// -------------------------------------------------------------
// Fetch Global Matrix Tree Data for Focused Booster
// -------------------------------------------------------------
$level1Nodes = [];
$level2Nodes = []; // Keyed by L1 booster ID

if ($focusBooster) {
    $stmtL1 = $pdo->prepare("SELECT * FROM user_boosters WHERE upline_booster_id = ? ORDER BY id ASC LIMIT 3");
    $stmtL1->execute([$focusBooster['id']]);
    $level1Nodes = $stmtL1->fetchAll(PDO::FETCH_ASSOC);

    foreach ($level1Nodes as $l1) {
        $stmtL2 = $pdo->prepare("SELECT * FROM user_boosters WHERE upline_booster_id = ? ORDER BY id ASC LIMIT 3");
        $stmtL2->execute([$l1['id']]);
        $level2Nodes[$l1['id']] = $stmtL2->fetchAll(PDO::FETCH_ASSOC);
    }
}

include '../includes/header.php';
?>

<style>
.global-queue-card {
    background: rgba(6, 17, 33, 0.85);
    border: 1px solid #ffb703;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 25px;
}

.metric-pill {
    background: rgba(255, 183, 3, 0.1);
    border: 1px solid rgba(255, 183, 3, 0.3);
    border-radius: 8px;
    padding: 12px 16px;
    text-align: center;
}

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
    min-width: 800px;
    padding: 10px 0;
}

/* Tree Node Base */
.tree-node {
    border-radius: 12px;
    padding: 12px 18px;
    text-align: center;
    transition: transform 0.2s, box-shadow 0.2s;
    box-shadow: 0 4px 12px rgba(0,0,0,0.4);
    position: relative;
    z-index: 2;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
}
.tree-node:hover {
    transform: translateY(-3px);
}

/* Node Types */
.node-user {
    background: linear-gradient(135deg, rgba(255, 183, 3, 0.3), rgba(255, 183, 3, 0.1));
    border: 2px solid #ffb703;
    color: #fff;
    min-width: 210px;
}
.node-user .node-title {
    font-size: 14px;
    font-weight: bold;
    color: #ffb703;
}

.node-filled {
    background: linear-gradient(135deg, rgba(46, 204, 113, 0.25), rgba(46, 204, 113, 0.08));
    border: 2px solid #2ecc71;
    color: #2ecc71;
    min-width: 200px;
}
.node-filled .node-title {
    font-size: 14px;
    font-weight: bold;
    color: #2ecc71;
}

.node-empty {
    background: rgba(255, 255, 255, 0.03);
    border: 2px dashed #4a5568;
    color: #718096;
    min-width: 200px;
    cursor: default;
}
.node-empty .node-title {
    font-size: 14px;
    font-weight: bold;
    color: #718096;
}

/* Subnode (Level 2) Styles */
.subnode-user {
    background: rgba(255, 183, 3, 0.2);
    border: 1px solid #ffb703;
    color: #ffb703;
    border-radius: 8px;
    padding: 6px 10px;
    font-size: 11px;
    font-weight: bold;
    min-width: 75px;
    text-align: center;
}
.subnode-filled {
    background: rgba(46, 204, 113, 0.18);
    border: 1px solid #2ecc71;
    color: #2ecc71;
    border-radius: 8px;
    padding: 6px 10px;
    font-size: 11px;
    font-weight: bold;
    min-width: 75px;
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
    min-width: 75px;
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
            <i class="fa-solid fa-globe"></i> Global Booster Matrix Tree & Queue Tracker
        </div>
        <div class="profile-breadcrumb">
            <a href="index.php">Home</a> &raquo; 
            <a href="boosterIncome.php">Booster</a> &raquo; 
            Global Booster Tree
        </div>
    </div>

    <!-- GLOBAL QUEUE ACTIVATION TRACKER FOR CURRENT USER -->
    <div class="global-queue-card">
        <h3 style="color: #ffb703; margin-bottom: 15px; font-size: 17px; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-clock-rotate-left"></i> My Active Boosters Global Activation Tracker
        </h3>

        <?php if (empty($queueMetrics)): ?>
            <div style="color: #a0aec0; font-size: 14px;">
                You currently have no active boosters waiting in the global queue. 
                <a href="buyBooster.php" style="color: #ffb703; font-weight: bold;">Buy a booster to join the global matrix!</a>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 15px;">
                <?php foreach ($queueMetrics as $qm): ?>
                    <?php 
                    $b = $qm['booster'];
                    $bId = $b['id'];
                    $curCount = intval($b['downline_count']);
                    ?>
                    <div style="background: rgba(0,0,0,0.3); border: 1px solid rgba(255,183,3,0.3); border-radius: 10px; padding: 15px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <strong style="color: #ffb703; font-size: 15px;">Booster #<?php echo $bId; ?></strong>
                            <span style="background: rgba(241, 196, 15, 0.2); color: #f1c40f; padding: 2px 8px; border-radius: 10px; font-size: 11px; border: 1px solid #f1c40f; font-weight: bold;">
                                Active (<?php echo $curCount; ?>/3 Downlines)
                            </span>
                        </div>
                        
                        <div style="font-size: 13px; color: #e2e8f0; line-height: 1.6;">
                            <div>Global Queue Ahead: <strong style="color: #3498db;"><?php echo $qm['spots_ahead']; ?> open spot(s)</strong></div>
                            <?php if ($curCount < 1): ?>
                                <div style="margin-top: 6px; background: rgba(46, 204, 113, 0.1); border-left: 3px solid #2ecc71; padding: 6px 10px; border-radius: 4px;">
                                    <i class="fa-solid fa-user-plus" style="color: #2ecc71;"></i> 
                                    <strong><?php echo $qm['needed_1st']; ?> global purchase(s)</strong> needed for your <strong>1st downline</strong> to activate.
                                </div>
                            <?php elseif ($curCount < 2): ?>
                                <div style="margin-top: 6px; background: rgba(46, 204, 113, 0.1); border-left: 3px solid #2ecc71; padding: 6px 10px; border-radius: 4px;">
                                    <i class="fa-solid fa-user-plus" style="color: #2ecc71;"></i> 
                                    <strong><?php echo $qm['needed_2nd']; ?> global purchase(s)</strong> needed for your <strong>2nd downline</strong> to activate.
                                </div>
                            <?php else: ?>
                                <div style="margin-top: 6px; background: rgba(46, 204, 113, 0.1); border-left: 3px solid #2ecc71; padding: 6px 10px; border-radius: 4px;">
                                    <i class="fa-solid fa-user-plus" style="color: #2ecc71;"></i> 
                                    <strong><?php echo $qm['needed_completion']; ?> global purchase(s)</strong> needed for <strong>Cycle Completion ($10.00 Payout)</strong>.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- GLOBAL TREE CONTROLS & LEGEND -->
    <div style="background: rgba(6, 17, 33, 0.75); border: 1px solid rgba(255, 183, 3, 0.3); border-radius: 12px; padding: 20px; margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 15px;">
            <!-- Tree Root Focus Input/Select -->
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <label for="focusSelect" style="color: #ffb703; font-weight: bold; font-size: 14px;">Inspect Tree Root:</label>
                <select id="focusSelect" onchange="window.location.href='boosterTree.php?id='+this.value" style="background: #061121; color: #ffb703; border: 1px solid #ffb703; padding: 8px 14px; border-radius: 6px; font-weight: bold; font-size: 14px; cursor: pointer; outline: none;">
                    <?php if (!empty($myBoosters)): ?>
                        <optgroup label="My Boosters">
                            <?php foreach ($myBoosters as $mb): ?>
                                <option value="<?php echo $mb['id']; ?>" <?php echo ($focusBooster && intval($focusBooster['id']) === intval($mb['id'])) ? 'selected' : ''; ?>>
                                    ⭐ My Booster #<?php echo $mb['id']; ?> (<?php echo ucfirst($mb['status']); ?> - <?php echo $mb['downline_count']; ?>/3)
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endif; ?>
                    <?php if ($focusBooster && $focusBooster['user_id'] !== $user_id): ?>
                        <optgroup label="Global Node">
                            <option value="<?php echo $focusBooster['id']; ?>" selected>
                                🌐 Global Booster #<?php echo $focusBooster['id']; ?> (<?php echo ucfirst($focusBooster['status']); ?>)
                            </option>
                        </optgroup>
                    <?php endif; ?>
                </select>

                <a href="boosterTree.php?id=1" style="color: #3498db; text-decoration: none; font-size: 13px; font-weight: bold; padding: 6px 12px; background: rgba(52, 152, 219, 0.15); border: 1px solid #3498db; border-radius: 6px;">
                    <i class="fa-solid fa-tree"></i> View Global Root (#1)
                </a>
            </div>

            <div>
                <a href="boosterIncome.php" style="background: rgba(255, 183, 3, 0.15); color: #ffb703; border: 1px solid #ffb703; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 13px; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-arrow-left"></i> Back to Booster Payout Log
                </a>
            </div>
        </div>

        <!-- Legend -->
        <div class="legend-bar">
            <div class="legend-item">
                <div class="legend-box" style="background: rgba(255, 183, 3, 0.3); border: 2px solid #ffb703;"></div>
                <span style="color: #ffb703;">Your Booster (Gold)</span>
            </div>
            <div class="legend-item">
                <div class="legend-box" style="background: rgba(46, 204, 113, 0.3); border: 2px solid #2ecc71;"></div>
                <span style="color: #2ecc71;">Filled Global Spot (Green)</span>
            </div>
            <div class="legend-item">
                <div class="legend-box" style="background: rgba(255, 255, 255, 0.05); border: 2px dashed #4a5568;"></div>
                <span style="color: #a0aec0;">Empty Spot (Grey)</span>
            </div>
        </div>
    </div>

    <!-- GLOBAL TREE VISUALIZATION -->
    <div class="tree-container">
        <?php if (!$focusBooster): ?>
            <div style="text-align: center; padding: 50px 20px; color: #a0aec0;">
                <i class="fa-solid fa-globe fa-3x" style="color: #ffb703; margin-bottom: 15px;"></i>
                <h3 style="color: #fff; margin-bottom: 10px;">No Global Matrix Boosters Found</h3>
                <p>Purchase a booster to start the global matrix tree!</p>
            </div>
        <?php else: ?>
            <div class="tree-wrapper">
                <!-- ROOT NODE OF CURRENT TREE VIEW -->
                <?php 
                $isRootMine = ($focusBooster['user_id'] === $user_id);
                $rootClass = $isRootMine ? 'node-user' : 'node-filled';
                ?>
                <a href="boosterTree.php?id=<?php echo $focusBooster['id']; ?>" class="tree-node <?php echo $rootClass; ?>">
                    <div class="node-title">
                        <?php if ($isRootMine): ?>
                            <i class="fa-solid fa-crown" style="color: #ffb703;"></i> Your Booster #<?php echo $focusBooster['id']; ?>
                        <?php else: ?>
                            <i class="fa-solid fa-globe"></i> Global Booster #<?php echo $focusBooster['id']; ?>
                        <?php endif; ?>
                    </div>
                    <div style="font-size: 12px; margin-top: 4px; color: #fff;">
                        Downlines: <strong><?php echo $focusBooster['downline_count']; ?> / 3</strong>
                    </div>
                    <div style="margin-top: 6px;">
                        <?php if ($focusBooster['status'] === 'completed'): ?>
                            <span style="background: rgba(46, 204, 113, 0.25); color: #2ecc71; padding: 2px 8px; border-radius: 10px; font-size: 10px; border: 1px solid #2ecc71; font-weight: bold;">Completed</span>
                        <?php else: ?>
                            <span style="background: rgba(241, 196, 15, 0.25); color: #f1c40f; padding: 2px 8px; border-radius: 10px; font-size: 10px; border: 1px solid #f1c40f; font-weight: bold;">Active</span>
                        <?php endif; ?>
                    </div>
                </a>

                <!-- Vertical Line -->
                <div class="tree-line-v"></div>

                <!-- Horizontal Connector Line -->
                <div style="width: 66%; height: 2px; background: #ffb703; margin-bottom: 0;"></div>

                <!-- LEVEL 1 ROW (3 DOWNLINE SPOTS) -->
                <div class="tree-level1-row">
                    <?php for ($i = 0; $i < 3; $i++): ?>
                        <?php 
                        $l1Child = isset($level1Nodes[$i]) ? $level1Nodes[$i] : null;
                        $isL1Filled = ($l1Child !== null);
                        $isL1Mine = ($isL1Filled && $l1Child['user_id'] === $user_id);
                        ?>
                        <div class="tree-branch-col">
                            <div class="<?php echo $isL1Filled ? 'tree-line-v-green' : 'tree-line-v-grey'; ?>"></div>

                            <?php if ($isL1Filled): ?>
                                <?php $l1Class = $isL1Mine ? 'node-user' : 'node-filled'; ?>
                                <a href="boosterTree.php?id=<?php echo $l1Child['id']; ?>" class="tree-node <?php echo $l1Class; ?>" title="Click to inspect this booster tree">
                                    <div class="node-title">
                                        <?php if ($isL1Mine): ?>
                                            <i class="fa-solid fa-crown" style="color:#ffb703;"></i> Your Booster #<?php echo $l1Child['id']; ?>
                                        <?php else: ?>
                                            <i class="fa-solid fa-user-check"></i> Spot #<?php echo ($i + 1); ?>: Filled
                                        <?php endif; ?>
                                    </div>
                                    <div style="font-size: 11px; margin-top: 3px; color: #e2e8f0;">
                                        Downlines: <?php echo $l1Child['downline_count']; ?>/3
                                    </div>
                                </a>

                                <div class="tree-line-v-green"></div>

                                <!-- LEVEL 2 SUB-NODES -->
                                <div class="tree-level2-row">
                                    <?php 
                                    $l2Children = isset($level2Nodes[$l1Child['id']]) ? $level2Nodes[$l1Child['id']] : [];
                                    for ($j = 0; $j < 3; $j++): 
                                        $l2Child = isset($l2Children[$j]) ? $l2Children[$j] : null;
                                        $isL2Filled = ($l2Child !== null);
                                        $isL2Mine = ($isL2Filled && $l2Child['user_id'] === $user_id);
                                    ?>
                                        <?php if ($isL2Mine): ?>
                                            <a href="boosterTree.php?id=<?php echo $l2Child['id']; ?>" class="subnode-user" title="Your Booster #<?php echo $l2Child['id']; ?>">
                                                ⭐ Mine
                                            </a>
                                        <?php elseif ($isL2Filled): ?>
                                            <a href="boosterTree.php?id=<?php echo $l2Child['id']; ?>" class="subnode-filled" title="Filled Spot">
                                                <i class="fa-solid fa-check"></i> Filled
                                            </a>
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
                                    <div style="font-size: 11px; margin-top: 3px; color: #718096;">
                                        Waiting for Global Join
                                    </div>
                                </div>

                                <div class="tree-line-v-grey"></div>

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
