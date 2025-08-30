<!DOCTYPE html>
<html>
<head>
    <title>Students List</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            background-color: #f5f5f5; 
        }
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
            background: white; 
            padding: 20px; 
            border-radius: 8px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
        }
        table { 
            border-collapse: collapse; 
            width: 100%; 
            margin-top: 20px; 
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 12px; 
            text-align: left; 
        }
        th { 
            background-color: #f2f2f2; 
            font-weight: bold; 
        }
        .btn { 
            padding: 8px 16px; 
            text-decoration: none; 
            margin: 2px; 
            border-radius: 4px; 
            display: inline-block; 
            transition: all 0.3s ease; 
            border: none;
            cursor: pointer;
        }
        .btn-add { 
            background-color: #4CAF50; 
            color: white; 
            margin-bottom: 20px; 
        }
        .btn-logout { 
            background-color: #ff9800; 
            color: white; 
            float: right;
        }
        .btn-edit { 
            background-color: #008CBA; 
            color: white; 
        }
        .btn-delete { 
            background-color: #f44336; 
            color: white; 
        }
        .btn:hover { 
            opacity: 0.8; 
            transform: translateY(-1px); 
        }
        .clickable-heading {
            cursor: pointer;
            color: #333;
            text-decoration: none;
            transition: color 0.3s ease;
            margin-bottom: 20px;
        }
        .clickable-heading:hover {
            color: #007cba;
            text-decoration: underline;
        }
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }
        .stats {
            background: #e3f2fd;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            color: #1976d2;
        }
        .error, .success {
            margin: 15px 0;
            padding: 12px;
            border-radius: 4px;
        }
        .error {
            color: #e74c3c;
            background-color: #ffebee;
            border: 1px solid #ffcdd2;
            border-left: 4px solid #e74c3c;
        }
        .success {
            color: #2e7d32;
            background-color: #e8f5e9;
            border: 1px solid #c8e6c9;
            border-left: 4px solid #2e7d32;
        }
        .fade-out {
            animation: fadeOut 0.5s ease-in-out;
            animation-fill-mode: forwards;
        }
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-actions">
            <a href="<?php echo site_url('students'); ?>" class="clickable-heading">
                <h1>Student Management System</h1>
            </a>
            <a href="<?php echo site_url('auth/logout'); ?>" class="btn btn-logout">
                Logout
            </a>
        </div>
        
        <div id="response-message" style="display: none;"></div>
        
        <?php if ($this->session->flashdata('success')): ?>
            <div class="success">
                <?php echo $this->session->flashdata('success'); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($this->session->flashdata('error')): ?>
            <div class="error">
                <?php echo $this->session->flashdata('error'); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($students) && !empty($students)): ?>
            <div class="stats">
                Total Students: <strong><?php echo count($students); ?></strong>
            </div>
        <?php endif; ?>
        
        <a href="<?php echo site_url('students/manage/add'); ?>" class="btn btn-add">
            Add New Student
        </a>
        
        <?php if (isset($students) && !empty($students)): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($students as $student): ?>
                    <tr id="student-<?php echo $student->id; ?>">
                        <td><?php echo htmlspecialchars($student->id); ?></td>
                        <td><?php echo htmlspecialchars($student->name); ?></td>
                        <td><?php echo htmlspecialchars($student->email); ?></td>
                        <td><?php echo htmlspecialchars($student->phone ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($student->address ?? 'N/A'); ?></td>
                        <td>
                            <a href="<?php echo site_url('students/manage/edit/'.$student->id); ?>" 
                               class="btn btn-edit" title="Edit Student">Edit</a>
                            <button class="btn btn-delete js-delete-btn" 
                                    data-id="<?php echo $student->id; ?>" 
                                    title="Delete Student">Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-data">
                <h3>No students found</h3>
                <p>Get started by <a href="<?php echo site_url('students/manage/add'); ?>">adding the first student</a></p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add event listeners to all delete buttons
            const deleteButtons = document.querySelectorAll('.js-delete-btn');
            
            deleteButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    const studentId = this.getAttribute('data-id');
                    deleteStudent(studentId);
                });
            });
            
            // Delete student function
            window.deleteStudent = function(id) {
                if (!confirm('Are you sure you want to delete this student? This action cannot be undone.')) {
                    return;
                }
                
                // Disable the delete button to prevent multiple clicks
                const deleteBtn = document.querySelector('[data-id="' + id + '"]');
                if (deleteBtn) {
                    deleteBtn.disabled = true;
                    deleteBtn.textContent = 'Deleting...';
                }
                
                // Create form data
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);
                formData.append('<?php echo $this->config->item('csrf_token_name'); ?>', '<?php echo $this->security->get_csrf_hash(); ?>');

                console.log('Deleting student with ID:', id);
                console.log('Sending to URL:', '<?php echo site_url("students/manage"); ?>');
                
                // Make the AJAX request
                fetch('<?php echo site_url("students/manage"); ?>', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    console.log('Response headers:', response.headers);
                    
                    // Get response as text first to check for any errors
                    return response.text().then(text => {
                        console.log('Raw response:', text);
                        
                        if (!response.ok) {
                            throw new Error('HTTP ' + response.status + ': ' + text);
                        }
                        
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('JSON parse error:', e);
                            throw new Error('Invalid JSON response: ' + text);
                        }
                    });
                })
                .then(data => {
                    console.log('Parsed response data:', data);
                    
                    const responseMessage = document.getElementById('response-message');
                    responseMessage.classList.remove('error', 'success');
                    responseMessage.style.display = 'none';

                    if (data.success) {
                        // Show success message
                        responseMessage.classList.add('success');
                        responseMessage.innerHTML = '<strong>Success:</strong> ' + data.message;
                        responseMessage.style.display = 'block';
                        
                        // Remove the row with animation
                        const row = document.getElementById('student-' + id);
                        if (row) {
                            row.classList.add('fade-out');
                            setTimeout(() => {
                                row.remove();
                                
                                // Update student count
                                const statsDiv = document.querySelector('.stats strong');
                                if (statsDiv) {
                                    const currentCount = parseInt(statsDiv.textContent) || 0;
                                    const newCount = Math.max(0, currentCount - 1);
                                    statsDiv.textContent = newCount;
                                    
                                    // If no students left, show the no-data message
                                    if (newCount === 0) {
                                        const tableContainer = document.querySelector('table').parentNode;
                                        tableContainer.innerHTML = `
                                            <div class="no-data">
                                                <h3>No students found</h3>
                                                <p>Get started by <a href="<?php echo site_url('students/add'); ?>">adding the first student</a></p>
                                            </div>
                                        `;
                                    }
                                }
                            }, 500);
                        }
                        
                        // Hide success message after 3 seconds
                        setTimeout(() => {
                            responseMessage.style.display = 'none';
                        }, 3000);
                        
                    } else {
                        // Show error message
                        responseMessage.classList.add('error');
                        responseMessage.innerHTML = '<strong>Error:</strong> ' + (data.message || 'Unknown error occurred');
                        responseMessage.style.display = 'block';
                        
                        // Re-enable the delete button
                        if (deleteBtn) {
                            deleteBtn.disabled = false;
                            deleteBtn.textContent = 'Delete';
                        }
                    }
                    
                    // Update CSRF token if provided
                    if (data.csrf_token) {
                        // Update any hidden CSRF inputs
                        const csrfInputs = document.querySelectorAll('input[name="<?php echo $this->config->item('csrf_token_name'); ?>"]');
                        csrfInputs.forEach(input => {
                            input.value = data.csrf_token;
                        });
                        
                        // Update meta tag if it exists
                        const metaTag = document.querySelector('meta[name="<?php echo $this->config->item('csrf_token_name'); ?>"]');
                        if (metaTag) {
                            metaTag.setAttribute('content', data.csrf_token);
                        }
                    }
                })
                .catch(error => {
                    console.error('Delete request failed:', error);
                    
                    const responseMessage = document.getElementById('response-message');
                    responseMessage.classList.remove('error', 'success');
                    responseMessage.classList.add('error');
                    responseMessage.innerHTML = '<strong>Error:</strong> ' + error.message;
                    responseMessage.style.display = 'block';
                    
                    // Re-enable the delete button
                    if (deleteBtn) {
                        deleteBtn.disabled = false;
                        deleteBtn.textContent = 'Delete';
                    }
                });
            };
        });
    </script>
</body>
</html>