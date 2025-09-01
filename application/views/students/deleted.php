<?php 
$page_title = 'Deleted Students';
$this->load->view('layout/header', array('page_title' => $page_title)); 
?>

<div class="container">
    <div class="breadcrumb">
        <a href="<?php echo site_url('students'); ?>">Students</a>
        <span> / Deleted Students</span>
    </div>
    
    <h1>Deleted Students Archive</h1>
    
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
                    <th>Actions</th>
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
                    <td>
                        <button class="btn btn-edit" onclick="restoreStudent(<?php echo $student->id; ?>)">Restore</button>
                        <button class="btn btn-delete" onclick="permanentDelete(<?php echo $student->id; ?>)">Permanent Delete</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="no-data">
            <h3>No deleted students found</h3>
            <p>All students are currently active. <a href="<?php echo site_url('students'); ?>">View active students</a></p>
        </div>
    <?php endif; ?>
</div>

<script>
function restoreStudent(id) {
    if (confirm('Are you sure you want to restore this student?')) {
        // Implementation for restore functionality
        alert('Restore functionality needs to be implemented in the controller.');
    }
}

function permanentDelete(id) {
    if (confirm('Are you sure you want to permanently delete this student? This action cannot be undone!')) {
        // Implementation for permanent delete functionality
        alert('Permanent delete functionality needs to be implemented in the controller.');
    }
}
</script>

<?php $this->load->view('layout/footer'); ?>