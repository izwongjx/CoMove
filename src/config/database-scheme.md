---
config:
  theme: base
  layout: elk
---
erDiagram
    direction LR

    RIDER {
        CHAR(8) rider_id PK "UNIQUE, format R00001"
        NVARCHAR(50) name "NOT NULL"
        VARCHAR(100) email "NOT NULL, UNIQUE"
        VARCHAR(255) password "NOT NULL"
        VARCHAR(20) phone_number ""
        MEDIUMBLOB profile_photo ""
        DATETIME created_at "NOT NULL, DEFAULT CURRENT_TIMESTAMP"
        VARCHAR(20) rider_status "NOT NULL, DEFAULT 'active', CHECK (rider_status IN ('active','banned'))"
        INT green_points "DERIVED: SUM(RIDER_GREEN_POINT_LOG.points_earned 
                                    WHERE RIDER_GREEN_POINT_LOG.rider_id = RIDER.rider_id) 
                                    - SUM(REWARD.points_required FROM RIDER_REDEMPTION 
                                    JOIN REWARD ON RIDER_REDEMPTION.reward_id = REWARD.reward_id
                                    WHERE RIDER_REDEMPTION.rider_id = RIDER.rider_id)"
    }

    DRIVER {
        CHAR(8) driver_id PK "UNIQUE, format D00001"
        NVARCHAR(50) name "NOT NULL"
        VARCHAR(100) email "NOT NULL, UNIQUE"
        VARCHAR(255) password "NOT NULL"
        VARCHAR(20) phone_number "NOT NULL"
        MEDIUMBLOB profile_photo ""
        DATETIME created_at "NOT NULL, DEFAULT CURRENT_TIMESTAMP"
        CHAR(8) approved_by FK "ADMIN"
        VARCHAR(20) driver_status "NOT NULL, DEFAULT 'pending', CHECK (driver_status IN ('pending','active','rejected','banned'))"
        FLOAT driver_rating "DERIVED: AVG(RATING.rating_score)
                                        FROM RATING
                                        JOIN TRIP ON RATING.trip_id = TRIP.trip_id
                                        WHERE TRIP.driver_id = DRIVER.driver_id"
        CHAR(12) nric_number "NOT NULL, UNIQUE"
        MEDIUMBLOB nric_front_image "NOT NULL"
        MEDIUMBLOB nric_back_image "NOT NULL"
        MEDIUMBLOB lisence_front_image "NOT NULL"
        MEDIUMBLOB lisence_back_image "NOT NULL"
        DATE lisence_expiry_date "NOT NULL"
        VARCHAR(20) vehicle_model ""
        CHAR(10) plate_number "NOT NULL, UNIQUE"
        VARCHAR(20) color ""
        INT green_points "DERIVED: SUM(DRIVER_GREEN_POINT_LOG.points_earned 
                                        WHERE DRIVER_GREEN_POINT_LOG.driver_id = DRIVER.driver_id) 
                                        - SUM(REWARD.points_required FROM DRIVER_REDEMPTION 
                                        JOIN REWARD ON DRIVER_REDEMPTION.reward_id = REWARD.reward_id 
                                        WHERE DRIVER_REDEMPTION.driver_id = DRIVER.driver_id)"
    }

    ADMIN {
        CHAR(8) admin_id PK "UNIQUE, format A00001"
        NVARCHAR(50) name "NOT NULL"
        VARCHAR(255) password "NOT NULL"
        DATETIME created_at "NOT NULL, DEFAULT CURRENT_TIMESTAMP"
    }

    TRIP {
        CHAR(8) trip_id PK "UNIQUE, format T00001"
        CHAR(8) driver_id FK "NOT NULL"
        NVARCHAR(100) start_location "NOT NULL"
        NVARCHAR(100) end_location "NOT NULL"
        DATETIME departure_time "NOT NULL"
        INT total_seats "NOT NULL, DEFAULT 0"
        INT available_seats "DERIVED: TRIP.total_seats - SUM(RIDE_REQUEST.seats_requested
                                    WHERE RIDE_REQUEST.trip_id = TRIP.trip_id
                                    AND request_status = 'approved')"
        INT estimated_duration ""
        FLOAT total_amount "NOT NULL, DEFAULT 0"
        VARCHAR(20) trip_status "NOT NULL, DEFAULT 'scheduled', CHECK (trip_status IN ('scheduled','ongoing','completed'))"
    }

    RIDE_REQUEST {
        CHAR(8) request_id PK "UNIQUE, format RE00001"
        CHAR(8) trip_id FK "NOT NULL"
        CHAR(8) rider_id FK "NOT NULL"
        INT seats_requested "NOT NULL"
        VARCHAR(20) request_status "NOT NULL, DEFAULT 'pending', CHECK (request_status IN ('pending','approved','rejected'))"
        DATETIME requested_at "NOT NULL, DEFAULT CURRENT_TIMESTAMP"
        FLOAT amount_paid "DERIVED: (TRIP.total_amount / TRIP.total_seats) * RIDE_REQUEST.seats_requested 
									WHERE RIDE_REQUEST.trip_id = TRIP.trip_id"
        VARCHAR(20) payment_method ""
        MEDIUMBLOB proof_of_payment ""
    }

    RATING {
        CHAR(8) rating_id PK "UNIQUE, format RA00001"
        CHAR(8) trip_id FK "NOT NULL"
        CHAR(8) rider_id FK "NOT NULL"
        INT rating_score "NOT NULL, CHECK (rating_score >=1 AND rating_score <=5)"
        NVARCHAR(255) comment ""
        DATETIME created_at "NOT NULL, DEFAULT CURRENT_TIMESTAMP"
    }

    REWARD {
        CHAR(8) reward_id PK "UNIQUE, format REW00001"
        NVARCHAR(50) reward_name "NOT NULL"
        INT points_required "NOT NULL"
        VARCHAR(50) category "NOT NULL"
        INT stock "NOT NULL, DEFAULT 0"
    }

    RIDER_REDEMPTION {
        CHAR(8) redemption_id PK "UNIQUE, format REDR00001"
        CHAR(8) rider_id FK "NOT NULL"
        CHAR(8) reward_id FK "NOT NULL"
        DATETIME redeemed_at "NOT NULL, DEFAULT CURRENT_TIMESTAMP"
    }

    DRIVER_REDEMPTION {
        CHAR(8) redemption_id PK "UNIQUE, format REDD00001"
        CHAR(8) driver_id FK "NOT NULL"
        CHAR(8) reward_id FK "NOT NULL"
        DATETIME redeemed_at "NOT NULL, DEFAULT CURRENT_TIMESTAMP"
    }

    RIDER_GREEN_POINT_LOG {
        CHAR(8) log_id PK "UNIQUE, format LGR00001"
        CHAR(8) rider_id FK "NOT NULL"
        INT points_earned "NOT NULL, DEFAULT 0"
        VARCHAR(50) source "NOT NULL"
        DATETIME created_at "NOT NULL, DEFAULT CURRENT_TIMESTAMP"
    }

    DRIVER_GREEN_POINT_LOG {
        CHAR(8) log_id PK "UNIQUE, format LGD00001"
        CHAR(8) driver_id FK "NOT NULL"
        INT points_earned "NOT NULL, DEFAULT 0"
        VARCHAR(50) source "NOT NULL"
        DATETIME created_at "NOT NULL, DEFAULT CURRENT_TIMESTAMP"
    }

    GREEN_POINT_MULTIPLIER {
        CHAR(8) multiplier_id PK "UNIQUE, format GM00001"
        FLOAT multiplier_value "NOT NULL, DEFAULT 1"
        CHAR(8) set_by FK "ADMIN"
        DATETIME set_at "DEFAULT CURRENT_TIMESTAMP"
    }

    RIDER_SOCIAL_LINK {
        CHAR(8) link_id PK "UNIQUE, format SL00001"
        CHAR(8) rider_id FK "NOT NULL"
        NVARCHAR(50) title "NOT NULL"
        VARCHAR(255) link_url "NOT NULL"
    }

    RIDER_FRIEND {
        CHAR(8) friend_id PK "UNIQUE, format F00001"
        CHAR(8) rider_id FK "NOT NULL"
        CHAR(8) friend_rider_id FK "NOT NULL"
        VARCHAR(20) status "NOT NULL, DEFAULT 'pending', CHECK (status IN ('pending','accepted','rejected'))"
    }

    TRIP_SHARE {
        CHAR(8) share_id PK "UNIQUE, format S00001"
        CHAR(8) trip_id FK "NOT NULL"
        CHAR(8) rider_id FK "NOT NULL"
        VARCHAR(20) visibility "NOT NULL, DEFAULT 'private', CHECK (visibility IN ('private','friends'))"
        DATETIME shared_at "NOT NULL, DEFAULT CURRENT_TIMESTAMP"
    }

    OTP {
        CHAR(8) otp_id PK "UNIQUE, format OTP00001"
        VARCHAR(100) email_address "NOT NULL"
        VARCHAR(10) otp_code "NOT NULL"
        BOOLEAN is_used "NOT NULL DEFAULT FALSE"
        DATETIME expires_at "NOT NULL"
        DATETIME created_at "DEFAULT CURRENT_TIMESTAMP"
    }
    RIDER ||--o{ RIDER_SOCIAL_LINK : "has"
    RIDER ||--o{ RIDER_FRIEND : "sends_request"
    RIDER ||--o{ RIDER_FRIEND : "receives_request"
    RIDER ||--o{ TRIP_SHARE : "shares"
    TRIP ||--o{ TRIP_SHARE : "is_shared"
    DRIVER ||--o{ TRIP : "creates"
    RIDER ||--o{ RIDE_REQUEST : "makes"
    TRIP ||--o{ RIDE_REQUEST : "has"
    TRIP ||--o{ RATING : "receives"
    RIDER ||--o{ RATING : "gives"
    RIDER ||--o{ RIDER_GREEN_POINT_LOG : "earns"
    DRIVER ||--o{ DRIVER_GREEN_POINT_LOG : "earns"
    RIDER ||--o{ RIDER_REDEMPTION : "redeems"
    DRIVER ||--o{ DRIVER_REDEMPTION : "redeems"
    REWARD ||--o{ RIDER_REDEMPTION : "used_in"
    REWARD ||--o{ DRIVER_REDEMPTION : "used_in"
    ADMIN ||--o{ DRIVER : "approves"
    ADMIN ||--o{ GREEN_POINT_MULTIPLIER : "sets"