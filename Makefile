run:
	docker compose -f docker-compose.yaml -f docker-compose.prod.yaml up -d --build

run-dev:
	docker compose -f docker-compose.yaml -f docker-compose.dev.yaml up -d --build;
	yarn --cwd ./client run dev

purge:
	docker compose -f docker-compose.yaml down --rmi all --volumes --remove-orphans