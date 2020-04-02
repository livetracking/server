#!/usr/bin/env bash

echo "Git"
git fetch
git reset --hard origin/master

echo "Run Phing with build.xml"
./vendor/phing/phing/bin/phing

echo "Done"