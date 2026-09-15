<?php
require_once '../libs/db.php';
require_once '../libs/auth.php';
require_once '../libs/config.php';

requireLogin();
$user_id = $_SESSION['user_id'];

// Determine requested root booster to focus (default to user's first booster or global root #1)
$requestedId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$focusBooster = null;

if ($requestedId > 0) {
    $stmtFocus = $pdo->prepare("SELECT * FROM user_boosters WHERE id = ?");
    $stmtFocus->execute([$requestedId]);
    $focusBooster = $stmtFocus->fetch(PDO::FETCH_ASSOC);
}

if (empty($focusBooster)) {
    // Default to current user's first booster if available, else global root #1
    $stmtMyFirst = $pdo->prepare("SELECT * FROM user_boosters WHERE user_id = ? ORDER BY id ASC LIMIT 1");
    $stmtMyFirst->execute([$user_id]);
    $focusBooster = $stmtMyFirst->fetch(PDO::FETCH_ASSOC);

    if (empty($focusBooster)) {
        $stmtGlobalRoot = $pdo->query("SELECT * FROM user_boosters ORDER BY id ASC LIMIT 1");
        $focusBooster = $stmtGlobalRoot->fetch(PDO::FETCH_ASSOC);
    }
}

// Fetch all user's boosters for selector dropdown
$stmtMyBoosters = $pdo->prepare("SELECT id, status FROM user_boosters WHERE user_id = ? ORDER BY id ASC");
$stmtMyBoosters->execute([$user_id]);
$myBoosters = $stmtMyBoosters->fetchAll(PDO::FETCH_ASSOC);

// Fetch Level 1 and Level 2 nodes for the global tree visual
$level1Nodes = [];
$level2Nodes = []; // Keyed by Level 1 booster ID

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
    min-width: 750px;
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
    min-width: 140px;
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
    min-width: 65px;
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
            <div style="display: flex; align-items: center; gap: 10px;">
                <?php if (!empty($myBoosters)): ?>
                    <label for="boosterSelect" style="color: #ffb703; font-weight: bold; font-size: 14px;">Select Position:</label>
                    <select id="boosterSelect" onchange="window.location.href='boosterTree.php?id='+this.value" style="background: #061121; color: #ffb703; border: 1px solid #ffb703; padding: 8px 14px; border-radius: 6px; font-weight: bold; font-size: 14px; cursor: pointer; outline: none;">
                        <?php foreach ($myBoosters as $mb): ?>
                            <option value="<?php echo $mb['id']; ?>" <?php echo ($focusBooster && intval($focusBooster['id']) === intval($mb['id'])) ? 'selected' : ''; ?>>
                                My Position #<?php echo $mb['id']; ?> (<?php echo ucfirst($mb['status']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
                <a href="boosterTree.php?id=1" style="color: #3498db; text-decoration: none; font-size: 13px; font-weight: bold; padding: 6px 12px; background: rgba(52, 152, 219, 0.15); border: 1px solid #3498db; border-radius: 6px;">
                    <i class="fa-solid fa-tree"></i> Global Top Node
                </a>
            </div>

            <div>
                <a href="boosterIncome.php" style="background: rgba(255, 183, 3, 0.15); color: #ffb703; border: 1px solid #ffb703; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 13px;">
                    <i class="fa-solid fa-arrow-left"></i> Back to Booster Log
                </a>
            </div>
        </div>

        <!-- Clean Legend -->
        <div class="legend-bar">
            <div class="legend-item">
                <div class="legend-box" style="background: #2ecc71;"></div>
                <span style="color: #2ecc71;">Your Position</span>
            </div>
            <div class="legend-item">
                <div class="legend-box" style="background: #3498db;"></div>
                <span style="color: #3498db;">Other's Position</span>
            </div>
            <div class="legend-item">
                <div class="legend-box" style="background: rgba(255, 255, 255, 0.05); border: 1px dashed #718096;"></div>
                <span style="color: #718096;">Empty</span>
            </div>
        </div>
    </div>

    <!-- Tree Card -->
    <div class="tree-card">
        <?php if (!$focusBooster): ?>
            <div style="text-align: center; padding: 40px; color: #a0aec0;">
                No boosters in global tree yet.
            </div>
        <?php else: ?>
            <div class="tree-wrapper">
                <!-- ROOT NODE -->
                <?php 
                $isRootUser = ($focusBooster['user_id'] === $user_id);
                $rootClass = $isRootUser ? 'node-user' : 'node-other';
                $rootLabel = $isRootUser ? 'Your Position' : 'Filled';
                ?>
                <a href="boosterTree.php?id=<?php echo $focusBooster['id']; ?>" class="badge-node <?php echo $rootClass; ?>">
                    <?php echo $rootLabel; ?>
                </a>

                <div class="tree-line-v"></div>

                <!-- Horizontal Connector Line -->
                <div style="width: 66%; height: 2px; background: #ffb703;"></div>

                <!-- LEVEL 1 ROW -->
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
                                <a href="boosterTree.php?id=<?php echo $l1['id']; ?>" class="badge-node <?php echo $l1Class; ?>" title="Click to view node">
                                    <?php echo $l1Label; ?>
                                </a>

                                <div class="tree-line-v-sub"></div>

                                <!-- LEVEL 2 SUB-NODES -->
                                <div class="tree-level2-row">
                                    <?php 
                                    $l2Children = isset($level2Nodes[$l1['id']]) ? $level2Nodes[$l1['id']] : [];
                                    for ($j = 0; $j < 3; $j++):
                                        $l2 = isset($l2Children[$j]) ? $l2Children[$j] : null;
                                        $isL2Filled = ($l2 !== null);
                                        $isL2User = ($isL2Filled && $l2['user_id'] === $user_id);
                                    ?>
                                        <?php if ($isL2User): ?>
                                            <a href="boosterTree.php?id=<?php echo $l2['id']; ?>" class="subnode subnode-user">Your Position</a>
                                        <?php elseif ($isL2Filled): ?>
                                            <a href="boosterTree.php?id=<?php echo $l2['id']; ?>" class="subnode subnode-other">Filled</a>
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
                                    <span class="subnode-empty subnode">Empty</span>
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
