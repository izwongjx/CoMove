-- Dummy data for COMOVE (aligned with database-scheme.md)
-- Safe re-run: deletes only the IDs below, then re-inserts.
USE COMOVE;

-- Load default profile image (src/public-assets/images/profile-icon.png)
SET @profile_photo = LOAD_FILE('C:/xampp/htdocs/APU-Comove/src/public-assets/images/profile-icon.png');
-- Load ID/license images (front used for both sides)
SET @nric_front = LOAD_FILE('C:/xampp/htdocs/APU-Comove/src/public-assets/images/ic-front.jpg');
SET @license_front = LOAD_FILE('C:/xampp/htdocs/APU-Comove/src/public-assets/images/license-front.png');


-- Config tables
INSERT INTO GREEN_POINT_CONFIG (multiplier_value)
VALUES (1);

INSERT INTO SYSTEM_CONFIG (driver_registration)
VALUES (TRUE);

-- Admin
INSERT INTO ADMIN (admin_id, name, email, password, created_at) VALUES
(1, 'System Admin', 'admin@comove.local', '0e7517141fb53f21ee439b355b5a1d0a', '2026-03-01 09:00:00');

-- Drivers (1-3)
INSERT INTO DRIVER (
    driver_id, name, email, password, phone_number, profile_photo, created_at, approved_by, driver_status,
    nric_number, nric_front_image, nric_back_image, license_front_image, license_back_image, license_expiry_date,
    vehicle_model, plate_number, color
) VALUES
(1, 'Driver', 'driver@mail.apu.edu.my', 'd5a658db5e6d22bfebbf6b6e6805c716', '012-1111222', @profile_photo, '2026-03-02 10:00:00', 1, 'active',
 '900101101234', @nric_front, @nric_front, @license_front, @license_front, '2028-12-31', 'Toyota Vios', 'WXY1234', 'Silver'),
(2, 'Daniel Lim', 'daniel.lim@mail.apu.edu.my', 'd5a658db5e6d22bfebbf6b6e6805c716', '012-3333444', @profile_photo, '2026-03-02 11:00:00', 1, 'active',
 '900202105678', @nric_front, @nric_front, @license_front, @license_front, '2029-06-30', 'Perodua Alza', 'BHG9876', 'White'),
(3, 'TP082976', 'tp082976@mail.apu.edu.my', 'd5a658db5e6d22bfebbf6b6e6805c716', '012-5555666', @profile_photo, '2026-03-02 11:30:00', 1, 'active',
 '900303109999', @nric_front, @nric_front, @license_front, @license_front, '2029-12-31', 'Honda City', 'VAX7788', 'Blue'),
(4, 'Haziq Omar', 'haziq.omar@mail.apu.edu.my', 'd5a658db5e6d22bfebbf6b6e6805c716', '012-7777888', @profile_photo, '2026-03-04 09:20:00', 1, 'active',
 '900404101010', @nric_front, @nric_front, @license_front, @license_front, '2030-05-31', 'Perodua Bezza', 'JQK5555', 'Grey'),
(5, 'Sharon Ng', 'sharon.ng@mail.apu.edu.my', 'd5a658db5e6d22bfebbf6b6e6805c716', '012-9999000', @profile_photo, '2026-03-04 10:05:00', 1, 'active',
 '900505202020', @nric_front, @nric_front, @license_front, @license_front, '2031-01-31', 'Honda HR-V', 'PKL2468', 'Red');

