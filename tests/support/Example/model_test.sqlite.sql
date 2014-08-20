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
INSERT INTO login VALUES(1,'jayf','asfsafadf');
INSERT INTO login VALUES(2,'brucel','ljalfasdf');
INSERT INTO attachment VALUES(1,1,'test.jpg',23.2);
INSERT INTO attachment VALUES(2,1,'abc.pdf',34.03);
INSERT INTO attachment VALUES(3,2,'low.mov',3020.31);
INSERT INTO attachment VALUES(4,3,'page.txt',302.1);
INSERT INTO attachment VALUES(5,2,'flow.diagram',23.3);
INSERT INTO user VALUES(1,'Name New1408530031','80 Illust Rd. Sydney','1981-03-21');
INSERT INTO user VALUES(2,'Bruce L','300 Pitt, Sydney','1977-02-21');
INSERT INTO user VALUES(3,'Fry Steve','1 Infinite Loop, Redmond','1972-11-23');
