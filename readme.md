A simple project to learn how to use a playbook.

------------------------------------------------

php -S localhost:3000 -t public

Php 8.2
Symfony 6.2.8

Install Composer,
Symfony CLI

- composer require
- symfony server:start

### Install with playbook

Clone the github repositories on your server, change the ip in hosts by the ip of your server and change the root login and password for ssh connexion.
And just run the cmd: ansible-playbook playbook.yml -i hosts
