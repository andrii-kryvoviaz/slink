run:
	docker buildx bake -f docker-bake.hcl prod --load
	docker compose -p slink -f docker/docker-compose.yaml -f docker/docker-compose.prod.yaml up -d

test-e2e:
	docker buildx bake -f docker-bake.hcl e2e --load
	docker compose -p slink-e2e -f docker/docker-compose.yaml -f docker/docker-compose.e2e.yaml up -d
	@docker compose -p slink-e2e -f docker/docker-compose.yaml -f docker/docker-compose.e2e.yaml exec slink sh -c 'until wget -qO- http://localhost:8080/api/health > /dev/null 2>&1; do sleep 1; done'
	cd services/client && npx playwright test; \
	rc=$$?; \
	cd ../.. && docker compose -p slink-e2e -f docker/docker-compose.yaml -f docker/docker-compose.e2e.yaml down --volumes; \
	exit $$rc

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