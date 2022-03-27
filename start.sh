
echo -e "\e[32mStarting application...\033[0m"

# Start MariaDB and Redis

if pidof -x "mysqld" -o $$ > /dev/null; then
echo "MariaDB already running. No need to run again."
else
mysqld --defaults-file=/home/runner/$REPL_SLUG/system/config/my.cnf --datadir="/home/runner/$REPL_SLUG/system/mariadb" --silent-startup &
fi

# Since Redis is a cache DB, keep this running after repl stop and only after full repl reboot, so if app crashes for whatever reason contents in Redis is not lost.
if pidof -x "redis-server" -o $$ > /dev/null; then
echo "Redis already running. No need to run again."
else
redis-server /home/runner/$REPL_SLUG/system/config/redis.conf --daemonize yes
fi

# Serve application
cd src
echo -e "\e[32mLaravel app live!\033[0m"
php artisan serve -q --host 0.0.0.0
