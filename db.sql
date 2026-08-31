-- Better Bracket PostgreSQL schema and tournament seed data.
-- Create a database first, then run this file with psql.

-- DROP DATABASE "BetterBracket";
/*
CREATE DATABASE "BetterBracket"
  WITH OWNER = postgres
       ENCODING = 'UTF8'
       TABLESPACE = pg_default
       LC_COLLATE = 'C'
       LC_CTYPE = 'C'
       CONNECTION LIMIT = -1;
*/

CREATE TABLE users
(
  id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  email character varying(125) NOT NULL,
  password varchar(256) NOT NULL,
  date_joined timestamp without time zone DEFAULT statement_timestamp(),
  CONSTRAINT emails UNIQUE(email)
);

CREATE TABLE users_profile
(
  user_id integer PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE,
  first character varying(125) NOT NULL,
  last character varying(125) NOT NULL,
  description TEXT,
  caption character varying(125)
);

CREATE TABLE groups
(
  id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  name character varying(125) NOT NULL,
  date_created timestamp without time zone DEFAULT statement_timestamp(),
  CONSTRAINT groups_name_unique UNIQUE(name)
);

CREATE TABLE groups_profile
(
  group_id integer PRIMARY KEY REFERENCES groups(id) ON DELETE CASCADE,
  picturelocation character varying(256),
  description TEXT,
  caption character varying(125)
);


CREATE TABLE user_groups
(
  group_id integer NOT NULL REFERENCES groups(id) ON DELETE CASCADE,
  user_id integer NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  PRIMARY KEY (group_id, user_id)
);

CREATE TABLE teams  (
  id INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  team_name character varying(126) NOT NULL,
  seed integer NOT NULL CHECK (seed BETWEEN 1 AND 16),
  region varchar(8) NOT NULL CHECK (region IN ('south', 'west', 'east', 'midwest')),
  CONSTRAINT teams_region_seed_unique UNIQUE(region, seed)
);

CREATE TABLE games  (
  id INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  team_id_1 INTEGER NOT NULL REFERENCES teams(id),
  team_id_2 INTEGER NOT NULL REFERENCES teams(id),
  date_played timestamp NOT NULL,
  CONSTRAINT games_distinct_teams CHECK (team_id_1 <> team_id_2)
);

CREATE TABLE scores  (
  game_id INTEGER PRIMARY KEY REFERENCES games (id) ON DELETE CASCADE,
  score SMALLINT NOT NULL CHECK (score >= 0),
  score_2 SMALLINT NOT NULL CHECK (score_2 >= 0)
);





CREATE TABLE picks
(
  id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  group_id INTEGER NOT NULL REFERENCES groups(id) ON DELETE CASCADE,
  team_id INTEGER NOT NULL REFERENCES teams(id),
  region SMALLINT NOT NULL CHECK (region BETWEEN 0 AND 4),
  round SMALLINT NOT NULL CHECK (round BETWEEN 1 AND 6),
  game SMALLINT NOT NULL CHECK (game BETWEEN 1 AND 8),
  team SMALLINT NOT NULL CHECK (team BETWEEN 1 AND 2),
  CONSTRAINT picks_location_unique UNIQUE (user_id, group_id, region, round, game, team)
);

/*

Adding Team Data

 */
INSERT INTO teams (team_name,seed,region)VALUES('Florida',1,'south');
INSERT INTO teams (team_name,seed,region)VALUES('Kansas',2,'south');
INSERT INTO teams (team_name,seed,region)VALUES('Syracuse',3,'south');
INSERT INTO teams (team_name,seed,region)VALUES('UCLA',4,'south');
INSERT INTO teams (team_name,seed,region)VALUES('Virginia Commonwealth',5,'south');
INSERT INTO teams (team_name,seed,region)VALUES('Ohio State',6,'south');
INSERT INTO teams (team_name,seed,region)VALUES('New Mexico',7,'south');
INSERT INTO teams (team_name,seed,region)VALUES('Colorado',8,'south');
INSERT INTO teams (team_name,seed,region)VALUES('Pittsburgh',9,'south');
INSERT INTO teams (team_name,seed,region)VALUES('Stanford',10,'south');
INSERT INTO teams (team_name,seed,region)VALUES('Dayton',11,'south');
INSERT INTO teams (team_name,seed,region)VALUES('Stephen F. Austin',12,'south');
INSERT INTO teams (team_name,seed,region)VALUES('Tulsa',13,'south');
INSERT INTO teams (team_name,seed,region)VALUES('Western Michigan',14,'south');
INSERT INTO teams (team_name,seed,region)VALUES('Eastern Kentucky',15,'south');
INSERT INTO teams (team_name,seed,region)VALUES('Albany',16,'south');

