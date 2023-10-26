run:
	docker compose -f docker-compose.yaml up -d --build

run-dev:
	docker compose -f docker-compose.yaml -f docker-compose.dev.yaml up -d --build

purge:
	docker compose -f docker-compose.yaml down --rmi all --volumes --remove-orphans