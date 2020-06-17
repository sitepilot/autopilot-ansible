FROM prom/alertmanager:latest
MAINTAINER Sitepilot <support@sitepilot.io>

LABEL org.label-schema.vendor="Sitepilot" \
    org.label-schema.name="autopilot-alertmanager" \
    org.label-schema.description="Docker image which is used for monitoring Autopilot servers and sites." \
    org.label-schema.url="https://github.com/sitepilot/autopilot"

ENV ALERTMANAGER_SLACK_WEBHOOK=""

EXPOSE 9093

USER root

COPY tags /

RUN chown -R nobody:nogroup /autopilot
RUN chmod +x /autopilot/bin/entrypoint

USER nobody

VOLUME ["/alertmanager"]

ENTRYPOINT ["/autopilot/bin/entrypoint"]

CMD ["/bin/alertmanager", "--config.file=/etc/alertmanager/alertmanager.yml", "--storage.path=/alertmanager"]