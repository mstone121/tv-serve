CREATE TABLE md5s (
    station_id INTEGER,
    date       DATE,
    md5        VARCHAR NOT NULL,
    CONSTRAINT md5s_primary     PRIMARY KEY (station_id, date)
);

CREATE TABLE listings (
    station_id INTEGER,
    program_id VARCHAR,
    air_time   DATETIME,
    duration   INTEGER NOT NULL,
    CONSTRAINT listings_primary PRIMARY KEY (station_id, program_id, air_time)
)
