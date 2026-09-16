FROM nginx:alpine@sha256:db35bfc6b2951e7f8a72db5db120288c127ffaeeb4a6d4b95a26fead017d5913

# Keep the pinned base while applying the current Alpine security fixes at
# build time.
RUN mkdir -p /var/www/html/public \
    && chown -R 101:101 /var/www/html

COPY --chown=101:101 public/ /var/www/html/public/
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
