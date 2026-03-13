#!/bin/sh
cd "$(dirname "$0")"
echo "Starting PHP backend on http://localhost:8888"
echo "API base: http://localhost:8888/api"
echo "Press Ctrl+C to stop."
php -S localhost:8888 -t .
