<?php
require_once '../libs/db.php';
require_once '../libs/auth.php';
require_once '../libs/config.php';

requireLogin();
$user_id = $_SESSION['user_id'];

// Always start from Global Root (booster with upline_booster_id IS NULL or lowest ID)
// Allow optional ?id= to view a specific sub-tree root
$requestedId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$globalRoot = null;

if ($requestedId > 0) {
    $stmtReq = $pdo->prepare("SELECT * FROM user_boosters WHERE id = ?");
    $stmtReq->execute([$requestedId]);
    $globalRoot = $stmtReq->fetch(PDO::FETCH_ASSOC);
}

if (empty($globalRoot)) {
    // Fetch global root node (topmost booster in the system)
    $stmtRoot = $pdo->query("SELECT * FROM user_boosters WHERE upline_booster_id IS NULL ORDER BY id ASC LIMIT 1");
    $globalRoot = $stmtRoot->fetch(PDO::FETCH_ASSOC);

    // Fallback if upline_booster_id IS NULL isn't set
    if (empty($globalRoot)) {
        $stmtRootFallback = $pdo->query("SELECT * FROM user_boosters ORDER BY id ASC LIMIT 1");
        $globalRoot = $stmtRootFallback->fetch(PDO::FETCH_ASSOC);
    }
}

// Fetch Level 1 downlines (3 spots under root)
$level1Nodes = [];
$level2Nodes = []; // Keyed by Level 1 booster ID

if ($globalRoot) {
    $stmtL1 = $pdo->prepare("SELECT * FROM user_boosters WHERE upline_booster_id = ? ORDER BY id ASC LIMIT 3");
    $stmtL1->execute([$globalRoot['id']]);
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
.tree-card {
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
    padding: 20px 0;
}

/* Color Coded Tree Badges */
.badge-node {
    border-radius: 10px;
    padding: 12px 24px;
    font-size: 14px;
    font-weight: bold;
    text-align: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.4);
    display: inline-block;
    min-width: 150px;
    text-decoration: none;
    transition: transform 0.2s;
}
.badge-node:hover {
    transform: translateY(-2px);
}

/* User's Position -> GREEN */
.node-user {
    background: #2ecc71;
    color: #000;
    border: 2px solid #27ae60;
}

/* Other's Position -> BLUE */
.node-other {
    background: #3498db;
    color: #fff;
    border: 2px solid #2980b9;
}

/* Empty Position -> GREY */
.node-empty {
    background: rgba(255, 255, 255, 0.05);
    color: #718096;
    border: 2px dashed #4a5568;
    cursor: default;
}
.node-empty:hover {
    transform: none;
}

/* Level 2 Sub-badges */
.subnode {
    border-radius: 6px;
    padding: 6px 12px;
    font-size: 11px;
    font-weight: bold;
    min-width: 70px;
    text-align: center;
    display: inline-block;
    text-decoration: none;
}
.subnode-user {
    background: #2ecc71;
    color: #000;
}
.subnode-other {
    background: #3498db;
    color: #fff;
}
.subnode-empty {
    background: rgba(255, 255, 255, 0.03);
    color: #718096;
    border: 1px dashed #4a5568;
}

/* Tree Connectors */
.tree-line-v {
    width: 2px;
    height: 25px;
    background: #ffb703;
}
.tree-line-v-sub {
    width: 2px;
    height: 18px;
    background: rgba(255, 183, 3, 0.5);
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
    margin-top: 6px;
}

.legend-bar {
    display: flex;
    justify-content: center;
    gap: 30px;
    flex-wrap: wrap;
    background: rgba(0, 0, 0, 0.3);
    padding: 12px 20px;
    border-radius: 8px;
    border: 1px solid rgba(255, 183, 3, 0.2);
    margin-top: 15px;
}
.legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: bold;
}
.legend-box {
    width: 18px;
    height: 18px;
    border-radius: 4px;
}
</style>

