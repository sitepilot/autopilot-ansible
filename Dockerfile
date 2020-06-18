FROM sitepilot/autopilot-base:latest
MAINTAINER Sitepilot <support@sitepilot.io>

LABEL org.label-schema.vendor="Sitepilot" \
    org.label-schema.name="autopilot" \
    org.label-schema.description="A tool for provisioning and maintaining WordPress sites and servers using Ansible and Laravel." \
    org.label-schema.url="https://github.com/sitepilot/autopilot"

ADD . /var/www/html

RUN chown -R $AUTOPILOT_USER_NAME:$AUTOPILOT_USER_NAME /var/www/html