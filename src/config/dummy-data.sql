-- Dummy data for COMOVE (aligned with database-scheme.md)
-- Safe re-run: deletes only the IDs below, then re-inserts.
USE COMOVE;

-- Load default profile image (src/public-assets/images/profile-icon.png)
SET @profile_photo = LOAD_FILE('C:/xampp/htdocs/RWDD Assignment/src/public-assets/images/profile-icon.png');
-- Load ID/license images (front used for both sides)
SET @nric_front = LOAD_FILE('C:/xampp/htdocs/RWDD Assignment/src/public-assets/images/ic-front.jpg');
SET @license_front = LOAD_FILE('C:/xampp/htdocs/RWDD Assignment/src/public-assets/images/license-front.png');


-- Config tables
INSERT INTO GREEN_POINT_CONFIG (multiplier_value, driver_base_point, rider_base_point, min_price)
VALUES (1, 10, 5, 5.00);

INSERT INTO SYSTEM_CONFIG (driver_registration, rider_registration, system_maintenance)
VALUES (TRUE, TRUE, FALSE);

-- Admin
INSERT INTO ADMIN (admin_id, name, password, created_at) VALUES
(1, 'System Admin', 'Admin@123', '2026-03-01 09:00:00');

-- Drivers (1-3)
INSERT INTO DRIVER (
    driver_id, name, email, password, phone_number, profile_photo, created_at, approved_by, driver_status,
    nric_number, nric_front_image, nric_back_image, license_front_image, license_back_image, license_expiry_date,
    vehicle_model, plate_number, color
) VALUES
(1, 'Aisyah Rahman', 'aisyah.rahman@mail.apu.edu.my', 'Driver@123', '012-1111222', @profile_photo, '2026-03-02 10:00:00', 1, 'active',
 '900101-10-1234', @nric_front, @nric_front, @license_front, @license_front, '2028-12-31', 'Toyota Vios', 'WXY1234', 'Silver'),
(2, 'Daniel Lim', 'daniel.lim@mail.apu.edu.my', 'Driver@123', '012-3333444', @profile_photo, '2026-03-02 11:00:00', 1, 'active',
 '900202-10-5678', @nric_front, @nric_front, @license_front, @license_front, '2029-06-30', 'Perodua Alza', 'BHG9876', 'White'),
(3, 'TP082975', 'tp082975@mail.apu.edu.my', 'Driver@123', '012-5555666', @profile_photo, '2026-03-02 11:30:00', 1, 'active',
 '900303-10-9999', @nric_front, @nric_front, @license_front, @license_front, '2029-12-31', 'Honda City', 'VAX7788', 'Blue');

-- Riders
INSERT INTO RIDER (
    rider_id, name, email, password, phone_number, profile_photo, created_at, rider_status
) VALUES
(1, 'Alicia Tan', 'alicia.tan@mail.apu.edu.my', 'Rider@123', '016-2222333', @profile_photo, '2026-03-03 09:15:00', 'active'),
(2, 'Marcus Lim', 'marcus.lim@mail.apu.edu.my', 'Rider@123', '016-4444555', @profile_photo, '2026-03-03 09:20:00', 'active'),
(3, 'Nur Aina', 'nur.aina@mail.apu.edu.my', 'Rider@123', '016-6666777', @profile_photo, '2026-03-03 09:25:00', 'active'),
(4, 'Jason Lee', 'jason.lee@mail.apu.edu.my', 'Rider@123', '016-8888999', @profile_photo, '2026-03-03 09:30:00', 'active'),
(5, 'TP082975', 'tp082975r@mail.apu.edu.my', 'Rider@123', '016-1234567', @profile_photo, '2026-03-03 09:35:00', 'active');

-- Trips (more trips per driver, APU-centric locations)
INSERT INTO TRIP (
    trip_id, driver_id, start_location, end_location, departure_time, total_seats, estimated_duration, total_amount, gained_point, trip_status
) VALUES
(1, 1, 'APU Campus (Technology Park Malaysia)', 'LRT Bukit Jalil', '2026-03-20 08:30:00', 4, 30, 40.00, 5, 'scheduled'),
(2, 1, 'APU Residence', 'Sunway Pyramid', '2026-03-21 18:15:00', 6, 45, 90.00, 8, 'scheduled'),
(3, 1, 'APU Campus', 'KL Sentral', '2026-03-22 09:00:00', 4, 50, 60.00, 8, 'scheduled'),
(4, 2, 'TPM Gate', 'Pavilion Bukit Jalil', '2026-03-20 07:45:00', 4, 20, 24.00, 3, 'scheduled'),
(5, 2, 'APU Campus', 'IOI City Mall', '2026-03-23 17:30:00', 5, 35, 55.00, 6, 'scheduled'),
(6, 2, 'APU Residence', 'Mid Valley Megamall', '2026-03-25 10:30:00', 4, 40, 48.00, 6, 'scheduled'),
(7, 3, 'APU Campus', 'Subang Jaya KTM', '2026-03-24 08:00:00', 4, 35, 36.00, 5, 'scheduled'),
(8, 3, 'APU Residence', 'KLIA Transit (Putrajaya)', '2026-03-26 14:00:00', 6, 60, 120.00, 10, 'scheduled');

