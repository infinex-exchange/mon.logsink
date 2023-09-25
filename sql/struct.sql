CREATE EXTENSION IF NOT EXISTS timescaledb;

CREATE ROLE "mon.logsink" LOGIN PASSWORD 'password';

create table logs(
    time timestamptz(6) not null,
    hostname varchar(64) not null,
    service varchar(64) not null,
    instance integer not null,
    level smallint not null,
    msg text not null
);
SELECT create_hypertable('logs', 'time');
SELECT add_retention_policy('logs', INTERVAL '2 months');

GRANT INSERT ON logs TO "mon.logsink";