-- Riders
INSERT INTO RIDER (
    rider_id, name, email, password, phone_number, profile_photo, created_at, rider_status
) VALUES
(1, 'Rider', 'rider@mail.apu.edu.my', 'be8b9b5b17c5202129e66656d0b111ad', '016-2222333', @profile_photo, '2026-03-03 09:15:00', 'active'),
(2, 'Marcus Lim', 'marcus.lim@mail.apu.edu.my', 'be8b9b5b17c5202129e66656d0b111ad', '016-4444555', @profile_photo, '2026-03-03 09:20:00', 'active'),
(3, 'Nur Aina', 'nur.aina@mail.apu.edu.my', 'be8b9b5b17c5202129e66656d0b111ad', '016-6666777', @profile_photo, '2026-03-03 09:25:00', 'active'),
(4, 'Jason Lee', 'jason.lee@mail.apu.edu.my', 'be8b9b5b17c5202129e66656d0b111ad', '016-8888999', @profile_photo, '2026-03-03 09:30:00', 'active'),
(5, 'TP082977', 'tp082977@mail.apu.edu.my', 'be8b9b5b17c5202129e66656d0b111ad', '016-1234567', @profile_photo, '2026-03-03 09:35:00', 'active'),
(6, 'Samantha Khoo', 'samantha.khoo@mail.apu.edu.my', 'be8b9b5b17c5202129e66656d0b111ad', '016-1010101', @profile_photo, '2026-03-04 10:10:00', 'active'),
(7, 'Arif Zulkifli', 'arif.zulkifli@mail.apu.edu.my', 'be8b9b5b17c5202129e66656d0b111ad', '016-2020202', @profile_photo, '2026-03-04 10:20:00', 'active'),
(8, 'Mei Lin', 'mei.lin@mail.apu.edu.my', 'be8b9b5b17c5202129e66656d0b111ad', '016-3030303', @profile_photo, '2026-03-04 10:30:00', 'active');

-- Trips (more trips per driver, APU-centric locations)
INSERT INTO TRIP (
    trip_id, driver_id, start_location, end_location, departure_time, total_seats, estimated_duration, total_amount, gained_point, trip_status
) VALUES
(1, 1, 'APU Campus (Technology Park Malaysia)', 'LRT Bukit Jalil', '2026-03-20 08:30:00', 4, 30, 40.00, 5, 'completed'),
(2, 1, 'APU Residence', 'Sunway Pyramid', '2026-03-21 18:15:00', 6, 45, 90.00, 8, 'completed'),
(3, 1, 'APU Campus', 'KL Sentral', '2026-03-22 09:00:00', 4, 50, 60.00, 8, 'completed'),
(4, 2, 'TPM Gate', 'Pavilion Bukit Jalil', '2026-03-20 07:45:00', 4, 20, 24.00, 3, 'completed'),
(5, 2, 'APU Campus', 'IOI City Mall', '2026-03-23 17:30:00', 5, 35, 55.00, 6, 'completed'),
(6, 2, 'APU Residence', 'Mid Valley Megamall', '2026-03-25 10:30:00', 4, 40, 48.00, 6, 'completed'),
(7, 3, 'APU Campus', 'Subang Jaya KTM', '2026-03-24 08:00:00', 4, 35, 36.00, 5, 'completed'),
(8, 3, 'APU Residence', 'KLIA Transit (Putrajaya)', '2026-03-26 14:00:00', 6, 60, 120.00, 10, 'completed'),
(9, 1, 'APU Campus', 'Mid Valley Megamall', '2026-03-15 08:10:00', 4, 40, 45.00, 6, 'completed'),
(10, 1, 'APU Residence', 'KLCC', '2026-03-16 18:00:00', 4, 50, 70.00, 8, 'completed'),
(11, 1, 'APU Campus', 'IOI City Mall', '2026-03-19 17:45:00', 5, 35, 55.00, 6, 'completed'),
(12, 1, 'APU Residence', 'Pavilion Kuala Lumpur', '2026-03-27 09:15:00', 4, 55, 80.00, 9, 'completed'),
(13, 1, 'APU Campus', 'Sunway Pyramid', '2026-03-28 13:00:00', 6, 40, 75.00, 7, 'completed'),
(14, 4, 'APU Campus', 'Sri Petaling LRT', '2026-03-21 08:10:00', 4, 25, 22.00, 3, 'completed'),
(15, 4, 'APU Residence', 'Mid Valley Megamall', '2026-03-22 12:30:00', 4, 40, 48.00, 6, 'completed'),
(16, 5, 'APU Campus', 'KLCC', '2026-03-21 18:20:00', 5, 45, 65.00, 8, 'completed'),
(17, 5, 'APU Residence', 'Sunway Pyramid', '2026-03-23 09:00:00', 4, 40, 52.00, 6, 'completed'),
(18, 2, 'APU Campus', 'Puchong IOI', '2026-03-17 18:00:00', 4, 35, 40.00, 5, 'completed'),
(19, 1, 'APU Campus', 'Bukit Bintang', '2026-04-05 09:00:00', 4, 45, 65.00, 7, 'scheduled'),
(20, 2, 'APU Residence', 'Bandar Sunway', '2026-05-03 08:30:00', 4, 35, 42.00, 5, 'scheduled'),
(21, 3, 'APU Campus', 'Putrajaya Sentral', '2026-05-18 17:45:00', 5, 40, 58.00, 6, 'scheduled'),
(22, 4, 'APU Campus', 'Cheras Leisure Mall', '2026-06-02 08:15:00', 4, 30, 38.00, 5, 'scheduled'),
(23, 5, 'APU Residence', 'One Utama', '2026-06-20 10:00:00', 6, 60, 120.00, 10, 'scheduled');

