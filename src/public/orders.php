<?php
include 'header.php'; 
?>

<body class="container py-4 bg-light">
  <div class="row g-4">

    <!-- Orders Table -->
    <div class="col-md-12">
      <div class="card">
        <h4>Orders</h4>
        <div class="table-responsive">
          <table class="table" id="orders-list"></table>
        </div>
      </div>
    </div>

  </div>

  <script>
    // Load orders on page load
    loadOrders();
  </script>
</body>
</html>
