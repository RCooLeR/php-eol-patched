# syntax=docker/dockerfile:1
# check=error=true
ARG BASE_IMAGE=php:8.1.34-fpm-bookworm@sha256:e3893eeb8f6fb719b6efa7e011e76a65cb1322054226250cedac1138b406aba4
ARG SOURCE_STAGE=patched-source
FROM ${BASE_IMAGE} AS dependencies
ARG PHP_RELEASE=8.1.34
COPY scripts/install-build-deps.sh /build/scripts/install-build-deps.sh
RUN test "$(dpkg --print-architecture)" = amd64 \
    && /bin/bash /build/scripts/install-build-deps.sh "${PHP_RELEASE}"

FROM dependencies AS patched-source
COPY scripts/apply-patches.sh /build/scripts/apply-patches.sh
COPY patches/ /build/patches/
RUN /bin/bash /build/scripts/apply-patches.sh "${PHP_RELEASE}"

# An explicit named build context permits rebuilding modified LGPL source.
FROM dependencies AS supplied-source
COPY --from=php-source / /usr/src/php/
RUN mkdir -p /source \
    && tar -cJf "/source/php-${PHP_RELEASE}-patched.tar.xz" -C /usr/src/php . \
    && cd /source && sha256sum "php-${PHP_RELEASE}-patched.tar.xz" > SHA256SUMS

FROM ${SOURCE_STAGE} AS selected-source

FROM selected-source AS build
ARG BUILD_JOBS=2
COPY scripts/build-php.sh /build/scripts/build-php.sh
RUN /bin/bash /build/scripts/build-php.sh "${PHP_RELEASE}"

FROM build AS assembled
COPY scripts/assemble-runtime.sh /build/scripts/assemble-runtime.sh
COPY config/ /build/config/
COPY LICENSES/ /build/LICENSES/
COPY LICENSE THIRD_PARTY_NOTICES.md README.md PATCHES.md /build/
COPY patches/SHA256SUMS /build/patches/SHA256SUMS
RUN /bin/bash /build/scripts/assemble-runtime.sh "${PHP_RELEASE}"

FROM assembled AS test
COPY tests/ /tests/
COPY scripts/build-test-modules.sh /build/scripts/build-test-modules.sh
RUN /bin/bash /build/scripts/build-test-modules.sh
USER 33:33
WORKDIR /tmp
ENTRYPOINT []
CMD ["/bin/bash", "/tests/test-target.sh"]

FROM scratch AS source
COPY --from=selected-source /source/ /
COPY Dockerfile docker-bake.hcl /build/
COPY .dockerignore /build/
COPY scripts/ /build/scripts/
COPY config/ /build/config/
COPY patches/ /build/patches/
COPY LICENSES/ /build/LICENSES/
COPY tests/ /build/tests/
COPY LICENSE THIRD_PARTY_NOTICES.md README.md PATCHES.md SECURITY.md compose.example.yaml /build/

FROM scratch AS runtime
ARG PHP_RELEASE=8.1.34
LABEL org.opencontainers.image.title="php-eol-patched" \
      org.opencontainers.image.version="${PHP_RELEASE}" \
      org.opencontainers.image.source="https://github.com/RCooLeR/php-eol-patched" \
      org.opencontainers.image.description="Selected PHP security backports for temporary migration use. Upgrade to supported PHP."
COPY --from=assembled /runtime-rootfs/ /
ENV PATH=/usr/local/bin:/usr/local/sbin
USER 33:33
WORKDIR /var/www/html
EXPOSE 9000
STOPSIGNAL SIGQUIT
CMD ["/usr/local/sbin/php-fpm", "--nodaemonize"]
