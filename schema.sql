PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS Bus_Company (
    id TEXT PRIMARY KEY,
    name TEXT NOT NULL UNIQUE,
    logo_path TEXT,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS User (
    id TEXT PRIMARY KEY,
    full_name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    role TEXT NOT NULL CHECK(role IN ('user','company','admin')),
    password TEXT NOT NULL,
    company_id TEXT,
    balance INTEGER DEFAULT 5000,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES Bus_Company(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS Trips (
    id TEXT PRIMARY KEY,
    company_id TEXT NOT NULL,
    destination_city TEXT NOT NULL,
    arrival_time TEXT NOT NULL,
    departure_time TEXT NOT NULL,
    departure_city TEXT NOT NULL,
    price INTEGER NOT NULL,
    capacity INTEGER NOT NULL,
    created_date TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES Bus_Company(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS Tickets (
    id TEXT PRIMARY KEY,
    trip_id TEXT NOT NULL,
    user_id TEXT NOT NULL,
    status TEXT DEFAULT 'active' CHECK(status IN ('active','canceled','expired')),
    total_price INTEGER NOT NULL,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (trip_id) REFERENCES Trips(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES User(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS Booked_Seats (
    id TEXT PRIMARY KEY,
    ticket_id TEXT NOT NULL,
    seat_number INTEGER NOT NULL,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES Tickets(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS Coupons (
    id TEXT PRIMARY KEY,
    code TEXT NOT NULL UNIQUE,
    discount REAL NOT NULL,
    company_id TEXT,
    usage_limit INTEGER NOT NULL,
    expire_date TEXT NOT NULL,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES Bus_Company(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS User_Coupons (
    id TEXT PRIMARY KEY,
    coupon_id TEXT NOT NULL,
    user_id TEXT NOT NULL,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (coupon_id) REFERENCES Coupons(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES User(id) ON DELETE CASCADE
);
