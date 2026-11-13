#!/bin/sh
# Starts e-imzo-server. Console output (java.util.logging SimpleFormatter,
# see config/logging.properties) goes to `docker logs` and to a daily file
# logs/ddMMyyyy.log (new file after midnight, TZ from the environment).
set -e

LOG_DIR=${LOG_DIR:-/opt/e-imzo-server/logs}
mkdir -p "$LOG_DIR"

FIFO=/tmp/e-imzo-server.log.fifo
rm -f "$FIFO"
mkfifo "$FIFO"

awk -v dir="$LOG_DIR" '{
  f = dir "/" strftime("%d%m%Y") ".log"
  if (f != cur) { if (cur != "") close(cur); cur = f }
  print >> cur; fflush(cur)
  print; fflush()
}' < "$FIFO" &

# exec: java becomes PID 1 and receives SIGTERM from `docker stop`
exec java -Dfile.encoding=UTF-8 \
  -Djava.util.logging.config.file=config/logging.properties \
  -Dproperties.filename=config/config.properties \
  $JAVA_OPTS -jar e-imzo-server.jar > "$FIFO" 2>&1
