# Tronmatix — stock inventory system: Claude Code prompt sequence (v4, full fix pass)

**How to use this:** run one prompt at a time in Claude Code. After each one, read the diff before moving to the next. Each prompt tells Claude Code to check your existing code first instead of guessing.

**What changed from v3 → v4:** every fix below closes a real ambiguity gap, not a rewrite of the concurrency logic (which was already correct in v3):
1. **Prompt 1** — `unit_cost` sign convention is now explicit: always stored positive (a cost basis, never signed like `quantity`), with reversal rows told to copy the original movement's `unit_cost` unchanged.
2. **Prompt 2** — `adjust()`, `reportDamaged()`, and `reverse()` are now told exactly what to store in `unit_cost` for each case, instead of leaving Claude Code to invent a convention.
3. **Prompt 3** — line items are merged by `product_id` before the sell loop, so a duplicate SKU in one order can't lock the same product row twice or fragment the `$reference` history.
4. **Prompt 3** — the pre-check pass in step 4 now explicitly reuses the same sorted, batched query instead of risking an N+1 loop.
5. **Prompt 7** — added a dedicated test for reversing a `receive()` after later stock was already sold — the trickiest edge case in `reverse()`, previously described but never tested.
6. **Prompt 8** — backfill now explicitly says to leave `cost_price` null when no cost data exists, rather than guessing a number, consistent with how Prompt 6's dashboard widget already handles nulls.

**Before you run Prompt 1:** confirm Tronmatix products are single-SKU. Everything below assumes one stock count per product row. If there's a `product_variants` table (or similar) already, stock needs to live there instead — tell Claude Code that up front rather than letting it guess.

---

## Prompt 1 — Database schema & migrations

**What it does:** creates the ledger table and a database-level safety net so stock can never go negative, even if a bug slips through the app code.

```
I'm adding a stock inventory system to this Laravel project. Look at my existing
Product model and migration first to see its current columns — and check
whether products already have variants (a product_variants table or similar).
Everything below assumes one stock count per product row; tell me before
proceeding if that's not how this schema works.

Then create:
1. A migration that adds these columns to the products table (only if they
   don't already exist): current_stock (integer, default 0), cost_price
   (decimal 10,2, nullable), low_stock_threshold (integer, default 5).
2. A new migration for a stock_movements table with columns: id, product_id
   (foreign key to products, cascade on delete), type (string: in, out,
   adjustment, damaged, reversal — no separate "return" type; a customer
   return from a cancelled/refunded order is handled by reverse()'s
   positive-quantity 'reversal' row, not a distinct type), quantity (integer
   — always signed to match its effect on stock: positive when stock
   increases (in, adjustment-up, or a reversal of an out-movement), negative
   when stock decreases (out, damaged, adjustment-down, or a reversal of an
   in-movement)), unit_cost (decimal 10,2, nullable — unlike quantity, this
   is NEVER signed; it always represents a positive cost-per-unit basis
   regardless of whether the movement increases or decreases stock. A
   reversal row's unit_cost is copied unchanged from the movement it
   reverses — don't flip its sign and don't recompute it, since it's
   describing the same unit cost, just undoing the quantity effect), note
   (text, nullable), a nullable morphs column named "reference" (use
   $table->nullableMorphs('reference') rather than manual reference_type/
   reference_id columns — it creates the right composite index automatically
   and works with Eloquent's morphTo()), reversed_movement_id (nullable,
   self-referencing foreign key to stock_movements.id — set on a reversal
   row to point at the movement it reversed, so we can trace it and block
   double-reversal), created_by (foreign key to users, nullOnDelete() —
   never cascade-delete ledger rows just because a user account gets
   removed), timestamps. Add a composite index on (product_id, created_at),
   since the history page will always query by product ordered by date.
   Also add a unique index on reversed_movement_id — since it's nullable,
   the unique constraint only ever applies to whichever single reversal row
   points at a given movement, so this becomes a DB-level guarantee that no
   movement can ever be reversed twice, backing up the application-level
   check in reverse() rather than relying on it alone.
3. Add a database check constraint on products.current_stock >= 0
   (PostgreSQL: use DB::statement in the migration —
   ALTER TABLE products ADD CONSTRAINT stock_non_negative CHECK
   (current_stock >= 0)). Add the matching DROP CONSTRAINT IF EXISTS in the
   down() method. This is a last-resort safety net so the database itself
   rejects anything that tries to push stock negative, even if a bug slips
   past the application layer.

Run the migrations and show me the resulting schema.
```

