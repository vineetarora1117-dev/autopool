# Autopool MLM Simulation

## 1. System Rules & Parameters
* **Entry Package:** $11
* **Matrix Structure:** 2xN Forced Matrix (Binary - max 2 downlines per node)
* **Placement Rule:** Top-to-Bottom, Left-to-Right. A new member is always placed under the lowest available ID that has less than 2 direct downlines.

## 2. Fund Distribution ($11 Total)
* **Sponsor (Direct Referral):** $5
* **Autopool:** $4
* **Level Income:** $1 (Pending further clarification)
* **Reward:** $1 (Pending further clarification)
* **Total Allocated:** $11

---

## 3. Simulation - Matrix Filling & Calculation

Let's simulate the first 7 users joining the system. We will track their placement, IDs, and the Autopool distribution.

**Assumption for this simulation:** User 1 is the global top ID and sponsors everyone to make calculation simpler. 

### Step-by-Step Placement

1. **User 1 joins.** (Root node)
   * **Placement:** Root
   * **Available Spots:** Left & Right

2. **User 2 joins.** (Sponsored by User 1)
   * **Placement:** Under User 1 (Left) - Lowest available ID is User 1.
   * **Sponsor gets:** $5 (User 1)
   * **Autopool gets:** $4 goes to the pool / upline.

3. **User 3 joins.** (Sponsored by User 1)
   * **Placement:** Under User 1 (Right)
   * **Sponsor gets:** $5 (User 1)
   * **Autopool gets:** $4 goes to the pool.
   * *Note: User 1 now has 2 downlines and is full.*

4. **User 4 joins.** (Sponsored by User 1)
   * **Placement:** User 1 is full. The next lowest available ID is User 2. Placed under User 2 (Left).
   * **Sponsor gets:** $5 (User 1)
   * **Autopool gets:** $4.

5. **User 5 joins.** (Sponsored by User 1)
   * **Placement:** Placed under User 2 (Right).
   * **Sponsor gets:** $5 (User 1)
   * **Autopool gets:** $4.
   * *Note: User 2 is now full.*

6. **User 6 joins.** (Sponsored by User 1)
   * **Placement:** User 1 and User 2 are full. The next lowest available ID is User 3. Placed under User 3 (Left).
   * **Sponsor gets:** $5 (User 1)
   * **Autopool gets:** $4.

7. **User 7 joins.** (Sponsored by User 1)
   * **Placement:** Placed under User 3 (Right).
   * **Sponsor gets:** $5 (User 1)
   * **Autopool gets:** $4.
   * *Note: User 3 is now full.*

### Matrix Tree Visualization
```text
           [ U1 ]
          /      \
      [ U2 ]    [ U3 ]
      /    \    /    \
   [U4]  [U5] [U6]  [U7]
```

---

## 4. Financial Verification (First 7 Users)

* **Total Revenue Collected:** 7 Users * $11 = **$77**

**Total Distributed:**
* **Sponsor Income:** 6 Users sponsored by U1 (U2-U7) * $5 = **$30**
* **Autopool Income Generated:** 7 Users * $4 = **$28**
* **Level Income Reserve:** 7 Users * $1 = **$7**
* **Reward Reserve:** 7 Users * $1 = **$7**

*Wait, does U1 have a sponsor? For this simulation, we can assume U1's $5 sponsor fee goes to the admin or a global dummy sponsor.*
Including U1's sponsor fee: 7 * $5 = **$35**

**Total Accounting:**
$35 (Sponsor) + $28 (Autopool) + $7 (Level) + $7 (Reward) = **$77** (Matches Total Revenue)

## 5. Autopool Distribution Breakdown ($4 Total)

When a user joins, their **$4 Autopool fee** is distributed upwards to 8 levels of uplines in the matrix (where the joining user is at Level 0):

* **Level 1 to Level 4 Uplines:** Receive **$0.125** each (4 levels * $0.125 = $0.50)
* **Level 5 to Level 8 Uplines:** Receive **$0.875** each (4 levels * $0.875 = $3.50)
* **Total Distributed:** $0.50 + $3.50 = **$4.00**

## 6. Level Income Conditions ($1)

The $1 allocated to Level Income is distributed based on the level of the joining member in relation to their sponsor uplines.
To qualify for level income from a specific level, a user must meet the **direct referral criteria**:
* **Level 1:** Requires 2 direct referrals
* **Level 2:** Requires 4 direct referrals (2 additional)
* **Level 3:** Requires 6 direct referrals (2 additional)
* ... and so on ...
* **Level 10:** Requires 20 direct referrals

If a user does not meet the direct referral condition for a specific level, they will not receive the level income from that level.

## Next Steps
- Implement the **Level Income ($1)** logic into `api.php`.
- Define the **Reward ($1)** criteria.
