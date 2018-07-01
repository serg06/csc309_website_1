#!/bin/bash
echo "NOTE: CD INTO DEV BEFORE RUNNING SETUP.SH (so that schema.sql can load restaurants.txt properly)"
echo "Welcome to our CSC309 Assignment 1. Please enter the information as prompted to establish a connection."
read -p "Enter the db_user: " db_user
read -p "Enter the db_hostname: " db_hostname
read -p "Enter the db_name: " db_name
read -p "Enter the db_password: " db_password

sed -e "s/DB_NAME/$db_name/" -e "s/DB_HOSTNAME/$db_hostname/" -e "s/DB_USER/$db_user/" -e "s/DB_PASSWORD/$db_password/" dbconnect_string_template.php > ../lib/dbconnect_string.php

psql "dbname='$db_name' user='$db_user' password='$db_password' host='$db_hostname'" -f schema.sql

# set permissions
find ~/www -type d | xargs chmod 711 # only execute (go into) folders
find ~/www -type f | xargs chmod 644 # only read files
find ~/www -iname '*.php' | xargs chmod 600 # don't read php files
find ~/www -name .git | xargs chmod 700 # for security