---

## Prompt 2 — Models & the StockService (the most important prompt)

**What it does:** centralizes every stock change through one service class with database locking, so two sales happening at the same instant can't both succeed on the last unit in stock.

```
Now create the model layer for stock movements.

1. Create a StockMovement model with a belongsTo relationship to Product, a
   morphTo() reference() relationship, a self-referencing reversedMovement()
   relationship (via reversed_movement_id), fillable fields, and a type enum
   if Laravel 11 enums fit cleanly here.
2. Add a stockMovements() hasMany relationship on the Product model.
3. Create a custom InsufficientStockException (extends \Exception) that
   carries the product and the shortfall amount, so callers can catch it
   specifically and build a clear error message instead of catching a bare
   Exception.
4. Create a StockService class (app/Services/StockService.php) with these
   methods:
   - receiveStock(Product $product, int $quantity, float $unitCost,
     ?string $note = null, ?int $userId = null): StockMovement — unitCost
     here is always a positive cost-per-unit and is stored on the movement
     as-is.
   - sell(Product $product, int $quantity, ?Model $reference = null,
     ?int $userId = null): StockMovement — throws InsufficientStockException
     if $quantity exceeds current stock. unit_cost is not meaningful for a
     sale (that's a selling-price concern, not a cost concern) — leave it
     null on 'out' movements.
   - adjust(Product $product, int $countedQuantity, ?string $note = null,
     ?int $userId = null): ?StockMovement — computes the difference between
     counted and current, logs it as type 'adjustment'. If the difference is
     0, don't create a no-op movement row — return null instead. Leave
     unit_cost null on adjustment rows; a stock count doesn't imply a cost
     basis for the discrepancy, and inventing one would misrepresent the
     dashboard's total-inventory-value calculation in Prompt 6.
   - reportDamaged(Product $product, int $quantity, string $note,
     ?int $userId = null): StockMovement — logs a negative-quantity
     movement of type 'damaged' (covers both damaged and lost stock, per the
     combined "Report damaged/lost" form in Prompt 5). Leave unit_cost null
     here too, for the same reason as adjust() — same rationale, don't
     guess a cost.
   - reverse(StockMovement $movement, ?int $userId = null): StockMovement —
     for order cancellations/refunds. Do the double-reversal check AFTER
     acquiring the product lock, not before: since $movement belongs to
     exactly one product, lock that product row first
     (Product::lockForUpdate()->find($movement->product_id)), and only then
     check whether a movement already exists with reversed_movement_id
     equal to $movement->id — if so, throw, because it's already been
     reversed and a double-cancel or a retried webhook must not reverse it
     twice. Checking for the existing reversal before the lock is acquired
     recreates the same race condition flagged below for the stock-level
     check: two concurrent reverse() calls on the same movement could both
     pass a pre-lock check before either one commits. Creates an
     opposite-signed movement (type 'reversal'), sets its
     reversed_movement_id to $movement->id, copies unit_cost unchanged from
     $movement (don't flip its sign — see the schema note in Prompt 1), and
     — same as every other method here — goes through the lock+transaction
     and can itself throw InsufficientStockException if reversing would
     push stock negative (relevant if you ever reverse a 'receive' after
     some of that stock has already been sold on).

Critical requirement, and this is the part to slow down on: every method
must wrap its stock_movements insert AND the products.current_stock update
inside a single DB::transaction(), and must call
Product::lockForUpdate()->find($product->id) — re-fetching the row under the
lock, not trusting the $product instance passed in, which may be stale —
before reading current stock. The order matters: acquire the lock first,
THEN read the fresh current_stock, THEN validate, THEN write. Validating
against the un-locked $product argument before acquiring the lock recreates
the exact race condition this is meant to prevent. Add comments explaining
why the lock is there and why the re-fetch happens inside it.
```

---

## Prompt 3 — Hook into the existing order flow

**What it does:** wires automatic stock-out into your existing order confirmation logic, and automatic stock-back-in on cancellation/refund, without you touching the order code by hand.

