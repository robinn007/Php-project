<?php 
$page_title = 'Students Dashboard';
$this->load->view('layout/header', array('page_title' => $page_title)); 
?>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h1>
  <a href="<?php echo site_url('dashboard'); ?>" 
     style="cursor: pointer; color: #333; text-decoration: none; transition: color 0.3s ease; margin-bottom: 20px;" 
     onmouseover="this.style.color='#007bff'; this.style.textDecoration='underline';" 
     onmouseout="this.style.color='#333'; this.style.textDecoration='none';">
     Student Management System
  </a>
</h1>

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
    <div class="stats" style="margin-top:20px;">
        Total Students...: <strong><?php echo count($students); ?></strong>
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
                           class="btn btn-edit" title="Edit Student...">Edit</a>
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
        formData.append('<?php echo $this->security->get_csrf_token_name(); ?>', '<?php echo $this->security->get_csrf_hash(); ?>');

        console.log('Deleting student with ID:', id);
        console.log('Sending to URL:', '<?php echo site_url("students/manage/delete"); ?>');
        
        // Make the AJAX request - send to the delete-specific endpoint
        fetch('<?php echo site_url("students/manage/delete"); ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            console.log('Response status:', response.status);
            
            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            } else {
                return response.text().then(text => {
                    // Try to parse as JSON even if content-type is wrong
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        throw new Error('Expected JSON but got: ' + text);
                    }
                });
            }
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
                                        <p>Get started by <a href="<?php echo site_url('students/manage/add'); ?>">adding the first student</a></p>
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
                const csrfInputs = document.querySelectorAll('input[name="<?php echo $this->security->get_csrf_token_name(); ?>"]');
                csrfInputs.forEach(input => {
                    input.value = data.csrf_token;
                });
                
                // Update meta tag if it exists
                const metaTag = document.querySelector('meta[name="<?php echo $this->security->get_csrf_token_name(); ?>"]');
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

<?php $this->load->view('layout/footer'); ?>