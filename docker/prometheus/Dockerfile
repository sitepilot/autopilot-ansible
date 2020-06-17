FROM prom/prometheus:latest
MAINTAINER Sitepilot <support@sitepilot.io>

LABEL org.label-schema.vendor="Sitepilot" \
    org.label-schema.name="autopilot-prometheus" \
    org.label-schema.description="Docker image which is used for monitoring Autopilot servers and sites." \
    org.label-schema.url="https://github.com/sitepilot/autopilot"

EXPOSE 9090

COPY tags /

VOLUME ["/prometheus"]
