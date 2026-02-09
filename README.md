# Reisi Eestis - Trip Booking Application

A web-based trip booking application that allows users to browse, filter, and book bus trips across Estonia. The application fetches live pricing data from an external API and manages orders through a MySQL database.

## Features

- **Trip Browsing**: View available bus trips with detailed information
- **Advanced Filtering**: Filter trips by:
  - Departure and arrival cities
  - Price range
  - Distance range
  - Duration range
  - Bus company
- **Shopping Cart**: Add/remove trips to/from cart
- **Order Management**: Checkout and place orders with passenger information
- **Automatic Sync**: Periodic synchronization with external bus schedule API
- **Responsive Design**: Mobile-friendly interface built with Bootstrap 5

## Technology Stack

- **Backend**: PHP 8.2 with Apache
- **Database**: MySQL 8.0
- **Frontend**: HTML5, CSS3, JavaScript (jQuery)
- **UI Framework**: Bootstrap 5
- **Containerization**: Docker & Docker Compose

## Project Structure

```
reisi-eestis/
├── src/
│   ├── api/                # Backend API endpoints
│   │   ├── cart.php        # Shopping cart management
│   │   ├── checkout.php    # Order processing
│   │   ├── orders.php      # Order history retrieval
│   │   ├── schedules.php   # Trip listing
│   │   └── sync.php        # Data synchronization
│   ├── config/
│   │   └── db.php          # Database initialization
│   └── public/             # Web root
│       ├── index.php       # Main page
│       ├── checkout.php    # Checkout page
│       ├── orders.php      # Orders page
│       ├── header.php      # Shared header template
│       └── assets/
│           ├── app.js      # Frontend logic
│           └── app.css     # Styling
├── docker/
│   ├── php/
│   │   ├── Dockerfile      # PHP/Apache configuration
│   │   └── api.conf        # Apache API routing
│   └── mysql/
│       └── init.sql        # Database schema
├── docker-compose.yml      # Orchestration config
└── .env.dist               # Template file for .env
```

## Prerequisites

- Docker & Docker Compose installed on your system

## Installation & Setup

### 1. Clone the Repository

```bash
git clone <repository-url>
cd reisi-eestis
```

### 2. Configure Environment Variables

The application uses environment variables stored in a `.env` file. A template file `.env.dist` is provided.

#### Windows (PowerShell)

```powershell
# Copy the template to create the actual .env file
Copy-Item .env.dist .env

# Edit .env with your editor (replace env variables as needed, e.g., API credentials)
notepad .env
```

#### Linux / macOS (Bash)

```bash
# Copy the template to create the actual .env file
cp .env.dist .env

# Edit .env with your editor (replace env variables as needed, e.g., API credentials)
nano .env
# or use your preferred editor: vim, code, etc.
```

**Important**: The `.env` file contains sensitive credentials and should **never be committed** to version control. It is listed in `.gitignore`.

### Environment Variables Reference

- `DB_HOST` - MySQL service name (default: `mysql` for Docker)
- `DB_NAME` - Database name (default: `reisid`)
- `DB_USER` - Database user (default: `root`)
- `DB_PASS` - Database password (default: `root`)
- `DB_CHARSET` - Character set (default: `utf8mb4`)
- `API_URL` - External API endpoint for bus schedules
- `API_USER` - API username (required for authentication)
- `API_PASS` - API password or MD5 hash (required for authentication)
- `TZ` - Timezone (default: `Europe/Tallinn`)

### 3. Start the Application

```bash
docker-compose up --build
```

This will:
- Build the PHP/Apache container
- Start MySQL service with database schema
- Mount source code as volumes for development
- Expose services on configured ports

### 4. Access the Application

- **Main Application**: http://localhost:8080
- **Database**: localhost:3306 (MySQL)
  - Username: (from `DB_USER` in `.env`, default: `root`)
  - Password: (from `DB_PASS` in `.env`, default: `root`)
  - Database: (from `DB_NAME` in `.env`, default: `reisid`)

## How to Use

### Browsing Trips

1. Visit http://localhost:8080
2. View all available trips in the right panel
3. Trip data is automatically synced from the API on page load (if expired)

### Filtering Trips

Use the left-side filter panel to refine results:
- Select departure city (**From**)
- Select destination city (**To**)
- Set minimum/maximum price range
- Set minimum/maximum distance
- Set minimum/maximum trip duration
- Select specific bus company

Filters are applied in real-time as you select options.

### Adding Trips to Cart

1. Find a desired trip in the results table
2. Click the "Book" button
3. Trip is added to your shopping cart

