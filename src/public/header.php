<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reisi Eestis proovitöö</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <!-- App CSS -->
  <link href="/assets/app.css" rel="stylesheet">

  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <!-- jQuery JS -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

  <!-- App JS -->
  <script src="/assets/app.js"></script>

  <!-- Favicons -->
  <link rel="icon" type="image/png" href="/favicons/favicon-96x96.png" sizes="96x96" />
  <link rel="icon" type="image/svg+xml" href="/favicons/favicon.svg" />
  <link rel="shortcut icon" href="/favicons/favicon.ico" />
  <link rel="apple-touch-icon" sizes="180x180" href="/favicons/apple-touch-icon.png" />
  <link rel="manifest" href="/favicons/site.webmanifest" />
</head>

<body>
  <header class="site-header navbar navbar-expand-md navbar-light bg-light">

    <!-- Nav bar -->
    <div class="container-fluid d-flex justify-content-between align-items-end">

      <a href="/" class="navbar-brand logo-link mb-0">
        <i class="bi bi-bus-front fs-3 me-2"></i>Reisi Eestis
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
        aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="mainNav">
        <nav class="navbar-nav ms-auto">
          <a href="/" class="nav-link <?= ($_SERVER['REQUEST_URI'] == '/' ? 'active' : '') ?>">Trips</a>
          <a href="/checkout.php"
            class="nav-link <?= ($_SERVER['REQUEST_URI'] == '/checkout.php' ? 'active' : '') ?>">Bookings</a>
          <a href="/orders.php"
            class="nav-link <?= ($_SERVER['REQUEST_URI'] == '/orders.php' ? 'active' : '') ?>">Orders</a>
        </nav>
      </div>

    </div>
    <!-- Nav bar -->

    <!-- Toast container -->
    <div aria-live="polite" aria-atomic="true" class="position-relative">
      <div class="toast-container position-fixed top-0 end-0 p-3" id="toast-container">
        <!-- Toasts will be dynamically added here -->
      </div>
    </div>
    <!-- Toast container -->

  </header>
</body>

</html>
