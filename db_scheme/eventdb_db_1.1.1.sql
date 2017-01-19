﻿create table  if not exists draw_awards(
    award_id int(5) not null primary key auto_increment,
    award_type varchar(10) not null default 'normal',
    goods_id int(11) default '0',
    goods_quantity int(11) default '0',
    award_name varchar(255) default '',
    award_price double(8,2) default '0',
    num int(11) default '0',
    stock int(11) default '0',
    update_time timeStamp
);

create table  if not exists draw_logs(
    id int(11) not null primary key auto_increment,
    user_id int(11) not null,
    award_id int(11) default '0',
    create_time timeStamp
);

create table  if not exists draw_user(
    user_id int(11) not null primary key,
    counts int(11) default '0',
    create_time timeStamp,
    update_time timeStamp
);

create table  if not exists draw_user_log(
    id int(11) not null primary key auto_increment,
    user_id int(11) not null,
    counts int(11) default '0',
    notes text ,
    create_time timeStamp
);

insert into draw_awards values(1,'bk',0,50,'50贝壳','1.00',10000,10000,'2016-12-26 14:05:00'),
(2,'vip',2699,1,'口袋故事1天VIP','0.83',10000,10000,'2016-12-26 14:05:00'),
(3,'vip',2699,3,'口袋故事3天VIP','2.50',10000,10000,'2016-12-26 14:05:00'),
(4,'vip',2699,7,'口袋故事7天VIP','5.83',10000,10000,'2016-12-26 14:05:00'),
(5,'vip',2699,15,'口袋故事15天VIP','12.5',10000,10000,'2016-12-26 14:05:00'),
(6,'vip',2699,30,'口袋故事30天VIP','25.00',10000,10000,'2016-12-26 14:05:00');