### Viewing Cart

1. Click the "Bookings" link in the navigation bar
2. View all items in your cart with total price
3. Remove items if necessary
4. Proceed to checkout

### Placing an Order

1. Navigate to "Bookings" page
2. Enter your first name and last name
3. Review booked trips
4. Click "Confirm order"

### Viewing Orders

1. Click the "Orders" link in the navigation bar
2. View your order history with all placed orders and details

## API Endpoints

All API endpoints are located under `/api/`:

### GET `/api/schedules.php`

Returns all available schedules for the current price list.

**Response**: JSON array of schedule objects

### GET `/api/cart.php`

Returns items in the current user's shopping cart.

**Query Parameters**:
- `action=list` - Get cart items (default)

### POST `/api/cart.php`

**Query Parameters**:
- `action=add` - Add item to cart
  - POST data: `schedule_id` (required)
  
- `action=remove` - Remove item from cart
  - POST data: `schedule_id` (required)

### POST `/api/checkout.php`

Process an order.

**POST Data**:
- `first_name` (required)
- `last_name` (required)

**Response**: Success message with order ID or error

### GET `/api/orders.php`

Retrieve all orders.

**Response**: JSON array of order objects

### GET `/api/sync.php`

Synchronize trip schedules from external API.

- Runs automatically on page load (async)
- Only syncs if price list has expired
- No manual intervention needed

## Database Schema

### price_lists
Stores API price list metadata and schedule data

- `id` - Primary key
- `api_id` - External API identifier
- `schedule_data_json` - Full API response JSON
- `valid_until` - Expiration timestamp
- `created_at` - Creation timestamp

### schedules
Individual trip schedules

- `id` - UUID from API
- `route_id` - Route identifier
- `from_city` - Departure city
- `to_city` - Destination city
- `distance` - Trip distance (km)
- `price` - Trip price (EUR)
- `start_time` - Departure time
- `end_time` - Arrival time
- `company_id` - Bus company identifier
- `company_name` - Bus company name
- `price_list_id` - Reference to price_lists

### orders
Customer orders

- `order_item_id` - Primary key
- `order_id` - UUID for the order
- `price_list_api_id` - Reference to price list
- `first_name` - Customer first name
- `last_name` - Customer last name
- `from_city` - Departure city
- `to_city` - Destination city
- `start_time` - Trip departure time
- `end_time` - Trip arrival time
- `price` - Trip price at time of booking
- `company_name` - Bus company name
- `created_at` - Order timestamp

## Development

### Local Development with Docker

All source code in `src/` is mounted as a volume, so changes take effect immediately without container restart.

1. Edit files in `src/` folder
2. Refresh browser to see changes
3. Check browser console and docker logs for errors

### Debugging Database

Connect to MySQL container:

```bash
docker exec -it <container-id> mysql -u root -p reisid
```

### View Logs

```bash
docker-compose logs -f php      # PHP/Apache logs
docker-compose logs -f mysql    # MySQL logs
```

### Stop Services

```bash
docker-compose down              # Stop and remove containers
docker-compose down -v           # Stop, remove containers and volumes
```

## Known Issues & TODOs

### TODO List

- **Implement Cron Job for Price List Sync** [src/public/assets/app.js:437]
  - Currently, price list synchronization happens on every page load (asynchronous)
  - Recommendation: Set up a cron job to trigger sync at regular intervals instead
  - Benefit: Reduces unnecessary API calls and improves page load performance
  - Status: Not yet implemented

## API Configuration

The external API for bus schedules is configured in [src/api/sync.php](src/api/sync.php):

- **Endpoint and Authentication**: Basic HTTP Auth (credentials and endpoint url are store in .env file)
- **Sync triggering**: Triggered on every page load
- **Data Sync Condition**: Only syncs when current price list has expired

## Troubleshooting

### Port Already in Use

If ports 8080 or 3306 are in use, modify `docker-compose.yml`:

```yaml
ports:
  - "8081:80"        # Change 8080 to preferred port
```

### Database Connection Failed

Ensure MySQL is running:

```bash
docker-compose ps      # Check if mysql service is running
docker-compose logs mysql   # View MySQL logs
```

### Cart Not Persisting

The application uses PHP sessions. Ensure cookies are enabled in your browser.

### API Sync Issues

Check that:
1. Internet connection is available
2. API endpoint is accessible
3. Valid credentials are configured in sync.php
4. Review logs: `docker-compose logs php`

## License

This is a demonstration/test project.

## Contact

e-mail: jan@cidox.ee
