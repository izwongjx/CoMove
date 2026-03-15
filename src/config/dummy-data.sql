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
('A00001', 'System Admin', 'Admin@123', '2026-03-01 09:00:00');

-- Drivers (1-3)
INSERT INTO DRIVER (
    driver_id, name, email, password, phone_number, profile_photo, created_at, approved_by, driver_status,
    nric_number, nric_front_image, nric_back_image, license_front_image, license_back_image, license_expiry_date,
    vehicle_model, plate_number, color
) VALUES
('D00001', 'Aisyah Rahman', 'aisyah.rahman@mail.apu.edu.my', 'Driver@123', '012-1111222', @profile_photo, '2026-03-02 10:00:00', 'A00001', 'active',
 '900101-10-1234', @nric_front, @nric_front, @license_front, @license_front, '2028-12-31', 'Toyota Vios', 'WXY1234', 'Silver'),
('D00002', 'Daniel Lim', 'daniel.lim@mail.apu.edu.my', 'Driver@123', '012-3333444', @profile_photo, '2026-03-02 11:00:00', 'A00001', 'active',
 '900202-10-5678', @nric_front, @nric_front, @license_front, @license_front, '2029-06-30', 'Perodua Alza', 'BHG9876', 'White'),
('D00003', 'TP082975', 'tp082975@mail.apu.edu.my', 'Driver@123', '012-5555666', @profile_photo, '2026-03-02 11:30:00', 'A00001', 'active',
 '900303-10-9999', @nric_front, @nric_front, @license_front, @license_front, '2029-12-31', 'Honda City', 'VAX7788', 'Blue');

-- Riders
INSERT INTO RIDER (
    rider_id, name, email, password, phone_number, profile_photo, created_at, rider_status
) VALUES
('R00001', 'Alicia Tan', 'alicia.tan@mail.apu.edu.my', 'Rider@123', '016-2222333', @profile_photo, '2026-03-03 09:15:00', 'active'),
('R00002', 'Marcus Lim', 'marcus.lim@mail.apu.edu.my', 'Rider@123', '016-4444555', @profile_photo, '2026-03-03 09:20:00', 'active'),
('R00003', 'Nur Aina', 'nur.aina@mail.apu.edu.my', 'Rider@123', '016-6666777', @profile_photo, '2026-03-03 09:25:00', 'active'),
('R00004', 'Jason Lee', 'jason.lee@mail.apu.edu.my', 'Rider@123', '016-8888999', @profile_photo, '2026-03-03 09:30:00', 'active'),
('R00005', 'TP082975', 'tp082975r@mail.apu.edu.my', 'Rider@123', '016-1234567', @profile_photo, '2026-03-03 09:35:00', 'active');

-- Trips (more trips per driver, APU-centric locations)
INSERT INTO TRIP (
    trip_id, driver_id, start_location, end_location, departure_time, total_seats, estimated_duration, total_amount, gained_point, trip_status
) VALUES
('T00001', 'D00001', 'APU Campus (Technology Park Malaysia)', 'LRT Bukit Jalil', '2026-03-20 08:30:00', 4, 30, 40.00, 5, 'scheduled'),
('T00002', 'D00001', 'APU Residence', 'Sunway Pyramid', '2026-03-21 18:15:00', 6, 45, 90.00, 8, 'scheduled'),
('T00003', 'D00001', 'APU Campus', 'KL Sentral', '2026-03-22 09:00:00', 4, 50, 60.00, 8, 'scheduled'),
('T00004', 'D00002', 'TPM Gate', 'Pavilion Bukit Jalil', '2026-03-20 07:45:00', 4, 20, 24.00, 3, 'scheduled'),
('T00005', 'D00002', 'APU Campus', 'IOI City Mall', '2026-03-23 17:30:00', 5, 35, 55.00, 6, 'scheduled'),
('T00006', 'D00002', 'APU Residence', 'Mid Valley Megamall', '2026-03-25 10:30:00', 4, 40, 48.00, 6, 'scheduled'),
('T00007', 'D00003', 'APU Campus', 'Subang Jaya KTM', '2026-03-24 08:00:00', 4, 35, 36.00, 5, 'scheduled'),
('T00008', 'D00003', 'APU Residence', 'KLIA Transit (Putrajaya)', '2026-03-26 14:00:00', 6, 60, 120.00, 10, 'scheduled');

