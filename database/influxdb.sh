#!/usr/bin/env bash

#
# Create 'public' user with known password 'livetracking'.
# Create random user with random password and all privileges (admin).
#

# command_exists() tells if a given command exists.
function command_exists() {
	command -v "$1" >/dev/null 2>&1
}

if ! command_exists influx; then
	echo "!!! 'influx' is needed. Please install 'influx'."
	exit 1
fi
if ! command_exists perl; then
	echo "!!! 'perl' is needed. Please install 'perl'."
	exit 1
fi

MY_USERNAME=$(perl -le 'print map { ("A".."Z", "a".."z", 0..10, "_")[rand 63] } 1..24')
MY_PASSWORD=$(perl -le 'print map { ("A".."Z", "a".."z", 0..10, "-", "_")[rand 64] } 1..60')

echo
echo "*** Create database 'root' for misc stuff"
influx -execute "CREATE DATABASE \"root\" WITH DURATION 4w REPLICATION 1 NAME \"4weeks\";"

echo "*** Create user with password and all privileges (admin)"
echo "    This user is used to connect the web interface to the database."
echo "    Store the credentials in the 'src/settings.php'."
echo ">> Username: $MY_USERNAME"
echo ">> Passsword: $MY_PASSWORD"
influx --database "root" -execute "CREATE USER \"$MY_USERNAME\" WITH PASSWORD '$MY_PASSWORD' WITH ALL PRIVILEGES;" 

echo
echo "*** Create database 'public'"
influx -execute "CREATE DATABASE \"public\" WITH DURATION 8h REPLICATION 1 NAME \"8hours\";"
echo "*** Create user 'public' with password 'livetracking' and without any privileges"
influx --database "public" -execute "CREATE USER public WITH PASSWORD 'livetracking';" 

echo
echo "*** Show users:"
echo
influx --database "root" -execute "SHOW USERS;" 


echo
echo "!!! Manual activity:"
echo "!!! Enable authentication by setting the 'auth-enabled' option to 'true' in the [http] section of the InfluxDB configuration file ('influxdb.conf')."
echo

echo
echo "--- If you need another administrator, just run the script again."
echo
