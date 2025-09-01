<?php 
$page_title = 'Sign Up';
$this->load->view('layout/header', array('page_title' => $page_title)); 
?>

<div class="container">
    <div class="breadcrumb">
        <a href="<?php echo site_url('/'); ?>">Home</a> 
        <span> / Sign Up</span>
    </div>
    
    <h1>Create New Account</h1>
    
    <?php if ($this->session->flashdata('success')): ?>
        <div class="success">
            <strong>Success:</strong> <?php echo htmlspecialchars($this->session->flashdata('success')); ?>
        </div>
    <?php endif; ?>
    
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
    
    <?php echo form_open('auth/signup', array('novalidate' => 'novalidate')); ?>
        <input type="hidden" name="<?php echo $this->config->item('csrf_token_name'); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
        
        <div class="form-group">
            <label for="username">
                Username <span class="required">*</span>
            </label>
            <input type="text" 
                   name="username" 
                   id="username" 
                   value="<?php echo set_value('username'); ?>" 
                   required
                   placeholder="Enter username"
                   maxlength="50">
        </div>
        
        <div class="form-group">
            <label for="email">
                Email Address 9 <span class="required">*</span>
            </label>
            <input type="email" 
                   name="email" 
                   id="email" 
                   value="<?php echo set_value('email'); ?>" 
                   required
                   placeholder="Enter email address 10"
                   maxlength="100">
        </div>
        
        <div class="form-group">
            <label for="password">
                Password <span class="required">*</span>
            </label>
            <input type="password" 
                   name="password" 
                   id="password" 
                   required
                   placeholder="Enter password (minimum 6 characters)"
                   minlength="6">
        </div>
        
        <div class="form-group">
            <label for="confirm_password">
                Confirm Password <span class="required">*</span>
            </label>
            <input type="password" 
                   name="confirm_password" 
                   id="confirm_password" 
                   required
                   placeholder="Confirm password"
                   minlength="6">
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-submit">
                Sign Up
            </button>
            <a href="<?php echo site_url('auth/login'); ?>" class="btn btn-cancel">
                Cancel
            </a>
        </div>
    <?php echo form_close(); ?>
    
    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #666; font-size: 12px;">
        <p>Already have an account? <a href="<?php echo site_url('auth/login'); ?>">Log in here</a></p>
        <p><em>Fields marked with <span class="required">*</span> are required.</em></p>
    </div>
</div>

<script>
    document.querySelector('form').addEventListener('submit', function(e) {
        const username = document.getElementById('username').value.trim();
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        if (!username || !email || !password || !confirmPassword) {
            e.preventDefault();
            alert('Please fill in all required fields.');
            return false;
        }
        
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            e.preventDefault();
            alert('Please enter a valid email address 11.');
            return false;
        }
        
        if (password.length < 6) {
            e.preventDefault();
            alert('Password must be at least 6 characters long.');
            return false;
        }
        
        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Passwords do not match.');
            return false;
        }
    });
</script>

<?php $this->load->view('layout/footer'); ?>