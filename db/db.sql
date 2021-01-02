DROP TABLE IF EXISTS top250;

/*==============================================================*/
/* Table: top250                                                */
/*==============================================================*/
CREATE TABLE top250
(
   id                   INT NOT NULL AUTO_INCREMENT,
   img                  VARCHAR(100) COMMENT 'url',
   `name`                 VARCHAR(50) COMMENT '电影名称',
   director             VARCHAR(50) COMMENT '导演',
   star                 VARCHAR(20) COMMENT '评分',
   `quote`                VARCHAR(50) COMMENT '标语',
   m_id                 VARCHAR(30) COMMENT '电影的id',
   order_num            int comment '排序字段',
   PRIMARY KEY (id)
)ENGINE=INNODB DEFAULT CHARSET=utf8;

ALTER TABLE top250 COMMENT '豆瓣top250数据';

drop table if exists movie_detail;

/*==============================================================*/
/* Table: movie_detail                                          */
/*==============================================================*/
create table movie_detail
(
    id                   int not null auto_increment,
    title                varchar(50) comment '标题',
    img                  varchar(255) comment 'url',
    director             varchar(100) comment '导演',
    scriptwriter         varchar(255) comment '编剧',
    actor                varchar(255) comment '演员',
    type                 varchar(100) comment '类别',
    date                 varchar(255) comment '上映时间',
    runtime              varchar(50) comment '电影时长',
    rating               varchar(30) comment '评分',
    summary              text comment '描述',
    m_id                 varchar(50) comment '电影id',
    primary key (id)
)ENGINE=INNODB DEFAULT CHARSET=utf8;

alter table movie_detail comment '电影详情';


DROP TABLE IF EXISTS movie_reviews;

/*==============================================================*/
/* Table: movie_reviews                                         */
/*==============================================================*/
CREATE TABLE movie_reviews
(
    id                   INT NOT NULL AUTO_INCREMENT,
    avatar               VARCHAR(255) COMMENT '头像url',
    `name`                 VARCHAR(100) COMMENT '名称',
    rating               VARCHAR(50) COMMENT '推荐',
    `date`                 VARCHAR(100) COMMENT '时间',
    content              TEXT COMMENT '内容',
    m_id                 VARCHAR(50) COMMENT '电影id',
    order_num            int comment '排序字段',
    PRIMARY KEY (id)
)ENGINE=INNODB DEFAULT CHARSET=utf8;

ALTER TABLE movie_reviews COMMENT '电影评论';

drop table if exists playing;

/*==============================================================*/
/* Table: playing                                               */
/*==============================================================*/
create table playing
(
    id                   int not null auto_increment,
    m_id                 varchar(100) comment '电影id',
    title                varchar(255) comment '标题',
    score                varchar(50) comment '评分',
    star                 varchar(20) comment '星',
    year                 varchar(50) comment '上映时间',
    duration             varchar(50) comment '时长',
    region               varchar(50) comment '地区',
    director             varchar(30) comment '导演',
    actors               varchar(255) comment '主演',
    votecount            varchar(50) comment '想看人数',
    img                  varchar(255) comment 'url',
    primary key (id)
)ENGINE=INNODB DEFAULT CHARSET=utf8;

alter table playing comment '正在上映';

DROP TABLE IF EXISTS showing;

/*==============================================================*/
/* Table: showing                                               */
/*==============================================================*/
CREATE TABLE showing
(
    id                   INT NOT NULL AUTO_INCREMENT,
    m_id                 VARCHAR(20) COMMENT '电影id',
    img                  VARCHAR(255) COMMENT 'url',
    title                VARCHAR(100) COMMENT '标题',
    `date`                 VARCHAR(50) COMMENT '日期',
    plot                 VARCHAR(50) COMMENT '类型',
    region               VARCHAR(50) COMMENT '地区',
    see                  VARCHAR(50) COMMENT '观看',
    PRIMARY KEY (id)
)ENGINE=INNODB DEFAULT CHARSET=utf8;

ALTER TABLE showing COMMENT '即将上映';



