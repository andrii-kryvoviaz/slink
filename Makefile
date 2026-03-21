run:
	docker buildx bake -f docker-bake.hcl prod --load
	docker compose -p slink -f docker/docker-compose.yaml -f docker/docker-compose.prod.yaml up -d

run-dev:
	docker buildx bake -f docker-bake.hcl dev --load
	docker compose -p slink -f docker/docker-compose.yaml -f docker/docker-compose.dev.yaml up -d
	docker attach slink

purge:
	docker compose -f docker/docker-compose.yaml down --rmi all --volumes --remove-orphans

release:
	@if [ -z $(version) ]; then echo "Version is required. Usage: make release version=x.x.x"; exit 1; fi
	@echo "Releasing version $(version)"
	gh workflow run release.yml -f version=$(version)