<!DOCTYPE html>
<html>
<head>
    <title><?php echo ($action === 'edit' ? 'Edit' : 'Add'); ?> Student - Student Management System</title>
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
        textarea {
            min-height: 80px;
            resize: vertical;
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
        .btn-submit:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
            transform: none;
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
        .success { 
            color: #2e7d32; 
            margin: 15px 0; 
            padding: 12px;
            background-color: #e8f5e9;
            border: 1px solid #c8e6c9;
            border-radius: 4px;
            border-left: 4px solid #2e7d32;
        }
        .form-actions {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .loading {
            display: none;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="breadcrumb">
            <a href="<?php echo site_url('students'); ?>">Students</a>
            <span> / <?php echo ($action === 'edit' ? 'Edit' : 'Add'); ?> Student</span>
        </div>
        
        <h1><?php echo ($action === 'edit' ? 'Edit' : 'Add'); ?> Student</h1>
        
        <?php if (isset($error)): ?>
            <div class="error">
                <strong>Error:</strong> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <div id="response-message" style="display: none;"></div>
        <div id="loading" class="loading">Processing...</div>
        
        <?php 
        $form_action = ($action === 'edit') ? 'students/edit/' . (isset($id) ? $id : '') : 'students/add';
        echo form_open($form_action, array('id' => 'student-form', 'novalidate' => 'novalidate')); 
        ?>
            <div class="form-group">
                <label for="name">
                    Full Name <span class="required">*</span>
                </label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       value="<?php echo set_value('name', isset($student->name) ? $student->name : ''); ?>" 
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
                       value="<?php echo set_value('email', isset($student->email) ? $student->email : ''); ?>" 
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
                       value="<?php echo set_value('phone', isset($student->phone) ? $student->phone : ''); ?>"
                       placeholder="Enter phone number (optional)"
                       maxlength="20">
            </div>
            
            <div class="form-group">
                <label for="address">
                    Address
                </label>
                <textarea name="address" 
                          id="address" 
                          placeholder="Enter address (optional)"><?php echo set_value('address', isset($student->address) ? $student->address : ''); ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-submit" id="submit-btn">
                    <?php echo ($action === 'edit' ? 'Update' : 'Add'); ?> Student
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
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('student-form');
            const submitBtn = document.getElementById('submit-btn');
            const loading = document.getElementById('loading');
            const responseMessage = document.getElementById('response-message');
            
            form.addEventListener('submit', function(e) {
                // Basic client-side validation
                const name = document.getElementById('name').value.trim();
                const email = document.getElementById('email').value.trim();
                
                if (!name || !email) {
                    e.preventDefault();
                    showMessage('error', 'Please fill in all required fields.');
                    return false;
                }
                
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    e.preventDefault();
                    showMessage('error', 'Please enter a valid email address.');
                    return false;
                }
                
                // Show loading state
                submitBtn.disabled = true;
                submitBtn.textContent = 'Processing...';
                loading.style.display = 'block';
                hideMessage();
            });
            
            function showMessage(type, message) {
                responseMessage.className = type;
                responseMessage.innerHTML = '<strong>' + (type === 'error' ? 'Error:' : 'Success:') + '</strong> ' + message;
                responseMessage.style.display = 'block';
                
                // Scroll to message
                responseMessage.scrollIntoView({ behavior: 'smooth' });
            }
            
            function hideMessage() {
                responseMessage.style.display = 'none';
            }
        });
    </script>
</body>
</html>