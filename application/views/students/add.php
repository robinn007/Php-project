<!DOCTYPE html>
<html>
<head>
    <title>Add Student - Student Management System</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            background-color: #f5f5f5; 
        }
        .container { 
            max-width: 600px; 
            margin: 0 auto; 
            background: white; 
            padding: 30px; 
            border-radius: 8px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
        }
        .breadcrumb {
            margin-bottom: 20px;
            color: #666;
            font-size: 14px;
        }
        .breadcrumb a {
            color: #007cba;
            text-decoration: none;
        }
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 10px;
        }
        .form-group { 
            margin: 20px 0; 
        }
        label { 
            display: block; 
            margin-bottom: 5px; 
            font-weight: bold;
            color: #333;
        }
        .required {
            color: #e74c3c;
        }
        input[type="text"], input[type="email"], textarea { 
            width: 100%; 
            max-width: 400px;
            padding: 12px; 
            border: 1px solid #ddd; 
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        input[type="text"]:focus, input[type="email"]:focus, textarea:focus {
            border-color: #4CAF50;
            outline: none;
            box-shadow: 0 0 5px rgba(76, 175, 80, 0.3);
        }
        .btn { 
            padding: 12px 24px; 
            margin: 5px; 
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
            display: inline-block;
        }
        .btn-submit { 
            background-color: #4CAF50; 
            color: white; 
        }
        .btn-submit:hover {
            background-color: #45a049;
            transform: translateY(-1px);
        }
        .btn-cancel { 
            background-color: #f44336; 
            color: white; 
        }
        .btn-cancel:hover {
            background-color: #da190b;
            transform: translateY(-1px);
        }
        .error { 
            color: #e74c3c; 
            margin: 15px 0; 
            padding: 12px;
            background-color: #ffebee;
            border: 1px solid #ffcdd2;
            border-radius: 4px;
            border-left: 4px solid #e74c3c;
        }
        .form-actions {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="breadcrumb">
            <a href="<?php echo site_url('/'); ?>">Home</a> 
            <span> / </span>
            <a href="<?php echo site_url('students'); ?>">Students</a>
            <span> / Add New Student</span>
        </div>
        
        <h1>Add New Student here .......</h1>
        
        <?php if (isset($error)): ?>
            <div class="error">
                <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if (validation_errors()): ?>
            <div class="error">
                <strong>Please fix the following errors:</strong>
                <?php echo validation_errors(); ?>
            </div>
        <?php endif; ?>
        
        <?php echo form_open('students/create', array('novalidate' => 'novalidate')); ?>
            <div class="form-group">
                <label for="name">
                    Full Name <span class="required">*</span>
                </label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       value="<?php echo set_value('name'); ?>" 
                       required
                       placeholder="Enter student's full name"
                       maxlength="100">
            </div>
            
            <div class="form-group">
                <label for="email">
                    Email Address <span class="required">*</span>
                </label>
                <input type="email" 
                       name="email" 
                       id="email" 
                       value="<?php echo set_value('email'); ?>" 
                       required
                       placeholder="Enter email address"
                       maxlength="100">
            </div>
            
            <div class="form-group">
                <label for="phone">
                    Phone Number
                </label>
                <input type="text" 
                       name="phone" 
                       id="phone" 
                       value="<?php echo set_value('phone'); ?>"
                       placeholder="Enter phone number (optional)"
                       maxlength="20">
            </div>
            
            <div class="form-group">
                <label for="address">
                    Address
                </label>
                <textarea name="address" 
                          id="address" 
                          placeholder="Enter address (optional)"><?php echo set_value('address'); ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-submit">
                    Add Student
                </button>
                <a href="<?php echo site_url('students'); ?>" class="btn btn-cancel">
                     Cancel
                </a>
            </div>
        <?php echo form_close(); ?>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #666; font-size: 12px;">
            <p><em>Fields marked with <span class="required">*</span> are required.</em></p>
        </div>
    </div>
    
    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            
            if (!name || !email) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return false;
            }
            
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Please enter a valid email address.');
                return false;
            }
        });
    </script>
</body>
</html>