#!/usr/bin/env bash

#
# Simulate a live recording with a Velo Hero CSV export
#
# Get a public Velo Hero CSV export:
#   curl "http://app.velohero.com/export/activity/csv/11021" -o export.csv

################################################################################
#### Configuration Section
################################################################################

MY_INFLUXDB_HOST="127.0.0.1"
MY_INFLUXDB_PORT="8086"

################################################################################
#### END Configuration Section
################################################################################

function usage {
	returnCode="$1"
	echo -e "Usage: $0 -u <USERNAME> -p <PASSWORD> -f <VELOHERO_ACTIVITY_CSV_EXPORT>
	USERNAME                     = InfluxDB username
	PASSWORD                     = InfluxDB password
	VELOHERO_ACTIVITY_CSV_EXPORT = Velo Hero CSV export"
	exit "$returnCode"
}

# exit_with_failure() outputs a message before exiting the script.
function exit_with_failure() {
	echo
	echo "FAILURE: $1"
	echo
	exit 9
}

# command_exists() tells if a given command exists.
function command_exists() {
	command -v "$1" >/dev/null 2>&1
}

# check_file() check if the file exists and is readable
function check_file() {
	if [ ! -r "$1" ]; then
		exit_with_failure "Can not read CSV file '$1'"
	fi
}

to_seconds () {
	IFS=: read -r h m s <<< "$1"
	echo $(( 10#$h * 3600 + 10#$m * 60 + 10#$s ))
}

if ! command_exists curl; then
	exit_with_failure "'curl' is needed. Please install 'curl'. More details can be found at https://curl.haxx.se/"
fi

while getopts u:p:f:h opt
do
	case $opt in
		u) MY_USERNAME="$OPTARG" ;;
		p) MY_PASSWORD="$OPTARG" ;;
		f) VELOHERO_CSV_EXPORT="$OPTARG" ;;
		h) usage 0 ;;
		*) 
			echo "Invalid option: -$OPTARG"
			usage 1
			;;
	esac
done

if [ -z "$MY_USERNAME" ]; then
	exit_with_failure "Empty username. Set username with '-u <USERNAME>'."
fi
if [ -z "$MY_PASSWORD" ]; then
	exit_with_failure "Empty password. Set password with '-p <PASSWORD>'."
fi
if [ -z "$VELOHERO_CSV_EXPORT" ]; then
	exit_with_failure "Empty Velo Hero CSV export. Set file with '-f <VELOHERO_ACTIVITY_CSV_EXPORT>'."
fi
check_file "$VELOHERO_CSV_EXPORT"

echo "Velo Hero CSV Export --> InfluxDB (Live Tracking)"

last_duration_sec=0
while IFS=';' read -r duration hr_bpm spd_kph alt_m cad_rpm pwr_w lat lng || [[ -n "$duration" ]]; do
	if [[ "$duration" = [0-9][0-9]:[0-9][0-9]:[0-9][0-9] ]]; then
		duration_sec=$(to_seconds "$duration")
		sleep_for_sec=$((duration_sec-last_duration_sec))
		last_duration_sec=$duration_sec
		printf "\n%5s | %3s bpm | %3.0f kph | %5s m | %3s rpm | %4s w" "$duration_sec" "$hr_bpm" "$spd_kph" "$alt_m" "$cad_rpm" "$pwr_w"
		if curl -fs -u "$MY_USERNAME:$MY_PASSWORD" -X POST "http://$MY_INFLUXDB_HOST:$MY_INFLUXDB_PORT/write?db=$MY_USERNAME" --data-binary "samples spd_kph=$spd_kph,alt_m=$alt_m,hr_bpm=$hr_bpm,cad_rpm=$cad_rpm,pwr_w=$pwr_w,lat=$lat,lng=$lng"; then
			printf " [OK]"
		else
			printf " [ERROR]"
		fi
		sleep $sleep_for_sec
	fi
done <"$VELOHERO_CSV_EXPORT"

echo