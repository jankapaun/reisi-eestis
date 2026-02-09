<?php
include 'header.php'; 
?>
<body class="container py-4 bg-light">
  <div class="row g-4">

    <!-- Left Column: Cart items and summary -->
    <div class="col-md-6 col-lg-8">
      <div class="card">
        <h4>Your booked trips</h4>
        <div class="table-responsive">
          <table class="table table-striped" id="checkout-cart"></table>
        </div>
      </div>
    </div>

    <!-- Right Column: Customer Info -->
    <div class="col-md-6 col-lg-4">
      <div class="card">
        <h4>Order booked trips</h4>
        <div class="mb-3">
          <label for="first" class="form-label">First name</label>
          <input id="first" type="text" class="form-control" placeholder="First name">
        </div>
        <div class="mb-3">
          <label for="last" class="form-label">Last name</label>
          <input id="last" type="text" class="form-control" placeholder="Last name">
        </div>
        <button id="confirm-order" class="btn btn-success btn-lg mt-3">Confirm order</button>
      </div>
    </div>

  </div>

  <script>
    // Load cart initially
    loadCheckoutCart();
  </script>
</body>
</html>