-- Ride Requests (interconnected)
INSERT INTO RIDE_REQUEST (
    request_id, trip_id, rider_id, seats_requested, request_status, requested_at, amount_paid, payment_method, proof_of_payment, gained_point
) VALUES
(1, 1, 1, 1, 'approved', '2026-03-18 20:00:00', 10.00, 'Cash', NULL, 1),
(2, 1, 2, 1, 'approved', '2026-03-18 21:00:00', 10.00, 'Card', 'C:/xampp/htdocs/APU-Comove/src/roles/driver/uploads/proof-2.png', 1),
(3, 2, 3, 2, 'approved', '2026-03-19 19:00:00', 30.00, 'Card', 'C:/xampp/htdocs/APU-Comove/src/roles/driver/uploads/proof-3.png', 2),
(4, 2, 4, 1, 'pending',  '2026-03-19 19:10:00', 15.00, 'Cash', NULL, 0),
(5, 3, 1, 1, 'approved', '2026-03-19 19:20:00', 15.00, 'Card', 'C:/xampp/htdocs/APU-Comove/src/roles/driver/uploads/proof-5.png', 1),
(6, 4, 5, 1, 'approved', '2026-03-18 18:10:00', 6.00, 'Cash', NULL, 1),
(7, 5, 2, 1, 'approved', '2026-03-20 08:05:00', 11.00, 'Card', 'C:/xampp/htdocs/APU-Comove/src/roles/driver/uploads/proof-7.png', 1),
(8, 6, 3, 1, 'approved', '2026-03-21 09:00:00', 12.00, 'Cash', NULL, 1),
(9, 7, 4, 2, 'approved', '2026-03-21 09:10:00', 18.00, 'Card', 'C:/xampp/htdocs/APU-Comove/src/roles/driver/uploads/proof-9.png', 2),
(10, 8, 1, 2, 'approved', '2026-03-22 12:10:00', 40.00, 'Card', 'C:/xampp/htdocs/APU-Comove/src/roles/driver/uploads/proof-10.png', 3),
(11, 9, 1, 1, 'approved', '2026-03-14 19:00:00', 11.25, 'Card', 'C:/xampp/htdocs/APU-Comove/src/roles/driver/uploads/proof-11.png', 1),
(12, 9, 2, 1, 'approved', '2026-03-14 19:05:00', 11.25, 'Cash', NULL, 1),
(13, 10, 1, 2, 'approved', '2026-03-16 16:10:00', 35.00, 'Card', 'C:/xampp/htdocs/APU-Comove/src/roles/driver/uploads/proof-13.png', 2),
(14, 10, 3, 1, 'approved', '2026-03-16 16:20:00', 17.50, 'Cash', NULL, 1),
(15, 11, 1, 1, 'approved', '2026-03-19 16:40:00', 11.00, 'Card', 'C:/xampp/htdocs/APU-Comove/src/roles/driver/uploads/proof-15.png', 1),
(16, 11, 4, 1, 'approved', '2026-03-19 16:50:00', 11.00, 'Cash', NULL, 1),
(17, 12, 1, 1, 'pending',  '2026-03-26 20:15:00', 20.00, 'Card', 'C:/xampp/htdocs/APU-Comove/src/roles/driver/uploads/proof-17.png', 0),
(18, 13, 1, 2, 'approved', '2026-03-27 10:05:00', 25.00, 'Card', 'C:/xampp/htdocs/APU-Comove/src/roles/driver/uploads/proof-18.png', 2),
(19, 14, 6, 1, 'approved', '2026-03-20 18:00:00', 5.50, 'Cash', NULL, 1),
(20, 14, 1, 1, 'approved', '2026-03-20 18:05:00', 5.50, 'Card', 'C:/xampp/htdocs/APU-Comove/src/roles/driver/uploads/proof-20.png', 1),
(21, 15, 7, 2, 'pending',  '2026-03-21 20:20:00', 24.00, 'Card', 'C:/xampp/htdocs/APU-Comove/src/roles/driver/uploads/proof-21.png', 0),
(22, 16, 8, 1, 'approved', '2026-03-21 10:00:00', 13.00, 'Card', 'C:/xampp/htdocs/APU-Comove/src/roles/driver/uploads/proof-22.png', 1),
(23, 16, 1, 1, 'approved', '2026-03-21 10:05:00', 13.00, 'Cash', NULL, 1),
(24, 17, 6, 1, 'approved', '2026-03-22 08:10:00', 13.00, 'Card', 'C:/xampp/htdocs/APU-Comove/src/roles/driver/uploads/proof-24.png', 1),
(25, 18, 2, 1, 'approved', '2026-03-16 17:10:00', 10.00, 'Card', 'C:/xampp/htdocs/APU-Comove/src/roles/driver/uploads/proof-25.png', 1),
(26, 18, 1, 1, 'approved', '2026-03-16 17:12:00', 10.00, 'Cash', NULL, 1),
(27, 12, 2, 1, 'approved', '2026-03-26 20:20:00', 20.00, 'Card', 'C:/xampp/htdocs/APU-Comove/src/roles/driver/uploads/proof-27.png', 1),
(28, 13, 3, 1, 'approved', '2026-03-27 10:10:00', 12.50, 'Card', 'C:/xampp/htdocs/APU-Comove/src/roles/driver/uploads/proof-28.png', 1),
(29, 19, 2, 1, 'approved', '2026-04-03 18:30:00', 16.25, 'Card', 'C:/xampp/htdocs/APU-Comove/src/roles/driver/uploads/proof-29.png', 1),
(30, 19, 3, 1, 'pending',  '2026-04-04 10:15:00', 16.25, 'Cash', NULL, 0),
(31, 19, 4, 2, 'pending',  '2026-04-04 11:45:00', 32.50, 'Card', 'C:/xampp/htdocs/APU-Comove/src/roles/driver/uploads/proof-31.png', 0);

