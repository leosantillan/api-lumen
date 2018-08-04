#!/bin/bash  
echo "\n\nInstalling NNTSA Vehicles API...\n"  

echo "\n\nSetting environment variables file\n"
cp .env.example .env

echo "\n\nRunning server\n"
php -S localhost:8080 -t ./public
