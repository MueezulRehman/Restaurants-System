POS Short-Payment Behaviour
===========================

This document describes the "short payment" feature added to the POS:

- Purpose: allow cashiers to accept small underpayments without creating
  a customer debt, or record the shortfall as debt depending on manager
  configuration.

How it works
------------

- Global defaults are defined in `config/pos.php`:
  - `allow_short_payment_without_debt` (boolean)
  - `short_payment_threshold` (integer, rupees)
- Each `Restaurant` may override these via two new columns:
  - `pos_allow_short_payment_without_debt` (boolean)
  - `pos_short_payment_threshold` (integer)

Manager UI
----------

- The restaurant edit/create pages include POS settings to toggle the
  behaviour and set the threshold (Rs.). Managers can change this per
  restaurant in the admin area.

POS Flow
--------

- When a POS checkout results in a shortfall (total &gt; amount received):
  - If short payments are allowed and the shortfall &lt;= threshold, the
    cashier is shown a confirmation modal to either accept the payment
    (do not add debt) or charge the shortfall to the customer's account.
    If the cashier confirms acceptance, the checkout completes and no
    customer debt is created.
  - Otherwise, the shortfall is automatically recorded as customer debt
    and the checkout requires a customer to be selected.

Implementation notes
--------------------

- Backend: `PosController::checkout()` reads the restaurant-aware POS
  config and enforces behaviour; it accepts a request flag
  `accept_short_payment_without_debt` when the cashier confirms.
- Frontend: the POS page shows a short-payment modal and sets the
  `accept_short_payment_without_debt` hidden input when the cashier
  confirms acceptance.
