<?php
// admin_settings.php
session_start();
include "db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: sign_in.php");
    exit();
}

// Handle settings update
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // You can add settings to a database table or use a config file
    $message = "Settings updated successfully!";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Settings - Flower 'n Go</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --bg:#0A0A0A; --card:#1A1A1A; --accent:#D4AF37; --text:#FFF; }
        body { background:var(--bg); color:var(--text); font-family:Arial; padding:20px; }
        .container { max-width:800px; margin:0 auto; }
        .back-btn { display:inline-block; background:#333; color:#FFF; padding:10px 20px; text-decoration:none; border-radius:8px; margin-bottom:20px; }
        .setting-card { background:#1A1A1A; padding:20px; border-radius:10px; margin-bottom:20px; }
        .setting-group { margin-bottom:15px; }
        label { display:block; margin-bottom:5px; color:#AAA; }
        input, select, textarea { width:100%; padding:10px; background:#111; border:1px solid #333; border-radius:5px; color:#FFF; }
        .btn-save { background:var(--accent); color:#000; padding:10px 30px; border:none; border-radius:5px; cursor:pointer; font-weight:bold; }
    </style>
</head>
<body>
    <div class="container">
        <a href="admin_dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        
        <h1><i class="fas fa-cog"></i> Admin Settings</h1>
        
        <?php if(isset($message)): ?>
            <div style="background:#10B98120; color:#10B981; padding:10px; border-radius:5px; margin-bottom:20px;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="setting-card">
                <h3>Store Information</h3>
                <div class="setting-group">
                    <label>Store Name</label>
                    <input type="text" name="store_name" value="Flower 'n Go" required>
                </div>
                <div class="setting-group">
                    <label>Contact Email</label>
                    <input type="email" name="store_email" value="contact@flowerngo.com" required>
                </div>
                <div class="setting-group">
                    <label>Contact Phone</label>
                    <input type="text" name="store_phone" value="+63 912 345 6789">
                </div>
            </div>
            
            <div class="setting-card">
                <h3>Order Settings</h3>
                <div class="setting-group">
                    <label>Default Order Status</label>
                    <select name="default_status">
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                <div class="setting-group">
                    <label>Send Order Emails</label>
                    <select name="send_emails">
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>
            </div>
            
            <button type="submit" class="btn-save">
                <i class="fas fa-save"></i> Save Settings
            </button>
        </form>
    </div>
</body>
</html>