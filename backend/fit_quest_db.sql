CREATE DATABASE IF NOT EXISTS fit_quest_db;
USE fit_quest_db;

CREATE TABLE IF NOT EXISTS player (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    age INT NOT NULL,
    lvl INT DEFAULT 1,
    xp INT DEFAULT 0,
    hp INT DEFAULT 100,
    atk INT DEFAULT 10,
    arm INT DEFAULT 5,
    pts INT DEFAULT 0,
    stamina INT DEFAULT 100,
    height FLOAT DEFAULT 0,
    weight FLOAT DEFAULT 0,
    lastRefresh BIGINT DEFAULT 0,
    lastStaminaRegen BIGINT DEFAULT 0
);

CREATE TABLE IF NOT EXISTS tower (
    id INT AUTO_INCREMENT PRIMARY KEY,
    player_id INT NOT NULL,
    towerReached INT DEFAULT 1,
    FOREIGN KEY (player_id) REFERENCES player(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS quest (
    id INT AUTO_INCREMENT PRIMARY KEY,
    player_id INT NOT NULL,
    quest_id VARCHAR(50) NOT NULL,
    date_completed VARCHAR(50) NOT NULL,
    FOREIGN KEY (player_id) REFERENCES player(id) ON DELETE CASCADE
);