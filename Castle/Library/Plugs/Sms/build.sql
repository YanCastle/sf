drop table if exists {$PREFIX}sms_driver;

drop table if exists {$PREFIX}sms_log;

drop table if exists {$PREFIX}sms_template;

/*==============================================================*/
/* Table: {$PREFIX}sms_driver                                   */
/*==============================================================*/
create table {$PREFIX}sms_driver
(
   DID                  int(11) not null auto_increment,
   DClass               char(250),
   DConfig              text,
   primary key (DID)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*==============================================================*/
/* Table: {$PREFIX}sms_log                                      */
/*==============================================================*/
create table {$PREFIX}sms_log
(
   LID                  int(11) not null auto_increment,
   Time                 int(10) not null,
   Sender               char(50),
   Receiver             text not null,
   Content              char(250) not null,
   `Data`                 text,
   Success              tinyint(1) not null default 0 comment '1:成功，0:失败',
   TID                  int(11),
   primary key (LID)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*==============================================================*/
/* Table: {$PREFIX}sms_template                                 */
/*==============================================================*/
create table {$PREFIX}sms_template
(
   TID                  int(11) not null auto_increment,
   `Name`                 char(250) not null,
   Content              char(250) not null,
   Time                 int(10) not null,
   `User`                 char(50),
   DriverTemplateID     char(50),
   primary key (TID)
)ENGINE=MyISAM DEFAULT CHARSET=utf8;

alter table {$PREFIX}sms_log add constraint FK_Reference_1 foreign key (TID)
      references {$PREFIX}sms_template (TID) on delete restrict on update restrict;
