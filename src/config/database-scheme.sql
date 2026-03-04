
CREATE DATABASE IF NOT EXISTS CoMove;
USE CoMove;

-- =========================================
-- ADMIN
-- =========================================
CREATE TABLE ADMIN (
    admin_id CHAR(8) PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- =========================================
-- RIDER
-- =========================================
CREATE TABLE RIDER (
    rider_id CHAR(8) PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone_number VARCHAR(20),
    profile_photo MEDIUMBLOB,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    rider_status VARCHAR(20) NOT NULL DEFAULT 'active',
    CHECK (rider_status IN ('active', 'banned'))
);

-- =========================================
-- DRIVER
-- =========================================
CREATE TABLE DRIVER (
    driver_id CHAR(8) PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    profile_photo MEDIUMBLOB,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    approved_by CHAR(8),
    driver_status VARCHAR(20) NOT NULL DEFAULT 'pending',
    nric_number CHAR(12) NOT NULL UNIQUE,
    nric_front_image MEDIUMBLOB NOT NULL,
    nric_back_image MEDIUMBLOB NOT NULL,
    lisence_front_image MEDIUMBLOB NOT NULL,
    lisence_back_image MEDIUMBLOB NOT NULL,
    lisence_expiry_date DATE NOT NULL,
    vehicle_model VARCHAR(20),
    plate_number CHAR(10) NOT NULL UNIQUE,
    color VARCHAR(20),
    CHECK (driver_status IN ('pending','active','rejected','banned')),
    FOREIGN KEY (approved_by) REFERENCES ADMIN(admin_id)
);

-- =========================================
-- TRIP
-- =========================================
CREATE TABLE TRIP (
    trip_id CHAR(8) PRIMARY KEY,
    driver_id CHAR(8) NOT NULL,
    start_location VARCHAR(100) NOT NULL,
    end_location VARCHAR(100) NOT NULL,
    departure_time DATETIME NOT NULL,
    available_seats INT NOT NULL DEFAULT 0,
    estimated_duration INT,
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    trip_status VARCHAR(20) NOT NULL DEFAULT 'scheduled',
    CHECK (trip_status IN ('scheduled','ongoing','completed','cancelled')),
    FOREIGN KEY (driver_id) REFERENCES DRIVER(driver_id)
);

-- =========================================
-- RIDE REQUEST
-- =========================================
CREATE TABLE RIDE_REQUEST (
    request_id CHAR(8) PRIMARY KEY,
    trip_id CHAR(8) NOT NULL,
    rider_id CHAR(8) NOT NULL,
    seats_requested INT NOT NULL,
    request_status VARCHAR(20) NOT NULL DEFAULT 'pending',
    requested_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    amount_paid DECIMAL(10,2),
    payment_method VARCHAR(20),
    proof_of_payment MEDIUMBLOB,
    CHECK (request_status IN ('pending','approved','rejected')),
    FOREIGN KEY (trip_id) REFERENCES TRIP(trip_id),
    FOREIGN KEY (rider_id) REFERENCES RIDER(rider_id)
);

-- =========================================
-- RATING
-- =========================================
CREATE TABLE RATING (
    rating_id CHAR(8) PRIMARY KEY,
    trip_id CHAR(8) NOT NULL,
    driver_id CHAR(8) NOT NULL,
    rider_id CHAR(8) NOT NULL,
    rating_score INT NOT NULL,
    comment VARCHAR(255),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CHECK (rating_score >= 1 AND rating_score <= 5),
    FOREIGN KEY (trip_id) REFERENCES TRIP(trip_id),
    FOREIGN KEY (driver_id) REFERENCES DRIVER(driver_id),
    FOREIGN KEY (rider_id) REFERENCES RIDER(rider_id)
);

-- =========================================
-- REWARD
-- =========================================
CREATE TABLE REWARD (
    reward_id CHAR(8) PRIMARY KEY,
    reward_name VARCHAR(50) NOT NULL,
    points_required INT NOT NULL,
    category VARCHAR(50) NOT NULL,
    stock INT NOT NULL DEFAULT 0
);

-- =========================================
-- RIDER REDEMPTION
-- =========================================
CREATE TABLE RIDER_REDEMPTION (
    redemption_id CHAR(8) PRIMARY KEY,
    rider_id CHAR(8) NOT NULL,
    reward_id CHAR(8) NOT NULL,
    redeemed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rider_id) REFERENCES RIDER(rider_id),
    FOREIGN KEY (reward_id) REFERENCES REWARD(reward_id)
);

