<div class="stats" ng-show="students.length">
  Total: {{ tableTitle }}: <strong>{{ students.length }}</strong>
</div>
<table ng-show="students.length">
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
   <tr ng-repeat="student in students" id="student-{{ student.id }}">
      <td>{{ student.id }}</td>
      <td>{{ student.name }}</td>
      <td>
        <!-- Using the email-link directive -->
        <email-link 
          email="{{ student.email }}" 
          name="{{ student.name }}">
        </email-link>
      </td>
      <td>
        <!--  phone-link directive replaces your original code -->
        <phone-link phone="{{ student.phone }}"></phone-link>
      </td>
      <td>
        <div ng-bind-html="student.address || 'N/A'">{{ student.address }}</div>
      </td>
      <td>
        <a href="/ci/ang/students/edit/{{ student.id }}" class="btn btn-edit" title="Edit Student" ng-if="showEdit">Edit</a>
        <button class="btn btn-delete" ng-click="deleteStudent(student.id)" title="Delete Student" ng-if="showDelete">Delete</button>
        <button class="btn btn-edit" ng-click="restoreStudent(student.id)" ng-if="showRestore">Restore</button>
        <button class="btn btn-delete" ng-click="permanentDelete(student.id)" ng-if="showPermanentDelete">Permanent Delete</button>
      </td>
    </tr>
  </tbody>
</table>
<div class="no-data" ng-show="!students.length && !flashMessage">
  <h3>No {{ tableTitle | lowercase }} found</h3>
  <p ng-if="tableTitle == 'Students'">Get started by <a href="/students/add">adding the first student</a></p>
  <p ng-if="tableTitle == 'Deleted Students'">All students are currently active. <a href="/students">View active students</a></p>
</div>