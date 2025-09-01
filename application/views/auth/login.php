<?php 
$page_title = 'Login';
$this->load->view('layout/header', array('page_title' => $page_title)); 
?>

<div class="container">
    <div class="breadcrumb">
        <a href="<?php echo site_url('/'); ?>">Home</a> 
        <span> / Login</span>
    </div>
    
    <h1>Login to Your Account</h1>
    
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
    
    <?php if ($this->session->flashdata('success')): ?>
        <div class="success">
            <strong>Success:</strong> <?php echo htmlspecialchars($this->session->flashdata('success')); ?>
        </div>
    <?php endif; ?>
    
    <?php echo form_open('auth/login', array('novalidate' => 'novalidate')); ?>
        <div class="form-group">
            <label for="email">
                Email Address 6 <span class="required">*</span>
            </label>
            <input type="email" 
                   name="email" 
                   id="email" 
                   value="<?php echo set_value('email'); ?>" 
                   required
                   placeholder="Enter email address 7"
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
                   placeholder="Enter password">
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-submit">
                Login
            </button>
            <a href="<?php echo site_url('/'); ?>" class="btn btn-cancel">
                Cancel
            </a>
        </div>
    <?php echo form_close(); ?>
    
    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #666; font-size: 12px;">
        <p>Don't have an account? <a href="<?php echo site_url('auth/signup'); ?>">Sign up here</a></p>
        <p><em>Fields marked with <span class="required">*</span> are required.</em></p>
    </div>
</div>

<script>
    document.querySelector('form').addEventListener('submit', function(e) {
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;
        
        if (!email || !password) {
            e.preventDefault();
            alert('Please fill in all required fields.');
            return false;
        }
        
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            e.preventDefault();
            alert('Please enter a valid email address 8.');
            return false;
        }
    });
</script>

<?php $this->load->view('layout/footer'); ?>