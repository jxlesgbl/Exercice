env ?= .env
include $(env)
export $(shell sed 's/=.*//' $(env))

install:
	docker-compose build --no-cache
	docker-compose up -d

rebuild:
	docker-compose build --no-cache
	docker-compose up -d

start:
	docker-compose up -d

stop:
	docker-compose stop

php:
	docker-compose exec php bash

db:
	docker-compose exec db bash

apache:
	docker-compose exec apache bash

ssl:
	docker-compose stop
	cd .docker/cerbot; sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout apache-selfsigned.key -out apache-selfsigned.crt
	cd .docker/config/vhost-ssl; mv arpa3ssl.conf ../vhosts
	cd .docker/config/vhosts; rm arpa3.conf
	docker-compose up -d

