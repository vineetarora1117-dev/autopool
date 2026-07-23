<?php
/**
 * SAPG MLM Platform Configuration
 * Centralized settings for Packages and Infinity Boosters
 */

global $PACKAGE_CONFIG, $BOOSTER_CONFIG;

$PACKAGE_CONFIG = [
    'main_11' => [
        'id' => 1,
        'name' => 'Starter Pool',
        'cost' => 11.00,
        'req_downlines' => 0,
        'prev_package' => null,
        'autopool_levels' => 8,
        'autopool_l1_4' => 0.125,
        'autopool_l5_8' => 0.875,
        'sponsor_amount' => 5.00,
        'level_levels' => 10,
        'level_amount' => 0.10,
        'company_revenue' => 0.00,
        'reward_reserve' => 1.00,
        'wallet' => 'earnings_11_wallet'
    ],
    'main_30' => [
        'id' => 2,
        'name' => 'Bronze Pool',
        'cost' => 30.00,
        'req_downlines' => 2,
        'prev_package' => 'main_11',
        'autopool_levels' => 8,
        'autopool_l1_4' => 0.50,
        'autopool_l5_8' => 2.75,
        'sponsor_amount' => 10.00,
        'level_levels' => 10,
        'level_amount' => 0.30,
        'company_revenue' => 4.00,
        'reward_reserve' => 0.00,
        'wallet' => 'earnings_30_wallet'
    ],
    'main_60' => [
        'id' => 3,
        'name' => 'Silver Pool',
        'cost' => 60.00,
        'req_downlines' => 4,
        'prev_package' => 'main_30',
        'autopool_levels' => 8,
        'autopool_l1_4' => 1.00,
        'autopool_l5_8' => 5.50,
        'sponsor_amount' => 20.00,
        'level_levels' => 10,
        'level_amount' => 0.60,
        'company_revenue' => 8.00,
        'reward_reserve' => 0.00,
        'wallet' => 'earnings_60_wallet'
    ],
    'main_120' => [
        'id' => 4,
        'name' => 'Gold Pool',
        'cost' => 120.00,
        'req_downlines' => 6,
        'prev_package' => 'main_60',
        'autopool_levels' => 8,
        'autopool_l1_4' => 2.00,
        'autopool_l5_8' => 11.00,
        'sponsor_amount' => 40.00,
        'level_levels' => 10,
        'level_amount' => 1.20,
        'company_revenue' => 16.00,
        'reward_reserve' => 0.00,
        'wallet' => 'earnings_120_wallet'
    ],
    'main_240' => [
        'id' => 5,
        'name' => 'Platinum Pool',
        'cost' => 240.00,
        'req_downlines' => 8,
        'prev_package' => 'main_120',
        'autopool_levels' => 8,
        'autopool_l1_4' => 4.00,
        'autopool_l5_8' => 22.00,
        'sponsor_amount' => 80.00,
        'level_levels' => 10,
        'level_amount' => 2.40,
        'company_revenue' => 32.00,
        'reward_reserve' => 0.00,
        'wallet' => 'earnings_240_wallet'
    ],
    'main_480' => [
        'id' => 6,
        'name' => 'Diamond Pool',
        'cost' => 480.00,
        'req_downlines' => 10,
        'prev_package' => 'main_240',
        'autopool_levels' => 8,
        'autopool_l1_4' => 8.00,
        'autopool_l5_8' => 44.00,
        'sponsor_amount' => 160.00,
        'level_levels' => 10,
        'level_amount' => 4.80,
        'company_revenue' => 64.00,
        'reward_reserve' => 0.00,
        'wallet' => 'earnings_480_wallet'
    ]
];

$BOOSTER_CONFIG = [
    'booster_10' => [
        'id' => 1,
        'name' => '10 Booster',
        'cost' => 10.00,
        'total_generation' => 50.00,
        'user_earnings' => 20.00,
        'sponsor_income' => 10.00,
        'upgrade_reserve' => 20.00,
        'reentry_count' => 0,
        'wallet' => 'booster_10_wallet'
    ],
    'booster_20' => [
        'id' => 2,
        'name' => '20 Booster',
        'cost' => 20.00,
        'total_generation' => 100.00,
        'user_earnings' => 40.00,
        'sponsor_income' => 20.00,
        'upgrade_reserve' => 40.00,
        'reentry_count' => 0,
        'wallet' => 'booster_20_wallet'
    ],
    'booster_40' => [
        'id' => 3,
        'name' => '40 Booster',
        'cost' => 40.00,
        'total_generation' => 200.00,
        'user_earnings' => 80.00,
        'sponsor_income' => 40.00,
        'upgrade_reserve' => 80.00,
        'reentry_count' => 0,
        'wallet' => 'booster_40_wallet'
    ],
    'booster_80' => [
        'id' => 4,
        'name' => '80 Booster',
        'cost' => 80.00,
        'total_generation' => 400.00,
        'user_earnings' => 160.00,
        'sponsor_income' => 80.00,
        'upgrade_reserve' => 160.00,
        'reentry_count' => 0,
        'wallet' => 'booster_80_wallet'
    ],
    'booster_160' => [
        'id' => 5,
        'name' => '160 Booster',
        'cost' => 160.00,
        'total_generation' => 800.00,
        'user_earnings' => 320.00,
        'sponsor_income' => 160.00,
        'upgrade_reserve' => 320.00,
        'reentry_count' => 0,
        'wallet' => 'booster_160_wallet'
    ],
    'booster_320' => [
        'id' => 6,
        'name' => '320 Booster',
        'cost' => 320.00,
        'total_generation' => 1600.00,
        'user_earnings' => 1040.00,
        'sponsor_income' => 160.00,
        'upgrade_reserve' => 0.00,
        'reentry_count' => 40,
        'wallet' => 'booster_320_wallet'
    ]
];
