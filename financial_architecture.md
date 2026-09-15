# VCanCares — Financial Architecture (Money Flow)

> **Design Principle:** Not a single penny should be unaccounted for. Every rupee has a paper trail.

---

## The Big Picture — Where Does Money Sit?

```
┌──────────┐    Payment Gateway     ┌────────────────────┐
│   USER   │ ──────(Razorpay)─────► │  PLATFORM BANK A/C │
│  (Pays)  │                        │  (Settlement Pool)  │
└──────────┘                        └─────────┬──────────┘
                                              │
                                    Database tracks everything
                                              │
                                    ┌─────────▼──────────┐
                                    │   LEDGER (DB)       │
                                    │                     │
                                    │  User Wallet   ₹0   │  ← Only for refunds/credits
                                    │  Nurse Wallet  ₹X   │  ← Earnings accumulate here
                                    │  Platform Rev  ₹Y   │  ← Commission earned
                                    └─────────────────────┘
                                              │
                                    ┌─────────▼──────────┐
                                    │  Nurse Withdrawal   │
                                    │  (Bank Transfer)    │
                                    └─────────────────────┘
```

**Real money** sits in your **Platform's bank account** (via Razorpay settlement).  
**Database** is the **source of truth** for who owns what portion of that money.

---

## Q1: Should User Pre-Recharge Wallet or Pay at Booking Time?

