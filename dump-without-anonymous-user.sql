create table if not exists blueprints
(
    id int unsigned auto_increment
    primary key,
    id_author int unsigned null,
    slug varchar(100) not null,
    file_id varchar(100) not null,
    title varchar(255) not null,
    type enum('animation', 'behavior_tree', 'blueprint', 'material', 'metasound', 'niagara') default 'blueprint' not null,
    ue_version char(5) default '4.0' not null,
    current_version int unsigned not null,
    thumbnail varchar(255) null,
    description longtext null,
    exposure enum('public', 'unlisted', 'private') default 'public' not null,
    expiration datetime null,
    tags varchar(255) null,
    video varchar(255) null,
    video_provider varchar(255) null,
    comments_hidden tinyint(1) default '0',
    comments_closed tinyint(1) default '0',
    comments_count int unsigned default '0',
    created_at datetime not null,
    updated_at datetime null,
    published_at datetime null,
    deleted_at datetime null,
    constraint file_id_UNIQUE
    unique (file_id),
    constraint slug_UNIQUE
    unique (slug)
    )
    charset=utf8mb4;

create table if not exists blueprints_version
(
    id int unsigned auto_increment
    primary key,
    id_blueprint int unsigned not null,
    version int unsigned not null,
    reason text not null,
    created_at datetime not null,
    updated_at datetime null,
    published_at datetime null
)
    charset=utf8mb4;

create table if not exists comments
(
    id int unsigned auto_increment
    primary key,
    id_author int unsigned null,
    id_blueprint int unsigned not null,
    name_fallback varchar(255) null,
    content text not null,
    created_at datetime not null
    )
    charset=utf8mb4;

create table if not exists sessions
(
    id varchar(128) not null
    primary key,
    id_user int unsigned null,
    last_access datetime not null,
    content text not null
    )
    charset=utf8mb4;

create table if not exists tags
(
    id int unsigned auto_increment
    primary key,
    name varchar(100) not null,
    slug varchar(100) not null,
    constraint slug_UNIQUE
    unique (slug)
    )
    charset=utf8mb4;

create table if not exists users
(
    id int unsigned auto_increment
    primary key,
    username varchar(100) not null,
    password text null,
    slug varchar(100) not null,
    email varchar(100) null,
    password_reset varchar(255) null,
    password_reset_at datetime null,
    grade enum('member', 'admin') default 'member' not null,
    avatar varchar(255) null,
    remember_token char(255) null,
    created_at datetime not null,
    confirmed_token char(255) null,
    confirmed_sent_at datetime null,
    confirmed_at datetime null,
    last_login_at datetime null,
    constraint email_UNIQUE
    unique (email),
    constraint username_UNIQUE
    unique (username),
    constraint slug_UNIQUE
    unique (slug),
    constraint remember_token_UNIQUE
    unique (remember_token),
    constraint confirmed_token_UNIQUE
    unique (confirmed_token)
    )
    charset=utf8mb4;

create table if not exists users_api
(
    id_user int unsigned not null
    primary key,
    api_key varchar(100) not null,
    constraint api_key_UNIQUE
    unique (api_key)
    )
    charset=utf8mb4;

create table if not exists users_infos
(
    id_user int unsigned not null
    primary key,
    count_public_blueprint int unsigned default 0 not null,
    count_public_comment int unsigned default 0 not null,
    count_private_blueprint int unsigned default 0 not null,
    count_private_comment int unsigned default 0 not null,
    bio text null,
    link_website varchar(255) null,
    link_facebook varchar(255) null,
    link_twitter varchar(255) null,
    link_github varchar(255) null,
    link_twitch varchar(255) null,
    link_unreal varchar(255) null,
    link_youtube varchar(255) null
    )
    charset=utf8mb4;