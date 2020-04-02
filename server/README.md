# Live Tracking Server Configuration

Ubuntu Linux (18.04 LTS) is used as the operating system.


## Lighttpd

Lighttpd is used as a web server and proxy.

Configuration: `/etc/lighttpd/lighttpd.conf`

Change: `your-domain.local`to your domain.


## InfluxDB

InfluxDB is used as Timeseries database.

Configuration: `/etc/influxdb/influxdb.conf`


## SQLite

SQLite3 is used as database.

Database: `/var/lib/sqlite/users.sqlite`

Don't forget: The folder that houses the database file must be writeable.

    mkdir /var/lib/sqlite
    chown www-data:www-data /var/lib/sqlite