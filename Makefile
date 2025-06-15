run:
	docker compose -p slink -f docker/docker-compose.yaml -f docker/docker-compose.prod.yaml up -d --build

run-dev:
	docker compose -p slink -f docker/docker-compose.yaml -f docker/docker-compose.dev.yaml up -d --build;
	docker attach slink

purge:
	docker compose -f docker/docker-compose.yaml down --rmi all --volumes --remove-orphans

release:
	@if [ -z $(version) ]; then echo "Version is required. Usage: make release version=x.x.x"; exit 1; fi
	@echo "Releasing version $(version)"
	gh workflow run release.yml -f version=$(version)

gen-proto:
	mkdir -p proto/gen/go proto/gen/php
	protoc --go_out=proto/gen/go --go-grpc_out=proto/gen/go --php_out=proto/gen/php --proto_path=proto/contract proto/contract/image.proto