<div class="content-section active-view" style="padding: 20px; max-width: 1100px; margin: 0 auto;">
    <div class="profile-header-bar">
        <div class="profile-header-title">
            <i class="fa-solid fa-sitemap"></i> Global Booster Tree
        </div>
        <div class="profile-breadcrumb">
            <a href="index.php">Home</a> &raquo; 
            <a href="boosterIncome.php">Booster</a> &raquo; 
            Global Booster Tree
        </div>
    </div>

    <!-- Header Controls & Color Legend -->
    <div style="background: rgba(6, 17, 33, 0.75); border: 1px solid rgba(255, 183, 3, 0.3); border-radius: 12px; padding: 20px; margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div style="font-size: 15px; color: #fff; font-weight: bold;">
                <i class="fa-solid fa-globe" style="color: #ffb703;"></i> Global Booster Network Matrix
            </div>

            <div style="display: flex; gap: 10px; align-items: center;">
                <?php if ($requestedId > 0 && $globalRoot && $globalRoot['upline_booster_id']): ?>
                    <a href="boosterTree.php?id=<?php echo $globalRoot['upline_booster_id']; ?>" style="color: #ffb703; text-decoration: none; font-size: 13px; font-weight: bold; padding: 6px 12px; background: rgba(255, 183, 3, 0.15); border: 1px solid #ffb703; border-radius: 6px;">
                        <i class="fa-solid fa-arrow-up"></i> Up One Level
                    </a>
                <?php endif; ?>
                
                <a href="boosterTree.php" style="color: #3498db; text-decoration: none; font-size: 13px; font-weight: bold; padding: 6px 12px; background: rgba(52, 152, 219, 0.15); border: 1px solid #3498db; border-radius: 6px;">
                    <i class="fa-solid fa-tree"></i> Global Top Root
                </a>

                <a href="boosterIncome.php" style="background: rgba(255, 183, 3, 0.15); color: #ffb703; border: 1px solid #ffb703; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 13px;">
                    <i class="fa-solid fa-arrow-left"></i> Back to Log
                </a>
            </div>
        </div>

        <!-- Clean Color Legend -->
        <div class="legend-bar">
            <div class="legend-item">
                <div class="legend-box" style="background: #2ecc71;"></div>
                <span style="color: #2ecc71;">Your Position (Green)</span>
            </div>
            <div class="legend-item">
                <div class="legend-box" style="background: #3498db;"></div>
                <span style="color: #3498db;">Other's Position (Blue)</span>
            </div>
            <div class="legend-item">
                <div class="legend-box" style="background: rgba(255, 255, 255, 0.05); border: 1px dashed #718096;"></div>
                <span style="color: #718096;">Empty (Grey)</span>
            </div>
        </div>
    </div>

    <!-- Global Tree Container -->
    <div class="tree-card">
        <?php if (!$globalRoot): ?>
            <div style="text-align: center; padding: 40px; color: #a0aec0;">
                No boosters in global tree yet.
            </div>
        <?php else: ?>
            <div class="tree-wrapper">
                <!-- GLOBAL ROOT NODE (STARTS FROM SYSTEM ROOT) -->
                <?php 
                $isRootUser = ($globalRoot['user_id'] === $user_id);
                $rootClass = $isRootUser ? 'node-user' : 'node-other';
                $rootLabel = $isRootUser ? 'Your Position' : 'Filled';
                ?>
                <a href="boosterTree.php?id=<?php echo $globalRoot['id']; ?>" class="badge-node <?php echo $rootClass; ?>">
                    <?php echo $rootLabel; ?>
                </a>

                <div class="tree-line-v"></div>

                <!-- Horizontal Connector Line -->
                <div style="width: 66%; height: 2px; background: #ffb703;"></div>

                <!-- LEVEL 1 ROW (3 Downline Slots of Root) -->
                <div class="tree-level1-row">
                    <?php for ($i = 0; $i < 3; $i++): ?>
                        <?php 
                        $l1 = isset($level1Nodes[$i]) ? $level1Nodes[$i] : null;
                        $isL1Filled = ($l1 !== null);
                        $isL1User = ($isL1Filled && $l1['user_id'] === $user_id);
                        ?>
                        <div class="tree-branch-col">
                            <div class="tree-line-v-sub"></div>

                            <?php if ($isL1Filled): ?>
                                <?php 
                                $l1Class = $isL1User ? 'node-user' : 'node-other';
                                $l1Label = $isL1User ? 'Your Position' : 'Filled';
                                ?>
                                <a href="boosterTree.php?id=<?php echo $l1['id']; ?>" class="badge-node <?php echo $l1Class; ?>" title="Click to expand branch">
                                    <?php echo $l1Label; ?>
                                </a>

                                <div class="tree-line-v-sub"></div>

                                <!-- LEVEL 2 SUB-NODES (3 Downlines under each Level 1 Node) -->
                                <div class="tree-level2-row">
                                    <?php 
                                    $l2Children = isset($level2Nodes[$l1['id']]) ? $level2Nodes[$l1['id']] : [];
                                    for ($j = 0; $j < 3; $j++):
                                        $l2 = isset($l2Children[$j]) ? $l2Children[$j] : null;
                                        $isL2Filled = ($l2 !== null);
                                        $isL2User = ($isL2Filled && $l2['user_id'] === $user_id);
                                    ?>
                                        <?php if ($isL2User): ?>
                                            <a href="boosterTree.php?id=<?php echo $l2['id']; ?>" class="subnode subnode-user" title="Click to expand">Your Position</a>
                                        <?php elseif ($isL2Filled): ?>
                                            <a href="boosterTree.php?id=<?php echo $l2['id']; ?>" class="subnode subnode-other" title="Click to expand">Filled</a>
                                        <?php else: ?>
                                            <span class="subnode subnode-empty">Empty</span>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>

                            <?php else: ?>
                                <div class="badge-node node-empty">
                                    Empty
                                </div>

                                <div class="tree-line-v-sub"></div>

                                <div class="tree-level2-row">
                                    <span class="subnode subnode-empty">Empty</span>
                                    <span class="subnode subnode-empty">Empty</span>
                                    <span class="subnode subnode-empty">Empty</span>
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
