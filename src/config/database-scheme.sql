-- ============================================
-- Database: Ride-Sharing System
-- ============================================
CREATE DATABASE COMOVE;
USE COMOVE;
-- Drop tables if they exist (to avoid conflicts)
DROP TABLE IF EXISTS OTP, TRIP_SHARE, RIDER_FRIEND, RIDER_SOCIAL_LINK, SYSTEM_CONFIG, GREEN_POINT_CONFIG, DRIVER_REDEMPTION, RIDER_REDEMPTION, DRIVER_GREEN_POINT_LOG, RIDER_GREEN_POINT_LOG, REWARD, RATING, RIDE_REQUEST, TRIP, DRIVER, RIDER, ADMIN;

-- =====================
-- Admin Table
-- =====================
CREATE TABLE ADMIN (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    name NVARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL
);

-- =====================
-- Rider Table
-- =====================
CREATE TABLE RIDER (
    rider_id INT AUTO_INCREMENT PRIMARY KEY,
    name NVARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone_number VARCHAR(20),
    profile_photo MEDIUMBLOB,
    created_at DATETIME NOT NULL,
    rider_status VARCHAR(20) NOT NULL DEFAULT 'active' CHECK (rider_status IN ('active','banned'))
    -- green_points is derived, so we don't store it directly
);

-- =====================
-- Driver Table
-- =====================
CREATE TABLE DRIVER (
    driver_id INT AUTO_INCREMENT PRIMARY KEY,
    name NVARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    profile_photo MEDIUMBLOB,
    created_at DATETIME NOT NULL,
    approved_by INT,
    driver_status VARCHAR(20) NOT NULL DEFAULT 'pending' CHECK (driver_status IN ('pending','active','rejected','banned')),
    nric_number CHAR(12) NOT NULL UNIQUE,
    nric_front_image MEDIUMBLOB NOT NULL,
    nric_back_image MEDIUMBLOB NOT NULL,
    license_front_image MEDIUMBLOB NOT NULL,
    license_back_image MEDIUMBLOB NOT NULL,
    license_expiry_date DATE NOT NULL,
    vehicle_model VARCHAR(20),
    plate_number CHAR(10) NOT NULL UNIQUE,
    color VARCHAR(20),
    FOREIGN KEY (approved_by) REFERENCES ADMIN(admin_id)
    -- green_points & driver_rating are derived
);

-- =====================
-- Trip Table
-- =====================
CREATE TABLE TRIP (
    trip_id INT AUTO_INCREMENT PRIMARY KEY,
    driver_id INT NOT NULL,
    start_location NVARCHAR(100) NOT NULL,
    end_location NVARCHAR(100) NOT NULL,
    departure_time DATETIME NOT NULL,
    total_seats INT NOT NULL DEFAULT 0,
    estimated_duration INT,
    total_amount FLOAT NOT NULL DEFAULT 0,
    gained_point INT,
    trip_status VARCHAR(20) NOT NULL DEFAULT 'scheduled' CHECK (trip_status IN ('scheduled','ongoing','completed')),
    FOREIGN KEY (driver_id) REFERENCES DRIVER(driver_id)
);

-- =====================
-- Ride Request Table
-- =====================
CREATE TABLE RIDE_REQUEST (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    trip_id INT NOT NULL,
    rider_id INT NOT NULL,
    seats_requested INT NOT NULL,
    request_status VARCHAR(20) NOT NULL DEFAULT 'pending' CHECK (request_status IN ('pending','approved','rejected')),
    requested_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    amount_paid FLOAT,
    payment_method VARCHAR(20),
    proof_of_payment MEDIUMBLOB,
    gained_point INT,
    FOREIGN KEY (trip_id) REFERENCES TRIP(trip_id),
    FOREIGN KEY (rider_id) REFERENCES RIDER(rider_id)
);

-- =====================
-- Rating Table
-- =====================
CREATE TABLE RATING (
    rating_id INT AUTO_INCREMENT PRIMARY KEY,
    trip_id INT NOT NULL,
    rider_id INT NOT NULL,
    rating_score INT NOT NULL CHECK (rating_score BETWEEN 1 AND 5),
    comment NVARCHAR(255),
    created_at DATETIME NOT NULL,
    FOREIGN KEY (trip_id) REFERENCES TRIP(trip_id),
    FOREIGN KEY (rider_id) REFERENCES RIDER(rider_id)
);

