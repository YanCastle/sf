/*==============================================================*/
/* Table: prefix_user_access_dic                                */
/*==============================================================*/
CREATE TABLE prefix_user_access_dic
(
  AID    INT UNSIGNED NOT NULL AUTO_INCREMENT,
  Title  CHAR(250)    NOT NULL,
  Module CHAR(50)     NOT NULL,
  Class  CHAR(50)     NOT NULL,
  Action CHAR(50)     NOT NULL,
  Type   CHAR(50)     NOT NULL DEFAULT 'Controller'
  COMMENT 'Controller/Model/Object',
  AGID   CHAR(50)     NOT NULL,
  PRIMARY KEY (AID)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8;

/*==============================================================*/
/* Index: MCAT                                                  */
/*==============================================================*/
CREATE UNIQUE INDEX MCAT
  ON prefix_user_access_dic
  (
    Module,
    Class,
    Action,
    Type
  );

/*==============================================================*/
/* Table: prefix_user_access_group_dic                          */
/*==============================================================*/
CREATE TABLE prefix_user_access_group_dic
(
  AGID  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  Title CHAR(50)     NOT NULL,
  PRIMARY KEY (AGID)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8;

/*==============================================================*/
/* Table: prefix_user_group                                     */
/*==============================================================*/
CREATE TABLE prefix_user_group
(
  LID INT UNSIGNED NOT NULL AUTO_INCREMENT,
  GID INT UNSIGNED NOT NULL,
  UID INT UNSIGNED NOT NULL,
  PRIMARY KEY (LID)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8;

/*==============================================================*/
/* Index: UIDGID                                                */
/*==============================================================*/
CREATE UNIQUE INDEX UIDGID
  ON prefix_user_group
  (
    GID,
    UID
  );

/*==============================================================*/
/* Table: prefix_user_group_access                              */
/*==============================================================*/
CREATE TABLE prefix_user_group_access
(
  LID         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  GID         INT UNSIGNED  NOT NULL,
  AID         INT UNSIGNED  NOT NULL,
  `Condition` VARCHAR(1000) NOT NULL DEFAULT '“”',
  PRIMARY KEY (LID)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8;

/*==============================================================*/
/* Index: GIDAID                                                */
/*==============================================================*/
CREATE INDEX GIDAID
  ON prefix_user_group_access
  (
    AID,
    GID
  );

/*==============================================================*/
/* Table: prefix_user_group_dic                                 */
/*==============================================================*/
CREATE TABLE prefix_user_group_dic
(
  GID   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  Title CHAR(250)    NOT NULL,
  Sort  INT          NOT NULL DEFAULT 0,
  PGID  INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (GID)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8;

/*==============================================================*/
/* Index: Sort                                                  */
/*==============================================================*/
CREATE INDEX Sort
  ON prefix_user_group_dic
  (
    Sort
  );

/*==============================================================*/
/* Index: Title                                                 */
/*==============================================================*/
CREATE INDEX Title
  ON prefix_user_group_dic
  (
    Title
  );

CREATE VIEW `prefix_user_access_search` AS
  SELECT
    `prefix_user_group_access`.`GID`       AS `GID`,
    `prefix_user_group_access`.`Condition` AS `Condition`,
    `prefix_user_access_dic`.`AID`         AS `AID`,
    `prefix_user_access_dic`.`Title`       AS `Title`,
    `prefix_user_access_dic`.`Module`      AS `Module`,
    `prefix_user_access_dic`.`Class`       AS `Class`,
    `prefix_user_access_dic`.`Action`      AS `Action`,
    `prefix_user_access_dic`.`Type`        AS `Type`,
    `prefix_user_access_dic`.`AGID`        AS `AGID`,
    `prefix_user_group`.`UID`              AS `UID`
  FROM ((`prefix_user_group_access`
    JOIN `prefix_user_access_dic` ON ((`prefix_user_group_access`.`AID` = `prefix_user_access_dic`.`AID`))) JOIN
    `prefix_user_group` ON ((`prefix_user_group`.`GID` = `prefix_user_group_access`.`GID`)));