CREATE DATABASE IF NOT EXISTS fittrack_db;

USE fittrack_db;

CREATE TABLE profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    age INT NOT NULL,
    sex ENUM('male', 'female') NOT NULL,
    height DECIMAL(5,2) NOT NULL,
    weight DECIMAL(5,2) NOT NULL,
    activity_level VARCHAR(30) NOT NULL,
    fitness_goal VARCHAR(30) NOT NULL,
    workout_days INT DEFAULT 3,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE foods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    food_name VARCHAR(150) NOT NULL,
    calories DECIMAL(8,2) NOT NULL,
    protein DECIMAL(8,2) NOT NULL
);

CREATE TABLE food_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    profile_id INT NOT NULL,
    food_id INT NOT NULL,
    quantity DECIMAL(8,2) NOT NULL,
    log_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (profile_id) REFERENCES profiles(id),
    FOREIGN KEY (food_id) REFERENCES foods(id)
);

CREATE TABLE progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    profile_id INT NOT NULL,
    weight DECIMAL(5,2) NOT NULL,
    record_date DATE NOT NULL,
    FOREIGN KEY (profile_id) REFERENCES profiles(id)
);

INSERT INTO foods (food_name, calories, protein) VALUES
('Boiled Egg', 78, 6.3),
('Chicken Breast (100g)', 165, 31),
('White Rice (100g)', 130, 2.7),
('Banana', 105, 1.3),
('Whole Milk (250ml)', 150, 8),
('Oatmeal (100g)', 389, 16.9),
('Tuna (100g)', 132, 28);

