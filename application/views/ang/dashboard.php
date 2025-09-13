<div class="dashboard-container">
    <h2 class="dashboard-title">{{title}}</h2>
    <ng-include src="'/partials/flash-message'"></ng-include>

    <div class="welcome-section">
        <h3 class="welcome-message">Welcome, {{currentUser}}!</h3>
    </div>

    <div class="overview-actions">
        <div class="overview-section">
            <h4 class="section-title">Overview</h4>
            <p class="overview-item"><strong>Total Active Students:</strong> {{totalStudents}}</p>
            <p class="overview-item"><strong>Total Deleted Students:</strong> {{totalDeletedStudents}}</p>
        </div>
    </div>

    <div class="recent-students-section">
        <h4 class="section-title">Recently Added Students</h4>
        <table ng-if="recentStudents.length > 0" class="students-table">
            <thead>
                <tr class="table-header">
                    <th class="table-cell">Name</th>
                    <th class="table-cell">Email</th>
                    <th class="table-cell">Phone</th>
                    <th class="table-cell">Address</th>
                    <th class="table-cell">State</th>
                    <th class="table-cell">Created At</th>
                    <th class="table-cell">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr ng-repeat="student in recentStudents" class="table-row">
                    <td class="table-cell">{{student.name}}</td>
                    <td class="table-cell">{{student.email}}</td>
                    <td class="table-cell">{{student.phone}}</td>
                    <td class="table-cell">
                        <div>{{ student.address | addressFilter:'short' || 'N/A' }}</div>
                    </td>
                    <td class="table-cell">{{student.state || 'N/A'}}</td>
                    <td class="table-cell">{{student.created_at}}</td>
                    <td class="table-cell">
                        <button class="btn btn-primary" ng-click="goToEditStudent(student.id)">Edit</button>
                    </td>
                </tr>
            </tbody>
        </table>
        <p ng-if="recentStudents.length === 0" class="no-students-message">No recent students found.</p>
    </div>
</div>