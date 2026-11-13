FROM nginx:1.27-alpine

RUN apk add --no-cache curl jq

COPY nginx.conf /etc/nginx/nginx.conf
COPY docker-entrypoint.d/ /docker-entrypoint.d/
RUN chmod +x /docker-entrypoint.d/*.sh && mkdir -p /etc/nginx/eimzo
