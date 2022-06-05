#!/bin/bash
# todo change /var/www/project to smth specific
# get latest updates
apt-get update
apt-get upgrade -y

#install web-server
apt-get install lighttpd -y

#install php
apt-get install php7.0-cgi -y
apt-get install php7.0-fpm -y

#instal php modules
apt-get install php7.0-mbstring -y
apt-get install php7.0-xml -y
apt-get install php7.0-curl -y
apt-get install php7.0-gd -y
apt-get install php7.0-mysql -y

#todo install git
apt-get install git -y
#apt-get install curl -y
#mariaDB
apt-get install mariadb-server -y
# automatic begins mysql_secure_installation with root password for now
###
mysql -e "UPDATE mysql.user SET Password = PASSWORD('root') WHERE User = 'root'"
mysql -e "DELETE FROM mysql.user WHERE User=''"
mysql -e "DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1')"
mysql -e "DROP DATABASE IF EXISTS test"
mysql -e "DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%'"
mysql -e "FLUSH PRIVILEGES"
mysql -e "CREATE DATABASE project"
mysql -e "CREATE USER 'project'@'localhost' IDENTIFIED BY 'project'"
mysql -e "GRANT ALL PRIVILEGES ON project.* TO 'project'@'localhost'"
mysql -e "FLUSH PRIVILEGES"
###
#end automatic mysql_secure_installation

# create folder for site if not exists
mkdir -p /var/www/project
mkdir -p /var/www/project/httpdocs

#create user for ssh connect
adduser --disabled-password --gecos "" userssh
#generate ssh key for him
su - userssh -c 'rm -rf ~/.ssh'
su - userssh -c 'ssh-keygen -b 2048 -t rsa -f ~/.ssh/id_rsa -q -N ""'
su - userssh -c 'mv ~/.ssh/id_rsa.pub ~/.ssh/authorized_keys'
# delete comment from config to allow ssh connect with key
sed -i 's/#PubkeyAuthentication yes/PubkeyAuthentication yes/' /etc/ssh/sshd_config

#create user for sftp
adduser --disabled-password --gecos "" usersftp
#copy ssh key to sftp user
mkdir -p /home/usersftp/.ssh
chmod 700 /home/usersftp/.ssh
cp /home/userssh/.ssh/authorized_keys /home/usersftp/.ssh/authorized_keys
chown -R usersftp:usersftp /home/usersftp/.ssh
chmod 600 /home/usersftp/.ssh/authorized_keys

sed -i 's/Subsystem\tsftp\t\/usr\/lib\/openssh\/sftp-server/Subsystem\tsftp\tinternal-sftp/' /etc/ssh/sshd_config
#add new lines to config with user and his folder if it's not exists already
if ! grep -Fxq "Match Group usersftp" /etc/ssh/sshd_config
then
  echo -e 'Match Group usersftp\n\tX11Forwarding no\n\tAllowTcpForwarding no\n\tChrootDirectory /var/www/project\n\tForceCommand internal-sftp' >> /etc/ssh/sshd_config
fi
#change rights
chown root:root /var/www/project
chown usersftp:usersftp /var/www/project/httpdocs
mkdir -p /var/www/project/logs
chown usersftp:usersftp /var/www/project/logs

#certbot todo change domain
apt-get install certbot -y
#certbot certonly --non-interactive --webroot -w /var/www/project/httpdocs -d asfaca.xyz -m duxooa@gmail.com --agree-tos
#cat /etc/letsencrypt/live/asfaca.xyz/privkey.pem /etc/letsencrypt/live/asfaca.xyz/cert.pem > /etc/letsencrypt/live/asfaca.xyz/web.pem
#php mail
apt-get install sendmail -y



