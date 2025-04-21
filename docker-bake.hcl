variable "PHP_VERSION" {
  default = "8.4"
}

target "php-version" {
  args = {
    PHP_VERSION = PHP_VERSION
  }
}

// GitHub reference as defined in GitHub Actions (eg. refs/head/master)
variable "GITHUB_REF" {
  default = ""
}

target "git-ref" {
  args = {
    GIT_REF = GITHUB_REF
  }
}

target "docker-metadata-action" {
  tags = ["blueprintue-self-hosted-edition:local"]
}

group "default" {
  targets = ["image-local"]
}

group "validate" {
  targets = ["vendor-validate", "lint"]
}

target "vendor-update" {
  inherits = ["php-version"]
  dockerfile = "./dev.Dockerfile"
  target = "vendor-update"
  output = ["."]
}

target "vendor-validate" {
  inherits = ["php-version"]
  dockerfile = "./dev.Dockerfile"
  target = "vendor-validate"
}

target "lint" {
  inherits = ["php-version"]
  dockerfile = "./dev.Dockerfile"
  target = "lint"
}

target "test" {
  inherits = ["php-version"]
  dockerfile = "./dev.Dockerfile"
  target = "test"
  tags = ["blueprintue-self-hosted-edition:test"]
  output = ["type=docker"]
}

target "image" {
  inherits = ["docker-metadata-action"]
  dockerfile = "./Dockerfile"
}

target "image-local" {
  inherits = ["image"]
  output = ["type=docker"]
}

target "image-all" {
  inherits = ["image"]
  platforms = [
    "linux/amd64",
    "linux/arm/v7",
    "linux/arm64",
  ]
}
