group "default" {
  targets = ["php56", "php70", "php71", "php72", "php73", "php74", "php80", "php81"]
}

group "debian" {
  targets = ["php56-debian", "php70-debian", "php71-debian", "php72-debian", "php73-debian", "php74-debian", "php80-debian", "php81-debian"]
}

group "all" {
  targets = ["php56", "php70", "php71", "php72", "php73", "php74", "php80", "php81", "php56-debian", "php70-debian", "php71-debian", "php72-debian", "php73-debian", "php74-debian", "php80-debian", "php81-debian"]
}

target "php56" {
  context = "."
  dockerfile = "Dockerfile"
  target = "runtime"
  platforms = ["linux/amd64"]
  tags = ["php-eol-patched:5.6.40"]
  args = {
    PHP_RELEASE = "5.6.40"
    BASE_IMAGE = "php:5.6.40-fpm-stretch@sha256:4f070f1b7b93cc5ab364839b79a5b26f38d5f89461f7bc0cd4bab4a3ad7d67d7"
  }
}

target "php70" {
  context = "."
  dockerfile = "Dockerfile"
  target = "runtime"
  platforms = ["linux/amd64"]
  tags = ["php-eol-patched:7.0.33"]
  args = {
    PHP_RELEASE = "7.0.33"
    BASE_IMAGE = "php:7.0.33-fpm-stretch@sha256:8e8644c0fd61424939fff072ca617469550bbb659158301a2dcadf334746a1c2"
  }
}

target "php71" {
  context = "."
  dockerfile = "Dockerfile"
  target = "runtime"
  platforms = ["linux/amd64"]
  tags = ["php-eol-patched:7.1.33"]
  args = {
    PHP_RELEASE = "7.1.33"
    BASE_IMAGE = "php:7.1.33-fpm-stretch@sha256:b3ab332d16643ea8d6967c8b2d7e8678026224c8b283e8e667465efbf5123b73"
  }
}

target "php72" {
  context = "."
  dockerfile = "Dockerfile"
  target = "runtime"
  platforms = ["linux/amd64"]
  tags = ["php-eol-patched:7.2.34"]
  args = {
    PHP_RELEASE = "7.2.34"
    BASE_IMAGE = "php:7.2.34-fpm-buster@sha256:cde8c998f1d61d48b56a5f68b9af07737ace947a79a29febaac9b2e26d3d2490"
  }
}

target "php73" {
  context = "."
  dockerfile = "Dockerfile"
  target = "runtime"
  platforms = ["linux/amd64"]
  tags = ["php-eol-patched:7.3.33"]
  args = {
    PHP_RELEASE = "7.3.33"
    BASE_IMAGE = "php:7.3.33-fpm-buster@sha256:bb3522a0ebacafdb712297d0cc068cfba044044e7e0211546f65803b5afe116f"
  }
}

target "php74" {
  context = "."
  dockerfile = "Dockerfile"
  target = "runtime"
  platforms = ["linux/amd64"]
  tags = ["php-eol-patched:7.4.33"]
  args = {
    PHP_RELEASE = "7.4.33"
    BASE_IMAGE = "php:7.4.33-fpm-buster@sha256:f7c4f7267af327700c172034c2a9a354c85c86fadbe412cd11e709a37e475bda"
  }
}

target "php80" {
  context = "."
  dockerfile = "Dockerfile"
  target = "runtime"
  platforms = ["linux/amd64"]
  tags = ["php-eol-patched:8.0.30"]
  args = {
    PHP_RELEASE = "8.0.30"
    BASE_IMAGE = "php:8.0.30-fpm-bullseye@sha256:f4874097fc37a9d270e21523f40f621fe574183372d5133de6eb88ed2f663d54"
  }
}

target "php81" {
  context = "."
  dockerfile = "Dockerfile"
  target = "runtime"
  platforms = ["linux/amd64"]
  tags = ["php-eol-patched:8.1.34"]
  args = {
    PHP_RELEASE = "8.1.34"
    BASE_IMAGE = "php:8.1.34-fpm-bookworm@sha256:e3893eeb8f6fb719b6efa7e011e76a65cb1322054226250cedac1138b406aba4"
  }
}

target "php56-debian" {
  inherits = ["php56"]
  target = "debian"
  tags = ["php-eol-patched:5.6.40-debian"]
}

target "php70-debian" {
  inherits = ["php70"]
  target = "debian"
  tags = ["php-eol-patched:7.0.33-debian"]
}

target "php71-debian" {
  inherits = ["php71"]
  target = "debian"
  tags = ["php-eol-patched:7.1.33-debian"]
}

target "php72-debian" {
  inherits = ["php72"]
  target = "debian"
  tags = ["php-eol-patched:7.2.34-debian"]
}

target "php73-debian" {
  inherits = ["php73"]
  target = "debian"
  tags = ["php-eol-patched:7.3.33-debian"]
}

target "php74-debian" {
  inherits = ["php74"]
  target = "debian"
  tags = ["php-eol-patched:7.4.33-debian"]
}

target "php80-debian" {
  inherits = ["php80"]
  target = "debian"
  tags = ["php-eol-patched:8.0.30-debian"]
}

target "php81-debian" {
  inherits = ["php81"]
  target = "debian"
  tags = ["php-eol-patched:8.1.34-debian"]
}
