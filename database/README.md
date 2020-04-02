# Database Scripts

Scripts for the installation:

* `influxdb.sh`: Create InfluxDB database and admin user
* `users.sql`: SQLite `users` database schema
* `default_users.php`: Create SQL for default users with random password


Demo and test purposes:

* `vhcsv2influxdb.sh`: Simulate a live recording with a [Velo Hero](http://www.velohero.com/) CSV export


## Backup

#### SQLite

`sqlite3 /var/lib/sqlite/users.sqlite .dump > /private-backup/influxdb/dump.sql`

#### InfluxDB

`cp -r -p /var/lib/influxdb /private-backup/influxdb`

## Restore

#### SQLite

```
sqlite3 /var/lib/sqlite/users.sqlite < /private-backup/sqlite/dump.sql
```

#### InfluxDB

```
service influxd stop
cp -r -p /private-backup/influxdb /var/lib/influxdb
```