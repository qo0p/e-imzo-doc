FROM amazoncorretto:8-alpine3.22-jre

# tzdata: local time in log lines and in the daily file name
RUN apk add --no-cache tzdata

RUN mkdir -p /opt/e-imzo-server

COPY /config.properties /opt/e-imzo-server/config/
COPY /logging.properties /opt/e-imzo-server/config/
COPY /e-imzo-server.jar /opt/e-imzo-server/
COPY /lib /opt/e-imzo-server/lib
COPY /keys /opt/e-imzo-server/keys
COPY /entrypoint.sh /opt/e-imzo-server/entrypoint.sh

WORKDIR /opt/e-imzo-server

EXPOSE 8080
EXPOSE 8081

CMD ["/opt/e-imzo-server/entrypoint.sh"]