-- Ride Requests (interconnected)
INSERT INTO RIDE_REQUEST (
    request_id, trip_id, rider_id, seats_requested, request_status, requested_at, amount_paid, payment_method, proof_of_payment, gained_point
) VALUES
('RE00001', 'T00001', 'R00001', 1, 'approved', '2026-03-18 20:00:00', 10.00, 'Cash', NULL, 1),
('RE00002', 'T00001', 'R00002', 1, 'approved', '2026-03-18 21:00:00', 10.00, 'Card', NULL, 1),
('RE00003', 'T00002', 'R00003', 2, 'approved', '2026-03-19 19:00:00', 30.00, 'Card', NULL, 2),
('RE00004', 'T00002', 'R00004', 1, 'pending',  '2026-03-19 19:10:00', 15.00, 'Cash', NULL, 0),
('RE00005', 'T00003', 'R00001', 1, 'approved', '2026-03-19 19:20:00', 15.00, 'Card', NULL, 1),
('RE00006', 'T00004', 'R00005', 1, 'approved', '2026-03-18 18:10:00', 6.00, 'Cash', NULL, 1),
('RE00007', 'T00005', 'R00002', 1, 'approved', '2026-03-20 08:05:00', 11.00, 'Card', NULL, 1),
('RE00008', 'T00006', 'R00003', 1, 'approved', '2026-03-21 09:00:00', 12.00, 'Cash', NULL, 1),
('RE00009', 'T00007', 'R00004', 2, 'approved', '2026-03-21 09:10:00', 18.00, 'Card', NULL, 2),
('RE00010', 'T00008', 'R00001', 2, 'approved', '2026-03-22 12:10:00', 40.00, 'Card', NULL, 3);

-- Ratings (optional, none of the trips are completed yet)
INSERT INTO RATING (
    rating_id, trip_id, rider_id, rating_score, comment, created_at
) VALUES
('RA00001', 'T00001', 'R00001', 5, 'Smooth ride!', '2026-03-20 12:00:00'),
('RA00002', 'T00001', 'R00002', 4, 'Nice driver', '2026-03-20 12:05:00');

-- Rewards
INSERT INTO REWARD (reward_id, reward_pic, reward_name, points_required, category, stock) VALUES
('REW00001', NULL, 'Coffee Voucher', 20, 'Food', 50),
('REW00002', NULL, 'Parking Coupon', 15, 'Transport', 30);

-- Redemptions
INSERT INTO RIDER_REDEMPTION (redemption_id, rider_id, reward_id, redeemed_at) VALUES
('REDR00001', 'R00001', 'REW00001', '2026-03-10 12:30:00');

INSERT INTO DRIVER_REDEMPTION (redemption_id, driver_id, reward_id, redeemed_at) VALUES
('REDD00001', 'D00001', 'REW00002', '2026-03-10 12:45:00');

-- Green point logs
INSERT INTO RIDER_GREEN_POINT_LOG (log_id, rider_id, points_change, source, created_at) VALUES
('LGR00001', 'R00001', 5, 'Trip T00001', '2026-03-20 12:05:00'),
('LGR00002', 'R00002', 3, 'Trip T00001', '2026-03-20 12:05:00');

INSERT INTO DRIVER_GREEN_POINT_LOG (log_id, driver_id, points_change, source, created_at) VALUES
('LGD00001', 'D00001', 10, 'Trip T00001', '2026-03-20 12:05:00'),
('LGD00002', 'D00002', 6, 'Trip T00004', '2026-03-20 08:30:00');

-- Social links and friends
INSERT INTO RIDER_SOCIAL_LINK (link_id, rider_id, title, link_url) VALUES
('SL00001', 'R00001', 'LinkedIn', 'https://www.linkedin.com/'),
('SL00002', 'R00002', 'GitHub', 'https://github.com/');

INSERT INTO RIDER_FRIEND (friend_id, rider_id, friend_rider_id, status) VALUES
('F00001', 'R00001', 'R00002', 'accepted');

-- Trip share
INSERT INTO TRIP_SHARE (share_id, trip_id, rider_id, visibility, shared_at) VALUES
('S00001', 'T00001', 'R00001', 'friends', '2026-03-19 08:00:00');

-- OTP (example)
INSERT INTO OTP (otp_id, email_address, otp_code, is_used, expires_at, created_at) VALUES
('OTP00001', 'alicia.tan@mail.apu.edu.my', '123456', FALSE, '2026-03-31 23:59:59', '2026-03-19 10:00:00');
