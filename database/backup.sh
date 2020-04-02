#!/usr/bin/env bash

#
# Create rotating SQLite and InfluxDB backups
#

# Backup location
BACKUP_PATH='/private-backup'

# Location of the database
MY_DATABASE="/var/lib/sqlite/users.sqlite"
MY_INFLUX="/var/lib/influxdb"

# Maximum number of backups
ROT_PERIOD=30


# Fetch biggest id in dir
cd "$BACKUP_PATH" || exit

BIG_ID=$( ls -1 *_backup.tar.gz | wc -l ) &> /dev/null


DATE_TODAY=$( date +%Y%m%d-%H%M%S )

# Rep sauvegarde
REP="$BACKUP_PATH/$DATE_TODAY"
if [ ! -d "$REP" ];
then
  mkdir "$REP"
fi

if [ ! "$BIG_ID" ];
then
  BIG_ID=0
fi

# Rotation if at least 1_backup.tar.gz
NEXT_ID=0
for i in $( seq 1 $BIG_ID );
do
  NEXT_ID=$((i+1))
  NEXT_FILENAME=$NEXT_ID'_backup.tar.gz'
  FILENAME="$i""_backup.tar.gz"
    if [ -e "$FILENAME" ];
    then
     # echo "$FILENAME exists"
     if [ "$i" = "$ROT_PERIOD"  ];
     then
       # echo "Removing oldest archive..."
       rm "$i""_backup.tar.gz"
     else
       # echo "Rotating $i..."
       cp "$FILENAME" "$NEXT_FILENAME"
     fi
    fi
done

# Converting  entire database to an ASCII text file
sqlite3 "$MY_DATABASE" .dump >"$REP/sqlite.sql"

# Backup InfluxDB
# 'influxd backup' does not work properly!!!11
# Just copy the whole folder in the hope that it fits.
cp -r -p "$MY_INFLUX" "$REP"

# Compression
tar cfz 1_backup.tar.gz "$DATE_TODAY"

# Suppression
rm -rf "$DATE_TODAY"