-- Ride Requests (interconnected)
INSERT INTO RIDE_REQUEST (
    request_id, trip_id, rider_id, seats_requested, request_status, requested_at, amount_paid, payment_method, proof_of_payment, gained_point
) VALUES
(1, 1, 1, 1, 'approved', '2026-03-18 20:00:00', 10.00, 'Cash', NULL, 1),
(2, 1, 2, 1, 'approved', '2026-03-18 21:00:00', 10.00, 'Card', NULL, 1),
(3, 2, 3, 2, 'approved', '2026-03-19 19:00:00', 30.00, 'Card', NULL, 2),
(4, 2, 4, 1, 'pending',  '2026-03-19 19:10:00', 15.00, 'Cash', NULL, 0),
(5, 3, 1, 1, 'approved', '2026-03-19 19:20:00', 15.00, 'Card', NULL, 1),
(6, 4, 5, 1, 'approved', '2026-03-18 18:10:00', 6.00, 'Cash', NULL, 1),
(7, 5, 2, 1, 'approved', '2026-03-20 08:05:00', 11.00, 'Card', NULL, 1),
(8, 6, 3, 1, 'approved', '2026-03-21 09:00:00', 12.00, 'Cash', NULL, 1),
(9, 7, 4, 2, 'approved', '2026-03-21 09:10:00', 18.00, 'Card', NULL, 2),
(10, 8, 1, 2, 'approved', '2026-03-22 12:10:00', 40.00, 'Card', NULL, 3);

-- Ratings (optional, none of the trips are completed yet)
INSERT INTO RATING (
    rating_id, trip_id, rider_id, rating_score, comment, created_at
) VALUES
(1, 1, 1, 5, 'Smooth ride!', '2026-03-20 12:00:00'),
(2, 1, 2, 4, 'Nice driver', '2026-03-20 12:05:00');

-- Rewards
INSERT INTO REWARD (reward_id, reward_pic, reward_name, points_required, category, stock) VALUES
(1, NULL, 'Coffee Voucher', 20, 'Food', 50),
(2, NULL, 'Parking Coupon', 15, 'Transport', 30);

-- Redemptions
INSERT INTO RIDER_REDEMPTION (redemption_id, rider_id, reward_id, redeemed_at) VALUES
(1, 1, 1, '2026-03-10 12:30:00');

INSERT INTO DRIVER_REDEMPTION (redemption_id, driver_id, reward_id, redeemed_at) VALUES
(1, 1, 2, '2026-03-10 12:45:00');

-- Green point logs
INSERT INTO RIDER_GREEN_POINT_LOG (log_id, rider_id, points_change, source, created_at) VALUES
(1, 1, 5, 'Trip 1', '2026-03-20 12:05:00'),
(2, 2, 3, 'Trip 1', '2026-03-20 12:05:00');

INSERT INTO DRIVER_GREEN_POINT_LOG (log_id, driver_id, points_change, source, created_at) VALUES
(1, 1, 10, 'Trip 1', '2026-03-20 12:05:00'),
(2, 2, 6, 'Trip 4', '2026-03-20 08:30:00');

-- Social links and friends
INSERT INTO RIDER_SOCIAL_LINK (link_id, rider_id, title, link_url) VALUES
(1, 1, 'LinkedIn', 'https://www.linkedin.com/'),
(2, 2, 'GitHub', 'https://github.com/');

INSERT INTO RIDER_FRIEND (friend_id, rider_id, friend_rider_id, status) VALUES
(1, 1, 2, 'accepted');

-- Trip share
INSERT INTO TRIP_SHARE (share_id, trip_id, rider_id, visibility, shared_at) VALUES
(1, 1, 1, 'friends', '2026-03-19 08:00:00');

-- OTP (example)
INSERT INTO OTP (otp_id, email_address, otp_code, is_used, expires_at, created_at) VALUES
(1, 'alicia.tan@mail.apu.edu.my', '123456', FALSE, '2026-03-31 23:59:59', '2026-03-19 10:00:00');

