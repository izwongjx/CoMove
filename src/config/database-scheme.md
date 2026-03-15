---
config:
  theme: base
  layout: elk
---
erDiagram
	direction LR
	RIDER {
		INT rider_id PK "AUTO_INCREMENT"  
		NVARCHAR(50) name  "NOT NULL"  
		VARCHAR(100) email  "NOT NULL, UNIQUE"  
		VARCHAR(255) password  "NOT NULL"  
		VARCHAR(20) phone_number  ""  
		MEDIUMBLOB profile_photo  ""  
		DATETIME created_at  "NOT NULL, DEFAULT CURRENT_TIMESTAMP"  
		VARCHAR(20) rider_status  "NOT NULL, DEFAULT 'active', CHECK (rider_status IN ('active','banned'))"  
		INT green_points  "DERIVED: SUM(RIDER_GREEN_POINT_LOG.points_change WHERE RIDER_GREEN_POINT_LOG.rider_id = RIDER.rider_id)" 
	}

	DRIVER {
		INT driver_id PK "AUTO_INCREMENT"  
		NVARCHAR(50) name  "NOT NULL"  
		VARCHAR(100) email  "NOT NULL, UNIQUE"  
		VARCHAR(255) password  "NOT NULL"  
		VARCHAR(20) phone_number  "NOT NULL"  
		MEDIUMBLOB profile_photo  ""  
		DATETIME created_at  "NOT NULL, DEFAULT CURRENT_TIMESTAMP"  
		INT approved_by FK "ADMIN"  
		VARCHAR(20) driver_status  "NOT NULL, DEFAULT 'pending', CHECK (driver_status IN ('pending','active','rejected','banned'))"  
		DECIMAL(10,2) driver_rating  "DERIVED: AVG(RATING.rating_score)
                                        FROM RATING
                                        JOIN TRIP ON RATING.trip_id = TRIP.trip_id
                                        WHERE TRIP.driver_id = DRIVER.driver_id"  
		CHAR(12) nric_number  "NOT NULL, UNIQUE"  
		MEDIUMBLOB nric_front_image  "NOT NULL"  
		MEDIUMBLOB nric_back_image  "NOT NULL"  
		MEDIUMBLOB license_front_image  "NOT NULL"  
		MEDIUMBLOB license_back_image  "NOT NULL"  
		DATE license_expiry_date  "NOT NULL"  
		VARCHAR(20) vehicle_model  ""  
		CHAR(10) plate_number  "NOT NULL, UNIQUE"  
		VARCHAR(20) color  ""  
		INT green_points  "DERIVED: SUM(DRIVER_GREEN_POINT_LOG.points_change WHERE DRIVER_GREEN_POINT_LOG.driver_id = DRIVER.driver_id)" 
	}

	ADMIN {
		INT admin_id PK "AUTO_INCREMENT"  
		NVARCHAR(50) name  "NOT NULL"  
		VARCHAR(255) password  "NOT NULL"  
		DATETIME created_at  "NOT NULL, DEFAULT CURRENT_TIMESTAMP"  
	}

	TRIP {
		INT trip_id PK "AUTO_INCREMENT"  
		INT driver_id FK "NOT NULL"  
		NVARCHAR(100) start_location  "NOT NULL"  
		NVARCHAR(100) end_location  "NOT NULL"  
		DATETIME departure_time  "NOT NULL"  
		INT total_seats  "NOT NULL, DEFAULT 0"  
		INT available_seats  "DERIVED: TRIP.total_seats - SUM(RIDE_REQUEST.seats_requested
                                    WHERE RIDE_REQUEST.trip_id = TRIP.trip_id
                                    AND request_status = 'approved')"  
		INT estimated_duration  ""  
		DECIMAL(10,2) total_amount  "NOT NULL, DEFAULT 0"  
		INT gained_point  ""  
		VARCHAR(20) trip_status  "NOT NULL, DEFAULT 'scheduled', CHECK (trip_status IN ('scheduled','ongoing','completed'))"  
	}

	RIDE_REQUEST {
		INT request_id PK "AUTO_INCREMENT"  
		INT trip_id FK "NOT NULL"  
		INT rider_id FK "NOT NULL"  
		INT seats_requested  "NOT NULL"  
		VARCHAR(20) request_status  "NOT NULL, DEFAULT 'pending', CHECK (request_status IN ('pending','approved','rejected'))"  
		DATETIME requested_at  "NOT NULL, DEFAULT CURRENT_TIMESTAMP"  
		DECIMAL(10,2) amount_paid  "DERIVED: (TRIP.total_amount / TRIP.total_seats) * RIDE_REQUEST.seats_requested"  
		VARCHAR(20) payment_method  ""  
		MEDIUMBLOB proof_of_payment  ""  
		INT gained_point  ""  
	}

	RATING {
		INT rating_id PK "AUTO_INCREMENT"  
		INT trip_id FK "NOT NULL"  
		INT rider_id FK "NOT NULL"  
		INT rating_score  "NOT NULL, CHECK (rating_score >=1 AND rating_score <=5)"  
		NVARCHAR(255) comment  ""  
		DATETIME created_at  "NOT NULL, DEFAULT CURRENT_TIMESTAMP"  
	}

	REWARD {
		INT reward_id PK "AUTO_INCREMENT"  
		MEDIUMBLOB reward_pic
		NVARCHAR(50) reward_name  "NOT NULL"  
		INT points_required  "NOT NULL"  
		VARCHAR(50) category  "NOT NULL"  
		INT stock  "NOT NULL, DEFAULT 0"  
	}

	RIDER_REDEMPTION {
		INT redemption_id PK "AUTO_INCREMENT"  
		INT rider_id FK "NOT NULL"  
		INT reward_id FK "NOT NULL"  
		DATETIME redeemed_at  "NOT NULL, DEFAULT CURRENT_TIMESTAMP"  
	}

	DRIVER_REDEMPTION {
		INT redemption_id PK "AUTO_INCREMENT"  
		INT driver_id FK "NOT NULL"  
		INT reward_id FK "NOT NULL"  
		DATETIME redeemed_at  "NOT NULL, DEFAULT CURRENT_TIMESTAMP"  
	}

	RIDER_GREEN_POINT_LOG {
		INT log_id PK "AUTO_INCREMENT"  
		INT rider_id FK "NOT NULL"  
		INT points_change  "NOT NULL, DEFAULT 0"  
		VARCHAR(50) source  "NOT NULL"  
		DATETIME created_at  "NOT NULL, DEFAULT CURRENT_TIMESTAMP"  
	}

	DRIVER_GREEN_POINT_LOG {
		INT log_id PK "AUTO_INCREMENT"  
		INT driver_id FK "NOT NULL"  
		INT points_change  "NOT NULL, DEFAULT 0"  
		VARCHAR(50) source  "NOT NULL"  
		DATETIME created_at  "NOT NULL, DEFAULT CURRENT_TIMESTAMP"  
	}

	GREEN_POINT_CONFIG {
		INT multiplier_value  "NOT NULL, DEFAULT 1"  
		INT driver_base_point  ""
		INT rider_base_point ""
		FLOAT min_price ""
	}

	SYSTEM_CONFIG{
		BOOLEAN driver_registration "TRUE = registration open, FALSE = closed"
		BOOLEAN rider_registration "TRUE = registration open, FALSE = closed"
		BOOLEAN system_maintenance "TRUE = maintenance on, FALSE = normal operation"
	}

	RIDER_SOCIAL_LINK {
		INT link_id PK "AUTO_INCREMENT"  
		INT rider_id FK "NOT NULL"  
		NVARCHAR(50) title  "NOT NULL"  
		VARCHAR(255) link_url  "NOT NULL"  
	}

	RIDER_FRIEND {
		INT friend_id PK "AUTO_INCREMENT"  
		INT rider_id FK "NOT NULL"  
		INT friend_rider_id FK "NOT NULL"  
		VARCHAR(20) status  "NOT NULL, DEFAULT 'pending', CHECK (status IN ('pending','accepted','rejected'))"  
	}

	TRIP_SHARE {
		INT share_id PK "AUTO_INCREMENT"  
		INT trip_id FK "NOT NULL"  
		INT rider_id FK "NOT NULL"  
		VARCHAR(20) visibility  "NOT NULL, DEFAULT 'private', CHECK (visibility IN ('private','friends'))"  
		DATETIME shared_at  "NOT NULL, DEFAULT CURRENT_TIMESTAMP"  
	}

	OTP {
		INT otp_id PK "AUTO_INCREMENT"  
		VARCHAR(100) email_address  "NOT NULL"  
		VARCHAR(10) otp_code  "NOT NULL"  
		BOOLEAN is_used  "NOT NULL DEFAULT FALSE, TRUE = used, FALSE = not used"  
		DATETIME expires_at  "NOT NULL"  
		DATETIME created_at  "DEFAULT CURRENT_TIMESTAMP"  
	}

	RIDER||--o{RIDER_SOCIAL_LINK:"has"
	RIDER||--o{RIDER_FRIEND:"sends_request"
	RIDER||--o{RIDER_FRIEND:"receives_request"
	RIDER||--o{TRIP_SHARE:"shares"
	TRIP||--o{TRIP_SHARE:"is_shared"
	DRIVER||--o{TRIP:"creates"
	RIDER||--o{RIDE_REQUEST:"makes"
	TRIP||--o{RIDE_REQUEST:"has"
	TRIP||--o{RATING:"receives"
	RIDER||--o{RATING:"gives"
	RIDER||--o{RIDER_GREEN_POINT_LOG:"has"
	DRIVER||--o{DRIVER_GREEN_POINT_LOG:"has"
	RIDER||--o{RIDER_REDEMPTION:"redeems"
	DRIVER||--o{DRIVER_REDEMPTION:"redeems"
	REWARD||--o{RIDER_REDEMPTION:"used_in"
	REWARD||--o{DRIVER_REDEMPTION:"used_in"
	ADMIN||--o{DRIVER:"approves"
