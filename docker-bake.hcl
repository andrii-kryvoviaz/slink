variable "NODE_VERSION"                  { default = "24.17.0" }
variable "PHP_VERSION"                   { default = "8.5.7" }
variable "ALPINE_VERSION"                { default = "3.24" }
variable "COMPOSER_VERSION"              { default = "2" }
variable "MEMORY_LIMIT"                  { default = "512" }
variable "UPLOAD_MAX_FILESIZE_IN_BYTES"  { default = "52428800" }
variable "USER_ID"                       { default = "1000" }
variable "GROUP_ID"                      { default = "1000" }
variable "TAG"                           { default = "latest" }

group "default" { targets = ["prod"] }

target "_base" {
  dockerfile = "docker/Dockerfile.base"
  args = {
    NODE_VERSION                 = NODE_VERSION
    PHP_VERSION                  = PHP_VERSION
    ALPINE_VERSION               = ALPINE_VERSION
    MEMORY_LIMIT                 = MEMORY_LIMIT
    UPLOAD_MAX_FILESIZE_IN_BYTES = UPLOAD_MAX_FILESIZE_IN_BYTES
    USER_ID                      = USER_ID
    GROUP_ID                     = GROUP_ID
  }
}

target "_node" {
  inherits = ["_base"]
  target   = "node"
}

target "_common" {
  inherits = ["_base"]
  target   = "common"
}

target "_frankenphp" {
  inherits = ["_base"]
  target   = "frankenphp"
}

target "_staging" {
  inherits = ["_base"]
  target   = "staging"
}

target "_contexts" {
  contexts = {
    node         = "target:_node"
    common       = "target:_common"
    frankenphp   = "target:_frankenphp"
    staging      = "target:_staging"
  }
}

target "_prod-base" {
  dockerfile = "docker/Dockerfile.prod"
  target     = "prod"
  args = {
    ALPINE_VERSION               = ALPINE_VERSION
    COMPOSER_VERSION             = COMPOSER_VERSION
    UPLOAD_MAX_FILESIZE_IN_BYTES = UPLOAD_MAX_FILESIZE_IN_BYTES
  }
}

target "prod" {
  inherits   = ["_contexts", "_prod-base"]
  tags       = ["anirdev/slink:${TAG}"]
  labels = {
    "org.opencontainers.image.title"       = "Slink"
    "org.opencontainers.image.description" = "Self-hosted image sharing platform"
    "org.opencontainers.image.authors"     = "Andrii Klyvoviaz"
    "org.opencontainers.image.url"         = "https://docs.slinkapp.io"
    "org.opencontainers.image.source"      = "https://github.com/andrii-kryvoviaz/slink"
    "org.opencontainers.image.licenses"    = "AGPL-3.0"
  }
  attest     = [
    "type=provenance,mode=max",
    "type=sbom"
  ]
}

target "dev" {
  inherits   = ["_contexts"]
  dockerfile = "docker/Dockerfile.dev"
  target     = "dev"
  tags       = ["slink:dev"]
}

target "test" {
  inherits   = ["_contexts"]
  dockerfile = "docker/Dockerfile.test"
  target     = "test"
  tags       = ["slink:test"]
  args = {
    ALPINE_VERSION   = ALPINE_VERSION
    COMPOSER_VERSION = COMPOSER_VERSION
    MEMORY_LIMIT     = MEMORY_LIMIT
  }
}

target "e2e" {
  inherits   = ["_contexts", "_prod-base"]
  tags       = ["slink:e2e"]
}

target "permissions-test" {
  inherits   = ["_contexts"]
  dockerfile = "docker/Dockerfile.permissions-test"
  target     = "permissions-test"
  tags       = ["slink:permissions-test"]
  contexts = {
    test = "target:test"
  }
  args = {
    ALPINE_VERSION = ALPINE_VERSION
  }
}

target "prod-amd64" {
  inherits  = ["prod"]
  platforms = ["linux/amd64"]
  tags      = []
}

target "prod-arm64" {
  inherits  = ["prod"]
  platforms = ["linux/arm64"]
  tags      = []
}
