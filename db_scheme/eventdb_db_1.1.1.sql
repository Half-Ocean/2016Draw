create table  if not exists draw_awards(
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
    count int(11) default '0',
    create_time timeStamp,
    update_time timeStamp
);


create table  if not exists draw_user_log(
    id int(11) not null primary key auto_increment,
    user_id int(11) not null,
    count int(11) default '0',
    notes text ,
    create_time timeStamp
);