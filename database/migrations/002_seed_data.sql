BEGIN;

INSERT INTO app_roles (role_name)
VALUES
    ('Administrator'),
    ('Staff')
ON CONFLICT (role_name) DO NOTHING;

INSERT INTO crop_categories (category_name)
VALUES
    ('Cereal'),
    ('Vegetable'),
    ('Fruit'),
    ('Root Crop'),
    ('Legume')
ON CONFLICT (category_name) DO NOTHING;

INSERT INTO growing_seasons (season_name)
VALUES
    ('Wet Season'),
    ('Dry Season'),
    ('Year-round')
ON CONFLICT (season_name) DO NOTHING;

INSERT INTO soil_types (soil_type_name)
VALUES
    ('Loam'),
    ('Clay'),
    ('Sandy Loam'),
    ('Silt Loam')
ON CONFLICT (soil_type_name) DO NOTHING;

INSERT INTO irrigation_types (irrigation_type_name)
VALUES
    ('Rainfed'),
    ('Canal'),
    ('Drip'),
    ('Sprinkler')
ON CONFLICT (irrigation_type_name) DO NOTHING;

INSERT INTO planting_statuses (status_name)
VALUES
    ('Planned'),
    ('Growing'),
    ('Ready for Harvest'),
    ('Harvested'),
    ('Cancelled')
ON CONFLICT (status_name) DO NOTHING;

INSERT INTO harvest_quality_grades (grade_name)
VALUES
    ('Grade A'),
    ('Grade B'),
    ('Grade C')
ON CONFLICT (grade_name) DO NOTHING;

INSERT INTO harvest_remarks (remark_id, remark_description)
VALUES
    (1, 'High quality harvest'),
    (2, 'Good harvest'),
    (3, 'Acceptable quality'),
    (4, 'Low yield'),
    (5, 'Poor quality')
ON CONFLICT (remark_id) DO NOTHING;

INSERT INTO farmers (
    first_name,
    last_name,
    contact_number,
    address,
    date_registered
)
VALUES
    ('Juan', 'Dela Cruz', '09171234567', 'Cebu City', '2026-10-26'),
    ('Maria', 'Santos', '09181234567', 'Carcar City, Cebu', '2026-10-26'),
    ('Pedro', 'Reyes', '09191234567', 'Danao City, Cebu', '2026-10-26'),
    ('Ana', 'Garcia', '09201234567', 'Argao, Cebu', '2026-10-26'),
    ('Jose', 'Mendoza', '09211234567', 'Bogo City, Cebu', '2026-10-26'),
    ('Ramon', 'Flores', '09221234567', 'Toledo City, Cebu', '2026-10-26'),
    ('Elena', 'Cruz', '09231234567', 'Naga City, Cebu', '2026-10-26'),
    ('Antonio', 'Lim', '09241234567', 'Minglanilla, Cebu', '2026-10-26'),
    ('Rosa', 'Fernandez', '09251234567', 'Liloan, Cebu', '2026-10-26'),
    ('Mario', 'Villanueva', '09261234567', 'Compostela, Cebu', '2026-10-26'),
    ('Teresa', 'Navarro', '09271234567', 'Talisay City, Cebu', '2026-10-26'),
    ('Carlos', 'Ramos', '09281234567', 'Consolacion, Cebu', '2026-10-26'),
    ('Liza', 'Torres', '09291234567', 'Dalaguete, Cebu', '2026-10-26'),
    ('Roberto', 'Castillo', '09301234567', 'Barili, Cebu', '2026-10-26'),
    ('Susan', 'Aquino', '09311234567', 'Sibonga, Cebu', '2026-10-26');

INSERT INTO farms (
    farmer_id,
    farm_name,
    location,
    area_hectares,
    soil_type_id,
    irrigation_type_id
)
VALUES
    (1, 'Dela Cruz Farm', 'Cebu City', 2.50, 1, 1),
    (2, 'Santos Rice Farm', 'Carcar City, Cebu', 4.20, 2, 2),
    (3, 'Reyes Agricultural Farm', 'Danao City, Cebu', 3.80, 3, 1),
    (4, 'Garcia Vegetable Farm', 'Argao, Cebu', 1.75, 1, 3),
    (5, 'Mendoza Farm', 'Bogo City, Cebu', 5.00, 2, 2),
    (6, 'Flores Corn Farm', 'Toledo City, Cebu', 3.40, 3, 1),
    (7, 'Cruz Family Farm', 'Naga City, Cebu', 2.80, 1, 2),
    (8, 'Lim Agricultural Farm', 'Minglanilla, Cebu', 2.25, 2, 3),
    (9, 'Fernandez Farm', 'Liloan, Cebu', 3.10, 3, 1),
    (10, 'Villanueva Green Farm', 'Compostela, Cebu', 4.50, 1, 2),
    (11, 'Navarro Rice Field', 'Talisay City, Cebu', 3.75, 2, 2),
    (12, 'Ramos Farm', 'Consolacion, Cebu', 2.60, 1, 1),
    (13, 'Torres Vegetable Farm', 'Dalaguete, Cebu', 1.80, 3, 3),
    (14, 'Castillo Agricultural Farm', 'Barili, Cebu', 5.50, 2, 2),
    (15, 'Aquino Farm', 'Sibonga, Cebu', 3.20, 1, 1);

