/*==============================================================*/
/* Table: prefix_user_access_dic                                */
/*==============================================================*/
create table prefix_user_access_dic
(
   AID                  int unsigned not null auto_increment,
   Title                char(250) not null,
   Module               char(50) not null,
   Class                char(50) not null,
   Action               char(50) not null,
   Type                 char(50) not null default 'Controller' comment 'Controller/Model/Object',
   AGID                 char(50) not null,
   primary key (AID)
)ENGINE=MyISAM  DEFAULT CHARSET=utf8;

/*==============================================================*/
/* Index: MCAT                                                  */
/*==============================================================*/
create unique index MCAT on prefix_user_access_dic
(
   Module,
   Class,
   Action,
   Type
);

/*==============================================================*/
/* Table: prefix_user_access_group_dic                          */
/*==============================================================*/
create table prefix_user_access_group_dic
(
   AGID                 int unsigned not null auto_increment,
   Title                char(50) not null,
   primary key (AGID)
)ENGINE=MyISAM  DEFAULT CHARSET=utf8;

/*==============================================================*/
/* Table: prefix_user_group                                     */
/*==============================================================*/
create table prefix_user_group
(
   LID                  int unsigned not null auto_increment,
   GID                  int unsigned not null,
   UID                  int unsigned not null,
   primary key (LID)
)ENGINE=MyISAM  DEFAULT CHARSET=utf8;

/*==============================================================*/
/* Index: UIDGID                                                */
/*==============================================================*/
create unique index UIDGID on prefix_user_group
(
   GID,
   UID
);

/*==============================================================*/
/* Table: prefix_user_group_access                              */
/*==============================================================*/
create table prefix_user_group_access
(
   LID                  int unsigned not null auto_increment,
   GID                  int unsigned not null,
   AID                  int unsigned not null,
   `Condition`          varchar(1000) not null default '“”',
   primary key (LID)
)ENGINE=MyISAM  DEFAULT CHARSET=utf8;

/*==============================================================*/
/* Index: GIDAID                                                */
/*==============================================================*/
create index GIDAID on prefix_user_group_access
(
   AID,
   GID
);

/*==============================================================*/
/* Table: prefix_user_group_dic                                 */
/*==============================================================*/
create table prefix_user_group_dic
(
   GID                  int unsigned not null auto_increment,
   Title                char(250) not null,
   Sort                 int not null default 0,
   PGID                 int unsigned not null default 0,
   primary key (GID)
)ENGINE=MyISAM  DEFAULT CHARSET=utf8;

/*==============================================================*/
/* Index: Sort                                                  */
/*==============================================================*/
create index Sort on prefix_user_group_dic
(
   Sort
);

/*==============================================================*/
/* Index: Title                                                 */
/*==============================================================*/
create index Title on prefix_user_group_dic
(
   Title
);