```
Now integrate this into my existing order flow. First look at my Order model
and wherever orders get confirmed or paid.

1. Guard against double-processing first: check whether stock movements
   already exist referencing this order (via the reference() relationship).
   If they do, the order's already been stocked out — skip and return
   early. This protects against a payment webhook firing twice or a confirm
   action being double-submitted — but a plain "check, then later insert" is
   itself racy if the check runs before the transaction in step 3 opens: two
   near-simultaneous webhook deliveries could both pass the check before
   either has written anything. Repeat the check again immediately inside
   the outer transaction from step 3 (after locking the order row, e.g.
   Order::lockForUpdate(), if your Order model supports it) right before the
   sell() loop runs, so the second call is guaranteed to see the first
   call's movements before it commits to processing.
2. Before sorting, merge line items by product_id first: if an order has two
   separate line items for the same product (a duplicate SKU added twice, or
   a cart that doesn't merge duplicates), combine their quantities into one
   sell() call for that product rather than calling sell() twice. Two calls
   for the same product in one order would acquire and release the same row
   lock twice for no reason, and would also split that product's history
   into two movement rows referencing the same order instead of one —
   confusing on the history page in Prompt 5 for no benefit. If you want
   reversal to work at individual-line-item granularity rather than
   per-product-per-order, tell me before merging, since merging trades that
   granularity away.
3. Sort the (now-merged) line items by product_id before processing them,
   not by whatever order they were added to the cart in. If two orders share
   two of the same products, processing in a consistent, deterministic order
   across all orders prevents a deadlock where order A locks product 1 then
   waits on product 2, while order B locks product 2 then waits on product 1.
4. Wrap the entire loop — every line item's StockService::sell() call — in
   one outer DB::transaction() so the order's stock-out is all-or-nothing.
   If item 3 of 5 fails, items 1 and 2 must not be left decremented; the
   whole thing rolls back together. (StockService::sell()'s own internal
   transaction nests fine as a savepoint.) Pass the order (or the merged
   line item, if you kept per-line-item granularity per step 2) as the
   $reference argument on every sell() call — this is what the
   double-processing check in step 1 and the reverse-on-cancel logic in
   step 6 actually query against, so without it neither of those can find
   the right movements.
5. Catch InsufficientStockException specifically. Before attempting to
   confirm, do a quick pre-check pass across all (merged) line items and, if
   any is short, block confirmation with a clear error listing which
   products and by how much. Reuse the same merged-and-sorted list from
   steps 2–3 for this pass, and batch the stock read into a single query
   (e.g. one whereIn('id', $productIds) lookup) rather than querying
   current_stock once per line item in a loop — an N+1 query pattern here
   is wasteful even though this pass isn't locking anything. Treat this
   pre-check as a UX nicety only, regardless: the actual guarantee against
   overselling comes from the locked sell() calls inside the transaction in
   step 4, since stock can still change in the gap between the pre-check and
   the transaction actually starting.
6. When an order is cancelled or refunded, call StockService::reverse() for
   each of its related, not-already-reversed stock movements to add the
   stock back. Give this the same two protections as the sell() path above:
   sort the movements by product_id before looping, for the same
   deadlock-avoidance reason as step 3, and wrap the whole loop in one outer
   DB::transaction() so a cancelled order's stock-back-in is all-or-nothing
   rather than partially applied if one reversal fails partway through.

Show me exactly where in the existing code you're hooking this in before
making changes.

One more thing to flag back to me rather than solve silently: if this
project's checkout goes through a payment redirect (KHQR/PayWay, if it's
set up the same way as my other projects), there's a real gap between
"customer starts paying" and "order confirmed," during which someone else
could buy the last unit. Decrementing stock only at confirmation means a
customer can occasionally pay for something that's gone by the time the
payment webhook lands. Don't build a reservation system for this without
asking me — just tell me whether this project has that gap so I can decide
whether it's worth handling now or later.
```

---

## Prompt 4 — Admin controllers & validation

**What it does:** stops bad input (negative quantities, missing notes) from ever reaching the StockService.