-- Rewards
INSERT INTO REWARD (reward_id, reward_pic, reward_name, points_required, category, stock) VALUES
(1, NULL, 'APU Cafeteria Coffee Voucher', 20, 'Food', 50),
(2, NULL, 'APU Campus Parking Coupon', 15, 'Transport', 30),
(3, NULL, 'APU Partner Fuel Voucher RM20', 30, 'Fuel', 25),
(4, NULL, 'APU CoMove Windbreaker', 80, 'Merch', 10),
(5, NULL, 'APU Shuttle Ride Coupon', 25, 'Transport', 40),
(6, NULL, 'APU Cafeteria Snack Voucher', 18, 'Food', 60),
(7, NULL, 'APU Student Grab Voucher RM10', 25, 'Food', 45),
(8, NULL, 'APU Phone Holder', 12, 'Merch', 35),
(9, NULL, 'APU Partner Fuel Voucher RM30', 45, 'Fuel', 20),
(10, NULL, 'APU CoMove Tote Bag', 22, 'Merch', 50);

-- Redemptions
INSERT INTO RIDER_REDEMPTION (redemption_id, rider_id, reward_id, redeemed_at) VALUES
(1, 1, 1, '2026-03-10 12:30:00'),
(2, 1, 2, '2026-03-12 15:10:00'),
(3, 1, 6, '2026-03-18 11:40:00'),
(4, 1, 7, '2026-03-20 14:05:00'),
(5, 2, 5, '2026-03-18 16:20:00');