INSERT INTO teams (team_name,seed,region)VALUES('Arizona',1,'west');
INSERT INTO teams (team_name,seed,region)VALUES('Wisconsin',2,'west');
INSERT INTO teams (team_name,seed,region)VALUES('Creighton',3,'west');
INSERT INTO teams (team_name,seed,region)VALUES('San Diego State',4,'west');
INSERT INTO teams (team_name,seed,region)VALUES('Oklahoma',5,'west');
INSERT INTO teams (team_name,seed,region)VALUES('Baylor',6,'west');
INSERT INTO teams (team_name,seed,region)VALUES('Oregon',7,'west');
INSERT INTO teams (team_name,seed,region)VALUES('Gonzaga',8,'west');
INSERT INTO teams (team_name,seed,region)VALUES('Oklahoma State',9,'west');
INSERT INTO teams (team_name,seed,region)VALUES('Brigham Young',10,'west');
INSERT INTO teams (team_name,seed,region)VALUES('Nebraska',11,'west');
INSERT INTO teams (team_name,seed,region)VALUES('North Dakota State',12,'west');
INSERT INTO teams (team_name,seed,region)VALUES('New Mexico State',13,'west');
INSERT INTO teams (team_name,seed,region)VALUES('Louisiana-Lafayette',14,'west');
INSERT INTO teams (team_name,seed,region)VALUES('American',15,'west');
INSERT INTO teams (team_name,seed,region)VALUES('Weber State',16,'west');

INSERT INTO teams (team_name,seed,region)VALUES('Virginia',1,'east');
INSERT INTO teams (team_name,seed,region)VALUES('Villanova',2,'east');
INSERT INTO teams (team_name,seed,region)VALUES('Iowa State',3,'east');
INSERT INTO teams (team_name,seed,region)VALUES('Michigan State',4,'east');
INSERT INTO teams (team_name,seed,region)VALUES('Cincinnati',5,'east');
INSERT INTO teams (team_name,seed,region)VALUES('North Carolina',6,'east');
INSERT INTO teams (team_name,seed,region)VALUES('Connecticut',7,'east');
INSERT INTO teams (team_name,seed,region)VALUES('Memphis',8,'east');
INSERT INTO teams (team_name,seed,region)VALUES('George Washington',9,'east');
INSERT INTO teams (team_name,seed,region)VALUES('St. Joseph''s',10,'east');
INSERT INTO teams (team_name,seed,region)VALUES('Providence',11,'east');
INSERT INTO teams (team_name,seed,region)VALUES('Harvard',12,'east');
INSERT INTO teams (team_name,seed,region)VALUES('Delaware',13,'east');
INSERT INTO teams (team_name,seed,region)VALUES('North Carolina Central',14,'east');
INSERT INTO teams (team_name,seed,region)VALUES('Milwaukee',15,'east');
INSERT INTO teams (team_name,seed,region)VALUES('Coastal Carolina',16,'east');

INSERT INTO teams (team_name,seed,region)VALUES('Wichita State',1,'midwest');
INSERT INTO teams (team_name,seed,region)VALUES('Michigan',2,'midwest');
INSERT INTO teams (team_name,seed,region)VALUES('Duke',3,'midwest');
INSERT INTO teams (team_name,seed,region)VALUES('Louisville',4,'midwest');
INSERT INTO teams (team_name,seed,region)VALUES('St. Louis',5,'midwest');
INSERT INTO teams (team_name,seed,region)VALUES('Massachusetts',6,'midwest');
INSERT INTO teams (team_name,seed,region)VALUES('Texas',7,'midwest');
INSERT INTO teams (team_name,seed,region)VALUES('Kentucky',8,'midwest');
INSERT INTO teams (team_name,seed,region)VALUES('Kansas State',9,'midwest');
INSERT INTO teams (team_name,seed,region)VALUES('Arizona State',10,'midwest');
INSERT INTO teams (team_name,seed,region)VALUES('Tennessee',11,'midwest');
INSERT INTO teams (team_name,seed,region)VALUES('NC State',12,'midwest');
INSERT INTO teams (team_name,seed,region)VALUES('Manhattan',13,'midwest');
INSERT INTO teams (team_name,seed,region)VALUES('Mercer',14,'midwest');
INSERT INTO teams (team_name,seed,region)VALUES('Wofford',15,'midwest');
INSERT INTO teams (team_name,seed,region)VALUES('Cal Poly',16,'midwest');


