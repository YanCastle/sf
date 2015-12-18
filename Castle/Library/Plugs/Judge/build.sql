drop table if exists {$PREFIX}judge;
drop table if exists {$PREFIX}judge_log;
create table {$PREFIX}judge
(
   JudgeID              int(11) not null auto_increment,
   Title                char(250) not null,
   Content              text not null,
   Attach               text,
   `CreateTime`               int(10) not null,
   LastTime             int(10),
   PassTime             int(10),
   PassMethod           int(11) comment '1自动:0手动',
   PassUID              int(11),
   primary key (JudgeID)
);
create index Titime on {$PREFIX}judge
(
   Title
);
create index `CreateTime` on {$PREFIX}judge
(
   `CreateTime`
);
create index PassTime on {$PREFIX}judge
(
   LastTime
);
create table {$PREFIX}judge_log
(
   LogID                int(11) not null auto_increment,
   JudgeID              int(11) not null,
   `Time`                 int(10) not null,
   Result               int(11) not null,
   Memo                 text,
   Extend               text,
   UID                  int(11),
   Method               int(11) not null default 0,
   primary key (LogID)
);
create index JudgeID on {$PREFIX}judge_log
(
   JudgeID
);
create index Time on {$PREFIX}judge_log
(
   `Time`
);
alter table {$PREFIX}judge_log add constraint FK_JudgeID foreign key (JudgeID)
      references {$PREFIX}judge (JudgeID) on delete restrict on update restrict;
