UPGRADE="No"

echo "Let's get started with a dnf upgrade"
if [ "$UPGRADE" == "Yes" ]; then
    sudo dnf upgrade --refresh -y
fi

echo "Installing PHP and PHP modules..."
sudo dnf install -y php php-cli php-common php-fpm php-json php-pdo php-mbstring php-xml php-opcache php-curl php-zip php-intl php-gd php-bcmath php-xmlrpc
sudo dnf install php8.2-mysql
if ! sudo dnf install -y php; then
    echo "Failed to install PHP. Exiting."
    exit 1
fi

PHP_OK="$(php <<'PHP'
<?php
$v = PHP_VERSION;
$patched =
    (version_compare($v, "8.2.35", ">=") && version_compare($v, "8.3.0", "<")) ||
    (version_compare($v, "8.3.36", ">=") && version_compare($v, "8.4.0", "<")) ||
    (version_compare($v, "8.4.27", ">=") && version_compare($v, "8.5.0", "<")) ||
    version_compare($v, "8.5.12", ">=");
echo $patched ? "yes" : "no";
PHP
)"
if [ "$PHP_OK" != yes ]; then
    echo "PHP $(php -r 'echo PHP_VERSION;') is affected by PHP2Shell; install PHP 8.2.35+, 8.3.36+, 8.4.27+, or 8.5.12+ and re-run." >&2
    exit 1
fi

echo "Installing Nginx..."
sudo dnf install -y nginx
sudo ufw allow 'Nginx HTTP'
# If missing:
#	sudo nano /etc/ufw/applications.d/nginx
#[Nginx HTTP]
#title=Web Server
#description=HTTP traffic
#ports=80


echo "Installing Laravel Composer..."
if ! command -v composer >/dev/null 2>&1; then
	sudo dnf install -y composer
fi

echo "Setup Laravel Scout"
sudo composer require laravel/scout
sudo php artisan vendor:publish --provider="Laravel\Scout\ScoutServiceProvider"

echo "Installing NGINX..."
sudo dnf install nginx -y

echo "Enabling and starting NGINX..."
sudo systemctl start nginx
sudo systemctl enable nginx

echo "Setting up firewall..."
sudo firewall-cmd --permanent --add-service=http
sudo firewall-cmd --reload

echo "MariaDB setup..."
sudo dnf install mariadb-server -y
sudo systemctl start mariadb
sudo systemctl enable mariadb
sudo mysql_secure_installation
sudo dnf install php-mysqlnd

echo "Setup marketpalce DB in Maria/MySQL..."
echo ""
echo "OK, this part needs to be done manually (see install_step2.txt), then you can continue to run install_step3.sh"
echo "Setup your root password for MariaDB then run mysql --defaults-file=${CONFIG_FILE} -e "CREATE DATABASE>""


