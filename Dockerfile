# Default Dockerfile
#
# @link     https://www.hyperf.io
# @document https://hyperf.wiki
# @contact  group@hyperf.io
# @license  https://github.com/hyperf/hyperf/blob/master/LICENSE

#FROM hyperf/hyperf:8.1-alpine-v3.15-swoole
FROM registry.cn-zhangjiakou.aliyuncs.com/eic/uuoa:engine-3
LABEL maintainer="Hyperf Developers <group@hyperf.io>" version="1.0" license="MIT" app.name="Hyperf"

##
# ---------- env settings ----------
##
# --build-arg timezone=Asia/Shanghai
ARG timezone

ENV TIMEZONE=${timezone:-"Asia/Shanghai"} \
    APP_ENV=prod \
    SCAN_CACHEABLE=(true)
WORKDIR /opt/www
COPY . /opt/www
RUN composer install --no-dev -o && php bin/hyperf.php
EXPOSE 9501
ENTRYPOINT ["php", "/opt/www/bin/hyperf.php", "start"]
