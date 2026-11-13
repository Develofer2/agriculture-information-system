BEGIN;

CREATE TABLE app_roles (
    role_id smallint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    role_name varchar(30) NOT NULL UNIQUE,
    created_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE crop_categories (
    category_id smallint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    category_name varchar(50) NOT NULL UNIQUE,
    is_active boolean NOT NULL DEFAULT true
);

CREATE TABLE growing_seasons (
    season_id smallint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    season_name varchar(30) NOT NULL UNIQUE,
    is_active boolean NOT NULL DEFAULT true
);

CREATE TABLE soil_types (
    soil_type_id smallint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    soil_type_name varchar(50) NOT NULL UNIQUE,
    is_active boolean NOT NULL DEFAULT true
);

CREATE TABLE irrigation_types (
    irrigation_type_id smallint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    irrigation_type_name varchar(50) NOT NULL UNIQUE,
    is_active boolean NOT NULL DEFAULT true
);

CREATE TABLE planting_statuses (
    status_id smallint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    status_name varchar(30) NOT NULL UNIQUE,
    is_active boolean NOT NULL DEFAULT true
);

CREATE TABLE harvest_quality_grades (
    quality_grade_id smallint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    grade_name varchar(30) NOT NULL UNIQUE,
    is_active boolean NOT NULL DEFAULT true
);

CREATE TABLE harvest_remarks (
    remark_id smallint PRIMARY KEY,
    remark_description varchar(200) NOT NULL UNIQUE,
    is_active boolean NOT NULL DEFAULT true
);

CREATE TABLE app_users (
    user_id bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    full_name varchar(100) NOT NULL,
    email varchar(254) NOT NULL UNIQUE,
    password_hash varchar(255) NOT NULL,
    role_id smallint NOT NULL REFERENCES app_roles(role_id) ON DELETE RESTRICT,
    is_active boolean NOT NULL DEFAULT true,
    last_login_at timestamptz,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE farmers (
    farmer_id bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    first_name varchar(50) NOT NULL,
    last_name varchar(50) NOT NULL,
    contact_number varchar(20),
    address varchar(150) NOT NULL,
    date_registered date NOT NULL DEFAULT current_date,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE farms (
    farm_id bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    farmer_id bigint NOT NULL REFERENCES farmers(farmer_id) ON DELETE RESTRICT,
    farm_name varchar(100) NOT NULL,
    location varchar(150) NOT NULL,
    area_hectares numeric(8, 2) NOT NULL CHECK (area_hectares > 0),
    soil_type_id smallint NOT NULL REFERENCES soil_types(soil_type_id) ON DELETE RESTRICT,
    irrigation_type_id smallint NOT NULL REFERENCES irrigation_types(irrigation_type_id) ON DELETE RESTRICT,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE crops (
    crop_id bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    crop_name varchar(80) NOT NULL,
    variety varchar(80) NOT NULL,
    category_id smallint NOT NULL REFERENCES crop_categories(category_id) ON DELETE RESTRICT,
    growing_period_days integer NOT NULL CHECK (growing_period_days >= 1),
    season_id smallint NOT NULL REFERENCES growing_seasons(season_id) ON DELETE RESTRICT,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now(),
    UNIQUE (crop_name, variety)
);

CREATE TABLE plantings (
    planting_id bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    farm_id bigint NOT NULL REFERENCES farms(farm_id) ON DELETE RESTRICT,
    crop_id bigint NOT NULL REFERENCES crops(crop_id) ON DELETE RESTRICT,
    planting_date date NOT NULL,
    area_planted_ha numeric(8, 2) NOT NULL CHECK (area_planted_ha > 0),
    seed_quantity_kg numeric(8, 2) NOT NULL CHECK (seed_quantity_kg >= 0),
    expected_harvest_date date NOT NULL CHECK (expected_harvest_date >= planting_date),
    status_id smallint NOT NULL REFERENCES planting_statuses(status_id) ON DELETE RESTRICT,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE harvests (
    harvest_id bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    planting_id bigint NOT NULL REFERENCES plantings(planting_id) ON DELETE RESTRICT,
    harvest_date date NOT NULL,
    quantity_kg numeric(10, 2) NOT NULL CHECK (quantity_kg > 0),
    selling_price_per_kg numeric(10, 2) CHECK (selling_price_per_kg >= 0),
    quality_grade_id smallint NOT NULL REFERENCES harvest_quality_grades(quality_grade_id) ON DELETE RESTRICT,
    remark_id smallint NOT NULL REFERENCES harvest_remarks(remark_id) ON DELETE RESTRICT,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE audit_logs (
    log_id bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    actor_user_id bigint REFERENCES app_users(user_id) ON DELETE SET NULL,
    action varchar(10) NOT NULL CHECK (action IN ('INSERT', 'UPDATE', 'DELETE', 'LOGIN', 'LOGOUT')),
    entity_table varchar(60) NOT NULL,
    record_pk varchar(50),
    new_values jsonb,
    created_at timestamptz NOT NULL DEFAULT now()
);

CREATE INDEX idx_farms_farmer ON farms(farmer_id);
CREATE INDEX idx_plantings_farm ON plantings(farm_id);
CREATE INDEX idx_plantings_crop ON plantings(crop_id);
CREATE INDEX idx_harvests_planting ON harvests(planting_id);
CREATE INDEX idx_audit_created ON audit_logs(created_at DESC);

CREATE OR REPLACE FUNCTION set_updated_at()
RETURNS trigger
LANGUAGE plpgsql
AS $$
BEGIN
    NEW.updated_at = now();
    RETURN NEW;
END;
$$;

CREATE TRIGGER farmers_updated
BEFORE UPDATE ON farmers
FOR EACH ROW EXECUTE FUNCTION set_updated_at();

CREATE TRIGGER farms_updated
BEFORE UPDATE ON farms
FOR EACH ROW EXECUTE FUNCTION set_updated_at();

CREATE TRIGGER crops_updated
BEFORE UPDATE ON crops
FOR EACH ROW EXECUTE FUNCTION set_updated_at();

CREATE TRIGGER plantings_updated
BEFORE UPDATE ON plantings
FOR EACH ROW EXECUTE FUNCTION set_updated_at();

CREATE TRIGGER harvests_updated
BEFORE UPDATE ON harvests
FOR EACH ROW EXECUTE FUNCTION set_updated_at();

CREATE TRIGGER users_updated
BEFORE UPDATE ON app_users
FOR EACH ROW EXECUTE FUNCTION set_updated_at();

CREATE OR REPLACE FUNCTION validate_harvest_date()
RETURNS trigger
LANGUAGE plpgsql
AS $$
BEGIN
    IF NEW.harvest_date < (
        SELECT planting_date
        FROM plantings
        WHERE planting_id = NEW.planting_id
    ) THEN
        RAISE EXCEPTION 'Harvest date cannot be before planting date';
    END IF;

    RETURN NEW;
END;
$$;

CREATE TRIGGER harvest_date_valid
BEFORE INSERT OR UPDATE ON harvests
FOR EACH ROW EXECUTE FUNCTION validate_harvest_date();

COMMIT;