/*Games and scores 
<tr>
  <td>([A-Za-z \.\-\']+)</td>
  <td>(.+)</td>
  <td>([A-Za-z \.\-\']+)</td>
  <td>(.+)</td>
</tr>
INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = '$1'),
(SELECT id from teams where team_name = '$3'),
'2014-03-20');\n INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = '$1') AND team_id_2 = (SELECT id from teams where team_name = '$3')),
$2,$4);

*/


INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'Wisconsin'),
(SELECT id from teams where team_name = 'American'),
'2014-03-20');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'Wisconsin') AND team_id_2 = (SELECT id from teams where team_name = 'American')),
75,35);
INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'Pittsburgh'),
(SELECT id from teams where team_name = 'Colorado'),
'2014-03-20');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'Pittsburgh') AND team_id_2 = (SELECT id from teams where team_name = 'Colorado')),
77,48);
INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'Harvard'),
(SELECT id from teams where team_name = 'Cincinnati'),
'2014-03-20');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'Harvard') AND team_id_2 = (SELECT id from teams where team_name = 'Cincinnati')),
61,57);
INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'Syracuse'),
(SELECT id from teams where team_name = 'Western Michigan'),
'2014-03-20');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'Syracuse') AND team_id_2 = (SELECT id from teams where team_name = 'Western Michigan')),
77,53);
INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'Oregon'),
(SELECT id from teams where team_name = 'Brigham Young'),
'2014-03-20');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'Oregon') AND team_id_2 = (SELECT id from teams where team_name = 'Brigham Young')),
87,68);
INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'Florida'),
(SELECT id from teams where team_name = 'Albany'),
'2014-03-20');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'Florida') AND team_id_2 = (SELECT id from teams where team_name = 'Albany')),
67,55);
INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'Michigan State'),
(SELECT id from teams where team_name = 'Delaware'),
'2014-03-20');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'Michigan State') AND team_id_2 = (SELECT id from teams where team_name = 'Delaware')),
93,78);
INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'Connecticut'),
(SELECT id from teams where team_name = 'St. Joseph''s'),
'2014-03-20');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'Connecticut') AND team_id_2 = (SELECT id from teams where team_name = 'St. Joseph''s')),
89,81);
INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'Michigan'),
(SELECT id from teams where team_name = 'Wofford'),
'2014-03-20');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'Michigan') AND team_id_2 = (SELECT id from teams where team_name = 'Wofford')),
57,40);
INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'St. Louis'),
(SELECT id from teams where team_name = 'NC State'),
'2014-03-20');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'St. Louis') AND team_id_2 = (SELECT id from teams where team_name = 'NC State')),
83,80);
INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'North Dakota State'),
(SELECT id from teams where team_name = 'Oklahoma'),
'2014-03-20');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'North Dakota State') AND team_id_2 = (SELECT id from teams where team_name = 'Oklahoma')),
80,75);
INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'Texas'),
(SELECT id from teams where team_name = 'Arizona State'),
'2014-03-20');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'Texas') AND team_id_2 = (SELECT id from teams where team_name = 'Arizona State')),
87,85);
INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'Villanova'),
(SELECT id from teams where team_name = 'Milwaukee'),
'2014-03-20');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'Villanova') AND team_id_2 = (SELECT id from teams where team_name = 'Milwaukee')),
73,53);
INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'Louisville'),
(SELECT id from teams where team_name = 'Manhattan'),
'2014-03-20');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'Louisville') AND team_id_2 = (SELECT id from teams where team_name = 'Manhattan')),
71,64);
INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'Dayton'),
(SELECT id from teams where team_name = 'Ohio State'),
'2014-03-20');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'Dayton') AND team_id_2 = (SELECT id from teams where team_name = 'Ohio State')),
60,59);
INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'San Diego State'),
(SELECT id from teams where team_name = 'New Mexico State'),
'2014-03-20');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'San Diego State') AND team_id_2 = (SELECT id from teams where team_name = 'New Mexico State')),
73,69);



/* march 21 */

INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'Mercer'),
(SELECT id from teams where team_name = 'Duke'),
'2014-03-21');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'Mercer') AND team_id_2 = (SELECT id from teams where team_name = 'Duke')),
78,71);
INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'Baylor'),
(SELECT id from teams where team_name = 'Nebraska'),
'2014-03-21');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'Baylor') AND team_id_2 = (SELECT id from teams where team_name = 'Nebraska')),
74,60);
INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'Stanford'),
(SELECT id from teams where team_name = 'New Mexico'),
'2014-03-21');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'Stanford') AND team_id_2 = (SELECT id from teams where team_name = 'New Mexico')),
58,53);
INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'Arizona'),
(SELECT id from teams where team_name = 'Weber State'),
'2014-03-21');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'Arizona') AND team_id_2 = (SELECT id from teams where team_name = 'Weber State')),
68,59);
INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'Tennessee'),
(SELECT id from teams where team_name = 'Massachusetts'),
'2014-03-21');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'Tennessee') AND team_id_2 = (SELECT id from teams where team_name = 'Massachusetts')),
86,67);
INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'Creighton'),
(SELECT id from teams where team_name = 'Louisiana-Lafayette'),
'2014-03-21');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'Creighton') AND team_id_2 = (SELECT id from teams where team_name = 'Louisiana-Lafayette')),
76,66);
INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'Kansas'),
(SELECT id from teams where team_name = 'Eastern Kentucky'),
'2014-03-21');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'Kansas') AND team_id_2 = (SELECT id from teams where team_name = 'Eastern Kentucky')),
80,69);
INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'Gonzaga'),
(SELECT id from teams where team_name = 'Oklahoma State'),
'2014-03-21');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'Gonzaga') AND team_id_2 = (SELECT id from teams where team_name = 'Oklahoma State')),
85,77);
INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'Memphis'),
(SELECT id from teams where team_name = 'George Washington'),
'2014-03-21');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'Memphis') AND team_id_2 = (SELECT id from teams where team_name = 'George Washington')),
71,66);
INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'North Carolina'),
(SELECT id from teams where team_name = 'Providence'),
'2014-03-21');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'North Carolina') AND team_id_2 = (SELECT id from teams where team_name = 'Providence')),
79,77);
INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'Wichita State'),
(SELECT id from teams where team_name = 'Cal Poly'),
'2014-03-21');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'Wichita State') AND team_id_2 = (SELECT id from teams where team_name = 'Cal Poly')),
64,37);
INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'Stephen F. Austin'),
(SELECT id from teams where team_name = 'Virginia Commonwealth'),
'2014-03-21');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'Stephen F. Austin') AND team_id_2 = (SELECT id from teams where team_name = 'Virginia Commonwealth')),
77,75);
INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'Virginia'),
(SELECT id from teams where team_name = 'Coastal Carolina'),
'2014-03-21');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'Virginia') AND team_id_2 = (SELECT id from teams where team_name = 'Coastal Carolina')),
70,59);
INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'Kentucky'),
(SELECT id from teams where team_name = 'Kansas State'),
'2014-03-21');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'Kentucky') AND team_id_2 = (SELECT id from teams where team_name = 'Kansas State')),
56,49);
INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'Iowa State'),
(SELECT id from teams where team_name = 'North Carolina Central'),
'2014-03-21');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'Iowa State') AND team_id_2 = (SELECT id from teams where team_name = 'North Carolina Central')),
93,75);
INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'UCLA'),
(SELECT id from teams where team_name = 'Tulsa'),
'2014-03-21');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'UCLA') AND team_id_2 = (SELECT id from teams where team_name = 'Tulsa')),
76,59);



/* march 22 */

INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'Florida'),
(SELECT id from teams where team_name = 'Pittsburgh'),
'2014-03-22');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'Florida') AND team_id_2 = (SELECT id from teams where team_name = 'Pittsburgh')),
61,45);INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'Louisville'),
(SELECT id from teams where team_name = 'St. Louis'),
'2014-03-22');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'Louisville') AND team_id_2 = (SELECT id from teams where team_name = 'St. Louis')),
66,51);INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'Michigan'),
(SELECT id from teams where team_name = 'Texas'),
'2014-03-22');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'Michigan') AND team_id_2 = (SELECT id from teams where team_name = 'Texas')),
79,65);INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'San Diego State'),
(SELECT id from teams where team_name = 'North Dakota State'),
'2014-03-22');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'San Diego State') AND team_id_2 = (SELECT id from teams where team_name = 'North Dakota State')),
63,44);INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'Dayton'),
(SELECT id from teams where team_name = 'Syracuse'),
'2014-03-22');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'Dayton') AND team_id_2 = (SELECT id from teams where team_name = 'Syracuse')),
55,53);INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'Wisconsin'),
(SELECT id from teams where team_name = 'Oregon'),
'2014-03-22');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'Wisconsin') AND team_id_2 = (SELECT id from teams where team_name = 'Oregon')),
85,77);INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'Michigan State'),
(SELECT id from teams where team_name = 'Harvard'),
'2014-03-22');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'Michigan State') AND team_id_2 = (SELECT id from teams where team_name = 'Harvard')),
80,73);INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'Connecticut'),
(SELECT id from teams where team_name = 'Villanova'),
'2014-03-22');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'Connecticut') AND team_id_2 = (SELECT id from teams where team_name = 'Villanova')),
77,65);


