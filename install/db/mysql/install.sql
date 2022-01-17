create table if not exists perfcode_priceupdate_params
(
    ID int not null auto_increment,
    VALUE text not null default '',
    primary key (ID)
);