```
Now build the admin-facing side.

1. Create a StockController (or add to my existing admin product controller)
   with actions: receiveForm, receive (POST), adjustForm, adjust (POST),
   damagedForm, damaged (POST), history (a product's stock_movements
   timeline, paginated, newest first).
2. Add form request validation classes for each POST action — quantity must
   be a positive integer (except adjust, which takes the counted total and
   can be 0), unit_cost must be a positive number where relevant, note is
   required for damaged/lost.
3. Add routes under my existing admin route group, protected by whatever
   admin middleware I already use.
4. Each controller action should call the matching StockService method —
   don't duplicate the transaction/locking logic in the controller. Catch
   InsufficientStockException where relevant and turn it into a flashed
   validation error, not a 500.

Check my existing admin controllers first to match the pattern I already use
for validation, authorization, and double-submit protection.
```

---

## Prompt 5 — Blade views

**What it does:** the forms and pages staff use day to day.

```
Now build the Blade views, matching my existing admin panel's Tailwind
styling.

1. A "Receive stock" form: product select (searchable if I already have a
   pattern for that), quantity, cost price, optional note. If you also want
   an "update selling price" field on this form, that's a separate concern
   from stock — wire it as its own explicit $product->update(['price' =>
   ...]) call in the controller, not through StockService, and label it
   clearly in the UI so it's obviously changing the price, not the stock.
2. An "Adjust stock" form: product select, counted quantity, note — show the
   current system quantity next to the input so whoever's counting can see
   the difference live (Alpine.js: computed difference as they type).
3. A "Report damaged/lost" form: product select, quantity, required note.
4. A stock history page per product: table of all stock_movements (date,
   type, quantity, unit cost, note, who did it, and — for reversals — a link
   back to the movement it reversed), newest first, paginated.
5. On the product list page, add a stock column with a colored badge —
   normal, or red/amber if current_stock <= low_stock_threshold.

Match my existing Blade component structure and Tailwind classes rather than
introducing new patterns.
```

---

## Prompt 6 — Dashboard widgets

```
Add two widgets to my existing admin dashboard:
1. Total inventory value: sum of current_stock * cost_price across all
   products, treating null cost_price as 0 for this calculation (COALESCE at
   the query level). Also surface a small "N products missing a cost price"
   note so it's clear the number may be an undercount, not silently wrong.
2. Low stock count: number of products where
   current_stock <= low_stock_threshold, linking to a filtered product list.

Match the style of my existing dashboard widgets — check how the other stat
widgets are built first.
```

---

## Prompt 7 — Automated tests (do not skip this one)

**What it does:** proves the concurrency fix from Prompt 2 actually works, instead of trusting that it does.

```
Now write feature tests for the stock system — this is the part I most want
to get right, so be thorough.

1. Test that StockService::receiveStock() increases current_stock and
   creates a movement record with the right type, quantity, and a positive
   unit_cost.
2. Test that StockService::sell() decreases stock, and throws
   InsufficientStockException (never silently fails) when the quantity
   requested exceeds current stock.
3. Test that StockService::adjust() correctly computes and logs the
   difference in both directions (counted higher and counted lower than
   system stock), and returns null with no movement row created when counted
   equals current.
4. Test the reversal flow: sell, then reverse, confirm stock returns to its
   original value and that the reversal's unit_cost is null (since sell()
   leaves it null) — and test that reversing the same movement a second time
   throws instead of double-crediting stock. Also add a concurrency variant
   of this, using the same two-connection approach as item 6 below: fire two
   overlapping reverse() calls at the same movement from two separate
   connections and confirm exactly one succeeds and the other throws,
   proving the lock-then-check ordering fixed in Prompt 2 actually holds
   under concurrency and not just sequentially.
5. Test the trickiest edge case in reverse() directly, since it's easy to
   get the lock ordering subtly wrong here: receive 10 units, sell 8 of
   them (leaving 2), then attempt to reverse the original receiveStock()
   movement. Confirm this throws InsufficientStockException (reversing a
   10-unit receive would try to remove 10 units from a product that only
   has 2 left) rather than silently pushing current_stock negative or
   succeeding incorrectly. This is the one path in reverse() most likely to
   have an off-by-one or wrong-order locking bug, since it's the only case
   where the reversal itself can fail the same way a sell() can.
6. The concurrency test — read this before writing it. Calling sell() twice
   in a row from the same test, on the same PHP process, does NOT test the
   race condition. It runs sequentially either way and would pass whether or
   not the lock exists, which gives false confidence. To actually prove the
   lock works, this needs two genuinely separate, overlapping database
   connections. First, check and tell me whether this project's test
   database driver is PostgreSQL (matching production) or SQLite — SQLite's
   locking behavior doesn't reliably reflect what happens in production, so
   if the test suite defaults to SQLite, that needs addressing before this
   test means anything. Then write a test that opens two real connections,
   holds product row 1's lock open on connection A mid-transaction, attempts
   to acquire it from connection B, and confirms B blocks until A commits or
   rolls back. Separately, run two genuinely concurrent processes (e.g. via
   pcntl_fork if available, or two scripts fired at once against the test
   DB) against a product with exactly 1 unit of stock, and confirm exactly
   one succeeds and the other throws InsufficientStockException — never
   both succeeding, stock never going negative. If this project has no
   established pattern for this kind of test, stop and ask me how I want to
   handle it rather than writing something that looks like a concurrency
   test but doesn't actually exercise the lock.
7. Test that the database check constraint actually rejects a raw negative
   update (expect a QueryException), as a final safety-net check.

Use whatever test framework is already set up in this project (Pest or
PHPUnit) and follow the existing test file structure.
```

