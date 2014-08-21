DROP TABLE "attachment";
DROP TABLE "combined_key_table";
DROP TABLE "user";
DROP TABLE "login";
CREATE TABLE "attachment" (
  "id" int(11)  NOT NULL ,
  "user_id" int(11) NOT NULL,
  "filename" varchar(100) DEFAULT NULL,
  "size" decimal(10,2) DEFAULT NULL,
  PRIMARY KEY ("id")
);
CREATE TABLE "combined_key_table" (
  "id_1" int(11)  NOT NULL,
  "id_2" int(11) NOT NULL DEFAULT '0',
  "name" varchar(200) DEFAULT NULL,
  "user_id" int(11) DEFAULT NULL,
  PRIMARY KEY ("id_1","id_2")
);
CREATE TABLE "login" (
  "user_id" int(11)  NOT NULL,
  "username" varchar(50) NOT NULL DEFAULT '',
  "password" varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY ("user_id")
);
CREATE TABLE "user" (
  "id" int(11)  NOT NULL ,
  "name" varchar(50) DEFAULT NULL,
  "address" varchar(200) DEFAULT NULL,
  "birthdate" date DEFAULT NULL,
  PRIMARY KEY ("id")
);