INSERT INTO DRIVER_REDEMPTION (redemption_id, driver_id, reward_id, redeemed_at) VALUES
(1, 1, 2, '2026-03-10 12:45:00'),
(2, 1, 3, '2026-03-16 13:20:00'),
(3, 1, 4, '2026-03-19 09:05:00'),
(4, 4, 8, '2026-03-20 18:30:00'),
(5, 5, 9, '2026-03-21 09:10:00');

-- Green point logs
INSERT INTO RIDER_GREEN_POINT_LOG (log_id, rider_id, points_change, source, created_at) VALUES
(1, 1, 5, 'Trip 1', '2026-03-20 12:05:00'),
(2, 2, 3, 'Trip 1', '2026-03-20 12:05:00'),
(3, 1, 6, 'Trip 9', '2026-03-15 10:10:00'),
(4, 1, 8, 'Trip 10', '2026-03-16 20:45:00'),
(5, 1, 6, 'Trip 11', '2026-03-19 19:40:00'),
(6, 1, -18, 'Reward Redemption', '2026-03-18 11:40:00'),
(7, 1, -25, 'Reward Redemption', '2026-03-20 14:05:00'),
(8, 6, 3, 'Trip 14', '2026-03-21 08:40:00'),
(9, 8, 4, 'Trip 16', '2026-03-21 19:20:00'),
(10, 2, 5, 'Trip 18', '2026-03-17 20:10:00'),
(11, 1, 40, 'APU Green Day Bonus', '2026-03-24 09:00:00');

INSERT INTO DRIVER_GREEN_POINT_LOG (log_id, driver_id, points_change, source, created_at) VALUES
(1, 1, 10, 'Trip 1', '2026-03-20 12:05:00'),
(2, 2, 6, 'Trip 4', '2026-03-20 08:30:00'),
(3, 1, 12, 'Trip 9', '2026-03-15 10:10:00'),
(4, 1, 16, 'Trip 10', '2026-03-16 20:45:00'),
(5, 1, 12, 'Trip 11', '2026-03-19 19:40:00'),
(6, 1, -30, 'Reward Redemption', '2026-03-19 09:05:00'),
(7, 4, 6, 'Trip 14', '2026-03-21 08:40:00'),
(8, 5, 8, 'Trip 16', '2026-03-21 19:20:00'),
(9, 2, 9, 'Trip 18', '2026-03-17 20:10:00');

INSERT INTO RIDER_FRIEND (friend_id, rider_id, friend_rider_id, status) VALUES
(1, 1, 2, 'accepted'),
(2, 1, 3, 'accepted'),
(3, 1, 4, 'pending'),
(4, 1, 5, 'accepted'),
(5, 6, 7, 'accepted'),
(6, 6, 8, 'pending');

-- OTP (example)
INSERT INTO OTP (otp_id, email_address, otp_code, is_used, expires_at, created_at) VALUES
(1, 'alicia.tan@mail.apu.edu.my', '123456', FALSE, '2026-03-31 23:59:59', '2026-03-19 10:00:00'),
(2, 'alicia.tan@mail.apu.edu.my', '654321', TRUE, '2026-03-20 23:59:59', '2026-03-20 09:15:00'),
(3, 'alicia.tan@mail.apu.edu.my', '908172', FALSE, '2026-03-22 23:59:59', '2026-03-22 08:05:00'),
(4, 'samantha.khoo@mail.apu.edu.my', '445566', TRUE, '2026-03-23 23:59:59', '2026-03-23 09:00:00'),
(5, 'arif.zulkifli@mail.apu.edu.my', '778899', FALSE, '2026-03-24 23:59:59', '2026-03-24 08:30:00'),
(6, 'mei.lin@mail.apu.edu.my', '112233', FALSE, '2026-03-25 23:59:59', '2026-03-25 11:00:00');
