# === WC Products Edits Alert ===
This plugin will send a notification if any WooCommerce product edited. So that the website admin can follow the changes that are made to the products by shop managers.
<br>
Te notification will be sent to the website admin email.<br>

Notification will contain the price difference and any other changes as a JSON.

### Example
```html
================================
Product #32784 edited.
User : petadmin
Old price : 70
New price : 56
Old sale price : 60
New sale price : 55
************
Changes Json :
{"regular_price":"56","sale_price":"55","stock_quantity":null}
================================
```
