#!/bin/zsh

rm listings.db

sqlite3 listings.db < setup.sql

php setup.php
