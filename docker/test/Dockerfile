FROM ubuntu:20.04
MAINTAINER Sitepilot <support@sitepilot.io>

LABEL org.label-schema.vendor="Sitepilot" \
    org.label-schema.name="autopilot-test" \
    org.label-schema.description="Docker test image which is used to test Autopilot."

ENV LC_ALL C
ENV DEBIAN_FRONTEND noninteractive

# Define packages
ARG DEPS="\
    sudo \
    python3-apt \
    systemd \
    systemd-sysv \
    openssh-server \
    "

# Install packages
RUN set -e \
    && apt-get update \
    && apt-get install -y $DEPS

# Add configuration files
COPY tags /

# Allow running systemd in container
RUN cd /lib/systemd/system/sysinit.target.wants/ \
    && ls | grep -v systemd-tmpfiles-setup | xargs rm -f $1

RUN rm -f /lib/systemd/system/multi-user.target.wants/* \
    /etc/systemd/system/*.wants/* \
    /lib/systemd/system/local-fs.target.wants/* \
    /lib/systemd/system/sockets.target.wants/*udev* \
    /lib/systemd/system/sockets.target.wants/*initctl* \
    /lib/systemd/system/basic.target.wants/* \
    /lib/systemd/system/anaconda.target.wants/* \
    /lib/systemd/system/plymouth* \
    /lib/systemd/system/systemd-update-utmp*

RUN systemctl enable ssh

VOLUME [ "/sys/fs/cgroup" ]

CMD ["/lib/systemd/systemd"]
