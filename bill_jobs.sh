#!/bin/bash

while true
do
    php jobs_cron.php
    sleep 60  # Wait for 60 seconds before running again
done
