drop table if exists {$PREFIX}upload;

/*==============================================================*/
/* Table: {$PREFIX}upload                                       */
/*==============================================================*/
create table {$PREFIX}upload
(
   UploadID             int(11) not null auto_increment,
   FileName             char(250),
   Extension            char(50),
   MIME                 char(50),
   Size                 int(11),
   SaveName             char(250),
   SavePath             char(250),
   FileMd5              char(50),
   UploadTime           int(11) not null,
   UploaderUID          int(11),
   primary key (UploadID)
)ENGINE=MyISAM DEFAULT CHARSET=utf8;
