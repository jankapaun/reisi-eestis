<?php
include 'header.php'; 
?>

<body class="container py-4 bg-light">

  <div class="row g-4">

    <!-- Left Column: Filters -->
    <div class="col-md-6 col-lg-4">
      <div class="card filter-card">
        <h4>Filter trips</h4>

        <div class="row g-2">

          <div class="col-6">
            <label for="from" class="form-label">From</label>
            <select id="from" class="form-select">
              <option value="">Select</option>
            </select>
          </div>

          <div class="col-6">
            <label for="to" class="form-label">To</label>
            <select id="to" class="form-select">
              <option value="">Select</option>
            </select>
          </div>

          <div class="col-6">
            <label for="minPrice" class="form-label">Min price (€)</label>
            <input type="number" id="minPrice" class="form-control">
          </div>

          <div class="col-6">
            <label for="maxPrice" class="form-label">Max price (€)</label>
            <input type="number" id="maxPrice" class="form-control">
          </div>

          <div class="col-6">
            <label for="minDistance" class="form-label">Min distance (km)</label>
            <input type="number" id="minDistance" class="form-control">
          </div>

          <div class="col-6">
            <label for="maxDistance" class="form-label">Max distance (km)</label>
            <input type="number" id="maxDistance" class="form-control">
          </div>

          <div class="col-6">
            <label for="minDuration" class="form-label">Min duration (min)</label>
            <input type="number" id="minDuration" class="form-control">
          </div>

          <div class="col-6">
            <label for="maxDuration" class="form-label">Max duration (min)</label>
            <input type="number" id="maxDuration" class="form-control">
          </div>

          <div class="col-12">
            <label for="company" class="form-label">Company</label>
            <select id="company" class="form-select">
              <option value="">Select</option>
            </select>
          </div>

        </div>
      </div>
    </div>

    <!-- Left Column: Filtered trips -->
    <div class="col-md-6 col-lg-8">
      <div class="card trip-card">
        <h4>Available trips</h4>
        <div class="table-responsive">
          <table class="table table-striped mb-0" id="results"></table>
        </div>
      </div>
    </div>

  </div>

</body>
</html>