/*  march 23 */
INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'Stanford'),
(SELECT id from teams where team_name = 'Kansas'),
'2014-03-23');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'Stanford') AND team_id_2 = (SELECT id from teams where team_name = 'Kansas')),
60,57);
INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'Kentucky'),
(SELECT id from teams where team_name = 'Wichita State'),
'2014-03-23');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'Kentucky') AND team_id_2 = (SELECT id from teams where team_name = 'Wichita State')),
78,76);
INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'Iowa State'),
(SELECT id from teams where team_name = 'North Carolina'),
'2014-03-23');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'Iowa State') AND team_id_2 = (SELECT id from teams where team_name = 'North Carolina')),
85,83);
INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'Tennessee'),
(SELECT id from teams where team_name = 'Mercer'),
'2014-03-23');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'Tennessee') AND team_id_2 = (SELECT id from teams where team_name = 'Mercer')),
83,63);
INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'UCLA'),
(SELECT id from teams where team_name = 'Stephen F. Austin'),
'2014-03-23');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'UCLA') AND team_id_2 = (SELECT id from teams where team_name = 'Stephen F. Austin')),
77,60);
INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'Baylor'),
(SELECT id from teams where team_name = 'Creighton'),
'2014-03-23');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'Baylor') AND team_id_2 = (SELECT id from teams where team_name = 'Creighton')),
85,55);
INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'Virginia'),
(SELECT id from teams where team_name = 'Memphis'),
'2014-03-23');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'Virginia') AND team_id_2 = (SELECT id from teams where team_name = 'Memphis')),
78,60);
INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'Arizona'),
(SELECT id from teams where team_name = 'Gonzaga'),
'2014-03-23');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'Arizona') AND team_id_2 = (SELECT id from teams where team_name = 'Gonzaga')),
84,61);

/* march 27 */
INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'Dayton'),
(SELECT id from teams where team_name = 'Stanford'),
'2014-03-27');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'Dayton') AND team_id_2 = (SELECT id from teams where team_name = 'Stanford')),
82,72);
INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'Wisconsin'),
(SELECT id from teams where team_name = 'Baylor'),
'2014-03-27');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'Wisconsin') AND team_id_2 = (SELECT id from teams where team_name = 'Baylor')),
69,52);
INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'Florida'),
(SELECT id from teams where team_name = 'UCLA'),
'2014-03-27');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'Florida') AND team_id_2 = (SELECT id from teams where team_name = 'UCLA')),
79,68);
INSERT INTO games (team_id_1,team_id_2,date_played)
VALUES(
(SELECT id from teams where team_name = 'Arizona'),
(SELECT id from teams where team_name = 'San Diego State'),
'2014-03-27');
 INSERT INTO scores (game_id,score,score_2)
VALUES(
(SELECT id from games where team_id_1 = (SELECT id from teams where team_name = 'Arizona') AND team_id_2 = (SELECT id from teams where team_name = 'San Diego State')),
70,64);



CREATE VIEW TeamsGames AS
SELECT g.id, g.date_played, t.team_name, t.id AS team_id
FROM games g
JOIN teams t ON t.id IN (g.team_id_1, g.team_id_2);

CREATE VIEW GamesScores AS
SELECT g.id AS game_id, g.date_played, t1.team_name AS team_1,
       t2.team_name AS team_2, s.score AS score_1, s.score_2
FROM games g
JOIN teams t1 ON t1.id = g.team_id_1
JOIN teams t2 ON t2.id = g.team_id_2
JOIN scores s ON s.game_id = g.id;

CREATE VIEW bracket_teams AS
SELECT id AS team_id, team_name, seed, region, 1 AS round
FROM teams;
