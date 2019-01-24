PRAGMA foreign_keys = ON;

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
    CONSTRAINT listings_primary PRIMARY KEY (station_id, air_time)
);

CREATE TABLE programs (
    program_id  VARCHAR,
    resource_id INTEGER,
    title       VARCHAR NOT NULL,
    description TEXT,
    show_type   VARCHAR NOT NULL,
    CONSTRAINT programs_primary PRIMARY KEY (program_id)
);

CREATE TABLE casts (
   program_id VARCHAR,
   name       VARCHAR NOT NULL,
   role       VARCHAR NOT NULL,
   billing    INTEGER NOT NULL,
   CONSTRAINT casts_primary     PRIMARY KEY (program_id, name, role)
   CONSTRAINT casts_foreign     FOREIGN KEY (program_id) REFERENCES programs(program_id) ON DELETE CASCADE
);

CREATE TABLE crews (
   program_id VARCHAR,
   name       VARCHAR NOT NULL,
   role       VARCHAR NOT NULL,
   billing    INTEGER NOT NULL,
   CONSTRAINT crews_primary     PRIMARY KEY (program_id, name, role)
   CONSTRAINT crews_foreign     FOREIGN KEY (program_id) REFERENCES programs(program_id) ON DELETE CASCADE
);
