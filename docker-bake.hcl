variable "NODE_VERSION"                  { default = "24.15.0" }
variable "PHP_VERSION"                   { default = "8.5.5" }
variable "ALPINE_VERSION"                { default = "3.23" }
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

target "_contexts" {
  contexts = {
    node         = "target:_node"
    common       = "target:_common"
    frankenphp   = "target:_frankenphp"
  }
}

target "prod" {
  inherits   = ["_contexts"]
  dockerfile = "docker/Dockerfile.prod"
  target     = "prod"
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
  args = {
    ALPINE_VERSION               = ALPINE_VERSION
    COMPOSER_VERSION             = COMPOSER_VERSION
    UPLOAD_MAX_FILESIZE_IN_BYTES = UPLOAD_MAX_FILESIZE_IN_BYTES
  }
}

target "dev" {
  inherits   = ["_contexts"]
  dockerfile = "docker/Dockerfile.dev"
  target     = "dev"
  tags       = ["slink:dev"]
}

target "test" {
  dockerfile = "docker/Dockerfile.test"
  tags       = ["slink:test"]
  args = {
    PHP_VERSION  = PHP_VERSION
    MEMORY_LIMIT = MEMORY_LIMIT
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
