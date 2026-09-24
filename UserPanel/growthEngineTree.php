<?php
require_once '../libs/db.php';
require_once '../libs/auth.php';
require_once '../libs/config.php';

requireLogin();
$user_id = $_SESSION['user_id'];

// Fetch all boosters in the global matrix ordered by ID
$stmt = $pdo->query("SELECT id, user_id, upline_booster_id, downline_count, status FROM user_growth_engines ORDER BY id ASC");
$allBoosters = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Index boosters by ID and by upline_booster_id
$boostersById = [];
$childrenByUpline = [];
$globalRootId = null;

foreach ($allBoosters as $b) {
    $bId = intval($b['id']);
    $uId = $b['upline_booster_id'] ? intval($b['upline_booster_id']) : null;
    $boostersById[$bId] = $b;
    
    if ($uId === null && $globalRootId === null) {
        $globalRootId = $bId;
    }
    if ($uId !== null) {
        if (!isset($childrenByUpline[$uId])) {
            $childrenByUpline[$uId] = [];
        }
        $childrenByUpline[$uId][] = $b;
    }
}

// Function to recursively render a 1x3 tree node
function renderTreeNode($boosterId, $currentUserId, &$boostersById, &$childrenByUpline, $maxDepth = 10, $currentDepth = 1) {
    if (!$boosterId || $currentDepth > $maxDepth) {
        return;
    }

    $booster = isset($boostersById[$boosterId]) ? $boostersById[$boosterId] : null;
    if (!$booster) {
        return;
    }

    $isUser = ($booster['user_id'] === $currentUserId);
    $nodeColorClass = $isUser ? 'dot-user' : 'dot-other';
    $children = isset($childrenByUpline[$boosterId]) ? $childrenByUpline[$boosterId] : [];
    $numChildren = count($children);
    $hasChildren = ($numChildren > 0) || ($booster['status'] === 'active');

    echo '<div class="tree-branch-container">';
    // Node Dot
    echo '<div class="dot-node ' . $nodeColorClass . '"></div>';

    // Render Children if active/has downlines
    if ($hasChildren && $currentDepth < $maxDepth) {
        echo '<div class="line-down"></div>';
        echo '<div class="children-row">';
        for ($i = 0; $i < 3; $i++) {
            echo '<div class="child-col">';
            echo '<div class="line-to-child"></div>';
            if (isset($children[$i])) {
                renderTreeNode($children[$i]['id'], $currentUserId, $boostersById, $childrenByUpline, $maxDepth, $currentDepth + 1);
            } else {
                // Empty Slot
                echo '<div class="dot-node dot-empty"></div>';
            }
            echo '</div>';
        }
        echo '</div>';
    }
    echo '</div>';
}

include '../includes/header.php';
?>

<style>
/* Scrollable Container (Horizontal & Vertical) */
.global-tree-viewport {
    background: rgba(6, 17, 33, 0.9);
    border: 1px solid #ffb703;
    border-radius: 14px;
    padding: 30px 20px;
    margin-top: 20px;
    overflow: auto;
    max-height: 75vh;
    width: 100%;
    -webkit-overflow-scrolling: touch;
    touch-action: pan-x pan-y;
}

.global-tree-canvas {
    display: inline-flex;
    justify-content: center;
    min-width: 100%;
    padding: 20px 40px;
}

/* Dots Styling (Ultra Compact) */
.dot-node {
    width: 14px;
    height: 14px;
    border-radius: 50%;
    display: inline-block;
    flex-shrink: 0;
    box-shadow: 0 2px 5px rgba(0,0,0,0.3);
    position: relative;
    z-index: 2;
}

/* Green Dot = User's Position */
.dot-user {
    background: #2ecc71;
    border: 1.5px solid #27ae60;
    box-shadow: 0 0 8px rgba(46, 204, 113, 0.7);
}

/* Blue Dot = Other Members */
.dot-other {
    background: #3498db;
    border: 1.5px solid #2980b9;
    box-shadow: 0 0 6px rgba(52, 152, 219, 0.5);
}

/* Grey Dot = Empty Spot */
.dot-empty {
    background: rgba(255, 255, 255, 0.08);
    border: 1.5px dashed #718096;
}

/* Tree Connector Lines (Ultra Compact) */
.tree-branch-container {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.line-down {
    width: 1.5px;
    height: 8px;
    background: #ffb703;
}

.children-row {
    display: flex;
    justify-content: space-around;
    gap: 4px;
    position: relative;
}

.children-row::before {
    content: '';
    position: absolute;
    top: 0;
    left: 7px;
    right: 7px;
    height: 1.5px;
    background: #ffb703;
}

.child-col {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.line-to-child {
    width: 1.5px;
    height: 8px;
    background: #ffb703;
}

/* Legend Styling */
.legend-bar {
    display: flex;
    justify-content: center;
    gap: 30px;
    flex-wrap: wrap;
    background: rgba(0, 0, 0, 0.4);
    padding: 14px 24px;
    border-radius: 10px;
    border: 1px solid rgba(255, 183, 3, 0.3);
}
.legend-item {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    font-weight: bold;
}
</style>

<div class="content-section active-view" style="padding: 20px; max-width: 1100px; margin: 0 auto;">
    <div class="profile-header-bar">
        <div class="profile-header-title">
            <i class="fa-solid fa-sitemap"></i> Global Growth Engine Tree
        </div>
        <div class="profile-breadcrumb">
            <a href="index.php">Home</a> &raquo; 
            <a href="boosterIncome.php">Growth Engine</a> &raquo; 
            Global Growth Engine Tree
        </div>
    </div>

    <!-- Header Info & Legend -->
    <div style="background: rgba(6, 17, 33, 0.75); border: 1px solid rgba(255, 183, 3, 0.3); border-radius: 12px; padding: 20px; margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div style="font-size: 16px; color: #fff; font-weight: bold;">
                <i class="fa-solid fa-globe" style="color: #ffb703;"></i> Global Growth Engine Matrix Network
            </div>

            <div>
                <a href="boosterIncome.php" style="background: rgba(255, 183, 3, 0.15); color: #ffb703; border: 1px solid #ffb703; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 13px;">
                    <i class="fa-solid fa-arrow-left"></i> Back to Growth Engine Log
                </a>
            </div>
        </div>

        <!-- Color Legend -->
        <div class="legend-bar" style="margin-top: 15px;">
            <div class="legend-item">
                <div class="dot-node dot-user" style="width: 20px; height: 20px;"></div>
                <span style="color: #2ecc71;">Your Positions</span>
            </div>
            <div class="legend-item">
                <div class="dot-node dot-other" style="width: 20px; height: 20px;"></div>
                <span style="color: #3498db;">Other Members</span>
            </div>
            <div class="legend-item">
                <div class="dot-node dot-empty" style="width: 20px; height: 20px;"></div>
                <span style="color: #a0aec0;">Empty Spots</span>
            </div>
        </div>
    </div>

    <!-- Scrollable Global Tree Viewport -->
    <div class="global-tree-viewport">
        <?php if (!$globalRootId): ?>
            <div style="text-align: center; padding: 40px; color: #a0aec0;">
                No Growth Engines in global tree yet.
            </div>
        <?php else: ?>
            <div class="global-tree-canvas">
                <?php renderTreeNode($globalRootId, $user_id, $boostersById, $childrenByUpline, 10, 1); ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
