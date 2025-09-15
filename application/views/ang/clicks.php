<ng-include src="'/partials/header'" ng-init="showBreadcrumb=true; breadcrumbText='Clicks'"></ng-include>
<ng-include src="'/partials/flash-message'"></ng-include>

<!-- Search and Controls -->
<div class="controls-section" ng-show="!isLoading">
    <div class="search-controls">
        <input type="text" ng-model="searchQuery" placeholder="Search clicks..." class="search-input">
        <button ng-click="search()" class="btn btn-primary">Search</button>
        <button ng-click="clearSearch()" class="btn btn-secondary" ng-show="searchQuery">Clear</button>
        <button ng-click="exportClicks()" class="btn btn-success" ng-disabled="isExporting">
            <span ng-show="!isExporting">Export CSV</span>
            <span ng-show="isExporting">Exporting...</span>
        </button>
    </div>
    
    <div class="page-controls">
        <label>
            Show:
            <select ng-model="itemsPerPage" ng-change="changeItemsPerPage()">
                <option value="50">50</option>
                <option value="100">100</option>
                <option value="200">200</option>
                <option value="500">500</option>
            </select>
            per page
        </label>
    </div>
</div>

<!-- Stats -->
<div class="stats" ng-show="totalCount > 0">
    Total Clicks: <strong>{{ totalCount | number }}</strong> 
    <span ng-show="searchQuery"> (filtered)</span>
</div>


<!-- Loading indicator -->
<div class="loading" ng-show="isLoading">
    <p>Loading clicks...</p>
</div>

<!-- Clicks Table -->
<div class="table-container" ng-show="clicks.length && !isLoading">
    <table class="clicks-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>PID</th>
                <th>Link</th>
                <th>Campaign ID</th>
                <th>EIDT</th>
                <th>EID</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody>
            <tr ng-repeat="click in clicks" id="click-{{ click.id }}">
                <td>{{ click.id }}</td>
                <td>{{ click.pid }}</td>
                <td class="link-cell">
                    <div class="link-display">
                        <a ng-href="{{ click.link }}" target="_blank" ng-if="click.link" title="{{ click.link }}">
                            {{ click.link | limitTo:50 }}{{ click.link.length > 50 ? '...' : '' }}
                        </a>
                        <span ng-if="!click.link">N/A</span>
                    </div>
                </td>
                <td>{{ click.campaignId || 'N/A' }}</td>
                <td>{{ click.eidt || 'N/A' }}</td>
                <td>{{ click.eid || 'N/A' }}</td>
                <td>{{ click.timestamp }}</td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<div class="pagination-container" ng-show="totalPages > 1 && !isLoading">
    <div class="pagination-info">
        Page {{ currentPage }} of {{ totalPages }} 
        ({{ clicks.length }} of {{ totalCount | number }} total records)
    </div>
    
    <div class="pagination-controls">
        <button ng-click="prevPage()" ng-disabled="!hasPrev" class="btn btn-pagination">← Previous</button>
        
        <span class="page-numbers">
            <button ng-show="currentPage > 3" ng-click="goToPage(1)" class="btn btn-page">1</button>
            <span ng-show="currentPage > 4">...</span>
            
            <button ng-repeat="page in getPageNumbers()" 
                    ng-click="goToPage(page)" 
                    ng-class="{ 'active': page === currentPage }"
                    class="btn btn-page">
                {{ page }}
            </button>
            
            <span ng-show="currentPage < totalPages - 3">...</span>
            <button ng-show="currentPage < totalPages - 2" ng-click="goToPage(totalPages)" class="btn btn-page">{{ totalPages }}</button>
        </span>
        
        <button ng-click="nextPage()" ng-disabled="!hasNext" class="btn btn-pagination">Next →</button>
    </div>
</div>

<!-- No data message -->
<div class="no-data" ng-show="!clicks.length && !isLoading && totalCount === 0">
    <h3>No clicks found</h3>
    <p ng-show="!searchQuery">No click records available at the moment.</p>
    <p ng-show="searchQuery">No clicks match your search criteria. <button ng-click="clearSearch()" class="btn btn-link">Clear search</button></p>
