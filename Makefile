up:
	docker-compose up -d

start:
	docker-compose start

stop:
	docker-compose stop

down:
	docker-compose down

enter:
	docker-compose exec php /bin/bash

cc:
	docker-compose exec php bin/console c:c
