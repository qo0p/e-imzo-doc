FROM amazoncorretto:8-alpine3.22-jdk AS logging-handler

COPY /logging-handler/src /src

RUN mkdir /classes \
    && javac -encoding UTF-8 -d /classes $(find /src -name '*.java') \
    && jar cf /daily-file-handler.jar -C /classes .

FROM amazoncorretto:8-alpine3.22-jre

RUN mkdir -p /opt/e-imzo-server/logs

COPY --from=logging-handler /daily-file-handler.jar /usr/java/packages/lib/ext/

COPY /config.properties /opt/e-imzo-server/config/

COPY /logging.properties /opt/e-imzo-server/config/

COPY /e-imzo-server.jar /opt/e-imzo-server/

COPY /lib /opt/e-imzo-server/lib

COPY /keys /opt/e-imzo-server/keys

WORKDIR /opt/e-imzo-server

EXPOSE 8080

EXPOSE 8081

CMD java -Dfile.encoding=UTF-8 -Djava.util.logging.config.file=config/logging.properties -Dproperties.filename=config/config.properties $JAVA_OPTS -jar e-imzo-server.jar
