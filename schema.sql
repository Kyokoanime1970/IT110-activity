-- First create the database named classwall in pgAdmin or psql.
-- Then connect to classwall and run this file.

CREATE TABLE IF NOT EXISTS messages (
    id SERIAL PRIMARY KEY,
    author VARCHAR(100) NOT NULL,
    body TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
