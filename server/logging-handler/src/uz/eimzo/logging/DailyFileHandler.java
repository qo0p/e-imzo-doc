package uz.eimzo.logging;

import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.UnsupportedEncodingException;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.logging.ErrorManager;
import java.util.logging.Filter;
import java.util.logging.Formatter;
import java.util.logging.Level;
import java.util.logging.LogManager;
import java.util.logging.LogRecord;
import java.util.logging.SimpleFormatter;
import java.util.logging.StreamHandler;

/**
 * java.util.logging handler that writes to one file per day: {directory}/{ddMMyyyy}.log.
 * A new file is opened on the first record after midnight (JVM default time zone).
 *
 * logging.properties:
 *   uz.eimzo.logging.DailyFileHandler.directory   = logs
 *   uz.eimzo.logging.DailyFileHandler.datePattern = ddMMyyyy
 *   uz.eimzo.logging.DailyFileHandler.level       = INFO
 *   uz.eimzo.logging.DailyFileHandler.formatter   = java.util.logging.SimpleFormatter
 *   uz.eimzo.logging.DailyFileHandler.encoding    = UTF-8
 */
public class DailyFileHandler extends StreamHandler {

    private final File directory;
    private final SimpleDateFormat dateFormat;
    private String currentDay;

    public DailyFileHandler() {
        String prefix = getClass().getName();
        directory = new File(prop(prefix + ".directory", "logs"));
        dateFormat = new SimpleDateFormat(prop(prefix + ".datePattern", "ddMMyyyy"));
        setLevel(Level.parse(prop(prefix + ".level", "ALL")));
        setFormatter(newFormatter(prop(prefix + ".formatter", SimpleFormatter.class.getName())));
        try {
            setEncoding(prop(prefix + ".encoding", "UTF-8"));
        } catch (UnsupportedEncodingException e) {
            reportError(null, e, ErrorManager.OPEN_FAILURE);
        }
        directory.mkdirs();
    }

    @Override
    public synchronized void publish(LogRecord record) {
        // StreamHandler.isLoggable() is false until a stream is open,
        // so check level/filter here, then open the day's file.
        if (record == null) {
            return;
        }
        int level = getLevel().intValue();
        if (level == Level.OFF.intValue() || record.getLevel().intValue() < level) {
            return;
        }
        Filter filter = getFilter();
        if (filter != null && !filter.isLoggable(record)) {
            return;
        }
        String day = dateFormat.format(new Date(record.getMillis()));
        if (!day.equals(currentDay)) {
            open(day);
        }
        super.publish(record);
        flush();
    }

    private void open(String day) {
        try {
            directory.mkdirs();
            // setOutputStream flushes and closes the previous day's file
            setOutputStream(new FileOutputStream(new File(directory, day + ".log"), true));
            currentDay = day;
        } catch (IOException e) {
            reportError(null, e, ErrorManager.OPEN_FAILURE);
        }
    }

    private static String prop(String name, String def) {
        String v = LogManager.getLogManager().getProperty(name);
        return v == null || v.trim().isEmpty() ? def : v.trim();
    }

    private static Formatter newFormatter(String className) {
        try {
            return (Formatter) ClassLoader.getSystemClassLoader().loadClass(className).newInstance();
        } catch (Exception e) {
            return new SimpleFormatter();
        }
    }
}
