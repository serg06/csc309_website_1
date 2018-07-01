DROP TABLE IF EXISTS vote, restaurant, appuser CASCADE;
DROP DOMAIN IF EXISTS WHITELISTED;

CREATE DOMAIN WHITELISTED AS VARCHAR(50) CHECK (VALUE ~ '^[a-zA-Z0-9_]+$');

-- Users of the app and their profiles
CREATE TABLE appuser (
  id        WHITELISTED PRIMARY KEY,
  password  CHAR(32)    NOT NULL,
  firstName WHITELISTED NOT NULL,
  lastName  WHITELISTED NOT NULL,
  age       INTEGER     NOT NULL CHECK ((0 < age) AND (age < 100))
);

-- Restaurants and their scores
CREATE TABLE restaurant (
  rid      SERIAL PRIMARY KEY,
  rname    VARCHAR(50)            NOT NULL,
  score    DECIMAL DEFAULT 1200.0 NOT NULL,
  velocity DECIMAL DEFAULT 0.0    NOT NULL,
  wins     INTEGER DEFAULT 0      NOT NULL,
  losses   INTEGER DEFAULT 0      NOT NULL,
  ties     INTEGER DEFAULT 0      NOT NULL
);

-- Vote history
CREATE TABLE vote (
  rid1 INTEGER REFERENCES restaurant (rid),
  rid2 INTEGER REFERENCES restaurant (rid),
  uid  WHITELISTED REFERENCES appuser (id),
  PRIMARY KEY (rid1, rid2, uid),
  CHECK (rid1 < rid2) -- always insert like this, to prevent duplicates, speed up queries, etc
);

-- create user for TA testing
INSERT INTO appuser VALUES ('auser', MD5('apassword'), 'afirstname', 'alastname', 25);

-- create restaurant entries
\COPY restaurant(rname) from 'restaurants.txt';

-- (replace '' with ')
UPDATE restaurant
SET rname = replace(rname, '''''', '''');
