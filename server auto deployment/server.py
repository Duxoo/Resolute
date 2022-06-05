#! /usr/bin/python

import paramiko
import time
import requests
import sys

# host = "45.132.18.115"
host = sys.argv[1]
port = 22
username = "root"
# password = "0FM79Gt1LC"
password = sys.argv[2]
server_id = sys.argv[3]
domain = sys.argv[4]


def execute_command(command, input=''):
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, port, username, password)
    stdin, stdout, stderr = ssh.exec_command(command)
    if input:
        stdin.write(input)
    print(stdout.readlines())
    print("\n")
    ssh.close()


def upload_server_conf():
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, port, username, password)
    sftp = ssh.open_sftp()
    sftp.put('fastcgi.conf', '/etc/lighttpd/fastcgi.conf')
    sftp.put('lighttpd.conf', '/etc/lighttpd/lighttpd.conf')
    sftp.put('modules.conf', '/etc/lighttpd/modules.conf')
    sftp.put('vhosts.conf', '/etc/lighttpd/vhosts.conf')
    sftp.put('php.ini', '/etc/php/7.0/cgi/php.ini')
    stdin, stdout, stderr = ssh.exec_command("chown usersftp:usersftp /var/log/lighttpd; chown usersftp:usersftp "
                                             "/var/log/lighttpd/* ; chown usersftp:usersftp /var/cache/lighttpd ; "
                                             "chown usersftp:usersftp /var/cache/lighttpd/*; /etc/init.d/lighttpd "
                                             "restart; /etc/init.d/php7.0-fpm restart")
    print(stdout.readlines())
    sftp.close()
    ssh.close()


def download_webasyst():
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, port, username, password)
    # "git clone git://github.com/webasyst/webasyst-framework.git/var/www/project/httpdocs"
    '''stdin, stdout, stderr = ssh.exec_command("curl -s 'https://www.webasyst.ru/download/framework/' | tar xvz -C "
                                             "/var/www/project/httpdocs ;chown -R usersftp:usersftp "
                                             "/var/www/project/httpdocs/*")'''
    stdin, stdout, stderr = ssh.exec_command("git clone https://Duxoo:12@github.com/Duxoo/webasyst_for_coffee "
                                             "/var/www/project/httpdocs; mysql < "
                                             "/var/www/project/httpdocs/project.sql; chown -R usersftp:usersftp "
                                             "/var/www/project/httpdocs/*")
    print(stdout.readlines())
    ssh.close()


def upload_script():
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, port, username, password)
    sftp = ssh.open_sftp()
    sftp.put('server.sh', '/server.sh')
    sftp.close()
    ssh.close()


# todo rename where to save ssh key
def download_pub_key():
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, port, username, password)
    sftp = ssh.open_sftp()
    # todo change save folder
    sftp.get('/home/userssh/.ssh/id_rsa', r"C:\users\duxoo\Desktop\id_rsa")
    sftp.close()
    stdin, stdout, stderr = ssh.exec_command("su - userssh -c 'rm ~/.ssh/id_rsa'; /etc/init.d/ssh restart")
    ssh.close()


def create_ssl():
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, port, username, password)
    # todo ssl expired for domain, works well for new domains I guess
    stdin, stdout, stderr = ssh.exec_command("certbot certonly --non-interactive --webroot -w "
                                             "/var/www/project/httpdocs -d "+domain+" -m duxooa@gmail.com "
                                             "--agree-tos; cat /etc/letsencrypt/live/"+domain+"/privkey.pem "
                                             "/etc/letsencrypt/live/"+domain+"/cert.pem > "
                                             "/etc/letsencrypt/live/"+domain+"/web.pem; "
                                             "sed -i 's/#include \"vhosts.conf\"/include \"vhosts.conf\"/' /etc/lighttpd/lighttpd.conf; "
                                             "/etc/init.d/lighttpd restart; /etc/init.d/ssh restart")
    print(stdout.readlines())
    ssh.close()

start_time = time.time()
# загрузка bash-скрипта
upload_script()
ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect(host, port, username, password)
# запуск bash-скрипта
stdin, stdout, stderr = ssh.exec_command("cd /; chmod ugo+x server.sh; ./server.sh")
print(stderr.readlines())
print(stdout.readlines())
download_pub_key()
upload_server_conf()
download_webasyst()
create_ssl()
print("--- %s seconds ---" % (time.time() - start_time))