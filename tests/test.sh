#!/usr/bin/env bash

#
# Test everything :-)
# Create new user, do some stuff and delete user
#

MY_HOST="127.0.0.1"
MY_PORT="8080"
MY_INFLUXDB_HOST="127.0.0.1"
MY_INFLUXDB_PORT="8086"
MY_USERNAME="test__test__"
MY_NEW_USERNAME="test__new__test__"
MY_PASSWORD="Tést1234!"
MY_NEW_PASSWORD="test1234"

MY_ERRORS=0;

echo
echo "*** Ping"
if curl -f "http://$MY_HOST:$MY_PORT/ping"; then
	echo "--- OK"
else
	echo "!!! ERROR"
	MY_ERRORS=$((MY_ERRORS+1))
	exit 9
fi

echo
echo "*** Ping InfluxDB"
if curl -f "http://$MY_INFLUXDB_HOST:$MY_INFLUXDB_PORT/ping"; then
	echo "--- OK"
else
	echo "!!! ERROR"
	MY_ERRORS=$((MY_ERRORS+1))
	exit 9
fi

echo
echo "*** Register new user"
if curl -f --data "username=$MY_USERNAME" --data "password=$MY_PASSWORD" "http://$MY_HOST:$MY_PORT/register"; then
	echo "--- OK"
else
	echo "!!! ERROR"
	MY_ERRORS=$((MY_ERRORS+1))
	exit 9
fi

echo
echo "*** Register invalid username"
if curl -f --data "username=tést" --data "password=test1234" "http://$MY_HOST:$MY_PORT/register"; then
	echo "!!! ERROR"
	MY_ERRORS=$((MY_ERRORS+1))
else
	echo "--- OK"
fi

echo
echo "*** Show users"
if curl -f "http://$MY_HOST:$MY_PORT/users"; then
	echo "--- OK"
else
	echo "!!! ERROR"
	MY_ERRORS=$((MY_ERRORS+1))
fi

echo
echo "*** Show user"
if curl -f -u "$MY_USERNAME:$MY_PASSWORD" "http://$MY_HOST:$MY_PORT/user"; then
	echo "--- OK"
else
	echo "!!! ERROR"
	MY_ERRORS=$((MY_ERRORS+1))
fi

echo
echo "*** Show user without authorization"
if curl -f "http://$MY_HOST:$MY_PORT/user/$MY_USERNAME"; then
	echo "!!! ERROR"
	MY_ERRORS=$((MY_ERRORS+1))
else
	echo "--- OK"
fi

echo
echo "*** Change password"
if curl -f -u "$MY_USERNAME:$MY_PASSWORD" --data "password=$MY_PASSWORD" --data "new_password=$MY_NEW_PASSWORD" "http://$MY_HOST:$MY_PORT/user/change-password"; then
	echo "--- OK"
else
	echo "!!! ERROR"
	MY_ERRORS=$((MY_ERRORS+1))
fi

echo
echo "*** Change password back"
if curl -f -u "$MY_USERNAME:$MY_NEW_PASSWORD" --data "password=$MY_NEW_PASSWORD" --data "new_password=$MY_PASSWORD" "http://$MY_HOST:$MY_PORT/user/change-password"; then
	echo "--- OK"
else
	echo "!!! ERROR"
	MY_ERRORS=$((MY_ERRORS+1))
fi

echo
echo "*** Write to InfluxDB"
if curl -f -u "$MY_USERNAME:$MY_PASSWORD" "http://$MY_INFLUXDB_HOST:$MY_INFLUXDB_PORT/write?db=$MY_USERNAME" --data "test,bla=fa value=0.42"; then
	echo "--- OK"
else
	echo "!!! ERROR"
	MY_ERRORS=$((MY_ERRORS+1))
fi

echo
echo "*** Write to InfluxDB without authorization"
if curl -f "http://$MY_INFLUXDB_HOST:$MY_INFLUXDB_PORT/write?db=$MY_USERNAME" --data "test,bla=fa value=0.42"; then
	echo
	echo "!!! ERROR !!! ERROR !!! ERROR !!!"
	echo "!!! Enable authentication by setting the 'auth-enabled' option to 'true' in the [http] section of the InfluxDB configuration file ('influxd.conf')."
	echo
	MY_ERRORS=$((MY_ERRORS+1))
else 
	echo "--- OK"
fi

echo
echo "Test 'public' authorization:"

echo
echo "*** Write to InfluxDB"
if curl -f -u "public:livetracking" "http://$MY_INFLUXDB_HOST:$MY_INFLUXDB_PORT/write?db=$MY_USERNAME" --data "test,bla=fa value=0.42"; then
	echo "!!! ERROR"
	MY_ERRORS=$((MY_ERRORS+1))
else
	echo "--- OK"
fi


echo
echo "*** Read from InfluxDB"
if curl -f --get -u "public:livetracking" "http://$MY_INFLUXDB_HOST:$MY_INFLUXDB_PORT/query?db=$MY_USERNAME" --data-urlencode "q=SELECT * FROM test;"; then
	echo "--- OK"
else
	echo "!!! ERROR"
	MY_ERRORS=$((MY_ERRORS+1))
fi


echo
echo "*** SHOW USERS"
if curl -f -i -u "public:livetracking" http://$MY_INFLUXDB_HOST:$MY_INFLUXDB_PORT/query --data-urlencode "q=SHOW USERS"; then
	echo "!!! ERROR"
	MY_ERRORS=$((MY_ERRORS+1))
else
	echo "--- OK"
fi

echo
echo "*** SHOW DATABASES"
if curl -f -i -u "public:livetracking" http://$MY_INFLUXDB_HOST:$MY_INFLUXDB_PORT/query --data-urlencode "q=SHOW DATABASES"; then
	echo "--- OK"
else
	echo "!!! ERROR"
	MY_ERRORS=$((MY_ERRORS+1))
fi

echo
echo "*** SHOW GRANTS"
if curl -f -i -u "public:livetracking" http://$MY_INFLUXDB_HOST:$MY_INFLUXDB_PORT/query?db=public --data-urlencode "q=SHOW GRANTS FOR public"; then
	echo "!!! ERROR"
	MY_ERRORS=$((MY_ERRORS+1))
else
	echo "--- OK"
fi

echo
echo "*** CREATE RETENTION POLICY"
if curl -f -i -u "public:livetracking" http://$MY_INFLUXDB_HOST:$MY_INFLUXDB_PORT/query?db=public --data-urlencode "q=CREATE RETENTION POLICY two_hours ON public DURATION 2h REPLICATION 1 DEFAULT"; then
	echo "!!! ERROR"
	MY_ERRORS=$((MY_ERRORS+1))
else
	echo "--- OK"
fi

echo
echo "*** Delete user"
if curl -f -u "$MY_USERNAME:$MY_PASSWORD" -X DELETE --data "password=$MY_PASSWORD" "http://$MY_HOST:$MY_PORT/user"; then
	echo "--- OK"
else
	echo "!!! ERROR"
	MY_ERRORS=$((MY_ERRORS+1))
fi

if [ "$MY_ERRORS" -gt "0" ]; then
	echo
	echo "!!! +------------------+"
	echo "!!! |      ERRORS      |"
	echo "!!! +------------------+"
	echo
	exit 1
fi