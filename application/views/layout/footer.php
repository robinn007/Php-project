</main>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4>Student Management System</h4>
                    <p>Efficiently manage student records with our comprehensive platform.</p>
                </div>
                
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul class="footer-links">
                        <?php if ($this->session->userdata('user_id')): ?>
                            <li><a href="<?php echo site_url('students'); ?>">Dashboard</a></li>
                            <li><a href="<?php echo site_url('students/manage/add'); ?>">Add Student 6</a></li>
                            <li><a href="<?php echo site_url('students/test_db'); ?>">Database Status</a></li>
                        <?php else: ?>
                            <li><a href="<?php echo site_url('auth/login'); ?>">Login</a></li>
                            <li><a href="<?php echo site_url('auth/signup'); ?>">Sign Up</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Support</h4>
                    <ul class="footer-links">
                        <li><a href="<?php echo site_url('migrate'); ?>">Run Migrations</a></li>
                        <li><a href="<?php echo site_url('students/setup_database'); ?>">Setup Database</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <div class="footer-copyright">
                    <p>&copy; <?php echo date('Y'); ?> Student Management System. Built with CodeIgniter 2.</p>
                </div>
                <div class="footer-status">
                    <?php if ($this->session->userdata('user_id')): ?>
                        <span class="status-indicator online"></span>
                        <span>Connected as <?php echo htmlspecialchars($this->session->userdata('username')); ?></span>
                    <?php else: ?>
                        <span class="status-indicator offline"></span>
                        <span>Not logged in</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </footer>
    
    <style>
        /* Footer Styles */
        .footer {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            margin-top: auto;
        }
        
        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px 20px;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .footer-section h4 {
            color: #ecf0f1;
            margin-bottom: 15px;
            font-size: 18px;
            font-weight: 600;
        }
        
        .footer-section p {
            color: #bdc3c7;
            line-height: 1.6;
            margin-bottom: 10px;
        }
        
        .footer-links {
            list-style: none;
            padding: 0;
        }
        
        .footer-links li {
            margin-bottom: 8px;
        }
        
        .footer-links a {
            color: #bdc3c7;
            text-decoration: none;
            transition: color 0.3s ease;
            font-size: 14px;
        }
        
        .footer-links a:hover {
            color: #3498db;
        }
        
        .footer-bottom {
            border-top: 1px solid #34495e;
            padding-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .footer-copyright p {
            color: #95a5a6;
            font-size: 14px;
            margin: 0;
        }
        
        .footer-status {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #95a5a6;
            font-size: 14px;
        }
        
        .status-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }
        
        .status-indicator.online {
            background-color: #2ecc71;
            box-shadow: 0 0 6px #2ecc71;
        }
        
        .status-indicator.offline {
            background-color: #e74c3c;
        }
        
        /* Mobile responsive */
        @media (max-width: 768px) {
            .footer-content {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .footer-bottom {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
    
    <!-- Common JavaScript for all pages -->
    <script>
        // Auto-hide flash messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const flashMessages = document.querySelectorAll('.success, .error');
            flashMessages.forEach(function(message) {
                setTimeout(function() {
                    message.style.opacity = '0';
                    setTimeout(function() {
                        message.style.display = 'none';
                    }, 300);
                }, 5000);
            });
        });
        
        // Confirm logout
        document.addEventListener('DOMContentLoaded', function() {
            const logoutLinks = document.querySelectorAll('.btn-logout, a[href*="logout"]');
            logoutLinks.forEach(function(link) {
                link.addEventListener('click', function(e) {
                    if (!confirm('Are you sure you want to logout?')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>