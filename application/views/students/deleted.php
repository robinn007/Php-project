<!DOCTYPE html>
<html>
<head>
    <title>Deleted Students List</title>
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
        }
        .btn-add { 
            background-color: #4CAF50; 
            color: white; 
            margin-bottom: 20px; 
        }
        .btn-logout {
            background-color: #607d8b;
            color: white;
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
    </style>
</head>
<body>
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <a href="<?php echo site_url('dashboard'); ?>" class="clickable-heading">
                <h1>Deleted Students</h1>
            </a>
            <a href="<?php echo site_url('auth/logout'); ?>" class="btn btn-logout">
                Logout
            </a>
        </div>
        
        <?php if (isset($students) && !empty($students)): ?>
            <div class="stats">
                Total Deleted Students: <strong><?php echo count($students); ?></strong>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($students as $student): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($student->id); ?></td>
                        <td><?php echo htmlspecialchars($student->name); ?></td>
                        <td><?php echo htmlspecialchars($student->email); ?></td>
                        <td><?php echo htmlspecialchars($student->phone ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($student->address ?? 'N/A'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-data">
                <h3>No deleted students found</h3>
                <p><a href="<?php echo site_url('students'); ?>">Back to Students</a></p>
            </div>
        <?php endif; ?>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #666; font-size: 12px;">
            <p>Quick Links: 
                <a href="<?php echo site_url('/'); ?>">Home</a> | 
                <a href="<?php echo site_url('students'); ?>">Active Students</a> | 
                <a href="<?php echo site_url('students/test_db'); ?>">Test Database</a> | 
                <a href="<?php echo site_url('migrate'); ?>">Run Migrations</a>
            </p>
        </div>
    </div>
</body>
</html>