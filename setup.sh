#!/bin/zsh

echo "Removing existing DB..."
rm listings.db

echo "Creating new DB..."
sqlite3 listings.db < setup.sql

echo "Entering PHP setup..."
php setup.php