### ❌ Option A: Pre-Recharge Wallet
- User adds ₹5000 → Books ₹3000 service → ₹3000 deducted
- **Problems:**
  - Extra friction step (user doesn't know exact amount upfront)
  - RBI compliance issues (holding prepaid wallet money requires license)
  - User might add too much/too little
  - Refund complexity increases

### ✅ Option B: Direct Payment at Booking (RECOMMENDED)
- User selects bid → Pays exact `total_amount` via Razorpay → Booking confirmed
- **Why this wins:**
  - Zero friction — user pays exactly what's needed
  - No regulatory issues — you're a marketplace, not a bank
  - This is what **Urban Company, Practo, PharmEasy** all do
  - Simple, predictable, stable for 10+ years

### ✅ User Wallet = REFUND WALLET ONLY
- User wallet balance starts at ₹0
- Money enters user wallet ONLY from:
  - Cancellation refunds
  - Admin credits (dispute resolution, goodwill)
- At next booking, user can **choose**: Pay from wallet balance OR pay via gateway OR split
- This is exactly the Swiggy/Zomato model — proven at massive scale

---

## Q2: The Complete Money Flow

### STEP 1: User Pays at Booking

```
User selects bid (total_amount = ₹3000)
    │
    ├── Has wallet balance ₹500? 
    │   ├── YES → Deduct ₹500 from wallet, pay ₹2500 via Razorpay
    │   └── NO  → Pay full ₹3000 via Razorpay
    │
    ▼
Razorpay payment success → Webhook confirms
    │
    ▼
DB Transaction:
  1. wallet_transactions: DEBIT ₹500 from User Wallet (if used)
  2. booking.payment_status = PAID
  3. booking.payment_method = gateway / wallet+gateway / wallet
  4. booking.gateway_payment_id = 'pay_xyz123' (Razorpay ID)
  5. Sessions auto-generated
  6. booking.status = CONFIRMED
```

> [!IMPORTANT]
> The ₹3000 now sits in your Razorpay settlement pool. In the database, it's tracked as belonging to this booking.

### STEP 2: Sessions Run Daily

```
Day 1: User gets OTP → Nurse verifies → Session starts → Session ends ✅
Day 2: User gets OTP → Nurse verifies → Session starts → Session ends ✅
Day 3: User gets OTP → Nurse verifies → Session starts → Session ends ✅
```

> No money moves during sessions. Money is already collected. We're just tracking service delivery.

### STEP 3: Booking Completes → Nurse Gets Paid

```
All 3 sessions complete
    │
    ▼
DB Transaction:
  1. booking.status = COMPLETED
  2. wallet_transactions: CREDIT ₹2550 to Nurse Wallet (nurse_amount)
  3. Platform keeps ₹450 (commission_amount) — no wallet entry needed,
     it stays in settlement pool as revenue
```

### STEP 4: Nurse Withdraws to Bank

```
Nurse requests withdrawal of ₹2550
    │
    ▼
withdrawal_requests: status = PENDING
    │
    ▼
Admin approves (or auto-approve if amount < threshold)
    │
    ▼
DB Transaction:
  1. Razorpay Payout API → transfers ₹2550 to nurse's bank
  2. wallet_transactions: DEBIT ₹2550 from Nurse Wallet
  3. withdrawal_requests: status = COMPLETED
```

---

## Q3: Nurse Payout — Per Session or All at Once?

### ❌ Per-Session Payout
- Nurse gets ₹850 after each session
- **Problems:**
  - Multiple small bank transfers = high transaction fees
  - If user cancels mid-booking, you've already paid the nurse — now you're chasing money
  - Accounting nightmare

### ✅ After Booking Completion (RECOMMENDED)
- Nurse gets full `nurse_amount` after ALL sessions are done
- **Why:**
  - One clean payout per booking
  - If cancelled midway → you only pay for completed sessions (money is still with you)
  - Simple accounting: Booking done = nurse paid
  - This is what Urban Company does

### Settlement Cycle
- Nurse wallet accumulates earnings from completed bookings
- Nurse can withdraw anytime (min ₹100)
- OR auto-settlement every Monday (configurable)

---

## Q4: Cancellation Money Flow

### User Cancels (Paid Booking)

```
Booking: 5 days, ₹5000 total, 2 completed, 3 remaining
Per-session rate: ₹1000

Remaining amount: ₹3000
Slab: User cancelling 20hrs before → 85% refund
Refund: ₹3000 × 85% = ₹2550

DB Transaction:
  1. wallet_transactions: CREDIT ₹2550 to User Wallet
     (reason: CANCELLATION_REFUND)
  2. booking.status = CANCELLED
  3. booking.payment_status = PARTIALLY_REFUNDED
  4. Cancelled sessions marked CANCELLED
  5. Nurse gets paid for 2 completed sessions: ₹2000
     → CREDIT ₹2000 to Nurse Wallet
  6. Platform keeps: ₹5000 - ₹2550 - ₹2000 = ₹450 (cancellation + commission)
```

### Nurse Cancels (Paid Booking)

```
Same booking: 5 days, 2 completed, 3 remaining

DB Transaction:
  1. Full refund of remaining: ₹3000 → CREDIT to User Wallet
  2. Nurse paid for completed only: ₹2000 → CREDIT to Nurse Wallet
     (nurse loses commission on remaining)
  3. Nurse gets cancellation strike
  4. Platform keeps: ₹0 from remaining (penalty for nurse-caused cancel)
```

---

## Q5: Complete Ledger — Every Penny Tracked

| Event | User Wallet | Nurse Wallet | Platform Revenue |
|-------|-------------|-------------|-----------------|
| User pays ₹3000 via gateway | — | — | +₹3000 (in settlement) |
| Booking completes | — | +₹2550 | ₹450 retained |
| Nurse withdraws ₹2550 | — | -₹2550 | -₹2550 (payout) |
| User cancels, ₹850 refund | +₹850 | — | -₹850 (to user wallet) |
| User uses wallet for next booking | -₹850 | — | — |

> [!CAUTION]
> **Golden Rule:** `Platform Settlement Pool = Sum of all Nurse Wallet balances + Sum of all User Wallet balances + Platform Revenue`
>
> This equation MUST balance at all times. Run a daily reconciliation check.

---

## Database Changes Needed

### 1. Add to `bookings` table
```
payment_method     TINYINT  -- 1=gateway, 2=wallet, 3=wallet+gateway
gateway_payment_id VARCHAR  -- Razorpay payment_id
gateway_order_id   VARCHAR  -- Razorpay order_id  
wallet_amount_used DECIMAL  -- How much was deducted from wallet
```

### 2. New `withdrawal_requests` table
```
id
nurse_user_id       FK → users
amount              DECIMAL
status              TINYINT  -- 0=pending, 1=processing, 2=completed, 3=failed, 4=rejected
bank_account_name   VARCHAR
bank_account_number VARCHAR  (encrypted)
bank_ifsc           VARCHAR
gateway_payout_id   VARCHAR  -- Razorpay payout reference
processed_at        TIMESTAMP
admin_note          TEXT
created_at
```

### 3. Add new WalletTransaction reasons
```
REASON_BOOKING_PAYMENT     = 1  // User pays for booking (wallet portion)
REASON_CANCELLATION_REFUND = 2  // Refund to user wallet
REASON_NURSE_PAYOUT        = 3  // Earnings credited to nurse wallet  
REASON_ADMIN_CREDIT        = 4  // Manual admin credit
REASON_ADMIN_DEBIT         = 5  // Manual admin debit
REASON_PLATFORM_FEE        = 6  // Commission retained
REASON_WITHDRAWAL          = 7  // Nurse withdraws to bank ← NEW
REASON_GATEWAY_PAYMENT     = 8  // Full gateway payment logged ← NEW
```

---

## Updated Service Flow

### BookingService::confirmPayment() — REVISED

```php
// 1. Calculate wallet vs gateway split
// 2. If user has wallet balance → deduct wallet first
// 3. Remaining → Razorpay order created → user pays
// 4. On Razorpay webhook → confirm booking
// 5. Generate sessions
```

### BookingService::onBookingComplete() — NEW

```php
// 1. Credit nurse_amount to nurse wallet
// 2. Log platform commission as revenue
// 3. Update booking status
```

### WithdrawalService — NEW

```php
// 1. Nurse requests withdrawal
// 2. Validate balance >= amount
// 3. Create withdrawal_request (pending)
// 4. Admin approves → Razorpay Payout API
// 5. On success → debit nurse wallet
```

---

## Summary — The Rules

| Rule | Detail |
|------|--------|
| User pays at booking time | Via Razorpay (+ wallet if balance exists) |
| User wallet is REFUND-ONLY | Money enters only from refunds/admin credits |
| Money sits in platform bank | Razorpay settlement → your bank account |
| Nurse gets paid after completion | Full nurse_amount after all sessions done |
| Nurse wallet = earnings | Accumulates from completed bookings |
| Nurse withdraws to bank | Via withdrawal request → Razorpay Payout |
| Cancelled mid-booking | Nurse paid for completed sessions only |
| Every transaction has balance_after | 100% audit trail, zero ambiguity |
| Daily reconciliation | Platform pool = user wallets + nurse wallets + revenue |

---

> [!TIP]
> **This is the exact model used by Urban Company, Practo, and PharmEasy.** It's proven at scale, RBI-compliant, and has survived 10+ years of operations. No over-engineering, just solid fundamentals.
