# WooCommerce Partial Checkout Plugin

## Installation Guide

### **1. Download and Upload the Plugin**
1. Download the plugin `.zip` file.
2. Log in to your WordPress admin panel.
3. Navigate to **Plugins** → **Add New**.
4. Click **Upload Plugin**, then **Choose File**, and select the `.zip` file.
5. Click **Install Now** and then **Activate** the plugin.

### **2. Manual Installation via FTP**
1. Extract the `.zip` file.
2. Connect to your website via FTP/SFTP.
3. Upload the extracted plugin folder to `wp-content/plugins/`.
4. Log in to your WordPress admin panel.
5. Go to **Plugins** and activate **WooCommerce Partial Checkout**.

---

## How the Logic Works

### **1. Select Items in the Cart**
- Users can **check/uncheck** items in the cart.
- Only **selected items** will proceed to checkout.

### **2. Session-Based Cart Handling**
- The plugin **stores selected items in the session**.
- When navigating back to the cart, the previously selected items **remain checked**.

### **3. Unselected Items Stay in the Cart**
- After checkout, unselected items **remain in the cart**.
- Prevents users from losing cart contents when partially checking out.

---

## Webhooks and Settings

### **Webhook Configuration**
The plugin sends **order data** to a webhook URL after a successful checkout.

#### **Webhook Data Structure:**
```json
{
    "order_id": 12345,
    "total": 250.00,
    "customer_name": "John Doe",
    "customer_email": "johndoe@example.com",
    "payment_status": "completed",
    "items": [
        {
            "product_id": 101,
            "product_name": "Product 1",
            "quantity": 2,
            "price": 50.00
        },
        {
            "product_id": 202,
            "product_name": "Product 2",
            "quantity": 1,
            "price": 150.00
        }
    ]
}
```

### **How to Configure Webhooks**
1. Go to **WooCommerce → Settings → Checkout**.
2. Find the **Partial Checkout Settings** section.
3. Enter the **Webhook URL** where you want to receive order data.
4. Click **Save Changes**.

### **Enable/Disable Partial Checkout**
1. Go to **WooCommerce → Settings → Checkout**.
2. Toggle **Enable Partial Checkout** to **ON** or **OFF**.
3. Click **Save Changes**.


### **Challenges Faced**
1. I used the Woocommerce blocks on the cart page which prevented me to use some certain hooks but I was able to do a work around it.
2. I had some problems with the retaining the unselected items because Woo is clearing the sessions after checkout.