-- =====================
-- Reward Table
-- =====================
CREATE TABLE REWARD (
    reward_id INT AUTO_INCREMENT PRIMARY KEY,
    reward_pic MEDIUMBLOB,
    reward_name NVARCHAR(50) NOT NULL,
    points_required INT NOT NULL,
    category VARCHAR(50) NOT NULL,
    stock INT NOT NULL DEFAULT 0
);

-- =====================
-- Rider Redemption
-- =====================
CREATE TABLE RIDER_REDEMPTION (
    redemption_id INT AUTO_INCREMENT PRIMARY KEY,
    rider_id INT NOT NULL,
    reward_id INT NOT NULL,
    redeemed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rider_id) REFERENCES RIDER(rider_id),
    FOREIGN KEY (reward_id) REFERENCES REWARD(reward_id)
);

-- =====================
-- Driver Redemption
-- =====================
CREATE TABLE DRIVER_REDEMPTION (
    redemption_id INT AUTO_INCREMENT PRIMARY KEY,
    driver_id INT NOT NULL,
    reward_id INT NOT NULL,
    redeemed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (driver_id) REFERENCES DRIVER(driver_id),
    FOREIGN KEY (reward_id) REFERENCES REWARD(reward_id)
);

-- =====================
-- Rider Green Point Log
-- =====================
CREATE TABLE RIDER_GREEN_POINT_LOG (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    rider_id INT NOT NULL,
    points_change INT NOT NULL DEFAULT 0,
    source VARCHAR(50) NOT NULL,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (rider_id) REFERENCES RIDER(rider_id)
);

-- =====================
-- Driver Green Point Log
-- =====================
CREATE TABLE DRIVER_GREEN_POINT_LOG (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    driver_id INT NOT NULL,
    points_change INT NOT NULL DEFAULT 0,
    source VARCHAR(50) NOT NULL,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (driver_id) REFERENCES DRIVER(driver_id)
);

-- =====================
-- Green Point Config
-- =====================
CREATE TABLE GREEN_POINT_CONFIG (
    multiplier_value FLOAT NOT NULL DEFAULT 1,
    driver_base_point INT,
    rider_base_point INT,
    min_price FLOAT
);

-- =====================
-- System Config
-- =====================
CREATE TABLE SYSTEM_CONFIG (
    driver_registration BOOLEAN COMMENT 'TRUE = registration open, FALSE = closed',
    rider_registration BOOLEAN COMMENT 'TRUE = registration open, FALSE = closed',
    system_maintenance BOOLEAN COMMENT 'TRUE = maintenance on, FALSE = normal operation'
);

-- =====================
-- Rider Social Link
-- =====================
CREATE TABLE RIDER_SOCIAL_LINK (
    link_id INT AUTO_INCREMENT PRIMARY KEY,
    rider_id INT NOT NULL,
    title NVARCHAR(50) NOT NULL,
    link_url VARCHAR(255) NOT NULL,
    FOREIGN KEY (rider_id) REFERENCES RIDER(rider_id)
);

-- =====================
-- Rider Friend
-- =====================
CREATE TABLE RIDER_FRIEND (
    friend_id INT AUTO_INCREMENT PRIMARY KEY,
    rider_id INT NOT NULL,
    friend_rider_id INT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending' CHECK (status IN ('pending','accepted','rejected')),
    FOREIGN KEY (rider_id) REFERENCES RIDER(rider_id),
    FOREIGN KEY (friend_rider_id) REFERENCES RIDER(rider_id)
);

-- =====================
-- Trip Share
-- =====================
CREATE TABLE TRIP_SHARE (
    share_id INT AUTO_INCREMENT PRIMARY KEY,
    trip_id INT NOT NULL,
    rider_id INT NOT NULL,
    visibility VARCHAR(20) NOT NULL DEFAULT 'private' CHECK (visibility IN ('private','friends')),
    shared_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (trip_id) REFERENCES TRIP(trip_id),
    FOREIGN KEY (rider_id) REFERENCES RIDER(rider_id)
);

-- =====================
-- OTP Table
-- =====================
CREATE TABLE OTP (
    otp_id INT AUTO_INCREMENT PRIMARY KEY,
    email_address VARCHAR(100) NOT NULL,
    otp_code VARCHAR(10) NOT NULL,
    is_used BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'FALSE = not used, TRUE = used',
    expires_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL
);
