variable "PHP_VERSION" {
  default = "7.4"
}

target "php-version" {
  args = {
    PHP_VERSION = PHP_VERSION
  }
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
  inherits = ["ghaction-docker-meta"]
  dockerfile = "./Dockerfile"
}

target "image-local" {
  inherits = ["image"]
  output = ["type=docker"]
}