-- =========================================
-- DRIVER REDEMPTION
-- =========================================
CREATE TABLE DRIVER_REDEMPTION (
    redemption_id CHAR(8) PRIMARY KEY,
    driver_id CHAR(8) NOT NULL,
    reward_id CHAR(8) NOT NULL,
    redeemed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (driver_id) REFERENCES DRIVER(driver_id),
    FOREIGN KEY (reward_id) REFERENCES REWARD(reward_id)
);

-- =========================================
-- RIDER GREEN POINT LOG
-- =========================================
CREATE TABLE RIDER_GREEN_POINT_LOG (
    log_id CHAR(8) PRIMARY KEY,
    rider_id CHAR(8) NOT NULL,
    points_earned INT NOT NULL DEFAULT 0,
    source VARCHAR(50) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rider_id) REFERENCES RIDER(rider_id)
);

-- =========================================
-- DRIVER GREEN POINT LOG
-- =========================================
CREATE TABLE DRIVER_GREEN_POINT_LOG (
    log_id CHAR(8) PRIMARY KEY,
    driver_id CHAR(8) NOT NULL,
    points_earned INT NOT NULL DEFAULT 0,
    source VARCHAR(50) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (driver_id) REFERENCES DRIVER(driver_id)
);

-- =========================================
-- GREEN POINT MULTIPLIER
-- =========================================
CREATE TABLE GREEN_POINT_MULTIPLIER (
    multiplier_id CHAR(8) PRIMARY KEY,
    multiplier_value FLOAT NOT NULL DEFAULT 1,
    set_by CHAR(8),
    set_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (set_by) REFERENCES ADMIN(admin_id)
);

-- =========================================
-- RIDER SOCIAL LINK
-- =========================================
CREATE TABLE RIDER_SOCIAL_LINK (
    link_id CHAR(8) PRIMARY KEY,
    rider_id CHAR(8) NOT NULL,
    title VARCHAR(50) NOT NULL,
    link_url VARCHAR(255) NOT NULL,
    FOREIGN KEY (rider_id) REFERENCES RIDER(rider_id)
);

-- =========================================
-- RIDER FRIEND
-- =========================================
CREATE TABLE RIDER_FRIEND (
    friend_id CHAR(8) PRIMARY KEY,
    rider_id CHAR(8) NOT NULL,
    friend_rider_id CHAR(8) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    CHECK (status IN ('pending','accepted','rejected')),
    FOREIGN KEY (rider_id) REFERENCES RIDER(rider_id),
    FOREIGN KEY (friend_rider_id) REFERENCES RIDER(rider_id)
);

-- =========================================
-- TRIP SHARE
-- =========================================
CREATE TABLE TRIP_SHARE (
    share_id CHAR(8) PRIMARY KEY,
    trip_id CHAR(8) NOT NULL,
    rider_id CHAR(8) NOT NULL,
    visibility VARCHAR(20) NOT NULL DEFAULT 'private',
    shared_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CHECK (visibility IN ('private','friends')),
    FOREIGN KEY (trip_id) REFERENCES TRIP(trip_id),
    FOREIGN KEY (rider_id) REFERENCES RIDER(rider_id)
);

-- =========================================
-- OTP
-- =========================================
CREATE TABLE OTP (
    otp_id CHAR(8) PRIMARY KEY,
    email_address VARCHAR(100) NOT NULL,
    otp_code VARCHAR(10) NOT NULL,
    is_used BOOLEAN NOT NULL DEFAULT FALSE,
    expires_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);