---

## Prompt 8 — Backfill existing stock (run before this ships)

**What it does:** stops every already-live, already-selling product from silently showing zero stock the moment this deploys.

```
Before this goes live: my products already exist and are already being
sold, and the new current_stock column will default every one of them to 0.
If I deploy as-is, every product looks out of stock the moment this ships.

Look for any existing quantity data (a legacy stock column, an inventory
spreadsheet, anything) — ask me if you can't find one. Then either:
(a) write a one-off Artisan command that sets current_stock from that
    existing source, logging one opening-balance StockMovement per product
    (type 'in', note "Opening balance — stock system migration") so the
    ledger has a starting point rather than a gap, or
(b) if there's no existing quantity data anywhere, tell me clearly that I
    need to do a manual stock count and enter it through the "Receive
    stock" form before this can go live — don't invent numbers.

Regardless of which path applies: leave cost_price null for any product
where you don't have real cost data, exactly like Prompt 6's dashboard
widget already expects (it COALESCEs null cost_price to 0 and surfaces a
"N products missing a cost price" note). Don't estimate, average, or invent
a cost_price during backfill just to avoid a null — an invented number is
worse than a visible gap, since it would silently corrupt the total
inventory value on the dashboard instead of flagging itself.

Don't leave every product at 0 and call it done.
```

---

## Manual QA checklist (after all 8 prompts, before it goes live)

- [ ] Try selling more than available stock through the actual UI — confirm it's blocked with a clear message, not a generic error
- [ ] Cancel a paid order and confirm the stock number goes back up — then cancel it again and confirm stock does NOT go up a second time
- [ ] Do a stock count adjustment and confirm the history timeline shows the correct difference; confirm a zero-difference count doesn't clutter the history with a no-op row
- [ ] Submit a damaged/lost stock report and confirm it shows in history as its own type, separate from a counting adjustment
- [ ] Check the low-stock badge at the exact threshold, one unit below it, and one unit above it
- [ ] Open two browser tabs and try to buy the last unit of something from both at nearly the same time — only one should succeed, and current_stock should never dip below 0 even momentarily
- [ ] Place a multi-item order containing two products that both have low stock, from two tabs simultaneously, to sanity-check the deadlock-avoidance ordering in Prompt 3
- [ ] Place an order containing the same product twice as separate cart entries, and confirm it results in one merged movement row referencing the order, not two
- [ ] Receive stock, sell most of it, then try to reverse the original receive — confirm it's blocked with InsufficientStockException instead of going negative
- [ ] Confirm existing (pre-launch) products show correct starting stock, not 0, after Prompt 8 runs
- [ ] Confirm products with no historical cost data show up correctly in the "N products missing a cost price" dashboard note rather than a fabricated number
- [ ] If this project uses a payment redirect (KHQR/PayWay), manually test what happens when stock runs out between "payment started" and "payment confirmed"
