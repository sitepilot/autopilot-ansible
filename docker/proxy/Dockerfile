FROM caddy:alpine
MAINTAINER Sitepilot <support@sitepilot.io>

LABEL org.label-schema.vendor="Sitepilot" \
    org.label-schema.name="autopilot-proxy" \
    org.label-schema.description="Docker image which is used for proxying to Autopilot services." \
    org.label-schema.url="https://github.com/sitepilot/autopilot"

EXPOSE 80
EXPOSE 443

ENV PROXY_CERT_EMAIL="internal"
ENV PROXY_MONITOR_PASSWORD="supersecret"

COPY tags /

RUN chmod +x /autopilot/bin/entrypoint

ENTRYPOINT ["/autopilot/bin/entrypoint"]

CMD ["caddy", "run", "--config", "/etc/caddy/Caddyfile", "--adapter", "caddyfile"]