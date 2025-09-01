<?php 
$page_title = ($action === 'edit' ? 'Edit' : 'Add') . ' Student';
$this->load->view('layout/header', array('page_title' => $page_title)); 
?>

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
    <div id="loading" class="loading" style="display: none; color: #666; font-style: italic;">Processing...</div>
    
    <?php 
    // Dynamic form action based on the current action
    if ($action === 'edit' && isset($id)) {
        $form_action = 'students/manage/edit/' . $id;
    } else {
        $form_action = 'students/manage/add';
    }
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
                Email Address 17 <span class="required">*</span>
            </label>
            <input type="email" 
                   name="email" 
                   id="email" 
                   value="<?php echo set_value('email', isset($student->email) ? $student->email : ''); ?>" 
                   required
                   placeholder="Enter email address 18"
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
                showMessage('error', 'Please enter a valid email address 19.');
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

<?php $this->load->view('layout/footer'); ?>