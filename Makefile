LANE ?=
E2E_PROJECT := slink-e2e$(if $(LANE),-$(LANE))
E2E_WEB_PORT := $(if $(LANE),$(shell expr 3100 + $(LANE)),3100)
E2E_API_PORT := $(if $(LANE),$(shell expr 8180 + $(LANE)),8180)
E2E_IMAGE ?= slink:e2e
E2E_COMPOSE := COMPOSE_PROJECT_NAME=$(E2E_PROJECT) E2E_WEB_PORT=$(E2E_WEB_PORT) E2E_API_PORT=$(E2E_API_PORT) E2E_IMAGE=$(E2E_IMAGE) docker compose -f docker/docker-compose.yaml -f docker/docker-compose.e2e.yaml
E2E_HEALTH_WAIT := exec slink sh -c 'until wget -qO- http://localhost:8080/api/health > /dev/null 2>&1; do sleep 1; done'

run:
	docker buildx bake -f docker-bake.hcl prod --load
	docker compose -p slink -f docker/docker-compose.yaml -f docker/docker-compose.prod.yaml up -d

test-e2e:
	docker buildx bake -f docker-bake.hcl e2e --load$(if $(filter-out slink:e2e,$(E2E_IMAGE)), --set e2e.tags=$(E2E_IMAGE))
	$(E2E_COMPOSE) up -d
	@$(E2E_COMPOSE) $(E2E_HEALTH_WAIT)
	cd services/client && COMPOSE_PROJECT_NAME=$(E2E_PROJECT) E2E_BASE_URL=http://localhost:$(E2E_WEB_PORT) E2E_API_URL=http://localhost:$(E2E_API_PORT) yarn e2e; \
	rc=$$?; \
	cd ../.. && $(E2E_COMPOSE) down --volumes; \
	exit $$rc

e2e-up:
	$(E2E_COMPOSE) up -d
	@$(E2E_COMPOSE) $(E2E_HEALTH_WAIT)
	@echo "project $(E2E_PROJECT)"
	@echo "web     http://localhost:$(E2E_WEB_PORT)"
	@echo "api     http://localhost:$(E2E_API_PORT)"

e2e-down:
	$(E2E_COMPOSE) down --volumes

test-storage:
	docker compose -p slink-storage-test -f docker/docker-compose.storage-test.yaml up -d --wait
	cd services/api && vendor/bin/phpunit --group storage-integration --no-coverage; \
	rc=$$?; \
	cd ../.. && docker run --rm --network slink-storage-test_default -v $(PWD)/services/api:/app -w /app -e STORAGE_TEST_SMB_HOST=samba php:8.5-cli-alpine sh -lc 'apk add -q samba-client && php vendor/bin/phpunit --group storage-integration --filter SmbStorageContractTest --no-coverage'; \
	rc2=$$?; \
	docker compose -p slink-storage-test -f docker/docker-compose.storage-test.yaml down --volumes; \
	exit $$((rc | rc2))

run-dev:
	docker buildx bake -f docker-bake.hcl dev --load
	docker compose -p slink -f docker/docker-compose.yaml -f docker/docker-compose.dev.yaml up -d
	docker attach slink

run-dev-host:
	@if [ -z "$$(docker compose -p slink -f docker/docker-compose.yaml -f docker/docker-compose.dev-host.yaml ps --status=running -q slink)" ]; then \
		docker buildx bake -f docker-bake.hcl dev --load && \
		docker compose -p slink -f docker/docker-compose.yaml -f docker/docker-compose.dev-host.yaml up -d --wait; \
	else \
		echo "slink dev container already running — skipping bake + compose up"; \
	fi
	API_URL=http://localhost:8080 yarn --cwd services/client run dev:with-deps --host

purge:
	docker compose -f docker/docker-compose.yaml down --rmi all --volumes --remove-orphans

BUMP := $(filter patch minor major,$(MAKECMDGOALS))

patch minor major:
	@:

release:
	@[ -n "$(version)$(BUMP)" ] || { echo "Usage: make release patch|minor|major | version=x.y.z [dry=1]"; exit 1; }; \
	git fetch -q --tags origin main; \
	last=$$(git tag -l 'v[0-9]*.[0-9]*.[0-9]*' --sort=-v:refname | grep -v -- - | head -1); \
	if [ -n "$(version)" ]; then next="v$(patsubst v%,%,$(version))"; else \
	set -- $$(echo "$${last:-v0.0.0}" | tr -d v | tr . ' '); \
	case "$(firstword $(BUMP))" in major) next="v$$(($$1+1)).0.0";; minor) next="v$$1.$$(($$2+1)).0";; *) next="v$$1.$$2.$$(($$3+1))";; esac; fi; \
	echo "$${last:-none} -> $$next at $$(git rev-parse --short origin/main)"; \
	[ -z "$(dry)" ] || exit 0; \
	git tag "$$next" origin/main && git push origin "refs/tags/$$next"
