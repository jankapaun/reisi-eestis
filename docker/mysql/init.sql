-- ===========================
-- Table: price_lists
-- Stores snapshots of price lists fetched from the external API.
-- Each record represents a unique API price list with its validity period.
-- ===========================
CREATE TABLE price_lists (
  id INT AUTO_INCREMENT PRIMARY KEY,        -- Internal unique ID
  api_id VARCHAR(36),                       -- Unique ID from the external API
  schedule_data_json TEXT,                  -- Raw JSON data of schedules from API
  valid_until DATETIME,                     -- Expiration datetime of this price list
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP  -- Timestamp when record was created
);

-- ===========================
-- Table: schedules
-- Stores individual trips or schedules for routes.
-- Linked to a specific price list via price_list_id.
-- ===========================
CREATE TABLE schedules (
  id VARCHAR(36),                           -- Unique schedule ID (from API)
  route_id VARCHAR(36),                      -- Route identifier (from API)
  from_city VARCHAR(100),                    -- Departure city
  to_city VARCHAR(100),                      -- Arrival city
  distance INT,                              -- Distance in kilometers
  price DECIMAL(10,2),                       -- Price of the trip
  start_time DATETIME,                        -- Scheduled start datetime
  end_time DATETIME,                          -- Scheduled end datetime
  company_id VARCHAR(36),                     -- Company identifier
  company_name VARCHAR(100),                  -- Name of the company
  price_list_id INT                           -- Foreign key to price_lists.id
);

-- ===========================
-- Table: orders
-- Stores customer bookings based on schedules.
-- Each order item references a specific trip and price list.
-- ===========================
CREATE TABLE orders (
  order_item_id INT AUTO_INCREMENT PRIMARY KEY,  -- Internal unique ID for the order item
  order_id VARCHAR(36),                           -- UUID representing the entire order
  price_list_api_id VARCHAR(36),                  -- API price list used for this order
  first_name VARCHAR(100),                        -- Customer first name
  last_name VARCHAR(100),                         -- Customer last name
  from_city VARCHAR(100),                         -- Departure city for this booking
  to_city VARCHAR(100),                           -- Arrival city
  start_time DATETIME,                             -- Trip start time
  end_time DATETIME,                               -- Trip end time
  price DECIMAL(10,2),                             -- Price paid for this trip
  company_name VARCHAR(100),                       -- Name of the company providing the trip
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP    -- Timestamp when the order was created
);
