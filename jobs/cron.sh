#!/bin/bash

while true
do
    php cron.php
    sleep 60  # Wait for 60 seconds before running again
done