</div>

<style>
.controls-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 10px;
}

.search-controls {
    display: flex;
    gap: 10px;
    align-items: center;
}

.search-input {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    min-width: 200px;
}

.table-container {
    overflow-x: auto;
    margin-bottom: 20px;
}

.clicks-table {
    width: 100%;
    border-collapse: collapse;
}

.clicks-table th,
.clicks-table td {
    padding: 8px 12px;
    border: 1px solid #ddd;
    text-align: left;
}

.clicks-table th {
    background-color: #f5f5f5;
    font-weight: bold;
}

.link-cell {
    max-width: 350px;
}

.link-display a {
    color: #007bff;
    text-decoration: none;
}

.link-display a:hover {
    text-decoration: underline;
}

/* Row styling for better readability */
.clicks-table tbody tr:nth-child(even) {
    background-color: #f9f9f9;
}

.clicks-table tbody tr:hover {
    background-color: #e8f4f8;
}

/* Better text styling */
.text-muted {
    color: #6c757d;
    font-style: italic;
}

.clicks-table td {
    font-size: 16px;
    line-height: 1.4;
}

/* Improve number columns alignment */
.clicks-table td:nth-child(1), 
.clicks-table td:nth-child(2),
.clicks-table td:nth-child(4),
.clicks-table td:nth-child(5),
.clicks-table td:nth-child(6) {
    text-align: center;
    font-weight: 500;
}

.clicks-table td:nth-child(5){
    width: 150px !important;
}

/* Timestamp column */
.clicks-table td:nth-child(7) {
   font-family: system-ui;
    font-size: 16px;
    white-space: nowrap;
}

.pagination-container {
    margin-top: 30px;
    padding: 20px;
    background-color: #f8f9fa;
    border-radius: 8px;
    text-align: center;
}

.pagination-info {
    margin-bottom: 15px;
    color: #495057;
    font-weight: 500;
}

.pagination-controls {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.btn {
    padding: 8px 16px;
    border: 1px solid #dee2e6;
    background: white;
    cursor: pointer;
    border-radius: 6px;
    text-decoration: none;
    color: #495057;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn:hover:not(:disabled) {
    background-color: #e9ecef;
    border-color: #adb5bd;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
    background-color: #f8f9fa;
}

.btn-primary {
    background-color: #007bff;
    color: white;
    border-color: #007bff;
}

.btn-primary:hover:not(:disabled) {
    background-color: #0056b3;
    border-color: #0056b3;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
    border-color: #6c757d;
}

.btn-secondary:hover:not(:disabled) {
    background-color: #545b62;
    border-color: #545b62;
}

.btn-page.active {
    background-color: #007bff;
    color: white;
    border-color: #007bff;
    font-weight: 600;
}

.btn-link {
    border: none;
    background: none;
    color: #007bff;
    text-decoration: underline;
}

.loading {
    text-align: center;
    padding: 60px 20px;
    color: #6c757d;
    font-size: 16px;
}

.loading::after {
    content: '';
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid #f3f3f3;
    border-top: 3px solid #007bff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-left: 10px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.stats {
    margin-bottom: 20px;
    padding: 15px 20px;
    background: linear-gradient(45deg, black, transparent);
    color: white;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 500;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.no-data {
    text-align: center;
    padding: 60px 20px;
    background-color: #f8f9fa;
    border-radius: 8px;
    border: 2px dashed #dee2e6;
}

.no-data h3 {
    color: #495057;
    margin-bottom: 10px;
}

.no-data p {
    color: #6c757d;
    margin-bottom: 15px;
}

@media (max-width: 768px) {
    .controls-section {
        flex-direction: column;
        align-items: stretch;
    }
    
    .search-controls {
        justify-content: center;
    }
    
    .search-input {
        min-width: auto;
        flex: 1;
    }
    
    .clicks-table {
        min-width: 800px;
    }
    
    .clicks-table th:nth-child(3), .clicks-table td:nth-child(3) {
        min-width: 300px;
    }
}
</style>
</style>