# -*- mode: ruby -*-
# vi: set ft=ruby :


# Machine config block

# APPNAME is used for apache site name and logs folder
$APPNAME = 'tippspiel'

# APPURL is used as vhost server name, if empty server name is APPNAME.local
$APPURL = ''

# APPPUBLICPATH is used for document root, set to '' if not necessary
$APPPUBLICPATH = ''

# VMADDRESS is the private ip address, change if you run multiple vms in parallel
$VMADDRESS = '192.168.33.103'

# VMHOSTNAME is the machines hostname, used with laravel env detection e.g.
$VMHOSTNAME = 'vagrant'




# All Vagrant configuration is done below. The "2" in Vagrant.configure
# configures the configuration version (we support older styles for
# backwards compatibility). Please don't change it unless you know what
# you're doing.
Vagrant.configure("2") do |config|
  # The most common configuration options are documented and commented below.
  # For a complete reference, please see the online documentation at
  # https://docs.vagrantup.com.

  # Every Vagrant development environment requires a box. You can search for
  # boxes at https://vagrantcloud.com/search.
  config.vm.box = "debian/contrib-stretch64"

  # Set hostname for env detection
  config.vm.hostname = $VMHOSTNAME

  # config.vm.network "forwarded_port", guest: 80, host: 8080
  # config.vm.network "forwarded_port", guest: 80, host: 8080, host_ip: "127.0.0.1"

  # Create a private network, which allows host-only access to the machine
  # using a specific IP.
  config.vm.network "private_network", ip: $VMADDRESS

  # Share an additional folder to the guest VM. The first argument is
  # the path on the host to the actual folder. The second argument is
  # the path on the guest to mount the folder. And the optional third
  # argument is a set of non-required options.
  # config.vm.synced_folder "../data", "/vagrant_data"

  config.vm.provider "virtualbox" do |vb|
    vb.memory = "1024"
  end

  # Shell script provisioning (only run once, or when using --provision)
  config.vm.provision "shell", privileged: true, inline: <<-EOT
    export DEBIAN_FRONTEND=noninteractive
    apt-get update -qq

    # Install common tools
    apt-get install -qqy curl htop vim bindfs apt-transport-https

    # Install mysql
    echo "Installing mysql"
    apt-get install -qqy mysql-{client,server}
    sed -i 's/^bind-address/#bind-address/' /etc/mysql/my.cnf
    systemctl restart mysql

    # Update package sources for PHP 7.2
    echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/sury.list
    curl https://packages.sury.org/php/apt.gpg | apt-key add -
    apt-get update -qq

    # Install apache and php
    echo "Installing apache and php"
    apt-get install -qqy apache2 libapache2-mod-php php php-{apcu,curl,gd,intl,json,mbstring,mysql,xdebug,xml,zip}

    # Enable mod_rewrite, disable default vhost, set sticky bit on logs folder
    a2enmod rewrite
    a2dissite 000-default
    chmod g+s /var/log/apache2
    systemctl restart apache2

    # Remove web folder contents and configure vagrant shared folder mount
    echo "Linking apache web folder to /vagrant"
    rm -rf /var/www/html

    # Install composer
    echo "Installing composer"
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php -r "if (hash_file('sha384', 'composer-setup.php') === 'c5b9b6d368201a9db6f74e2611495f369991b72d9c8cbd3ffbc63edff210eb73d46ffbfce88669ad33695ef77dc76976') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
    php composer-setup.php
    php -r "unlink('composer-setup.php');"

    # Creating non-secure mysql user, with password as password
    mysql -uroot -e "CREATE USER 'user'@'%' IDENTIFIED BY 'password'; GRANT ALL PRIVILEGES ON *.* to 'user'@'%';" mysql

    # Create bash aliases file
    echo "# Vagrant generated file, change to your needs
alias ll='ls -pal'
alias l='ls -p1'

alias artisan='php ./artisan'
alias composer='php ~/composer.phar'

# A trick to change into /vagrant after login
cd /vagrant" > /home/vagrant/.bash_aliases
chown vagrant:vagrant /home/vagrant/.bash_aliases
  EOT


  # Predefine APPURL in case it's empty
  $APPURL = ($APPURL!='') ? $APPURL : $APPNAME+'.local'

  # Another provisioning block, this is application specific
  config.vm.provision "shell", privileged: true, inline: <<-EOT
    # Bootstrapping app
    mkdir /var/log/apache2/#{$APPNAME}
    chmod -R o-rwx /var/log/apache2/#{$APPNAME}
    touch /etc/apache2/sites-available/#{$APPNAME}.conf

    # Virtual host config for the main site
    echo "<VirtualHost *:80>
  ServerName #{$APPURL}

  DocumentRoot /var/www#{$APPPUBLICPATH}
  <Directory /var/www#{$APPPUBLICPATH}>
    Require all granted
    Options +Indexes +FollowSymLinks +MultiViews
    AllowOverride All
  </Directory>

  LogLevel warn
  ErrorLog /var/log/apache2/#{$APPNAME}/error.log
  CustomLog /var/log/apache2/#{$APPNAME}/access.log combined
</VirtualHost>" > /etc/apache2/sites-available/#{$APPNAME}.conf

    # Virtual host config for redirecting from the ip address
    echo "<VirtualHost *:80>
  RewriteEngine On
  RewriteRule ^ "http://xpanda.local/"
</VirtualHost>" > /etc/apache2/sites-available/catchall.conf

    # Restart apache
    a2ensite catchall #{$APPNAME}
    systemctl restart apache2

  EOT

  config.vm.provision "shell", privileged: true, run: 'always', inline: <<-EOT
    echo "Mounting /vagrant to /var/www (using bindfs)"
    bindfs /vagrant /var/www --force-group=www-data --perms=0000:ug+rwx:o+rx
  EOT


  # Final message to the user
  config.vm.post_up_message = <<-EOT

Your Vagrant box is finally up and running (on ip: #{$VMADDRESS})

To access it, insert this into your /etc/hosts file

  #{$VMADDRESS} #{$APPURL}


To access the database on #{$VMADDRESS}:3306 use these credentials:

  username: user
  password: password


If you use composer, check for your vendor folder. If it doesn't exist enter

  composer install

Composer is also available on the machine (using vagrant ssh)

###########################################################################
#       This debian installation is not secured for public/web use!       #
#                    Use only for local development!                      #
###########################################################################

  EOT

end