INSERT INTO crops (
    crop_name,
    variety,
    category_id,
    growing_period_days,
    season_id
)
VALUES
    ('Rice', 'NSIC Rc 222', 1, 115, 1),
    ('Corn', 'Yellow Corn', 1, 100, 2),
    ('Tomato', 'Diamante Max', 2, 90, 2),
    ('Eggplant', 'Morena', 2, 100, 3),
    ('Banana', 'Lakatan', 3, 300, 3),
    ('Mango', 'Carabao', 3, 150, 2),
    ('Peanut', 'BPI Pn 9', 5, 110, 2),
    ('Cassava', 'Golden Yellow', 4, 240, 3),
    ('Sweet Potato', 'NSIC Sp 30', 4, 120, 3),
    ('Watermelon', 'Sweet 16', 3, 85, 2);

INSERT INTO plantings (
    farm_id,
    crop_id,
    planting_date,
    area_planted_ha,
    seed_quantity_kg,
    expected_harvest_date,
    status_id
)
VALUES
    (11, 1, '2026-02-09', 0.50, 20.00, '2026-06-04', 4),
    (12, 2, '2026-02-12', 0.60, 25.00, '2026-05-23', 4),
    (13, 3, '2026-02-15', 0.70, 30.00, '2026-05-16', 4),
    (14, 4, '2026-02-18', 0.80, 35.00, '2026-05-29', 4),
    (15, 5, '2026-02-21', 0.90, 40.00, '2026-12-18', 4),
    (1, 6, '2026-02-24', 1.00, 45.00, '2026-07-24', 4),
    (2, 7, '2026-02-27', 1.10, 10.00, '2026-06-17', 4),
    (3, 8, '2026-03-02', 1.20, 15.00, '2026-10-28', 4),
    (4, 9, '2026-03-05', 1.30, 20.00, '2026-07-03', 4),
    (5, 10, '2026-03-08', 1.40, 25.00, '2026-06-01', 4),
    (6, 1, '2026-03-11', 0.50, 30.00, '2026-07-04', 2),
    (7, 2, '2026-03-14', 0.60, 35.00, '2026-06-22', 2),
    (8, 3, '2026-03-17', 0.70, 40.00, '2026-06-15', 2),
    (9, 4, '2026-03-20', 0.80, 45.00, '2026-06-28', 2),
    (10, 5, '2026-03-23', 0.90, 10.00, '2027-01-17', 2),
    (11, 6, '2026-03-26', 1.00, 15.00, '2026-08-23', 2),
    (12, 7, '2026-03-29', 1.10, 20.00, '2026-07-17', 2),
    (13, 8, '2026-04-01', 1.20, 25.00, '2026-11-27', 2),
    (14, 9, '2026-04-04', 1.30, 30.00, '2026-07-04', 2),
    (15, 10, '2026-04-07', 1.40, 35.00, '2027-01-23', 2);

INSERT INTO harvests (
    planting_id,
    harvest_date,
    quantity_kg,
    selling_price_per_kg,
    quality_grade_id,
    remark_id
)
VALUES
    (1, '2026-05-07', 900.00, 22.00, 1, 1),
    (2, '2026-04-25', 1025.00, 25.50, 2, 2),
    (3, '2026-04-18', 1150.00, 29.00, 3, 3),
    (4, '2026-05-01', 1275.00, 32.50, 1, 1),
    (5, '2026-11-20', 1400.00, 36.00, 2, 2),
    (6, '2026-06-26', 1525.00, 39.50, 3, 3),
    (7, '2026-05-20', 1650.00, 43.00, 1, 1),
    (8, '2026-09-30', 1775.00, 46.50, 2, 2),
    (9, '2026-06-05', 1900.00, 22.00, 3, 3),
    (10, '2026-05-04', 2025.00, 25.50, 1, 1),
    (11, '2026-06-06', 2150.00, 29.00, 2, 2),
    (12, '2026-05-25', 2275.00, 32.50, 3, 3),
    (13, '2026-05-18', 2400.00, 36.00, 1, 1),
    (14, '2026-05-31', 2525.00, 39.50, 2, 2),
    (15, '2026-12-20', 2650.00, 43.00, 3, 3),
    (16, '2026-07-26', 2775.00, 46.50, 1, 1),
    (17, '2026-06-19', 2900.00, 22.00, 2, 2),
    (18, '2026-10-30', 3025.00, 25.50, 3, 3),
    (19, '2026-07-05', 3150.00, 29.00, 1, 1),
    (20, '2026-06-03', 3275.00, 32.50, 2, 2);

COMMIT;
